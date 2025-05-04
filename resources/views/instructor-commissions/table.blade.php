<div class="card-body" id="item_List">
    <div class="listjs-table">

    <div class="table-responsive table-card mt-3 mb-1">
        <table class="table align-middle table-nowrap">
            <thead class="table-light">
                <tr>
                    <th scope="col" style="width: 50px;">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="checkAll"
                                value="option">
                        </div>
                    </th>
                    <th>STT</th>
                    <th>Giảng viên</th>
                    <th>Hoa hồng hiện tại (%)</th>
                    <th>Cập nhật lúc</th>
                    <th>Lịch sử thay đổi</th>
                </tr>
            </thead>
            <tbody class="list form-check-all">
                @foreach ($instructorCommissions as $instructorCommission)
                    <tr>
                        <th scope="row">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="itemID"
                                    value="{{ $instructorCommission->id }}">
                            </div>
                        </th>
                        <th style="width: 60px;">STT</th>
                        <th>Giảng viên</th>
                        <th style="width: 180px;">Lợi nhuận (%)</th>
                        <th style="width: 160px;">Ngày tham gia</th>
                        <th style="width: 160px;">Cập nhật lúc</th>
                        <th style="width: 120px;">Lịch sử</th>
                    </tr>
                </thead>
                <tbody class="list form-check-all">
                    @foreach ($instructorCommissions as $instructorCommission)
                        <tr>
                            <th scope="row">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="itemID"
                                        value="{{ $instructorCommission->id }}">
                                </div>
                            </th>

                            <td class="id">{{ $loop->iteration }}</td>

                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="{{ $instructorCommission->instructor->avatar ?? '' }}"
                                        alt="Avatar" class="user-avatar me-2">
                                    <div class="d-flex flex-column">
                                        <div class="fw-medium">
                                            {{ $instructorCommission->instructor->name ?? 'Không tìm thấy' }}
                                        </div>
                                        @if ($instructorCommission->instructor->code ?? false)
                                            <small
                                                class="text-muted">{{ $instructorCommission->instructor->code }}</small>
                                        @endif
                                        @if ($instructorCommission->instructor->email ?? false)
                                            <small
                                                class="text-muted">{{ $instructorCommission->instructor->email }}</small>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="input-group" style="width: 120px;">
                                    <input type="number" step="1"
                                        class="form-control input-rate text-center"
                                        data-id="{{ $instructorCommission->id }}"
                                        value="{{ fmod($instructorCommission->rate * 100, 1) == 0
                                            ? number_format($instructorCommission->rate * 100, 0)
                                            : number_format($instructorCommission->rate * 100, 2) }}"
                                        data-old="{{ fmod($instructorCommission->rate * 100, 1) == 0
                                            ? number_format($instructorCommission->rate * 100, 0)
                                            : number_format($instructorCommission->rate * 100, 2) }}"
                                        data-name="{{ $instructorCommission->instructor->name }}" />
                                    <span class="input-group-text bg-light">%</span>
                                </div>
                            </td>
                            <td><i
                                    class="ri-time-line text-muted me-1"></i>{{ $instructorCommission->instructor->created_at->format('d/m/Y H:i') ?? '' }}
                            </td>
                            <td><i
                                    class="ri-time-line text-muted me-1"></i>{{ $instructorCommission->updated_at->format('d/m/Y H:i') ?? '' }}
                            </td>
                            <td>
                                @php
                                    $logs = json_decode($instructorCommission->rate_logs, true);
                                @endphp

                                <button type="button" class="btn btn-sm btn-info"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalLog_{{ $instructorCommission->id }}">
                                    <i class="ri-eye-line"></i>
                                </button>

                            </td>

                           
                        
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{ $instructorCommissions->appends(request()->query())->links() }}
    </div>

    
</div>

