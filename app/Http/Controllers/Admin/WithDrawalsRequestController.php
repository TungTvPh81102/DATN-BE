<?php

namespace App\Http\Controllers\Admin;

use App\Exports\WithDrawalExport;
use App\Http\Controllers\Controller;
use App\Models\SupportedBank;
use App\Models\SystemFund;
use App\Models\SystemFundTransaction;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Notifications\ConfirmPaymentToInstructorNotification;
use App\Traits\ApiResponseTrait;
use App\Traits\LoggableTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class WithDrawalsRequestController extends Controller
{
    use LoggableTrait;

    public function index(Request $request)
    {
        try {
            $title = 'Yêu cầu rút tiền';
            $subTitle = 'Yêu cầu rút tiền';

            $queryWithdrawals = WithdrawalRequest::query()->latest('id');
            $countWithdrawals = WithdrawalRequest::query()->selectRaw(
                'count(id) as total_withdrawals,
                sum(status = "Hoàn thành") as completed_withdrawals,
                sum(status = "Đang xử lý") as pending_withdrawals,
                sum(status = "Từ chối") as failed_withdrawals'
            )->first();

            if ($request->hasAny(['status', 'request_date', 'completed_date', 'bank_name', 'amount_min', 'amount_max', 'account_number', 'account_holder']))
                $queryWithdrawals = $this->filter($request, $queryWithdrawals);

            if ($request->has('search_full'))
                $queryWithdrawals = $this->search($request, $queryWithdrawals);

            $withdrawals = $queryWithdrawals->paginate(10);
            $supportedBank = SupportedBank::query()->select('short_name', 'name',  'logo_rounded')->get();

            if ($request->ajax()) {
                $html = view('withdrawals.table', compact('withdrawals', 'supportedBank'))->render();
                return response()->json(['html' => $html]);
            }

            return view('withdrawals.index', compact(['title', 'subTitle', 'withdrawals', 'countWithdrawals', 'supportedBank']));
        } catch (\Exception $e) {

            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    public function show(string $id)
    {
        try {
            $title = 'Yêu cầu rút tiền';
            $subTitle = 'Yêu cầu rút tiền';

            $withDraw = WithdrawalRequest::query()
                ->with([
                    'wallet.user'
                ])
                ->findOrFail($id);

            return view('withdrawals.show', compact(['title', 'subTitle', 'withDraw']));
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Không tìm thấy giao dịch, vui lòng thử lại');
        }
    }

    public function confirmPayment(Request $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validate([
                'withdrawal_id' => 'required|exists:withdrawal_requests,id',
                'admin_comment' => 'nullable|string|max:255',
                'action' => 'required|in:approve,reject'
            ]);

            $withdrawal = WithdrawalRequest::query()
                ->with(['wallet.user'])
                ->find($data['withdrawal_id']);

            if ($withdrawal->status === 'Hoàn thành' || $withdrawal->status === 'Đã xử lý') {
                return response()->json([
                    'status' => false,
                    'message' => 'Yêu cầu đã được xử lý, hoặc đang xử lý. Vui lòng thử lại sau.',
                ]);
            }

            if ($withdrawal->status === 'Chờ xác nhận lại') {
                $transation = Transaction::query()
                    ->where('transactionable_id', $withdrawal->id)
                    ->where('transactionable_type', WithdrawalRequest::class)
                    ->first();

                $systemFundTransaction = SystemFundTransaction::query()
                    ->where('transaction_id', $transation->id)
                    ->first();

                if ($systemFundTransaction) {
                    $withdrawal->update([
                        'status' => 'Hoàn thành',
                        'admin_comment' =>  $withdrawal->admin_comment,
                    ]);
                }

                $this->sendOrUpdateNotification($withdrawal);

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Xử lý yêu cầu phản hồi thành công',
                ]);
            }

            if ($data['action'] === 'reject') {
                $wallet = $withdrawal->wallet;

                $wallet->update([
                    'balance' => $wallet->balance + $withdrawal->amount,
                ]);

                $withdrawal->update([
                    'status' => 'Từ chối',
                    'admin_comment' => $data['admin_comment'],
                ]);

                Transaction::query()->create([
                    'transaction_code' => 'ORDER' . time(),
                    'amount' => $withdrawal->amount,
                    'type' => 'withdrawal',
                    'status' => 'Từ chối yêu cầu',
                    'user_id' => Auth::id(),
                    'transactionable_id' => $withdrawal->id,
                    'transactionable_type' => WithdrawalRequest::class,
                ]);

                $this->sendRejectionNotification($withdrawal);

                DB::commit();

                return response()->json([
                    'status' => true,
                    'message' => 'Yêu cầu rút tiền đã bị từ chối thành công',
                ]);
            }

            $systemFundBalance = SystemFund::query()
                ->lockForUpdate()
                ->first();

            if (!$systemFundBalance === null || $systemFundBalance->pending_balance <= 0) {
                return response()->json([
                    'status' => false,
                    'message' => 'Hệ thống đang bảo trì, vui lý thử lại sau',
                ]);
            }

            if ($systemFundBalance->pending_balance < $withdrawal->amount) {
                return response()->json([
                    'status' => false,
                    'message' => 'Hệ thống đang bảo trì, vui lý thử lại sau',
                ]);
            }

            $systemFundBalance->decrement('pending_balance', $withdrawal->amount);

            $transation = Transaction::query()->create([
                'transaction_code' => 'ORDER' . time(),
                'amount' => $withdrawal->amount,
                'type' => 'withdrawal',
                'status' => 'Giao dịch thành công',
                'user_id' => Auth::id(),
                'transactionable_id' => $withdrawal->id,
                'transactionable_type' => WithdrawalRequest::class,
            ]);

            SystemFundTransaction::query()->create([
                'user_id' => Auth::id(),
                'transaction_id' => $transation->id,
                'total_amount' => $withdrawal->amount,
                'type' => 'withdrawal',
                'description' => $data['admin_comment'] ?? 'Thanh toán cho giảng viên: ' . $withdrawal->wallet->user->name,
            ]);

            $withdrawal->update([
                'admin_comment' => 'Đã thanh toán cho bạn với số tiền ' . number_format($withdrawal->amount),
                'status' => 'Đã xử lý',
                'completed_date' => now()
            ]);

            $this->sendOrUpdateNotification($withdrawal);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'Thao tác thành công',
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();

            $this->logError($e, $request->all());

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    protected function sendOrUpdateNotification($withdrawal)
    {
        $user = $withdrawal->wallet->user;
        
        $message = $withdrawal->status === 'Đã xử lý'
            ? "Yêu cầu thanh toán của bạn đã được xử lý thành công"
            : "Yêu cầu rút tiền của bạn đã được hệ thống phản hồi. Vui lòng kiểm tra lại.";

        $existingNotification = DatabaseNotification::query()
            ->where('notifiable_id', $user->id)
            ->where('notifiable_type', 'App\Models\User')
            ->where('type', ConfirmPaymentToInstructorNotification::class)
            ->whereJsonContains('data->withdrawal_id', $withdrawal->id)
            ->first();

        if ($existingNotification) {
            $existingNotification->update([
                'data' => [
                    'withdrawal_id' => $withdrawal->id,
                    'status' => $withdrawal->status,
                    'amount' => $withdrawal->amount,
                    'message' => $message,
                ]
            ]);

            $user->notify(new ConfirmPaymentToInstructorNotification(
                $withdrawal,
                $message
            ));
        } else {
            $user->notify(new ConfirmPaymentToInstructorNotification(
                $withdrawal,
                $message
            ));
        }
    }

    protected function sendRejectionNotification($withdrawal)
    {
        $user = $withdrawal->wallet->user;
        $message = "Yêu cầu rút tiền của bạn đã bị từ chối. Lý do: " . $withdrawal->admin_comment;

        $user->notify(new ConfirmPaymentToInstructorNotification(
            $withdrawal,
            $message
        ));
    }

    public function checkStatus(Request $request)
    {
        try {
            $withdrawalId = $request->input('withdrawal_id');

            Artisan::call('with-drawal-transation:check', [
                'id' => $withdrawalId
            ]);

            $withdrawal = WithdrawalRequest::query()->find($withdrawalId);

            if (!$withdrawal) {
                return response()->json([
                    'status' => false,
                    'message' => 'Không tìm thấy yêu cầu rút tiền.',
                ], 404);
            }

            $transaction = Transaction::query()
                ->where('transactionable_type', WithdrawalRequest::class)
                ->where('transactionable_id', $withdrawalId)
                ->first();

            if ($withdrawal->status === 'Từ chối') {
                return response()->json([
                    'status' => 'rejected',
                    'message' => 'Yêu cầu rút tiền đã bị từ chối.',
                    'withdrawal_request' => $withdrawal,
                    'transaction' => $transaction,
                ], 200);
            }

            $systemFundTransaction = null;
            if ($transaction) {
                $systemFundTransaction = SystemFundTransaction::query()
                    ->where('transaction_id', $transaction->id)->first();
            }

            return response()->json([
                'status' => 'success',
                'withdrawal_request' => $withdrawal,
                'transaction' => $transaction,
                'system_fund_transaction' => $systemFundTransaction,
            ], 200);
        } catch (\Exception $e) {
            $this->logError($e, $request->all());

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }

    private function filter($request, $query)
    {
        $filters = [
            'status' => ['queryWhere' => '='],
            'request_date' => ['queryWhere' => '>='],
            'completed_date' => ['queryWhere' => '<='],
            'bank_name' => ['queryWhere' => '='],
            'amount' => ['queryWhere' => 'BETWEEN', 'attribute' => ['amount_min', 'amount_max']],
            'account_holder' => ['queryWhere' => 'LIKE'],
            'account_number' => ['queryWhere' => 'LIKE'],
        ];


        foreach ($filters as $filter => $value) {
            if (!empty($value['queryWhere'])) {
                if ($value['queryWhere'] !== 'BETWEEN') {
                    $filterValue = $request->input($filter);
                    if (!empty($filterValue)) {
                        $filterValue = $value['queryWhere'] === 'LIKE' ? "%$filterValue%" : $filterValue;
                        $query->where($filter, $value['queryWhere'], $filterValue);
                    }
                } else {
                    $filterValueBetweenA = $request->input($value['attribute'][0]);
                    $filterValueBetweenB = $request->input($value['attribute'][1]);

                    if (!empty($filterValueBetweenA) && !empty($filterValueBetweenB)) {
                        $query->whereBetween($filter, [$filterValueBetweenA, $filterValueBetweenB]);
                    }
                }
            }
        }

        return $query;
    }

    private function search($request, $query)
    {
        if (!empty($request->search_full)) {
            $searchTerm = $request->search_full;

            $query->where(function ($query) use ($searchTerm) {
                $query->where('account_number', 'LIKE', "%$searchTerm%")
                    ->orWhere('account_holder', 'LIKE', "%$searchTerm%")
                    ->orwhere('note', 'LIKE', "%$searchTerm%");
            });
        }

        return $query;
    }

    public function export()
    {
        try {
            return Excel::download(new WithDrawalExport, 'withdrawals.xlsx');
        } catch (\Exception $e) {
            $this->logError($e);

            return redirect()->back()->with('error', 'Có lỗi xảy ra, vui lòng thử lại sau');
        }
    }
}
