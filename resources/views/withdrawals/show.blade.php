@extends('layouts.app')
@push('page-css')
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css"/>
    <style>
        :root {
            --primary-color: #4e73df;
            --success-color: #1cc88a;
            --warning-color: #f6c23e;
            --danger-color: #e74a3b;
            --secondary-color: #858796;
            --light-bg: #f8f9fc;
            --border-color: #e3e6f0;
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 0.35rem 0.75rem;
            border-radius: 50px;
            font-weight: 500;
            letter-spacing: 0.3px;
            text-transform: uppercase;
        }

        .info-card {
            box-shadow: 0 0.15rem 1.75rem rgba(0, 0, 0, 0.05);
            border-radius: 0.75rem;
            border: 1px solid var(--border-color);
            transition: transform 0.2s, box-shadow 0.2s;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .info-card:hover {
            box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.08);
        }

        .info-card .card-header {
            background-color: #fff;
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-weight: 600;
            font-size: 1.1rem;
            color: #333;
            margin-bottom: 0;
        }

        .info-table {
            margin-bottom: 0;
        }

        .info-table td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        .info-table td:first-child {
            font-weight: 600;
            width: 30%;
            background-color: rgba(78, 115, 223, 0.03);
            color: #4a5568;
        }

        .info-table tr:last-child td {
            border-bottom: none;
        }

        .qr-code-container {
            text-align: center;
            padding: 1.5rem;
            background-color: #fff;
            border-radius: 0.5rem;
            border: 1px dashed var(--border-color);
            margin: 0.5rem;
        }

        .qr-code-container img {
            border-radius: 0.5rem;
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.05);
        }

        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .action-buttons .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.5rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: center;
            min-width: 140px;
            transition: all 0.2s;
        }

        .action-buttons .btn i {
            margin-right: 0.5rem;
            font-size: 1rem;
        }

        .action-buttons .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.1);
        }

        .modal-content {
            border: none;
            border-radius: 0.75rem;
            overflow: hidden;
            box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.15);
        }

        .modal-header {
            background-color: #f8f9fc;
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
        }

        .modal-body {
            padding: 1.5rem;
        }

        .modal-footer {
            border-top: 1px solid var(--border-color);
            padding: 1rem 1.5rem;
        }

        .nav-tabs {
            border-bottom: 1px solid var(--border-color);
        }

        .nav-tabs .nav-link {
            border: none;
            border-bottom: 2px solid transparent;
            color: var(--secondary-color);
            font-weight: 500;
            padding: 0.75rem 1.25rem;
        }

        .nav-tabs .nav-link.active {
            border-color: var(--primary-color);
            color: var(--primary-color);
            background-color: transparent;
        }

        .form-control {
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            padding: 0.75rem 1rem;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.15rem rgba(78, 115, 223, 0.25);
        }

        .amount {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.1rem;
        }

        .admin-comment {
            background-color: #f8f9fc;
            border-radius: 0.5rem;
            padding: 1.25rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        @media (max-width: 768px) {
            .action-buttons {
                flex-direction: column;
            }

            .action-buttons .btn {
                width: 100%;
            }
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">{{ $title ?? '' }}</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>

                            <li class="breadcrumb-item active"><a href="">{{ $subTitle ?? '' }}</a></li>

                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Request information card -->
                <div class="info-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="ri-file-list-3-line mr-2"></i>
                            Thông tin yêu cầu rút tiền #{{ $withDraw->id ?? '' }}
                        </h5>
                        <div>
                            @if ($withDraw && $withDraw->status === 'Đang xử lý')
                                <span class="status-badge bg-warning text-dark">
                                    <i class="ri-time-line mr-1"></i> {{ $withDraw->status }}
                                </span>
                            @elseif ($withDraw && $withDraw->status === 'Chờ xác nhận lại')
                                <span class="status-badge bg-info text-white">
                                    <i class="ri-refresh-line mr-1"></i> {{ $withDraw->status }}
                                </span>
                            @elseif ($withDraw && $withDraw->status === 'Hoàn thành')
                                <span class="status-badge bg-success text-white">
                                    <i class="ri-check-double-line mr-1"></i> {{ $withDraw->status }}
                                </span>
                            @elseif ($withDraw && $withDraw->status === 'Từ chối')
                                <span class="status-badge bg-danger text-white">
                                    <i class="ri-close-circle-line mr-1"></i> {{ $withDraw->status }}
                                </span>
                            @else
                                <span class="status-badge bg-secondary text-white">
                                    <i class="ri-question-line mr-1"></i> {{ $withDraw->status ?? 'Không xác định' }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-borderless info-table mb-0">
                            <tbody>
                            <tr>
                                <td><i class="ri-user-3-line mr-2 text-primary"></i> Người gửi yêu cầu</td>
                                <td>
                                    <img src="{{$withDraw->wallet->user->avatar ?? '' }}" alt="Avatar"
                                         class="user-avatar me-3">
                                    {{ $withDraw->wallet->user->name ?? '' }}</td>
                            </tr>
                            <tr>
                                <td><i class="ri-bank-line mr-2 text-primary"></i> Ngân hàng</td>
                                <td><strong>{{ $withDraw->bank_name ?? '' }}</strong></td>
                            </tr>
                            <tr>
                                <td><i class="ri-bank-card-line mr-2 text-primary"></i> Số tài khoản</td>
                                <td><strong>{{ $withDraw->account_number ?? '' }}</strong></td>
                            </tr>
                            <tr>
                                <td><i class="ri-user-star-line mr-2 text-primary"></i> Chủ tài khoản</td>
                                <td><strong>{{ $withDraw->account_holder ?? '' }}</strong></td>
                            </tr>
                            <tr>
                                <td><i class="ri-money-dollar-circle-line mr-2 text-primary"></i> Số tiền</td>
                                <td><span class="amount">{{ number_format($withDraw->amount ?? 0)  }} VNĐ</span></td>
                            </tr>
                            <tr>
                                <td><i class="ri-calendar-event-line mr-2 text-primary"></i> Ngày gửi yêu cầu</td>
                                <td>{{ $withDraw->request_date ?? '' }}</td>
                            </tr>
                            <tr>
                                <td><i class="ri-file-text-line mr-2 text-primary"></i> Ghi chú</td>
                                <td>{{ $withDraw->note ?? 'Không có ghi chú' }}</td>
                            </tr>
                            @if ($withDraw && $withDraw->completed_date)
                                <tr>
                                    <td><i class="ri-calendar-check-line mr-2 text-primary"></i> Ngày xử lý</td>
                                    <td>{{ $withDraw->completed_date ?? '' }}</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Instructor confirmation card (if confirmed) -->
                @if ($withDraw && $withDraw->instructor_confirmation === 'confirmed')
                    <div class="info-card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="ri-checkbox-circle-line mr-2 text-success"></i>
                                Xác nhận từ giảng viên
                            </h5>
                            <span class="status-badge bg-success text-white">
                                <i class="ri-check-line mr-1"></i> Đã xác nhận
                            </span>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-borderless info-table mb-0">
                                <tbody>
                                <tr>
                                    <td><i class="ri-message-2-line mr-2 text-primary"></i> Phản hồi</td>
                                    <td>{{ $withDraw->instructor_confirmation_note ?? '' }}</td>
                                </tr>
                                <tr>
                                    <td><i class="ri-time-line mr-2 text-primary"></i> Thời gian phản hồi</td>
                                    <td>{{ $withDraw->instructor_confirmation_date ?? '' }}</td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <!-- Actions card -->
                <div class="info-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="ri-settings-4-line mr-2"></i>
                            Hành động
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="action-buttons">
                            <button data-id="{{ $withDraw->id ?? null }}" type="button" class="btn btn-info check-status-btn"
                                    data-bs-toggle="modal" data-bs-target="#transactionModal">
                                <i class="ri-search-line"></i> Kiểm tra trạng thái
                            </button>

                            @if (!is_null($withDraw) && $withDraw->status)
                                @if ($withDraw->status === 'Đang xử lý')
                                    <button type="button" class="btn btn-danger reject-btn" data-bs-toggle="modal"
                                            data-bs-target="#exampleModal" data-action="reject">
                                        <i class="ri-close-circle-line"></i> Từ chối
                                    </button>
                                    <button type="button" class="btn btn-primary approve-btn">
                                        <i class="ri-check-line"></i> Xác nhận
                                    </button>
                                @elseif ($withDraw->status === 'Chờ xác nhận lại')
                                    <button type="button" class="btn btn-primary approve-btn">
                                        <i class="ri-check-double-line"></i> Xác nhận
                                    </button>
                                @endif
                            @endif

                            <a href="{{ route('admin.withdrawals.index') }}" class="btn btn-outline-secondary">
                                <i class="ri-arrow-left-line"></i> Quay lại danh sách
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- QR Code card -->
                <div class="info-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="ri-qr-code-line mr-2"></i>
                            Mã QR
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="qr-code-container">
                            <img class="img-fluid" style="max-height: 250px;"
                                 src="{{ \Illuminate\Support\Facades\Storage::url($withDraw->qr_code ?? '') }}"
                                 alt="QR Code Thanh Toán">
                            <p class="text-muted mt-3 mb-0 small">Quét mã QR để thanh toán nhanh chóng</p>
                        </div>
                    </div>
                </div>

                @if ($withDraw && $withDraw->admin_comment)
                    <div class="info-card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="ri-message-3-line mr-2"></i>
                                Phản hồi từ hệ thống
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="admin-comment">
                                <p class="mb-0">{{ $withDraw->admin_comment ?? '' }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Transaction status card -->
                <div class="info-card">
                    <div class="card-header">
                        <h5 class="card-title">
                            <i class="ri-exchange-line mr-2"></i>
                            Trạng thái giao dịch
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center p-3">
                            @if ($withDraw && $withDraw->status == 'Đã xử lý')
                                <div class="mb-3">
                                    <i class="ri-check-double-line text-success" style="font-size: 3rem;"></i>
                                </div>
                                <h5 class="text-success">Giao dịch đã hoàn thành</h5>
                                <p class="text-muted mb-0">Ngày hoàn thành: {{ $withDraw->completed_date ?? '' }}</p>
                            @elseif ($withDraw && $withDraw->status === 'Từ chối')
                                <div class="mb-3">
                                    <i class="ri-close-circle-line text-danger" style="font-size: 3rem;"></i>
                                </div>
                                <h5 class="text-danger">Giao dịch đã bị từ chối</h5>
                            @else
                                <div class="mb-3">
                                    <i class="ri-time-line text-warning" style="font-size: 3rem;"></i>
                                </div>
                                <h5 class="text-warning">Giao dịch đang xử lý</h5>
                                <p class="text-muted mb-0">Vui lòng xác nhận hoặc từ chối yêu cầu</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Check Status Modal -->
    <div id="transactionModal" class="modal fade" tabindex="-1" aria-labelledby="transactionModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="transactionModalLabel">Chi tiết trạng thái giao dịch</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="transaction-details">
                        <div class="text-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Đang tải...</span>
                            </div>
                            <p>Đang kiểm tra giao dịch...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="confirmForm" action="{{ route('admin.withdrawals.confirmPayment') }}" method="post">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">
                            <i class="ri-edit-line mr-2"></i>
                            Xác nhận thanh toán
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @if ($withDraw && $withDraw->admin_comment)
                            <div class="mb-3">
                                <label class="form-label fw-medium">Phản hồi trước đó:</label>
                                <div class="admin-comment mb-2">
                                    <textarea id="oldComment" readonly rows="3" style="resize: none"
                                              class="form-control bg-light"
                                              placeholder="Phản hồi trước đó">{{ $withDraw->admin_comment ?? '' }}</textarea>
                                </div>
                            </div>
                        @endif
                        <input id="withdrawal_id" type="hidden" value="{{ $withDraw->id ?? null }}">
                        <div>
                            <label class="form-label fw-medium">Nội dung phản hồi:</label>
                            @if($withDraw && $withDraw->status === 'Chờ xác nhận lại')
                                <div class="d-flex justify-content-end mb-2">
                                    <button type="button" id="loadOldComment" class="btn btn-sm btn-secondary">
                                        <i class="ri-file-copy-line mr-1"></i> Sử dụng phản hồi trước đó
                                    </button>
                                </div>
                            @endif
                            <textarea id="comment" rows="4" style="resize: none" class="form-control"
                                      placeholder="Nhập nội dung phản hồi của bạn...">{{ old('admin_comment', '') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            <i class="ri-close-line mr-1"></i> Huỷ
                        </button>
                        <button id="confirmPayment" type="button" class="btn btn-primary">
                            <i class="ri-check-line mr-1"></i> Xác nhận
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('page-scripts')
    <script>
        $(document).ready(function () {
            $(document).on('click', '.check-status-btn', function () {
                var withdrawalId = $(this).data('id');

                var modal = $('#transactionModal');
                modal.modal('show');

                $('#transaction-details').html(`
            <div class="text-center">
                <div class="spinner-border text-primary" role="status" id="loading-spinner">
                    <span class="visually-hidden">Đang tải...</span>
                </div>
                <p>Đang kiểm tra giao dịch...</p>
            </div>
        `);

                $.ajax({
                    url: "{{ route('admin.withdrawals.check-status') }}",
                    type: "POST",
                    data: {
                        withdrawal_id: withdrawalId,
                    },
                    success: function (response) {
                        function formatCurrency(amount) {
                            return new Intl.NumberFormat('vi-VN', {style: 'currency', currency: 'VND'}).format(amount);
                        }

                        let transactionStatusBadge = "";
                        if (response.withdrawal_request.status === "Từ chối") {
                            transactionStatusBadge = `<span class="badge bg-danger">Từ chối</span>`;
                        } else if (response.withdrawal_request.status === "Hoàn thành") {
                            transactionStatusBadge = `<span class="badge bg-success">Hoàn thành</span>`;
                        } else if (response.withdrawal_request.status === "Đang xử lý") {
                            transactionStatusBadge = `<span class="badge bg-warning text-dark">Đang xử lý</span>`;
                        } else {
                            transactionStatusBadge = `<span class="badge bg-secondary">${response.withdrawal_request.status}</span>`;
                        }

                        var details = `
                    <div class="card border-0">
                        <div class="card-body px-0">
                            <ul class="nav nav-tabs" id="transactionTab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="withdrawal-tab" data-bs-toggle="tab" data-bs-target="#withdrawal-tab-pane" type="button" role="tab" aria-controls="withdrawal-tab-pane" aria-selected="true">Yêu Cầu Rút Tiền</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="transaction-tab" data-bs-toggle="tab" data-bs-target="#transaction-tab-pane" type="button" role="tab" aria-controls="transaction-tab-pane" aria-selected="false">Giao Dịch</button>
                                </li>
                                ${response.system_fund_transaction ?
                            `<li class="nav-item" role="presentation">
                        <button class="nav-link" id="fund-tab" data-bs-toggle="tab" data-bs-target="#fund-tab-pane" type="button" role="tab" aria-controls="fund-tab-pane" aria-selected="false">Quỹ Hệ Thống</button>
                    </li>` : ''
                        }
                            </ul>
                            <div class="tab-content p-3 border border-top-0 rounded-bottom" id="transactionTabContent">
                                <div class="tab-pane fade show active" id="withdrawal-tab-pane" role="tabpanel" aria-labelledby="withdrawal-tab" tabindex="0">
                                    <div class="table-responsive">
                                        <table class="table table-borderless">
                                            <tbody>
                                                <tr>
                                                    <td width="30%" class="fw-medium">Số tiền:</td>
                                                    <td class="text-primary fw-bold">${formatCurrency(response.withdrawal_request?.amount)}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-medium">Ngân hàng:</td>
                                                    <td>${response.withdrawal_request?.bank_name}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-medium">Số tài khoản:</td>
                                                    <td>${response.withdrawal_request?.account_number}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-medium">Chủ tài khoản:</td>
                                                    <td>${response.withdrawal_request?.account_holder}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-medium">Ghi chú:</td>
                                                    <td>${response.withdrawal_request?.note || 'Không có ghi chú'}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-medium">Trạng thái:</td>
                                                    <td>${transactionStatusBadge}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-medium">Ngày yêu cầu:</td>
                                                    <td>${response.withdrawal_request?.request_date}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-medium">Ngày hoàn thành:</td>
                                                    <td>${response.withdrawal_request?.completed_date || 'Chưa hoàn thành'}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="transaction-tab-pane" role="tabpanel" aria-labelledby="transaction-tab" tabindex="0">
                                    <div class="table-responsive">
                                        <table class="table table-borderless">
                                            <tbody>
                                                <tr>
                                                    <td width="30%" class="fw-medium">Mã giao dịch:</td>
                                                    <td>${response.transaction?.transaction_code || 'Không có'}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-medium">Loại giao dịch:</td>
                                                    <td>${response.transaction?.type || 'Không có'}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-medium">Số tiền:</td>
                                                    <td class="text-primary fw-bold">${formatCurrency(response.transaction?.amount)}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-medium">Trạng thái:</td>
                                                    <td>${transactionStatusBadge}</td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-medium">Ngày tạo:</td>
                                                    <td>${response.transaction?.created_at || 'Không có'}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                ${response.system_fund_transaction ?
                            `<div class="tab-pane fade" id="fund-tab-pane" role="tabpanel" aria-labelledby="fund-tab" tabindex="0">
                        <div class="table-responsive">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td width="30%" class="fw-medium">Mô tả:</td>
                                        <td>${response.system_fund_transaction?.description}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">Số tiền:</td>
                                        <td class="text-primary fw-bold">${formatCurrency(response.system_fund_transaction?.total_amount)}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-medium">Ngày tạo:</td>
                                        <td>${response.system_fund_transaction?.created_at}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>` :
                            `<div class="tab-pane fade" id="fund-tab-pane" role="tabpanel" aria-labelledby="fund-tab" tabindex="0">
                        <div class="alert alert-warning mb-0">
                            <i class="ri-information-line me-2"></i> Không tìm thấy thông tin quỹ hệ thống liên quan đến giao dịch này.
                        </div>
                    </div>`
                        }
                            </div>
                        </div>
                    </div>
                `;

                        $('#transaction-details').html(details);
                    },
                    error: function (xhr, status, error) {
                        $('#transaction-details').html(`
                    <div class="alert alert-danger">
                        <i class="ri-error-warning-line me-2"></i> Đã xảy ra lỗi trong quá trình kiểm tra giao dịch. Vui lòng thử lại sau.
                    </div>
                `);
                        console.error(xhr.responseText);
                    },
                });
            });

            $('#loadOldComment').on('click', function () {
                var oldComment = $('#oldComment').val();
                $('#comment').val(oldComment);
            });

            $(document).on('click', '.approve-btn', function () {
                const withdrawalId = $('#withdrawal_id').val();

                Swal.fire({
                    title: 'Xác nhận thanh toán?',
                    text: 'Bạn có chắc chắn muốn xác nhận thanh toán này?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Xác nhận',
                    cancelButtonText: 'Hủy',
                    confirmButtonColor: '#405189'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Đang xử lý...',
                            html: 'Vui lòng chờ trong giây lát',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });

                        $.ajax({
                            type: 'POST',
                            url: "{{ route('admin.withdrawals.confirmPayment') }}",
                            data: {
                                withdrawal_id: withdrawalId,
                                action: 'approve',
                                admin_comment: 'Đã xác nhận thanh toán'
                            },
                            success: function (response) {
                                if (response.status === true) {
                                    Swal.fire({
                                        title: 'Thao tác thành công!',
                                        text: response.message,
                                        icon: 'success'
                                    }).then(() => {
                                        window.location.href = "{{ route('admin.withdrawals.index') }}";
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Thao tác thất bại!',
                                        text: response.message,
                                        icon: 'error'
                                    });
                                }
                            },
                            error: function () {
                                Swal.fire({
                                    title: 'Thao tác thất bại!',
                                    text: 'Đã có lỗi xảy ra. Vui lòng thử lại.',
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            });

            $(document).on('click', '.reject-btn', function () {
                const modalTitle = 'Từ chối yêu cầu';
                const confirmButtonText = 'Từ chối';
                const confirmButtonClass = 'btn-danger';

                $('#exampleModalLabel').text(modalTitle);
                $('#confirmPayment').text(confirmButtonText);
                $('#confirmPayment').removeClass('btn-primary btn-danger btn-success').addClass(confirmButtonClass);
                $('#confirmPayment').data('action', 'reject');

                $('#exampleModal').modal('show');
            });

            $('#confirmPayment').on('click', function () {
                const comment = $("#comment").val();
                const action = $(this).data('action');

                if (comment.trim() === '') {
                    Swal.fire({
                        text: "Vui lòng nhập nội dung phản hồi",
                        icon: 'warning'
                    });
                    return;
                }

                const confirmButton = $('#confirmPayment');
                const cancelButton = $('.btn-secondary');
                confirmButton.prop('disabled', true);
                cancelButton.prop('disabled', true);

                const originalButtonText = confirmButton.html();
                confirmButton.html(
                    '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Đang xử lý...'
                );

                $.ajax({
                    type: 'POST',
                    url: $('#confirmForm').attr('action'),
                    data: {
                        withdrawal_id: $('#withdrawal_id').val(),
                        admin_comment: comment,
                        action
                    },
                    success: function (response) {
                        if (response.status === true) {
                            Swal.fire({
                                title: 'Thao tác thành công!',
                                text: response.message,
                                icon: 'success'
                            }).then(() => {
                                $('#exampleModal').modal('hide');
                                window.location.href = "{{ route('admin.withdrawals.index') }}";
                            });
                        } else {
                            Swal.fire({
                                title: 'Thao tác thất bại!',
                                text: response.message,
                                icon: 'error'
                            });
                        }
                    },
                    error: function (error) {
                        Swal.fire({
                            title: 'Thao tác thất bại!',
                            text: 'Đã có lỗi xảy ra. Vui lòng thử lại.',
                            icon: 'error'
                        });
                    },
                    complete: function () {
                        confirmButton.prop('disabled', false);
                        cancelButton.prop('disabled', false);
                        confirmButton.html(originalButtonText);
                    }
                });
            });
        });
    </script>
@endpush
