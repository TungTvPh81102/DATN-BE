<?php

namespace App\Http\Controllers\Admin;

use App\Exports\InstructorCommissionExport;
use App\Http\Controllers\Controller;
use App\Models\InstructorCommission;
use App\Models\User;
use App\Notifications\Client\InstructorModificationRate;
use App\Traits\ApiResponseTrait;
use App\Traits\FilterTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class InstructorCommissionController extends Controller
{
    use LoggableTrait, ApiResponseTrait, FilterTrait;

    public function index(Request $request)
    {
        $title = 'Quản lý và phân chia doanh thu';
        $subTitle = 'Phân chia doanh thu';

        $queryInstructorCommission = InstructorCommission::query()->with('instructor')->latest('created_at');

        if ($request->filled('instructor_name')) {
            $queryInstructorCommission->whereHas('instructor', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('instructor_name') . '%');
            });
        }

        if ($request->filled('instructor_code')) {
            $queryInstructorCommission->whereHas('instructor', function ($q) use ($request) {
                $q->where('code', 'like', '%' . $request->input('instructor_code') . '%');
            });
        }

        if ($request->filled('instructor_email')) {
            $queryInstructorCommission->whereHas('instructor', function ($q) use ($request) {
                $q->where('email', 'like', '%' . $request->input('instructor_email') . '%');
            });
        }

        if ($request->filled('commission_amount')) {
            $queryInstructorCommission->where('commission_amount', $request->input('commission_amount'));
        }

        if ($request->filled('start_date')) {
            $queryInstructorCommission->whereDate('created_at', '=', $request->input('start_date'));
        }

        if ($request->filled('commission_amount')) {
            $queryInstructorCommission->where('commission_amount', $request->input('commission_amount'));
        }
    
        if ($request->filled('start_date')) {
            $queryInstructorCommission->whereDate('created_at', '=', $request->input('start_date'));
        }
    
        if ($request->filled('end_date')) {
            $queryInstructorCommission->where('created_at', '<=', $request->input('end_date'));
        }

        if ($request->has('query') && $request->query('query')) {
            $searchTerm = $request->query('query');
            $queryInstructorCommission->whereHas('instructor', function ($q) use ($searchTerm) {
                $q->where('name', 'LIKE', '%' . $searchTerm . '%');
            });
        }

        $instructorCommissions = $queryInstructorCommission->paginate(10);

        if ($request->ajax()) {
            $html = view('instructor-commissions.table', compact(['instructorCommissions']))->render();
            return response()->json(['html' => $html]);
        }
        return view('instructor-commissions.index', compact('instructorCommissions', 'title', 'subTitle'));
    }

    public function updateInstructorCommission(Request $request)
    {
        try {
            $rate = $request->input('rate', 0.6);
            $id = $request->input('id', '');

            if ($rate > 0.9 || $rate < 0.1) {
                return $this->respondError('Hoa hồng của giảng viên phải nằm trong khoảng 10% đến 90%');
            }

            if (!$id) return $this->respondError('Thông tin không hợp lệ');

            $instructorCommission = InstructorCommission::find($id);

            if (!$instructorCommission) return $this->respondNotFound('Không tìm thấy thông tin');

            $logs = json_decode($instructorCommission->rate_logs, true);
            $logs = is_array($logs) ? $logs : [];

            $oldRate = $instructorCommission->rate;
            $newRate = round($rate, 2);

            $note = $newRate > $oldRate
                ? 'Tăng tỷ lệ hoa hồng từ ' . ($oldRate * 100) . '% lên ' . ($newRate * 100) . '%'
                : ($newRate < $oldRate
                    ? 'Giảm tỷ lệ hoa hồng từ ' . ($oldRate * 100) . '% xuống ' . ($newRate * 100) . '%'
                    : 'Giữ nguyên tỷ lệ hoa hồng');

            $logs[] = [
                'old_rate' => $instructorCommission->rate,
                'new_rate' => round($rate, 2),
                'changed_at' => now()->toDateTimeString(),
                'user_name' => auth()->user()->name ?? 'Hệ thống tự động đánh giá',
                'note' => $note
            ];

            $instructorCommission->rate =  $newRate;
            $instructorCommission->rate_logs = json_encode($logs);
            $instructorCommission->updated_at = now();
            $instructorCommission->save();


            $instructor = User::where('id', $instructorCommission->instructor_id)->first();

            if ($instructor) {
                if ($oldRate != $newRate) {
                    $instructor->notify(new InstructorModificationRate($newRate, $instructor));
                }
            }

            return $this->respondOk('Thay đổi hoa hồng của giảng viên thành công', $instructorCommission);
        } catch (\Exception $e) {
            $this->logError($e);

            return $this->respondServerError('Có lỗi xảy ra vui lòng thử lại');
        }
    }

    public function bulkUpdate(Request $request)
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:instructor_commissions,id',
                'method' => 'required|in:fixed,increment,decrement',
                'value' => 'required|numeric|min:0|max:100',
                'note' => 'nullable|string|max:255',
            ]);

            $ids = $validated['ids'];
            $method = $validated['method'];
            $value = $validated['value'] / 100;
            $updatedCount = 0;

            DB::beginTransaction();

            foreach ($ids as $id) {
                $commission = InstructorCommission::findOrFail($id);
                $oldRate = $commission->rate;
                $newRate = $oldRate;

                switch ($method) {
                    case 'fixed':
                        $newRate = $value;
                        break;
                    case 'increment':
                        $newRate = min(1, $oldRate + $value);
                        break;
                    case 'decrement':
                        $newRate = max(0, $oldRate - $value);
                        break;
                }

                if ($newRate > 0.9) {
                    $newRate = 0.9;
                }
                if ($newRate < 0.1) {
                    $newRate = 0.1;
                }

                if ($newRate == $oldRate) {
                    continue;
                }

                $logs = json_decode($commission->rate_logs, true) ?: [];

                $changeType = $newRate > $oldRate ? 'Tăng' : ($newRate < $oldRate ? 'Giảm' : 'Giữ nguyên');
                $formattedOld = rtrim(rtrim(number_format($oldRate * 100, 2), '0'), '.');
                $formattedNew = rtrim(rtrim(number_format($newRate * 100, 2), '0'), '.');
                $autoNote = match ($changeType) {
                    'Tăng' => "Tăng tỷ lệ hoa hồng từ {$formattedOld}% lên {$formattedNew}%",
                    'Giảm' => "Giảm tỷ lệ hoa hồng từ {$formattedOld}% xuống {$formattedNew}%",
                    default => 'Giữ nguyên tỷ lệ hoa hồng'
                };

                $logs[] = [
                    'old_rate' => $oldRate,
                    'new_rate' => $newRate,
                    'changed_at' => now(),
                    'user_name' => auth()->user()->name ?? 'Hệ thống tự động đánh giá',
                    'note' =>  $autoNote
                ];

                if ($newRate != $oldRate) {
                    $commission->rate = $newRate;
                    $commission->rate_logs = json_encode($logs);
                    $commission->save();

                    $instructor = User::find($commission->instructor_id);
                    if ($instructor) {
                        $instructor->notify(new InstructorModificationRate($newRate, $instructor));
                    }

                    $updatedCount++;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thành công ' . $updatedCount . ' giảng viên',
                'updated' => $updatedCount
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra: ' . $e->getMessage()
            ], 500);
        }
    }

    public function export()
    {
        try {
            return Excel::download(new InstructorCommissionExport, 'phan_chia_hoa_hong.xlsx');
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    
    

}
