<div class="listjs-table" id="customerList">
    <div class="table-responsive table-card mt-3 mb-1">
        <table class="table align-middle table-nowrap" id="customerTable">
            <thead class="table-light">
                <tr>
                    <th>STT</th>
                    <th>Mã giao dịch</th>
                    <th>Người thực hiện giao dịch</th>
                    <th>Email</th>
                    <th>Số tiền</th>
                    <th>Loại giao dịch</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo giao dịch</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody class="list">
                @foreach ($transactions as $transaction)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>{{ $transaction->transaction_code ?? 'Không có thông tin' }}</td>
                        <td><span
                                class="text-primary fw-bold">{{ $transaction->user->name ?? 'Không có thông tin' }}</span>
                            <br>
                            <small
                                class="text-muted">{{ $transaction->user->profile->phone ?? '' }}</small>
                        </td>
                        <td>{{ $transaction->user->email ?? 'Không có thông tin' }}</td>
                        <td>{{ number_format($transaction->amount) ?? 0 }} VND</td>
                        <td>
                            @if ($transaction->type == 'invoice')
                                <span class="badge bg-success">
                                    Mua bán
                                </span>
                            @elseif($transaction->type == 'withdrawal')
                                <span class="badge bg-info">
                                    Rút tiền
                                </span>
                            @endif
                        </td>
                        <td class="col-1">
                            @if ($transaction->status === 'Giao dịch thành công')
                                <span class="badge bg-success">
                                    {{ $transaction->status }}
                                </span>
                            @elseif($transaction->status === 'Chờ xử lý')
                                <span class="badge bg-warning">
                                    {{ $transaction->status }}
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    {{ $transaction->status }}
                                </span>
                            @endif
                        </td>
                        <td>{{ $transaction->created_at ?? '' }}
                        </td>
                        <td>
                            @if ($transaction->type === 'withdrawal')
                                <a
                                    href="{{ route('admin.withdrawals.show', $transaction->transactionable_id) }}">
                                    <button class="btn btn-sm btn-info edit-item-btn">
                                        <span class="ri-eye-line"></span>
                                    </button>
                                </a>
                            @else
                                <a
                                    href="{{ route('admin.transactions.show', $transaction->transaction_code) }}">
                                    <button class="btn btn-sm btn-info edit-item-btn">
                                        <span class="ri-eye-line"></span>
                                    </button>
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="row justify-content-end">
        {{ $transactions->appends(request()->query())->links() }}
    </div>
</div>