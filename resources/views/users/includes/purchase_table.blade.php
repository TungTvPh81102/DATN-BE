@foreach ($purchases as $index => $purchase)
    <tr>
        <td>{{ $purchases->firstItem() + $index }}</td>
        <td><code>{{ $purchase->code }}</code></td>
        <td>
            @if ($purchase->invoice_type == 'course')
                <span class="badge bg-soft-primary text-primary">
                    <i class="mdi mdi-book-open-variant me-1"></i>Khóa học
                </span>
            @elseif($purchase->invoice_type == 'membership')
                <span class="badge bg-soft-info text-info">
                    <i class="mdi mdi-certificate me-1"></i>Membership
                </span>
            @endif
        </td>
        <td>
            @if ($purchase->invoice_type == 'course')
                {{ $purchase->course->name }}
            @elseif($purchase->invoice_type == 'membership')
                {{ $purchase->membershipPlan->name }}
            @endif
        </td>
        <td>{{ number_format($purchase->final_amount) }} VND</td>
        <td>
            <span class="badge bg-soft-info text-info">
                <i class="mdi mdi-credit-card me-1"></i>{{ $purchase->payment_method }}
            </span>
        </td>
        <td>{{ \Carbon\Carbon::parse($purchase->created_at)->format('d/m/Y H:i') }}</td>
        <td>
            @if ($purchase->invoice_type == 'course')
                <a href="{{ route('admin.invoices.show', $purchase->code) }}"
                    class="btn btn-sm btn-info">
                    <i class="mdi mdi-eye"></i>
                </a>
            @elseif($purchase->invoice_type == 'membership')
                <a href="{{ route('admin.invoices.memberships.show', $purchase->code) }}"
                    class="btn btn-sm btn-info">
                    <i class="mdi mdi-eye"></i>
                </a>
            @endif
        </td>
    </tr>
@endforeach