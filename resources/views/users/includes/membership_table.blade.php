@foreach ($memberships as $index => $membership)
<tr>
    <td>{{ $memberships->firstItem() + $index }}
    </td>
    <td>
        <div class="d-flex align-items-center">
            <div class="flex-shrink-0 me-2">
                <div
                    class="avatar-xs bg-soft-primary rounded-circle text-center">
                    <i
                        class="mdi mdi-crown text-primary font-size-14 avatar-title"></i>
                </div>
            </div>
            <div>
                <h6 class="mb-0"
                    style="white-space: pre-line;">
                    {{ $membership->name }}</h6>
            </div>
        </div>
    </td>
    <td><code>{{ $membership->duration_months }}
            tháng</code></td>
    @if ($roleUser == 'instructor')
        <td>{{ number_format($membership->total_revenue) }}
            VND</td>
        <td>{{ $membership->total_bought }} người
            mua</td>
    @else
        <td>{{ \Carbon\Carbon::parse($membership->created_at)->format('d/m/Y') }}
        </td>
        <td>
            {{ optional($membership->created_at ? \Carbon\Carbon::parse($membership->created_at) : null)->addMonths($membership->duration_months)
                ?->format('d/m/Y') ?? 'Chưa có' }}
        </td>
    @endif
    <td>
        <a href="{{ route('admin.approvals.memberships.show', $membership->id) }}"
            class="btn btn-sm btn-info">
            <i class="mdi mdi-eye"></i>
        </a>
    </td>
</tr>
@endforeach