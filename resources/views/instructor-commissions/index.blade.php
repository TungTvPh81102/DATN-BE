@extends('layouts.app')
@push('page-css')
    <link href="{{ asset('assets/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .stat-card {
            border-radius: 16px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
            transition: all 0.3s ease;
            overflow: hidden;
            height: 160px;
            border: none;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.12);
        }

        .stat-icon {
            font-size: 2rem;
            height: 52px;
            width: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            margin-bottom: 16px;
        }

        .card-title-custom {
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #5a6a85;
        }

        .action-buttons .btn {
            border-radius: 8px;
            padding: 0.6rem 1.2rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        }

        .stat-value {
            font-size: 2.2rem;
            font-weight: 700;
            margin-bottom: 0;
            background: linear-gradient(45deg, #3b82f6, #6366f1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .input-rate {
            border: 1.5px solid #e2e8f0;
            border-radius: 6px 0 0 6px;
            font-weight: 600;
            color: #334155;
            padding: 8px 12px;
            transition: all 0.2s;
        }

        .input-rate:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.2);
        }

        .input-group-text {
            background-color: #f8fafc;
            color: #64748b;
            font-weight: 600;
            border: 1.5px solid #e2e8f0;
            border-left: none;
        }

        .modal-content {
            border: none;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            border-bottom: 1px solid #f1f5f9;
            padding: 18px 24px;
            background-color: #f8fafc;
            border-radius: 16px 16px 0 0;
        }

        .modal-footer {
            border-top: 1px solid #f1f5f9;
            padding: 16px 24px;
            background-color: #f8fafc;
            border-radius: 0 0 16px 16px;
        }

        .modal-body {
            padding: 24px;
        }

        .history-log-list li {
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: all 0.2s;
            border-bottom: 1px solid #f1f5f9;
        }

        .history-log-list li:hover {
            background-color: #f8fafc;
            transform: translateX(2px);
        }

        .history-log-list .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 500;
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 16px;
        }

        .alert-info {
            background-color: #eff6ff;
            color: #1e40af;
        }

        .method-card {
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .method-card.active {
            border-color: #6366f1;
            background-color: #f5f7ff;
        }

        .method-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
    </style>
@endpush
@php
    $title = 'Quản lý và phân chia doanh thu';
@endphp
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">{{ $title ?? '' }}</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">{{ $title ?? '' }}</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">{{ $subTitle }}</h4>
                        <div class="d-flex gap-2">
                            <div class="col-sm">
                                <div class="d-flex justify-content-sm-end">
                                    <div class="search-box ms-2">
                                        <input type="text" name="search_full" class="form-control search h-75"
                                            placeholder="Tìm kiếm..." data-search>
                                        <button id="search-full" class="h-75 ri-search-line search-icon m-0 p-0 border-0"
                                            style="background: none;"></button>
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary btn-sm h-75" data-bs-toggle="modal"
                                data-bs-target="#massUpdateModal">
                                <i class="ri-edit-2-line align-bottom"></i> Cập nhật hàng loạt
                            </button>


                            <a class="btn btn-sm btn-success h-75" href="{{ route('admin.courses.exportFile') }}">Export dữ
                                liệu</a>


                            <button class="btn btn-sm btn-primary h-75" id="toggleAdvancedSearch">
                                Tìm kiếm nâng cao
                            </button>

                        </div>

                    </div>

                    <!-- Tìm kiếm nâng cao -->

                    <div id="advancedSearch" class="card-header" style="display:none;">
                        <form>
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Mã giảng viên</label>
                                    <input class="form-control form-control-sm" name="instructor_code" type="text"
                                        value="{{ request()->input('instructor_code') ?? '' }}"
                                        placeholder="Nhập mã giảng viên..." data-advanced-filter>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Tên giảng viên</label>
                                    <input class="form-control form-control-sm" name="instructor_name" type="text"
                                        value="{{ request()->input('instructor_name') ?? '' }}"
                                        placeholder="Nhập tên giảng viên..." data-advanced-filter>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Email</label>
                                    <input class="form-control form-control-sm" name="instructor_email" type="text"
                                        value="{{ request()->input('instructor_email') ?? '' }}" placeholder="Nhập email..."
                                        data-advanced-filter>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Lợi nhuận</label>
                                    <input class="form-control form-control-sm" name="commission_amount" type="text"
                                        value="{{ request()->input('commission_amount') ?? '' }}"
                                        placeholder="Nhập lợi nhuận..." data-advanced-filter>
                                </div>
                                <div class="col-md-3 mt-2">
                                    <label class="form-label">Ngày tham gia</label>
                                    <input type="date" class="form-control form-control-sm" name="start_date"
                                        value="{{ request()->input('start_date') ?? '' }}" data-advanced-filter>
                                </div>

                                <div class="mt-3 text-end">
                                    <button class="btn btn-sm btn-success" type="reset" id="resetFilter">Reset</button>
                                    <button class="btn btn-sm btn-primary" id="applyAdvancedFilter">Áp dụng</button>
                                </div>

                            </div>
                        </form>
                    </div>

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


                                            <div class="modal fade" id="modalLog_{{ $instructorCommission->id }}"
                                                tabindex="-1"
                                                aria-labelledby="modalLogLabel_{{ $instructorCommission->id }}"
                                                aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-scrollable modal-md">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title"
                                                                id="modalLogLabel_{{ $instructorCommission->id }}">
                                                                Lịch sử thao tác
                                                            </h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal" aria-label="Đóng"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            @if ($logs)
                                                                @php
                                                                    usort($logs, function ($a, $b) {
                                                                        return strtotime($b['changed_at']) <=>
                                                                            strtotime($a['changed_at']);
                                                                    });
                                                                @endphp
                                                                <ul class="list-unstyled mb-0 small history-log-list">
                                                                    @foreach ($logs as $log)
                                                                        @php
                                                                            $formattedOldRate = isset($log['old_rate'])
                                                                                ? (fmod($log['old_rate'] * 100, 1) == 0
                                                                                    ? number_format(
                                                                                        $log['old_rate'] * 100,
                                                                                        0,
                                                                                    )
                                                                                    : number_format(
                                                                                        $log['old_rate'] * 100,
                                                                                        2,
                                                                                    ))
                                                                                : 'N/A';

                                                                            $formattedNewRate = isset($log['new_rate'])
                                                                                ? (fmod($log['new_rate'] * 100, 1) == 0
                                                                                    ? number_format(
                                                                                        $log['new_rate'] * 100,
                                                                                        0,
                                                                                    )
                                                                                    : number_format(
                                                                                        $log['new_rate'] * 100,
                                                                                        2,
                                                                                    ))
                                                                                : 'N/A';

                                                                            $noteClass = '';
                                                                            if (isset($log['note'])) {
                                                                                if (
                                                                                    strpos($log['note'], 'Tăng') !==
                                                                                    false
                                                                                ) {
                                                                                    $noteClass = 'text-success';
                                                                                } elseif (
                                                                                    strpos($log['note'], 'Giảm') !==
                                                                                    false
                                                                                ) {
                                                                                    $noteClass = 'text-danger';
                                                                                } else {
                                                                                    $noteClass = 'text-muted';
                                                                                }
                                                                            }
                                                                        @endphp
                                                                        <li>
                                                                            <div
                                                                                class="d-flex justify-content-between align-items-center mb-2">
                                                                                <span class="badge bg-light text-dark">
                                                                                    <i class="ri-calendar-line me-1"></i>
                                                                                    {{ \Carbon\Carbon::parse($log['changed_at'])->format('d/m/Y H:i') }}
                                                                                </span>
                                                                                <span
                                                                                    class="{{ $noteClass }} fw-medium">{{ $log['note'] ?? 'Thay đổi tỷ lệ' }}</span>
                                                                            </div>
                                                                            <div
                                                                                class="d-flex justify-content-between align-items-center">
                                                                                @if (isset($log['old_rate']))
                                                                                    <div class="d-flex align-items-center">
                                                                                        <span
                                                                                            class="text-muted">{{ $formattedOldRate }}%</span>
                                                                                        <i
                                                                                            class="ri-arrow-right-line mx-2"></i>
                                                                                        <span
                                                                                            class="badge bg-primary">{{ $formattedNewRate }}%</span>
                                                                                    </div>
                                                                                @else
                                                                                    <span
                                                                                        class="badge bg-primary">{{ $formattedNewRate }}%</span>
                                                                                @endif
                                                                                <span class="text-muted small">
                                                                                    <i class="ri-user-line me-1"></i>
                                                                                    {{ $log['user_name'] ?? 'Hệ thống tự động đánh giá' }}
                                                                                </span>
                                                                            </div>
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            @else
                                                                <em>Không có lịch sử thay đổi</em>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{ $instructorCommissions->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="massUpdateModal" tabindex="-1" aria-labelledby="massUpdateModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="massUpdateModalLabel">
                        <i class="ri-settings-line me-2"></i>Cập nhật hoa hồng hàng loạt
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="ri-information-line me-1"></i> Hệ thống sẽ cập nhật hoa hồng cho tất cả các giảng viên đã
                        được chọn.
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Phương thức cập nhật</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="updateMethod" id="methodFixedValue"
                                    value="fixed" checked>
                                <label class="form-check-label" for="methodFixedValue">
                                    <i class="ri-price-tag-3-line me-1"></i> Tỷ lệ cố định
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="updateMethod" id="methodIncrement"
                                    value="increment">
                                <label class="form-check-label" for="methodIncrement">
                                    <i class="ri-add-line me-1"></i> Tăng thêm
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="updateMethod" id="methodDecrement"
                                    value="decrement">
                                <label class="form-check-label" for="methodDecrement">
                                    <i class="ri-subtract-line me-1"></i> Giảm bớt
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="rateValue" class="form-label">Giá trị hoa hồng</label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="rateValue" min="0" max="100"
                                step="1" placeholder="Nhập giá trị" value="60">
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text" id="updateDescription">Thiết lập giá trị hoa hồng cố định</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="ri-close-line me-1"></i> Hủy
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmMassUpdate">
                        <i class="ri-check-line me-1"></i> Cập nhật
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('page-scripts')
    <script src="{{ asset('assets/js/custom/custom.js') }}"></script>
    <script src="{{ asset('assets/js/common/checkall-option.js') }}"></script>
    <script src="{{ asset('assets/js/common/delete-all-selected.js') }}"></script>
    <script src="{{ asset('assets/js/common/restore-all-selected.js') }}"></script>
    <script src="{{ asset('assets/js/common/filter.js') }}"></script>
    <script src="{{ asset('assets/js/common/search.js') }}"></script>
    <script src="{{ asset('assets/js/common/resetFilter.js') }}"></script>
    <script src="{{ asset('assets/js/common/handle-ajax-search&filter.js') }}"></script>
    <script>
        var routeUrlFilter = "{{ route('admin.instructor-commissions.index') }}";
        var routeDeleteAll = "{{ route('admin.coupons.destroy', ':itemID') }}";

        function updateRange() {
            let rangeValue = document.getElementById("amountMinRange").value;
            document.getElementById("amountMin").textContent = rangeValue;
        }

        $(document).on('click', '#resetFilter', function() {
            window.location = routeUrlFilter;
        });

        $(document).ready(function() {
            $('.input-rate').on('blur', function() {
                var $this = $(this);
                var id = $this.data('id');
                var newValue = parseFloat($this.val());
                var oldValue = parseFloat($this.data('old'));
                var nameInstructor = $this.data('name');

                if (isNaN(newValue) || !Number.isInteger(newValue)) {
                    toastr.error('Hoa hồng mới phải là một số Nguyên!');
                    $this.val(oldValue);
                    return;
                }

                if (newValue < 10) {
                    toastr.error('Hoa hồng không được giảm thấp hơn 10%');
                    $this.val(oldValue);
                    return;
                }

                if (newValue > 90) {
                    toastr.error('Hoa hồng không được tăng cao hơn 90%');
                    $this.val(oldValue);
                    return;
                }

                if (newValue === oldValue) {
                    return;
                }

                var note = newValue > oldValue ?
                    `tăng hoa hồng của giảng viên <b style="color:red">${nameInstructor}</b> từ <b>${oldValue}%</b> lên <b>${newValue}%</b>` :
                    `giảm hoa hồng của giảng viên <b style="color:red">${nameInstructor}</b> từ <b>${oldValue}%</b> xuống <b>${newValue}%</b>`;

                Swal.fire({
                    title: `Xác nhận thay đổi hoa hồng`,
                    html: `Bạn có chắc chắn muốn ${note} không?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Xác nhận',
                    cancelButtonText: 'Không',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('admin.instructor-commissions.update') }}",
                            type: 'PUT',
                            data: {
                                id: id,
                                rate: newValue / 100,
                            },
                            success: function(res) {
                                toastr.success('Cập nhật thành công');
                                updateTimeline(id, res.data);
                                $this.data('old', newValue);
                            },
                            error: function(err) {
                                toastr.error(err.responseJSON?.message ||
                                    'Cập nhật thất bại!');
                                $this.val(oldValue);
                            }
                        });
                    } else {
                        $this.val(oldValue);
                    }
                });
            });

            $('#rateValue').val(60);
            $('input[name="updateMethod"]').on('change', function() {
                var method = $('input[name="updateMethod"]:checked').val();

                if (method === 'fixed') {
                    $('#rateValue').val(60);
                    $('#rateValue').prop('readonly', true);
                    $('#updateDescription').text('Thiết lập giá trị hoa hồng cố định 60%');
                } else if (method === 'increment') {
                    $('#rateValue').val('');
                    $('#rateValue').prop('readonly', false);
                    $('#updateDescription').text('Tăng thêm giá trị % cho hoa hồng hiện tại');
                } else if (method === 'decrement') {
                    $('#rateValue').val('');
                    $('#rateValue').prop('readonly', false);
                    $('#updateDescription').text('Giảm bớt giá trị % từ hoa hồng hiện tại');
                }
            });

            $('#massUpdateModal').on('show.bs.modal', function() {
                var selectedIds = [];

                $('input[name="itemID"]:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    $(this).modal('hide');

                    toastr.warning('Vui lòng chọn ít nhất một giảng viên để cập nhật');
                    return;
                }
            });

            $('#confirmMassUpdate').on('click', function() {
                var selectedIds = [];

                $('input[name="itemID"]:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    toastr.warning('Vui lòng chọn ít nhất một giảng viên để cập nhật');
                    return;
                }

                var method = $('input[name="updateMethod"]:checked').val();
                var value = method === 'fixed' ? 60 : parseFloat($('#rateValue').val());
                if (method === 'fixed' && (value < 10 || value > 90)) {
                    toastr.error('Giá trị hoa hồng cố định phải nằm trong khoảng 10% đến 90%');
                    return;
                }

                if (method !== 'fixed' && (isNaN(value) || value < 0 || value > 80)) {
                    toastr.error('Vui lòng nhập giá trị hợp lệ (10-90)');
                    return;
                }

                var confirmText = '';
                switch (method) {
                    case 'fixed':
                        confirmText = `thiết lập hoa hồng cố định ${value}%`;
                        break;
                    case 'increment':
                        confirmText = `tăng hoa hồng thêm ${value}% `;
                        break;
                    case 'decrement':
                        confirmText = `giảm hoa hồng đi ${value}% `;
                        break;
                }

                Swal.fire({
                    title: 'Xác nhận cập nhật hàng loạt',
                    html: `Bạn có chắc muốn ${confirmText} cho <b>${selectedIds.length}</b> giảng viên đã chọn không?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Xác nhận',
                    cancelButtonText: 'Hủy',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Đang cập nhật...',
                            text: 'Vui lòng đợi trong giây lát',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            allowEnterKey: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            url: "{{ route('admin.instructor-commissions.bulk-update') }}",
                            type: 'POST',
                            data: {
                                ids: selectedIds,
                                method: method,
                                value: value,
                            },
                            success: function(response) {
                                Swal.close();

                                if (response.success) {
                                    Swal.fire({
                                        title: 'Thành công!',
                                        text: `Đã cập nhật ${response.updated} giảng viên thành công`,
                                        icon: 'success',
                                        confirmButtonText: 'Đóng'
                                    }).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    toastr.error(response.message || 'Đã xảy ra lỗi');
                                }
                            },
                            error: function(err) {
                                Swal.close();
                                toastr.error(err.responseJSON?.message ||
                                    'Đã xảy ra lỗi khi cập nhật');
                            }
                        });
                    }
                });
            });

            $('#checkAll').on('change', function() {
                $('input[name="itemID"]').prop('checked', $(this).prop('checked'));
            });

            $(document).on('change', 'input[name="itemID"]', function() {
                if ($('input[name="itemID"]:checked').length === $('input[name="itemID"]').length) {
                    $('#checkAll').prop('checked', true);
                } else {
                    $('#checkAll').prop('checked', false);
                }
            });

            $('.input-rate').on('click', function() {
                $(this).select();
            });

            $('.input-rate').on('keypress', function(e) {
                if (e.which === 13) {
                    $(this).blur();
                }
            });

            $('#massUpdateModal').on('hidden.bs.modal', function() {
                $('#rateValue').val('');
                $('input[name="updateMethod"][value="fixed"]').prop('checked', true).trigger('change');
            });
        });
    </script>
@endpush
