<?php

namespace App\Http\Controllers\API\Instructor;

use App\Exports\QuizExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\API\Lessons\StoreQuestionMultipleRequest;
use App\Http\Requests\API\Lessons\StoreQuestionRequest;
use App\Http\Requests\API\Lessons\StoreQuizLessonRequest;
use App\Http\Requests\API\Lessons\UpdateOrderQuestionRequest;
use App\Http\Requests\API\Lessons\UpdateQuestionRequest;
use App\Http\Requests\API\Lessons\UpdateQuizLessonRequest;
use App\Imports\QuizImport;
use App\Models\Chapter;
use App\Models\Lesson;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use App\Traits\UploadToLocalTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class QuizController extends Controller
{
    use LoggableTrait, ApiResponseTrait, UploadToCloudinaryTrait, UploadToLocalTrait;

    const FOLDER_QUIZ = 'quiz';

    public function storeLessonQuiz(StoreQuizLessonRequest $request, string $chapterId)
    {
        try {
            DB::beginTransaction();

            $data = $request->all();

            $data['slug'] = !empty($data['title'])
                ? Str::slug($data['title']) . '-' . Str::uuid()
                : Str::uuid();

            $chapter = Chapter::query()->where('id', $chapterId)->first();

            if (!$chapter) {
                return $this->respondNotFound('Không tìm thấy chương học');
            }

            if ($chapter->course->user_id !== auth()->id()) {
                return $this->respondForbidden('Bạn không có quyền thực hiện thao tác này');
            }

            $course = $chapter->course;
            $data['order'] = $chapter->lessons->max('order') + 1;
            $data['is_supplement'] = (int) $course->modification_request === 1;

            $quiz = Quiz::query()->create([
                'title' => $data['title'],
            ]);

            $lesson = Lesson::query()->create([
                'chapter_id' => $chapter->id,
                'title' => $data['title'],
                'slug' => $data['slug'],
                'type' => 'quiz',
                'lessonable_type' => Quiz::class,
                'lessonable_id' => $quiz->id,
                'order' => $data['order'],
                'content' => $data['content'] ?? null,
                'is_free_preview' => $data['is_free_preview'] ?? false,
                'is_supplement' => $data['is_supplement'],
            ]);

            DB::commit();

            return $this->respondCreated('Tạo bài ôn tập thành công', $lesson->load('lessonable'));
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);
            return $this->respondServerError('Không thể tạo câu hỏi, vui lòng thử lại');
        }
    }

    public function updateContentQuiz(UpdateQuizLessonRequest $request, string $quizId)
    {
        try {
            DB::beginTransaction();

            $quiz = Quiz::query()
                ->with('lessons')
                ->find($quizId);

            if (!$quiz) {
                return $this->respondNotFound('Không tìm thấy bài trắc nghiệm');
            }

            $lesson = $quiz->lessons
                ->where('lessonable_type', Quiz::class)
                ->where('lessonable_id', $quiz->id)
                ->first();

            if ($lesson->chapter->course->user_id !== auth()->id()) {
                return $this->respondForbidden('Bạn không có quyền thực hiện thao tác này');
            }

            $data = $request->validated();

            $quiz->update([
                'title' => $data['title'] ?? $quiz->title,
            ]);

            $lesson->update([
                'title' => $data['title'] ?? $lesson->title,
                'slug' => isset($data['title']) ? Str::slug($data['title']) . '-' . Str::uuid() : $lesson->slug,
                'content' => $data['content'] ?? $lesson->content,
            ]);

            DB::commit();

            return $this->respondOk('Cập nhật nội dung bài trắc nghiệm thành công', $quiz->load('lessons'));
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function storeQuestionMultiple(StoreQuestionMultipleRequest $request, string $quizId)
    {
        try {
            DB::beginTransaction();

            $data = $request->all();

            $quiz = Quiz::query()->find($quizId);

            if (!$quiz) {
                return $this->respondNotFound('Không tìm thấy bài học');
            }

            if (isset($data['questions']) && is_array($data['questions'])) {
                foreach ($data['questions'] as $question) {
                    $questionModel = Question::updateOrCreate(
                        [
                            'quiz_id' => $quiz->id,
                            'question' => $question['question'],
                        ],
                        [
                            'image' => $question['image'] ?? null,
                            'answer_type' => $question['answer_type'],
                            'description' => $question['description'] ?? null,
                        ]
                    );

                    $questionModel->answers()->delete();

                    if (isset($question['options']) && is_array($question['options'])) {
                        foreach ($question['options'] as $option) {
                            $questionModel->answers()->create([
                                'answer' => $option['answer'],
                                'is_correct' => $option['is_correct'] ?? false,
                            ]);
                        }
                    }
                }
            }
            DB::commit();

            return $this->respondCreated('Tạo bài trắc nghiệm thành công', $quiz);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);

            return $this->respondServerError('Không thể tạo câu hỏi, vui lòng thử lại');
        }
    }

    public function storeQuestionSingle(StoreQuestionRequest $request, string $quizId)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            $quiz = Quiz::query()->find($quizId);

            if (!$quiz) {
                return $this->respondNotFound('Không tìm thấy bài học');
            }

            $questionCount = Question::query()->where('quiz_id', $quiz->id)->count();
            if ($questionCount >= 50) {
                return $this->respondError('Bài trắc nghiệm đã đạt số lượng câu hỏi tối đa (50)');
            }

            if ($request->hasFile('image')) {
                $data['image'] = $this->uploadToLocal($data['image'], self::FOLDER_QUIZ);
            }

            $lastOrder = Question::query()->where('quiz_id', $quiz->id)->max('order') ?? 0;

            $order = $lastOrder + 1;

            $question = Question::query()->updateOrCreate(
                [
                    'quiz_id' => $quiz->id,
                    'question' => $data['question'],
                ],
                [
                    'image' => $data['image'] ?? null,
                    'answer_type' => $data['answer_type'],
                    'description' => $data['description'] ?? null,
                    'order' => $order,
                ]
            );

            if (isset($data['options']) && is_array($data['options'])) {
                foreach ($data['options'] as $option) {
                    $question->answers()->create([
                        'answer' => $option['answer'],
                        'is_correct' => $option['is_correct'] ?? false,
                    ]);
                }
            }

            DB::commit();

            return $this->respondCreated('Tạo bài trắc nghiệm thành công', $quiz->load('questions'));
        } catch (\Exception $e) {
            $this->logError($e);

            DB::rollBack();

            return $this->respondServerError('Không thể tạo câu hỏi, vui lòng thử lại');
        }
    }

    public function showQuiz(string $quizId)
    {
        try {
            $quiz = Quiz::query()
                ->with([
                    'lessons:lessonable_id,lessonable_type,content',
                    'questions' => function ($query) {
                        $query->select('id', 'quiz_id', 'question', 'image', 'answer_type', 'description')
                            ->orderBy('order', 'asc')
                            ->with(['answers:id,question_id,answer,is_correct']);
                    }
                ])
                ->select('id', 'title', 'created_at', 'updated_at')
                ->find($quizId);

            if (!$quiz) {
                return $this->respondNotFound('Không tìm thấy bài trắc nghiệm');
            }

            $formattedData = [
                'id' => $quiz->id,
                'title' => $quiz->title,
                'content' => $quiz->lessons->content,
                'questions' => $quiz->questions->map(function ($question) {
                    return [
                        'id' => $question->id,
                        'quiz_id' => $question->quiz_id,
                        'question' => $question->question,
                        'answer_type' => $question->answer_type,
                        'image' => $question->image,
                        'description' => $question->description,
                        'answers' => $question->answers->map(function ($answer) {
                            return [
                                'answer' => $answer->answer,
                                'is_correct' => $answer->is_correct
                            ];
                        })
                    ];
                })
            ];

            return $this->respondSuccess('Lấy thông tin bài trắc nghiệm thành công', $formattedData);
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function showQuestion(string $questionId)
    {
        try {
            $question = Question::query()->with('answers')->find($questionId);

            if (!$question) {
                return $this->respondNotFound('Không tìm thấy câu hỏi');
            }

            return $this->respondSuccess('Thông tin câu hỏi: ' . $questionId, $question);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function updateQuestion(UpdateQuestionRequest $request, $questionId)
    {
        try {
            $data = $request->validated();

            $question = Question::query()->find($questionId);

            if (!$question) {
                return $this->respondNotFound('Không tìm thấy câu hỏi');
            }

            if ($request->hasFile('image')) {
                if ($question->image) {
                    $this->deleteFromLocal($question->image, self::FOLDER_QUIZ);
                }
                $data['image'] = $this->uploadToLocal($request->file('image'), self::FOLDER_QUIZ);
            }

            $question->update([
                'question' => $data['question'] ?? $question->question,
                'image' => $data['image'] ?? $question->image,
                'answer_type' => $data['answer_type'] ?? $question->answer_type,
                'description' => $data['description'] ?? $question->description,
            ]);

            if (isset($data['options']) && is_array($data['options'])) {
                $question->answers()->delete();
                foreach ($data['options'] as $option) {
                    $question->answers()->create([
                        'answer' => $option['answer'] ?? null,
                        'is_correct' => $option['is_correct'] ?? false,
                    ]);
                }
            }

            return $this->respondOk('Cập nhật câu hỏi thành công', $question->load('answers'));
        } catch (\Exception $e) {

            $this->logError($e, $request->all());
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function deleteQuestion($questionId)
    {
        try {
            $question = Question::query()->find($questionId);

            if (!$question) {
                return $this->respondNotFound('Không tìm thấy câu hỏi');
            }

            if ($question->image && Storage::exists($question->image)) {
                $this->deleteFromLocal($question->image, self::FOLDER_QUIZ);
            }

            $question->answers()->delete();
            $question->delete();

            return $this->respondOk('Xóa câu hỏi thành công');
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function updateOrderQuestion(UpdateOrderQuestionRequest $request, string $quizId)
    {
        try {
            $data = $request->validated();

            $quiz = Quiz::query()->find($quizId);

            if (!$quiz) {
                return $this->respondNotFound('Không tìm thấy bài học');
            }

            DB::beginTransaction();

            foreach ($data as $item) {
                $question = $quiz->questions()->find($item['id']);

                if (!$question) {
                    return $this->respondNotFound('Không tìm thấy câu hỏi');
                }

                $question->update([
                    'order' => $item['order']
                ]);
            }

            DB::commit();

            return $this->respondOk(
                'Cập nhật thứ tự câu hỏi thành công',
                $quiz->fresh()->load('questions')
            );
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function importQuiz(Request $request, string $quizId)
    {
        try {
            $request->validate([
                'file' => 'required|mimes:xlsx,xls,csv',
                'type' => 'required|in:overwrite,add'
            ]);

            if ($request->input('type') === 'add') {
                $currentCount = Question::query()->where('quiz_id', $quizId)->count();
                if ($currentCount >= 50) {
                    return $this->respondError('Bài trắc nghiệm đã đạt số lượng câu hỏi tối đa (50)');
                }
            }

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $image->storeAs('imports', $image->getClientOriginalName());
                }
            }

            ini_set('max_execution_time', 300);

            $importer = new QuizImport($quizId, $request->input('type'));

            Excel::import($importer, $request->file('file'));
            $errors = $importer->getErrors();

            Storage::deleteDirectory('imports');

            if (!empty($errors)) {
                return $this->respondServerError($errors[0]);
            }

            return $this->respondOk('Import câu hỏi thành công');
        } catch (\Exception $e) {
            $this->logError($e);
            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau: ' . $e->getMessage());
        }
    }

    public function exportQuiz(Request $request, string $quizId)
    {
        try {
            $quiz = Quiz::query()
                ->with(['questions.answers'])
                ->find($quizId);

            if (!$quiz) {
                return $this->respondNotFound('Không tìm thấy bài học');
            }

            $quizData = [
                'questions' => $quiz->questions->map(function ($question) {
                    return [
                        'question' => $question->question,
                        'image' => $question->image,
                        'description' => $question->description,
                        'answer_type' => $question->answer_type,
                        'answers' => $question->answers->map(function ($answer) {
                            return [
                                'answer' => $answer->answer,
                                'is_correct' => $answer->is_correct
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ];

            return Excel::download(new QuizExport($quizData), 'quiz-' . $quiz->title . '.xlsx');
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError();
        }
    }

    public function downloadQuizForm()
    {
        try {
            $filePath = public_path('storage/csv/quiz_import_template.xlsx');

            if (!file_exists($filePath)) {
                return $this->respondNotFound('Không tìm thấy file mẫu');
            }

            return response()->download($filePath, 'quiz_import_template.xlsx');
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }
}
