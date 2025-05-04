<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Gifts\StoreGiftRequest;
use App\Http\Requests\Admin\StoreSpinRequest;
use App\Models\Coupon;
use App\Models\Gift;
use App\Models\Spin;
use App\Models\SpinConfig;
use App\Models\SpinHistory;
use App\Models\SpinSetting;
use App\Models\SpinType;
use App\Models\User;
use App\Traits\LoggableTrait;
use App\Traits\UploadToCloudinaryTrait;
use App\Traits\UploadToLocalTrait;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class SpinController extends Controller
{
    use LoggableTrait, UploadToLocalTrait;
    const FOLDER = 'gifts';
    public function index(Request $request)
    {
        // Lấy cấu hình vòng quay
        $spinSetting = SpinSetting::firstOrCreate(); // Lấy hoặc tạo bản ghi đầu tiên
        // Cấu hình tỷ lệ trúng
        $spinConfigs = SpinConfig::all();
        $spinTypes = SpinType::all();
        // Kiểm tra số lượng phần thưởng theo loại (no_reward, coupon, spin)
        $requiredTypes = ['no_reward', 'coupon', 'spin'];
        $spinConfigTypes = $spinConfigs->pluck('type')->unique()->toArray();
        $missingTypes = array_diff($requiredTypes, $spinConfigTypes);

        $typeNames = [
            'no_reward' => 'Chúc bạn may mắn lần sau',
            'coupon' => 'Mã giảm giá',
            'spin' => 'Tặng thêm 1 lượt quay',
        ];
        $missingTypeNames = array_map(function ($type) use ($typeNames) {
            return $typeNames[$type] ?? ucfirst($type); // Fallback nếu type không có trong ánh xạ
        }, $missingTypes);
        $hasEnoughSpinConfigTypes = count($missingTypes) === 0;

        // Lấy danh sách quà hiện vật được chọn
        $selectedGifts = Gift::where('is_selected', 1)->get();
        $requiredGifts = 2; // Số lượng quà hiện vật yêu cầu
        $currentSelectedGiftsCount = $selectedGifts->count();
        $hasEnoughGifts = $currentSelectedGiftsCount === $requiredGifts;

        // Tính tổng xác suất và kiểm tra xem có đạt 100% không
        $gifts = Gift::all()->where('is_selected', 1);
        $totalProbability = $spinConfigs->sum('probability') + $gifts->sum('probability');
        $isProbabilityValid = abs($totalProbability - 100) < 0.01; // Cho phép sai số nhỏ do float

        // Kiểm tra tất cả các điều kiện để hiển thị cảnh báo
        $isConfigValid = $hasEnoughSpinConfigTypes && $hasEnoughGifts && $isProbabilityValid;

        $spinStatus = $spinSetting->status;
        $showConfigWarning = !$isConfigValid;
        $gifts = Gift::all()->where('is_selected', 1);
        $giftsAll = Gift::all();
        $totalProbability = $spinConfigs->sum('probability') + $gifts->sum('probability');
        // Lấy danh sách quà hiện vật và mã giảm giá chưa được chọn để hiển thị trong modal
        $availableGifts = Gift::where('is_selected', 0)->get();
        // Tổng số lượt quay theo ngày, tháng, năm
        $spinStatsDay = SpinHistory::select(DB::raw('DATE(spun_at) as date'), DB::raw('COUNT(*) as spins'))
            ->groupBy('date')
            ->orderBy('date', 'desc')
            ->get();

        $spinStatsMonth = SpinHistory::select(
            DB::raw('YEAR(spun_at) as year'),
            DB::raw('MONTH(spun_at) as month'),
            DB::raw('COUNT(*) as spins')
        )
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->get();

        $spinStatsYear = SpinHistory::select(DB::raw('YEAR(spun_at) as year'), DB::raw('COUNT(*) as spins'))
            ->groupBy('year')
            ->orderBy('year', 'desc')
            ->get();

        // Danh sách người dùng trúng quà hiện vật
        $giftWinners = SpinHistory::where('reward_type', 'gift')
            ->with('user') // Giả sử có quan hệ user trong SpinHistory
            ->orderBy('spun_at', 'desc')
            ->get();
        // Thống kê số lượng quà đã trúng theo TÊN phần thưởng
        // Lấy tất cả phần thưởng đã trúng
        $winners = SpinHistory::select('reward_name', 'reward_type')
            ->selectRaw('COUNT(*) as total')
            ->groupBy('reward_name', 'reward_type')
            ->get();

        // Gộp dữ liệu theo nhóm
        $groupedWinners = [
            'Mã giảm giá' => 0,
            'Quà hiện vật' => 0,
        ];

        foreach ($winners as $winner) {
            $rewardName = $winner->reward_name;
            $rewardType = $winner->reward_type;
            $count = $winner->total;

            // Phân loại mã giảm giá
            if ($rewardType === 'coupon' || stripos($rewardName, 'Mã giảm giá') !== false) {
                $groupedWinners['Mã giảm giá'] += $count;
            }
            // Phân loại quà hiện vật
            elseif ($rewardType === 'gift') {
                $groupedWinners['Quà hiện vật'] += $count;
            }
            // Các phần thưởng khác (giữ nguyên)
            else {
                $groupedWinners[$rewardName] = $count;
            }
        }

        $totalSpins = SpinHistory::count(); // Tổng số lượt quay
        $totalWinners = SpinHistory::whereNotIn('reward_type', ['no_reward', 'spin'])->count();
        $winRate = $totalSpins > 0 ? ($totalWinners / $totalSpins) * 100 : 0;
        // Lịch sử quay của tất cả người chơi (mới)
        $spinHistoryQuery = SpinHistory::with('user')
            ->orderBy('spun_at', 'desc');

        // Xử lý tìm kiếm nếu có
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $spinHistoryQuery->where(function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                    ->orWhere('reward_name', 'like', "%{$search}%");
            });
        }

        // Phân trang (10 bản ghi mỗi trang)
        $spinHistories = $spinHistoryQuery->take(100)->get();
        return view('spins.index', compact(
            'spinConfigs',
            'gifts',
            'giftsAll',
            'availableGifts',
            'totalProbability',
            'spinStatsDay',
            'spinStatsMonth',
            'spinStatsYear',
            'giftWinners',
            'groupedWinners',
            'totalSpins',
            'totalWinners',
            'winRate',
            'spinConfigs',
            'selectedGifts',
            'hasEnoughSpinConfigTypes',
            'missingTypes',
            'hasEnoughGifts',
            'requiredGifts',
            'currentSelectedGiftsCount',
            'showConfigWarning',
            'isProbabilityValid',
            'spinHistories',
            'spinStatus',
            'spinSetting',
            'missingTypeNames',
            'spinTypes',
        ));
    }
    public function storeSpinConfig(StoreSpinRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->all();
            // dd( $data); die;
            $newCells = (int) ($data['cells'] ?? 1);

            // Tính tổng cells hiện tại
            $currentTotalCells = SpinConfig::sum('cells');

            // Nếu vượt quá 8 -> báo lỗi
            if ($currentTotalCells + $newCells > 6) {
                return redirect()->back()->with('error', 'Tổng số ô quà không được vượt quá 8.');
            }
            $spinType = SpinType::where('name', $data['type'])->first();

            if (!$spinType) {
                return redirect()->back()->with('error', 'Loại ô quà không hợp lệ.');
            }

            // Gán tên ô quà = display_name của loại
            $data['name'] = $spinType->display_name;
            $spinConfig = SpinConfig::create($data);
            DB::commit();
            return redirect()->back()->with('success', 'Thêm ô quà thành công!');
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['image']) && !empty($data['image']) && filter_var($data['image'], FILTER_VALIDATE_URL)) {
                $this->deleteImage($data['image'], 'spin_configs');
            }

            $this->logError($e);

            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function toggleSelection(Request $request, $type, $id)
    {
        try {
            $spinSetting = SpinSetting::first();
            if ($spinSetting && $spinSetting->status === 'active') {
                return redirect()->back()->with('error', 'Không thể thay đổi trạng thái quà hiện vật khi vòng quay đang hoạt động!');
            }

            if ($type === 'gift') {
                $item = Gift::findOrFail($id);

                if (!$item->is_selected) {
                    $selectedGiftsCount = Gift::where('is_selected', 1)->count();
                    if ($selectedGiftsCount >= 2) {
                        return redirect()->back()->with('error', 'Chỉ được phép thêm tối đa 2 quà hiện vật vào vòng quay!');
                    }
                }

                $item->is_selected = !$item->is_selected;
                $item->save();

                $message = $item->is_selected
                    ? 'Thêm quà hiện vật vào vòng quay thành công!'
                    : 'Bỏ quà hiện vật khỏi vòng quay thành công!';

                return redirect()->back()->with('success', $message);
            }

            return redirect()->back()->with('error', 'Loại phần thưởng không hợp lệ');
        } catch (\Exception $e) {
            $this->logError($e);
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // Cập nhật cấu hình tỷ lệ trúng (SpinConfig)
    public function updateSpinConfig(Request $request, $id)
    {
        try {
            $spinSetting = SpinSetting::first();
            if ($spinSetting && $spinSetting->status === 'active') {
                return redirect()->back()->with('error', 'Không thể cập nhật tỷ lệ trúng khi vòng quay đang hoạt động!');
            }
            $config = SpinConfig::findOrFail($id);
            $validator = Validator::make($request->all(), [
                'probability' => 'required|numeric|min:0|max:100',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $config->probability = $request->probability;
            $config->save();

            Log::info('Admin updated spin config', ['admin_id' => $request->user()->id, 'config' => $config]);
            return redirect()->back()->with('success', 'Cập nhật tỷ lệ thành công');
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // Thêm quà hiện vật
    public function addGift(StoreGiftRequest $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->validated();
            if ($request->hasFile('image')) {
                $data['thumbnail'] = $this->uploadToLocal($request->file('image'), self::FOLDER);
            }
            // dd($data);
            $gift = Gift::create($data);
            DB::commit();
            // Log::info('Admin added gift', ['admin_id' => $request->user()->id, 'gift' => $gift]);
            return redirect()->back()->with('success', 'Thêm quà thành công');
        } catch (\Exception $e) {
            DB::rollBack();

            if (isset($data['thumbnail']) && !empty($data['thumbnail']) && filter_var($data['thumbnail'], FILTER_VALIDATE_URL)) {
                $this->deleteImage($data['thumbnail'], 'gifts');
            }

            $this->logError($e);

            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    // Cập nhật quà hiện vật
    public function updateGift(Request $request, $id)
    {
        $spinSetting = SpinSetting::first();
        if ($spinSetting && $spinSetting->status === 'active') {
            return redirect()->back()->with('error', 'Không thể cập nhật tỷ lệ trúng khi vòng quay đang hoạt động!');
        }
        $gift = Gift::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'stock' => 'required|integer|min:0',
            'probability' => 'required|numeric|min:0|max:100',
            'description' => 'nullable|string',
            'thmubnail' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $gift->update($request->only(['name', 'stock', 'probability', 'description', 'image_url', 'is_active']));
        // Log::info('Admin updated gift', ['admin_id' => $request->user()->id, 'gift' => $gift]);
        return redirect()->back()->with('success', 'Cập nhật quà thành công');
    }

    // Xóa quà hiện vật
    public function deleteGift($id)
    {
        try {
            // Tìm quà theo ID
            $gift = Gift::findOrFail($id);

            // Kiểm tra xem quà có đang được sử dụng trong vòng quay không
            if ($gift->is_selected) {
                // Lấy tổng xác suất của các quà và spin configs
                $spinConfigs = SpinConfig::all();
                $selectedGifts = Gift::where('is_selected', 1)->get();
                $totalProbability = $spinConfigs->sum('probability') + $selectedGifts->sum('probability');

                // Nếu quà đang được chọn và ảnh hưởng đến tổng xác suất
                if ($totalProbability > 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Không thể xóa quà này vì nó đang được sử dụng trong vòng quay. Vui lòng bỏ chọn quà trước khi xóa.'
                    ], 400);
                }
            }

            // Xóa file ảnh nếu có
            if ($gift->thumbnail && Storage::disk('public')->exists($gift->thumbnail)) {
                Storage::disk('public')->delete($gift->thumbnail);
            }

            // Xóa quà
            $gift->delete();

            // Ghi log (nếu cần)
            // Log::info('Admin deleted gift', ['admin_id' => request()->user()->id, 'gift_id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Xóa quà hiện vật thành công!'
            ]);
        } catch (\Exception $e) {
            // Ghi log lỗi
            Log::error('Error deleting gift: ' . $e->getMessage(), [
                'admin_id' => request()->user()->id,
                'gift_id' => $id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi xóa quà: ' . $e->getMessage()
            ], 500);
        }
    }
    public function deleteSpinConfig($id)
    {
        // Lấy trạng thái vòng quay từ spin_settings
        $spinSetting = SpinSetting::first();

        // Kiểm tra nếu vòng quay đang hoạt động
        if ($spinSetting && $spinSetting->status === 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Không thể xóa ô quà khi vòng quay đang hoạt động!'
            ], 403); // 403 Forbidden
        }

        // Nếu vòng quay không hoạt động, tiến hành xóa
        $spinConfig = SpinConfig::findOrFail($id);
        $spinConfig->delete();

        return response()->json([
            'success' => true,
            'message' => 'Xóa ô quà thành công!'
        ]);
    }
    public function toggleSpinStatus(Request $request)
{
    $spinSetting = SpinSetting::firstOrCreate();

    $spinConfigs = SpinConfig::all();
    $requiredTypes = ['no_reward', 'coupon', 'spin'];
    $spinConfigTypes = $spinConfigs->pluck('type')->unique()->toArray();
    $hasEnoughSpinConfigTypes = count(array_diff($requiredTypes, $spinConfigTypes)) === 0;

    $selectedGifts = Gift::where('is_selected', 1)->get();
    $requiredGifts = 2;
    $hasEnoughGifts = $selectedGifts->count() === $requiredGifts;

    $totalProbability = $spinConfigs->sum('probability') + $selectedGifts->sum('probability');
    $isProbabilityValid = abs($totalProbability - 100) < 0.01;

    $isConfigValid = $hasEnoughSpinConfigTypes && $hasEnoughGifts && $isProbabilityValid;

    if ($request->input('status') === 'active' && !$isConfigValid) {
        $message = 'Không thể kích hoạt vòng quay do chưa đủ điều kiện!';
        if ($request->ajax()) {
            return response()->json(['success' => false, 'message' => $message], 400);
        }
        return redirect()->back()->with('error', $message);
    }

    $newStatus = $request->input('status') === 'active' ? 'active' : 'inactive';
    $spinSetting->update([
        'status' => $newStatus,
        'has_enough_spin_types' => $hasEnoughSpinConfigTypes,
        'has_enough_gifts' => $hasEnoughGifts,
        'is_probability_valid' => $isProbabilityValid,
        'total_probability' => $totalProbability,
    ]);

    $message = $newStatus === 'active' 
        ? 'Kích hoạt vòng quay thành công!' 
        : 'Vòng quay đã được tắt trạng thái hoạt động.';

    if ($request->ajax()) {
        return response()->json(['success' => true, 'message' => $message]);
    }

    return redirect()->back()->with('success', $message);
}

}
