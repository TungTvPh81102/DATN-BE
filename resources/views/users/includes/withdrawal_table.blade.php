@foreach ($transactions as $index => $transaction)
    <tr>
        <td>{{ $transactions->firstItem() + $index }}</td>
        <td><code>{{ $transaction->transaction_code }}</code></td>
        <td>{{ number_format($transaction->amount) }} VND</td>
        <td>
            <span class="badge bg-soft-primary text-primary">
                <i class="mdi mdi-bank me-1"></i>Chuyển khoản
            </span>
        </td>
        <td>
            @if ($transaction->transactionable->status == 'Đã xử lý')
                <span class="badge bg-soft-success text-success">Đã xử lý</span>
            @elseif($transaction->transactionable->status == 'Từ chối')
                <span class="badge bg-soft-danger text-danger">Từ chối</span>
            @else
                <span class="badge bg-soft-secondary text-secondary">{{ $transaction->transactionable->status }}</span>
            @endif
        </td>
        <td>{{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y H:i') }}</td>
        <td>
            <a href="{{ route('admin.withdrawals.show', $transaction->transactionable->id) }}"
                class="btn btn-sm btn-info">
                <i class="mdi mdi-eye"></i>
            </a>
        </td>
    </tr>
@endforeach