<?php

namespace App\Http\Controllers\API\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\Transaction\DepositTransactionRequest;
use App\Models\Coupon;
use App\Models\Course;
use App\Models\Transaction;
use App\Traits\LoggableTrait;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    use LoggableTrait;

    public function index()
    {
        try {
            $transactions = Transaction::query()->where('transactionable_id', Auth::id())->latest('id')->get();

            return response()->json([
                'message' => 'Danh sách giao dịch của: ' . Auth::user()->name,
                'transactions' => $transactions,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logError($e);

            return response()->json([
                'status' => false,
                'message' => 'Có lỗi xảy ra, vui lòng thử lại',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function show(string $id)
    {
        try {
            $transaction = Transaction::query()->findOrFail($id);

            return response()->json([
                'message' => 'Chi tiết giao dịch của: ' . Auth::user()->name,
                'transactions' => $transaction,
            ], Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logError($e);

            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy giao dịch',
            ], Response::HTTP_NOT_FOUND);
        }
    }
    public function deposit(DepositTransactionRequest $request)
    {
        try {
            $data = $request->validated();

            $deposit = Transaction::query()->create([
                'amount' => $request->amount,
                'coin' => round($request->amount / 1000, 2),
                'transactionable_id' => Auth::id(),
                'transactionable_type' => 'App\Models\User',
            ]);

            return response()->json([
                'message' => 'Giao dịch nạp tiền đang chờ xử lý',
                'deposit' => $deposit,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            $this->logError($e);

            return response()->json([
                'status' => false,
                'message' => 'Nạp tiền thất bại, vui lòng thử lại',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function buyCourse(){
        
    }
}
