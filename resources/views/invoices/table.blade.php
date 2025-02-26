<div class="listjs-table" id="customerList">
    <div class="table-responsive table-card mt-3 mb-1">
        <table class="table align-middle table-nowrap" id="customerTable">
            <thead class="table-light">
                <tr>
                    <th>STT</th>
                    <th>Mã hóa đơn</th>
                    <th>Người mua</th>
                    <th>Khoá học</th>
                    <th>Người hướng dẫn</th>
                    <th>Tổng thanh toán</th>
                    <th>Trạng thái</th>
                    <th>Ngày mua</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody class="list">
                @foreach ($invoices as $invoice)
                    <tr>
                        <td>{{ $loop->index + 1 }}</td>
                        <td>{{ $invoice->code }}</td>
                        <td><span
                                class="text-danger fw-bold">{{ $invoice->user->name ?? '' }}</span>
                        </td>
                        <td>
                            <img style="width: 70px; " src="{{ $invoice->course->thumbnail }}"
                                class="object-fit-cover rounded me-2" alt="">
                            <span>
                                {{ Str::limit($invoice->course->name ?? 'Không có tên', 40) }}
                            </span>
                        </td>
                        <td>
                            {{ $invoice->course->instructor->name ?? '' }}
                        </td>
                        <td>{{ number_format($invoice->final_amount ?? 0) }} VND</td>
                        <td>
                            <span class="badge bg-primary">Hoàn thành</span>
                        </td>
                        <td>{{ $invoice->created_at ? \Carbon\Carbon::parse($invoice->created_at)->format('d/m/Y') : '' }}
                        </td>
                        <td>
                            <a href="">
                                <button class="btn btn-sm btn-info edit-item-btn">
                                    <span class="ri-eye-line"></span>
                                </button>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="row justify-content-end">
        {{ $invoices->appends(request()->query())->links() }}
    </div>
</div>