<?php

namespace App\Http\Controllers\API\Instructor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Chapter;
use App\Models\Lesson;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AICourseController extends Controller
{
    use LoggableTrait, ApiResponseTrait;
    protected $geminiEndpoint = 'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-pro:generateContent';
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('GOOGLE_STUDIO_KEY');
    }

    public function generateContent(Request $request)
    {
        try {
            $message = $request->input('message');
            $courseId = $request->input('courseId');
            $history = $request->input('history', []);

            // Validate request
            if (!$message) {
                return response()->json(['error' => 'Thiếu nội dung tin nhắn'], 400);
            }

            // Get course context if courseId is provided
            $courseContext = '';
            if ($courseId) {
                $course = Course::with(['chapters', 'chapters.lessons'])->find($courseId);
                if ($course) {
                    $courseContext = $this->buildCourseContext($course);
                }
            }

            // Build conversation history for context
            $conversationHistory = $this->buildConversationHistory($history);

            // Prepare system prompt
            $systemPrompt = $this->getSystemPrompt($courseContext);

            // Call Gemini API
            $response = $this->callGeminiAPI($systemPrompt, $conversationHistory, $message);

            return $this->respondOk('Lay du lieu thanh công', $response);
        } catch (\Exception $e) {
            Log::error('AI Course Planning API Error: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi xử lý yêu cầu'], 500);
        }
    }

    /**
     * Build course context from course data
     */
    protected function buildCourseContext(Course $course)
    {
        $context = "Thông tin khóa học:\n";
        $context .= "Tên khóa học: {$course->title}\n";
        $context .= "Mô tả: {$course->description}\n\n";

        if ($course->chapters->count() > 0) {
            $context .= "Cấu trúc khóa học hiện tại:\n";

            foreach ($course->chapters as $chapter) {
                $context .= "- Chương {$chapter->order}: {$chapter->title}\n";

                if ($chapter->lessons->count() > 0) {
                    foreach ($chapter->lessons as $lesson) {
                        $lessonType = $this->getLessonType($lesson);
                        $context .= "  + Bài {$lesson->order}: {$lesson->title} (Loại: {$lessonType})\n";
                    }
                }
            }
        }

        return $context;
    }

    /**
     * Get readable lesson type
     */
    protected function getLessonType($lesson)
    {
        $lessonableType = $lesson->lessonable_type ?? '';

        return match ($lessonableType) {
            'App\\Models\\Document' => 'Tài liệu',
            'App\\Models\\Video' => 'Video',
            'App\\Models\\Quiz' => 'Bài kiểm tra',
            'App\\Models\\Coding' => 'Bài tập code',
            default => 'Không xác định'
        };
    }

    /**
     * Build conversation history for context
     */
    protected function buildConversationHistory($history)
    {
        if (empty($history)) {
            return [];
        }

        $formattedHistory = [];
        foreach ($history as $message) {
            if ($message['role'] === 'user') {
                $formattedHistory[] = [
                    'role' => 'user',
                    'parts' => [['text' => $message['content']]]
                ];
            } else if ($message['role'] === 'assistant') {
                $formattedHistory[] = [
                    'role' => 'model',
                    'parts' => [['text' => $message['content']]]
                ];
            }
        }

        return $formattedHistory;
    }

    /**
     * Get system prompt based on course context
     */
    protected function getSystemPrompt($courseContext = '')
    {
        $prompt = <<<EOT
Bạn là trợ lý AI chuyên về lập kế hoạch và thiết kế khóa học. Nhiệm vụ của bạn là giúp giảng viên phát triển chương trình giảng dạy hiệu quả, cấu trúc bài học logic và tạo nội dung phù hợp với mục tiêu học tập.

Bạn có thể truy cập và hiểu về các loại nội dung bài học sau:
1. Tài liệu: Nội dung lý thuyết, bài đọc, hướng dẫn bằng văn bản
2. Video: Bài giảng dạng video, demo thực hành, hướng dẫn trực quan
3. Quiz: Các câu hỏi trắc nghiệm và bài kiểm tra đánh giá
4. Coding: Bài tập lập trình thực hành

Khi được hỏi về kế hoạch khóa học, hãy đưa ra các gợi ý cụ thể về:
- Cấu trúc chương và bài học logic, mạch lạc
- Loại nội dung (tài liệu, video, quiz, coding) phù hợp cho từng bài học
- Ý tưởng nội dung cho từng bài
- Lộ trình học tập hiệu quả

Hãy giữ giọng điệu chuyên nghiệp, đồng thời thân thiện và hỗ trợ.

EOT;

        // Add course context if available
        if (!empty($courseContext)) {
            $prompt .= "\n\nBối cảnh khóa học hiện tại:\n" . $courseContext;
        }

        return $prompt;
    }

    /**
     * Call Gemini API with the constructed prompt
     */
    protected function callGeminiAPI($systemPrompt, $history, $userMessage)
    {
        // Construct the full payload
        $contents = [
            [
                'role' => 'user',
                'parts' => [['text' => $systemPrompt]]
            ],
            [
                'role' => 'model',
                'parts' => [['text' => 'Tôi hiểu nhiệm vụ của mình. Tôi sẽ giúp bạn lập kế hoạch và thiết kế khóa học một cách chuyên nghiệp.']]
            ]
        ];

        // Add conversation history
        if (!empty($history)) {
            $contents = array_merge($contents, $history);
        }

        // Add current user message
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $userMessage]]
        ];

        // Prepare request
        $url = $this->geminiEndpoint . '?key=' . $this->apiKey;
        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 8192,
            ]
        ];

        // Make request to Gemini API
        $response = Http::post($url, $payload);

        if (!$response->successful()) {
            Log::error('Gemini API Error: ' . $response->body());
            throw new \Exception('Lỗi khi gọi API Gemini: ' . $response->status());
        }

        $data = $response->json();

        // Extract text from response
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return $data['candidates'][0]['content']['parts'][0]['text'];
        }

        throw new \Exception('Không thể trích xuất phản hồi từ Gemini API');
    }

    /**
     * Create course structure based on AI recommendation
     */
    public function applyRecommendation(Request $request)
    {
        try {
            $courseId = $request->input('courseId');
            $recommendation = $request->input('recommendation');
            $type = $request->input('type', 'preview'); // 'preview' hoặc 'apply'
            $structure = $request->input('structure'); // Nhận cấu trúc đã chỉnh sửa từ frontend

            // Validate
            if (!$courseId) {
                return response()->json(['error' => 'Thiếu ID khóa học'], 400);
            }

            $course = Course::find($courseId);
            if (!$course) {
                return response()->json(['error' => 'Không tìm thấy khóa học'], 404);
            }

            // Nếu không có cấu trúc được chỉnh sửa, phân tích từ recommendation
            if (!$structure && $recommendation) {
                $structure = $this->parseRecommendation($recommendation);
            } else if (!$structure) {
                return response()->json(['error' => 'Không có cấu trúc khóa học'], 400);
            }

            if ($type === 'preview') {
                return response()->json([
                    'message' => 'Cấu trúc khóa học được đề xuất',
                    'structure' => $structure
                ]);
            } else {
                // Áp dụng cấu trúc cho khóa học
                $this->applyStructureToCourse($course, $structure);

                return response()->json([
                    'message' => 'Đã áp dụng cấu trúc khóa học thành công',
                    'courseId' => $course->id
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Apply AI Recommendation Error: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi áp dụng đề xuất: ' . $e->getMessage()], 500);
        }
    }
    /**
     * Parse AI recommendation to extract course structure
     * This is a simplified parser and would need to be enhanced for production
     */
    protected function parseRecommendation($recommendation)
    {
        // This is a simplistic parser - would need more sophisticated parsing in production
        $structure = [
            'chapters' => []
        ];

        // Simple regex to extract chapters and lessons
        preg_match_all('/(?:Chương|Chapter)\s+(\d+|[IVX]+):\s+(.+?)(?=(?:Chương|Chapter)|$)/s', $recommendation, $chapterMatches, PREG_SET_ORDER);

        foreach ($chapterMatches as $chapterMatch) {
            $chapterNumber = $chapterMatch[1];
            $chapterContent = $chapterMatch[2];

            $chapterTitle = '';
            if (preg_match('/^([^\n]+)/', trim($chapterContent), $titleMatch)) {
                $chapterTitle = trim($titleMatch[0]);
            }

            $lessons = [];
            preg_match_all('/(?:Bài|Lesson)\s+(\d+|[a-z]):\s+(.+?)(?=(?:Bài|Lesson)|$)/s', $chapterContent, $lessonMatches, PREG_SET_ORDER);

            foreach ($lessonMatches as $lessonMatch) {
                $lessonNumber = $lessonMatch[1];
                $lessonContent = $lessonMatch[2];

                $lessonTitle = '';
                $lessonType = 'document'; // Default

                if (preg_match('/^([^\n]+)/', trim($lessonContent), $titleMatch)) {
                    $lessonTitle = trim($titleMatch[0]);
                }

                // Try to determine lesson type
                if (stripos($lessonContent, 'video') !== false) {
                    $lessonType = 'video';
                } elseif (stripos($lessonContent, 'quiz') !== false || stripos($lessonContent, 'trắc nghiệm') !== false) {
                    $lessonType = 'quiz';
                } elseif (stripos($lessonContent, 'coding') !== false || stripos($lessonContent, 'lập trình') !== false) {
                    $lessonType = 'coding';
                }

                $lessons[] = [
                    'order' => intval($lessonNumber),
                    'title' => $lessonTitle,
                    'type' => $lessonType,
                    'description' => trim($lessonContent)
                ];
            }

            $structure['chapters'][] = [
                'order' => intval($chapterNumber),
                'title' => $chapterTitle,
                'lessons' => $lessons
            ];
        }

        return $structure;
    }

    /**
     * Apply the parsed structure to the course
     */
    protected function applyStructureToCourse($course, $structure)
    {
        \DB::transaction(function () use ($course, $structure) {
            // Get existing chapters to determine what to keep/update/delete
            $existingChapters = $course->chapters()->pluck('id', 'order')->toArray();

            foreach ($structure['chapters'] as $chapterData) {
                // Check if chapter with this order exists
                if (isset($existingChapters[$chapterData['order']])) {
                    // Update existing chapter
                    $chapterId = $existingChapters[$chapterData['order']];
                    $chapter = Chapter::find($chapterId);
                    $chapter->title = $chapterData['title'];
                    $chapter->save();
                } else {
                    // Create new chapter
                    $chapter = new Chapter([
                        'title' => $chapterData['title'],
                        'order' => $chapterData['order'],
                        'course_id' => $course->id
                    ]);
                    $chapter->save();
                }

                // Process lessons for this chapter
                $this->processLessons($chapter, $chapterData['lessons']);
            }
        });
    }

    /**
     * Process lessons for a chapter
     */
    protected function processLessons($chapter, $lessonsData)
    {
        // Tạo một transaction để đảm bảo tính toàn vẹn dữ liệu
        \DB::transaction(function () use ($chapter, $lessonsData) {
            // Lấy danh sách ID bài học hiện có để theo dõi những bài học cần giữ lại
            $keepLessonIds = [];

            // Lưu lại các thứ tự đã xử lý để tránh trùng lặp
            $processedOrders = [];

            foreach ($lessonsData as $lessonData) {
                // Xử lý trường hợp trùng thứ tự
                $order = $lessonData['order'];
                if (in_array($order, $processedOrders)) {
                    // Tìm thứ tự mới nếu bị trùng
                    $order = max($processedOrders) + 1;
                }
                $processedOrders[] = $order;

                // Map loại bài học
                $lessonableType = $this->mapLessonType($lessonData['type']);

                // Tìm bài học hiện có với thứ tự này
                $lesson = $chapter->lessons()->where('order', $order)->first();

                if ($lesson) {
                    // Cập nhật bài học hiện có
                    $lesson->title = $lessonData['title'];

                    // Cập nhật hoặc thay đổi lessonable nếu cần
                    if ($lesson->lessonable_type !== $lessonableType) {
                        // Tạo lessonable mới
                        $lessonable = $this->createLessonable($lessonableType, $lessonData);

                        // Cập nhật liên kết lesson
                        $lesson->lessonable()->dissociate();
                        $lesson->lessonable()->associate($lessonable);
                    } else {
                        // Cập nhật lessonable hiện có
                        $this->updateLessonable($lesson->lessonable, $lessonData);
                    }

                    $lesson->save();
                    $keepLessonIds[] = $lesson->id;
                } else {
                    // Tạo lessonable mới
                    $lessonable = $this->createLessonable($lessonableType, $lessonData);

                    // Tạo bài học mới
                    $lesson = new Lesson([
                        'title' => $lessonData['title'],
                        'order' => $order,
                        'description' => $lessonData['description'] ?? null,
                    ]);

                    // Liên kết bài học với chương và lessonable
                    $lesson->chapter()->associate($chapter);
                    $lesson->lessonable()->associate($lessonable);
                    $lesson->save();
                    $keepLessonIds[] = $lesson->id;
                }
            }

            // Xóa các bài học không có trong cấu trúc mới
            if (!empty($keepLessonIds)) {
                $chapter->lessons()->whereNotIn('id', $keepLessonIds)->delete();
            }
        });
    }
    /**
     * Map lesson type string to model class
     */
    protected function mapLessonType($type)
    {
        return match ($type) {
            'document' => 'App\\Models\\Document',
            'video' => 'App\\Models\\Video',
            'quiz' => 'App\\Models\\Quiz',
            'coding' => 'App\\Models\\Coding',
            default => 'App\\Models\\Document', // Default to document
        };
    }

    /**
     * Create new lessonable entity based on type
     */
    protected function createLessonable($type, $data)
    {
        switch ($type) {
            case 'App\\Models\\Document':
                $lessonable = new \App\Models\Document([
                    'title' => $data['title'],
                    'content' => $data['description'] ?? 'Nội dung tài liệu sẽ được thêm sau.',
                ]);
                break;

            case 'App\\Models\\Video':
                $lessonable = new \App\Models\Video([
                    'title' => $data['title'],
                    'description' => $data['description'] ?? 'Mô tả video sẽ được thêm sau.',
                    'url' => null,
                    'duration' => 0,
                ]);
                break;

            case 'App\\Models\\Quiz':
                $lessonable = new \App\Models\Quiz([
                    'title' => $data['title'],
                    'description' => $data['description'] ?? 'Mô tả bài kiểm tra sẽ được thêm sau.',
                    'time_limit' => 30, // Default 30 minutes
                    'pass_score' => 70, // Default 70%
                ]);
                break;

            case 'App\\Models\\Coding':
                $lessonable = new \App\Models\Coding([
                    'title' => $data['title'],
                    'description' => $data['description'] ?? 'Mô tả bài tập code sẽ được thêm sau.',
                    'language' => 'javascript', // Default language
                    'initial_code' => '// Code starter',
                    'test_code' => '// Test code here',
                ]);
                break;

            default:
                throw new \Exception('Loại bài học không được hỗ trợ: ' . $type);
        }

        $lessonable->save();
        return $lessonable;
    }

    /**
     * Update existing lessonable entity
     */
    protected function updateLessonable($lessonable, $data)
    {
        $lessonable->title = $data['title'];

        if (isset($data['description'])) {
            switch (get_class($lessonable)) {
                case 'App\\Models\\Document':
                    $lessonable->content = $data['description'];
                    break;

                default:
                    $lessonable->description = $data['description'];
                    break;
            }
        }

        $lessonable->save();
        return $lessonable;
    }

    /**
     * Generate AI lesson content based on lesson details
     * This is an optional enhancement that can be implemented to generate
     * more detailed content for each lesson type
     */
    public function generateLessonContent(Request $request)
    {
        try {
            $lessonId = $request->input('lessonId');
            $lessonType = $request->input('lessonType');

            $lesson = Lesson::with('lessonable')->find($lessonId);
            if (!$lesson) {
                return response()->json(['error' => 'Không tìm thấy bài học'], 404);
            }

            // Get the course context for better content generation
            $chapter = $lesson->chapter;
            $course = $chapter->course;

            // Build context for AI
            $context = "Khóa học: {$course->title}\n";
            $context .= "Chương {$chapter->order}: {$chapter->title}\n";
            $context .= "Bài học {$lesson->order}: {$lesson->title}\n";
            $context .= "Loại bài học: " . $this->getReadableLessonType($lesson->lessonable_type) . "\n";

            // Get appropriate prompt based on lesson type
            $prompt = $this->getLessonContentPrompt($lessonType, $lesson);

            // Generate content via Gemini API
            $generatedContent = $this->generateContentForLesson($context, $prompt);

            // Return the generated content without applying
            return response()->json([
                'message' => 'Đã tạo nội dung bài học',
                'content' => $generatedContent
            ]);
        } catch (\Exception $e) {
            Log::error('Generate Lesson Content Error: ' . $e->getMessage());
            return response()->json(['error' => 'Đã xảy ra lỗi khi tạo nội dung bài học'], 500);
        }
    }

    /**
     * Get appropriate prompt for generating lesson content based on type
     */
    protected function getLessonContentPrompt($lessonType, $lesson)
    {
        switch ($lessonType) {
            case 'document':
                return "Hãy tạo một tài liệu học tập đầy đủ cho bài học '{$lesson->title}'. Tài liệu nên bao gồm: giới thiệu, nội dung chính với các điểm mấu chốt, ví dụ minh họa, và tóm tắt. Sử dụng định dạng Markdown.";

            case 'video':
                return "Hãy tạo một kịch bản chi tiết cho video bài giảng '{$lesson->title}'. Kịch bản nên bao gồm: lời giới thiệu, các điểm cần trình bày, ví dụ trực quan, và kết luận. Thêm ghi chú về phần hiển thị trực quan hoặc demo nếu cần.";

            case 'quiz':
                return "Hãy tạo một bộ câu hỏi trắc nghiệm cho bài học '{$lesson->title}'. Bao gồm 5-10 câu hỏi đa lựa chọn, mỗi câu có 4 phương án trả lời, đánh dấu đáp án đúng và giải thích ngắn gọn.";

            case 'coding':
                return "Hãy tạo một bài tập lập trình cho bài học '{$lesson->title}'. Bao gồm: yêu cầu bài tập, mô tả đầu vào/đầu ra, ví dụ minh họa, mã khởi tạo, và các test case. Giả định người học có kiến thức cơ bản về lập trình.";

            default:
                return "Hãy tạo nội dung chi tiết cho bài học '{$lesson->title}'. Bao gồm các điểm chính, ví dụ minh họa, và các hoạt động thực hành nếu phù hợp.";
        }
    }

    /**
     * Get readable lesson type
     */
    protected function getReadableLessonType($lessonableType)
    {
        return match ($lessonableType) {
            'App\\Models\\Document' => 'Tài liệu',
            'App\\Models\\Video' => 'Video',
            'App\\Models\\Quiz' => 'Bài kiểm tra',
            'App\\Models\\Coding' => 'Bài tập code',
            default => 'Không xác định'
        };
    }

    /**
     * Generate content for lesson via Gemini API
     */
    protected function generateContentForLesson($context, $prompt)
    {
        // Construct the full prompt
        $fullPrompt = "Bối cảnh:\n{$context}\n\nYêu cầu:\n{$prompt}";

        // Call Gemini API with single prompt (simplified for this context)
        $url = $this->geminiEndpoint . '?key=' . $this->apiKey;
        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [['text' => $fullPrompt]]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.7,
                'topK' => 40,
                'topP' => 0.95,
                'maxOutputTokens' => 8192,
            ]
        ];

        // Make request to Gemini API
        $response = Http::post($url, $payload);

        if (!$response->successful()) {
            Log::error('Gemini API Error: ' . $response->body());
            throw new \Exception('Lỗi khi gọi API Gemini: ' . $response->status());
        }

        $data = $response->json();

        // Extract text from response
        if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
            return $data['candidates'][0]['content']['parts'][0]['text'];
        }

        throw new \Exception('Không thể trích xuất phản hồi từ Gemini API');
    }
}
