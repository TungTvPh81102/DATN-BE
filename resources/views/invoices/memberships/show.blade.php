@extends('layouts.app')
@push('page-css')
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css" />
    <style>
        :root {
            --primary-color: #405189;
            --primary-light: rgb(82, 84, 210);
            --secondary-color: #10b981;
            --border-color: #e9e9ef;
            --light-bg: #f9fafb;
            --text-muted: #64748b;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            border: none;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
        }

        .invoice-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            background-color: rgba(79, 70, 229, 0.03);
        }

        .invoice-detail {
            padding: 1.5rem;
        }

        .membership-icon {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(45deg, var(--primary-color), var(--primary-light));
            box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2);
        }

        .membership-badge {
            background: linear-gradient(45deg, var(--primary-color), var(--primary-light));
            color: white;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            display: inline-block;
            font-weight: 500;
            font-size: 0.8rem;
        }

        .benefit-list {
            list-style: none;
            padding-left: 0;
            margin-bottom: 0;
        }

        .benefit-list li {
            padding: 8px 0;
            border-bottom: 1px dashed var(--border-color);
            display: flex;
            align-items: center;
        }

        .benefit-list li:last-child {
            border-bottom: none;
        }

        .benefit-list li i {
            color: var(--primary-color);
            margin-right: 10px;
            font-size: 1rem;
        }

        .card {
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            border: none;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: white;
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
        }

        .card-title {
            color: #333;
            font-weight: 600;
            margin-bottom: 0;
        }

        .card-title i {
            color: var(--primary-color);
            margin-right: 0.5rem;
        }

        .activity-card {
            border-radius: 12px;
            overflow: hidden;
        }

        .activity-card .card-header {
            display: flex;
            align-items: center;
            background-color: white;
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .activity-card .card-header .header-icon {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-right: 0.75rem;
        }

        .activity-card .card-title {
            margin-bottom: 0;
            font-weight: 600;
            color: #333;
        }

        .activity-timeline-container {
            padding: 2rem;
            position: relative;
        }

        .timeline {
            position: relative;
            max-width: 1200px;
            margin: 0 auto;
        }

        .timeline::after {
            content: '';
            position: absolute;
            width: 3px;
            background-color: var(--border-color);
            top: 0;
            bottom: 0;
            left: 50%;
            margin-left: -1px;
            border-radius: 1.5px;
        }

        .timeline-item {
            padding: 10px 40px;
            position: relative;
            width: 50%;
            box-sizing: border-box;
        }

        .timeline-item::after {
            content: '';
            position: absolute;
            width: 25px;
            height: 25px;
            right: -12px;
            top: 20px;
            border-radius: 50%;
            z-index: 1;
            box-shadow: 0 0 0 4px white, 0 0 0 6px var(--border-color);
        }

        .timeline-left {
            left: 0;
        }

        .timeline-right {
            left: 50%;
        }

        .timeline-left::after {
            right: -12px;
        }

        .timeline-right::after {
            left: -14px;
        }

        .timeline-content {
            padding: 20px;
            background-color: white;
            position: relative;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.07);
            border-left: 4px solid var(--primary-color);
        }

        .timeline-created::after {
            background-color: #10b981;
        }

        .timeline-extend::after {
            background-color: #0ea5e9;
        }

        .timeline-cancelled::after {
            background-color: #ef4444;
        }

        .timeline-item-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .timeline-badge {
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            color: white;
            display: flex;
            align-items: center;
        }

        .timeline-badge i {
            margin-right: 5px;
            font-size: 1rem;
        }

        .timeline-date {
            display: flex;
            align-items: center;
            color: var(--text-muted);
            font-size: 0.85rem;
        }

        .timeline-date span {
            display: flex;
            align-items: center;
            margin-left: 10px;
        }

        .timeline-date i {
            margin-right: 4px;
        }

        .timeline-details {
            background-color: var(--light-bg);
            border-radius: 8px;
            padding: 15px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .detail-row:last-child {
            margin-bottom: 0;
        }

        .detail-label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }

        .detail-value {
            font-weight: 500;
            font-size: 0.9rem;
        }

        .detail-value.amount {
            font-weight: 600;
        }

        .detail-value.amount.positive {
            color: #10b981;
        }

        .detail-value.amount.negative {
            color: #ef4444;
        }

        .empty-timeline {
            text-align: center;
            padding: 3rem 1rem;
            background-color: var(--light-bg);
            border-radius: 10px;
            color: var(--text-muted);
        }

        .empty-timeline-icon {
            font-size: 3rem;
            color: var(--border-color);
            margin-bottom: 1rem;
        }

        .empty-timeline-message {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }

        .empty-timeline-submessage {
            font-size: 0.9rem;
        }

        @media screen and (max-width: 768px) {
            .timeline::after {
                left: 31px;
            }

            .timeline-item {
                width: 100%;
                padding-left: 70px;
                padding-right: 25px;
            }

            .timeline-right {
                left: 0;
            }

            .timeline-left::after,
            .timeline-right::after {
                left: 18px;
            }

            .timeline-content {
                border-left: 4px solid var(--primary-color);
                border-right: none;
            }

            .timeline-item-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .timeline-date {
                margin-top: 10px;
            }
        }

        .status-badge {
            padding: 0.35rem 0.75rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-active {
            background-color: var(--secondary-color);
            color: white;
        }

        .payment-info {
            background-color: var(--light-bg);
            border-radius: 8px;
            padding: 1rem 1.25rem;
            border-left: 3px solid var(--primary-color);
        }

        .admin-actions {
            background-color: var(--light-bg);
            border-radius: 8px;
            padding: 1.25rem;
            margin-top: 1.5rem;
        }

        .admin-actions .btn {
            margin-right: 0.5rem;
            margin-bottom: 0.5rem;
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            background-color: rgba(79, 70, 229, 0.05);
            color: var(--text-muted);
            font-weight: 600;
        }

        .section-header {
            display: flex;
            align-items: center;
            margin-bottom: 1rem;
            color: #333;
            font-weight: 600;
        }

        .section-header i {
            color: var(--primary-color);
            margin-right: 0.5rem;
            font-size: 1.1rem;
        }

        .membership-progress {
            height: 6px;
            border-radius: 3px;
            background-color: var(--border-color);
            margin-top: 8px;
            margin-bottom: 8px;
            overflow: hidden;
        }

        .membership-progress-bar {
            height: 100%;
            background-color: var(--primary-color);
            border-radius: 3px;
        }

        .duration-badge {
            background-color: rgba(79, 70, 229, 0.1);
            color: var(--primary-color);
            padding: 0.25rem 0.6rem;
            border-radius: 4px;
            font-weight: 500;
            font-size: 0.85rem;
            margin-left: 0.5rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">{{ $title ?? '' }}</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.invoices.index') }}">Gói thành viên đã
                                    bán</a>
                            </li>
                            <li class="breadcrumb-item active">{{ $subTitle ?? ' ' }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Thông tin hóa đơn <span class="text-muted">#{{ $invoice->code }}</span>
                        </h5>
                        <div>
                            <a href="{{ route('admin.invoices.memberships.index') }}" class="btn btn-sm btn-outline-primary me-2">
                                <i class="ri-arrow-left-line"></i> Quay lại
                            </a>
                            <a href="#" class="btn btn-sm btn-outline-info me-2">
                                <i class="ri-printer-line"></i> In hóa đơn
                            </a>
                            <a href="#" class="btn btn-sm btn-outline-success">
                                <i class="ri-mail-send-line"></i> Gửi email
                            </a>
                        </div>
                    </div>
                    <div class="invoice-header">
                        <div class="row">
                            <div class="col-md-6 d-flex">
                                <div class="membership-icon me-3">
                                    <i class="ri-vip-crown-fill text-white" style="font-size: 36px;"></i>
                                </div>
                                <div>
                                    <span class="membership-badge mb-2">
                                        <i class="ri-vip-crown-line me-1"></i> Thành viên VIP
                                    </span>
                                    <h4 class="mt-2 mb-1">{{ $invoice->membershipPlan->name }}
                                        <span class="duration-badge">{{ $invoice->membershipPlan->duration_months }}
                                            tháng</span>
                                    </h4>
                                    <p class="text-muted mb-1">Mã hóa đơn: <strong>{{ $invoice->code }}</strong></p>
                                </div>
                            </div>
                            <div class="col-md-6 text-md-end">
                                <h5 class="mb-2">Trạng thái thanh toán</h5>
                                <span class="badge bg-success font-size-14 px-3 py-2">{{ $invoice->status }}</span>
                                <p class="text-muted mt-3 mb-1">Ngày thanh toán:
                                    <strong>{{ \Carbon\Carbon::parse($invoice->created_at)->format('d/m/Y H:i:s') }}</strong>
                                </p>
                                <p class="text-muted mb-0">Phương thức:
                                    <strong>{{ strtoupper($invoice->payment_method) }}</strong></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="ri-secure-payment-line"></i> Thông tin thanh toán</h5>
                    </div>
                    <div class="card-body">
                        <div class="payment-info">
                            <div class="row mb-2">
                                <div class="col-7">Giá gói membership:</div>
                                <div class="col-5 text-end">{{ number_format($invoice->amount) }} VND</div>
                            </div>
                            @if ($invoice->coupon_discount)
                                <div class="row mb-2">
                                    <div class="col-7">Mã giảm giá:</div>
                                    <div class="col-5 text-end text-success">{{ $invoice->coupon_code }}</div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-7">Giảm giá:</div>
                                    <div class="col-5 text-end text-success">
                                        -{{ number_format($invoice->coupon_discount) }} VND</div>
                                </div>
                            @endif
                            <hr class="my-2">
                            <div class="row">
                                <div class="col-7"><strong>Tổng thanh toán:</strong></div>
                                <div class="col-5 text-end text-primary">
                                    <strong>{{ number_format($invoice->final_amount) }} VND</strong></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="ri-user-3-line"></i> Thông tin người mua</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="{{ $invoice->user->avatar }}" alt="{{ $invoice->user->name }}"
                                class="user-avatar me-3">
                            <div>
                                <h6 class="mb-1">{{ $invoice->user->name }}</h6>
                                <p class="mb-0 text-muted">{{ $invoice->user->email }}</p>
                            </div>
                            <div class="ms-auto">
                                <a href="#" class="btn btn-sm btn-outline-primary">
                                    <i class="ri-user-search-line"></i>
                                </a>
                            </div>
                        </div>
                        <div class="payment-info">
                            <div class="row mb-2">
                                <div class="col-5">Mã người dùng:</div>
                                <div class="col-7 text-end">{{ $invoice->user->code }}</div>
                            </div>
                            <div class="row">
                                <div class="col-5">Ngày đăng ký:</div>
                                <div class="col-7 text-end">
                                    {{ \Carbon\Carbon::parse($invoice->user->created_at)->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="ri-calendar-check-line"></i> Thông tin đăng ký</h5>
                    </div>
                    <div class="card-body">
                        <div class="payment-info mb-0">
                            <div class="row mb-2">
                                <div class="col-5">Trạng thái đăng ký:</div>
                                <div class="col-7 text-end">
                                    @if ($invoice->membershipPlan->membershipSubscription->status === 'active')
                                        <span class="status-badge status-active">Hoạt động</span>
                                    @elseif ($invoice->membershipPlan->membershipSubscription->status === 'cancelled')
                                        <span class="status-badge status-cancelled">Đã hủy</span>
                                    @elseif ($invoice->membershipPlan->membershipSubscription->status === 'expired')
                                        <span class="status-badge status-expired">Đã hết hạn</span>
                                    @else
                                        <span class="status-badge status-unknown">Không xác định</span>
                                    @endif
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5">Ngày bắt đầu:</div>
                                <div class="col-7 text-end">
                                    {{ \Carbon\Carbon::parse($invoice->membershipPlan->membershipSubscription->start_date)->format('d/m/Y') }}
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-5">Ngày kết thúc:</div>
                                <div class="col-7 text-end">
                                    {{ \Carbon\Carbon::parse($invoice->membershipPlan->membershipSubscription->end_date)->format('d/m/Y') }}
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-5">Thời gian còn lại:</div>
                                <div class="col-7 text-end text-primary fw-bold">
                                    @php
                                        $daysLeft = \Carbon\Carbon::parse(
                                            $invoice->membershipPlan->membershipSubscription->end_date,
                                        )->diffInDays(\Carbon\Carbon::now());
                                        $monthsLeft = floor($daysLeft / 30);
                                        $remainingDays = $daysLeft % 30;
                                    @endphp
                                    {{ $monthsLeft }} tháng {{ $remainingDays }} ngày
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="ri-file-list-3-line"></i> Chi tiết gói membership</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th style="width: 20%">Tên gói</th>
                                    <td>{{ $invoice->membershipPlan->name }}</td>
                                </tr>
                                <tr>
                                    <th>Mã gói</th>
                                    <td>{{ $invoice->membershipPlan->code }}</td>
                                </tr>
                                <tr>
                                    <th>Thời hạn</th>
                                    <td>{{ $invoice->membershipPlan->duration_months }} tháng</td>
                                </tr>
                                <tr>
                                    <th>Giá gói</th>
                                    <td>{{ number_format($invoice->membershipPlan->price) }} VND</td>
                                </tr>
                                <tr>
                                    <th>Mô tả</th>
                                    <td>{{ $invoice->membershipPlan->description }}</td>
                                </tr>
                                <tr>
                                    <th>Trạng thái gói</th>
                                    <td>
                                        @if ($invoice->membershipPlan->status === 'active')
                                            <span class="badge bg-success">Đang hoạt động</span>
                                        @else
                                            <span class="badge bg-danger">Không hoạt động</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card activity-card">
                    <div class="card-header">
                        <i class="ri-history-line header-icon"></i>
                        <h5 class="card-title">Lịch sử</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="activity-timeline-container">
                            @php
                                $activityLogs = $invoice->membershipPlan->membershipSubscription->activity_logs;
                            @endphp
                            
                            @if(!empty($activityLogs) && count($activityLogs) > 0)
                                <div class="timeline">
                                    @foreach ($activityLogs as $index => $log)
                                        @php
                                            $actionType = strtolower($log['action'] ?? 'created');
                                            $badgeClass = '';
                                            $iconClass = '';
                                            $actionText = '';
                                            $positionClass = $index % 2 == 0 ? 'timeline-left' : 'timeline-right';
                                            $timelineTypeClass = 'timeline-' . $actionType;
                                            
                                            switch ($actionType) {
                                                case 'created':
                                                    $badgeClass = 'bg-success';
                                                    $iconClass = 'ri-shopping-cart-line';
                                                    $actionText = 'Mua gói thành viên';
                                                    break;
                                                case 'extend':
                                                    $badgeClass = 'bg-info';
                                                    $iconClass = 'ri-refresh-line';
                                                    $actionText = 'Gia hạn gói';
                                                    break;
                                                case 'cancelled':
                                                    $badgeClass = 'bg-danger';
                                                    $iconClass = 'ri-close-circle-line';
                                                    $actionText = 'Hủy gói';
                                                    break;
                                                default:
                                                    $badgeClass = 'bg-primary';
                                                    $iconClass = 'ri-information-line';
                                                    $actionText = $log['action'];
                                                    break;
                                            }
                                            
                                            $formattedDate = \Carbon\Carbon::parse($log['data']['start_date'])->format('d/m/Y');
                                            $formattedTime = \Carbon\Carbon::parse($log['data']['start_date'])->format('H:i');
                                        @endphp
                                        
                                        <div class="timeline-item {{ $positionClass }} {{ $timelineTypeClass }}">
                                            <div class="timeline-content">
                                                <div class="timeline-item-header">
                                                    <div class="timeline-badge {{ $badgeClass }}">
                                                        <i class="{{ $iconClass }}"></i>
                                                        {{ $actionText }}
                                                    </div>
                                                    <div class="timeline-date">
                                                        <span>
                                                            <i class="ri-calendar-line"></i>
                                                            {{ $formattedDate }}
                                                        </span>
                                                        <span>
                                                            <i class="ri-time-line"></i>
                                                            {{ $formattedTime }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <div class="timeline-details">
                                                    <div class="detail-row">
                                                        <div class="detail-label">Mã giao dịch:</div>
                                                        <div class="detail-value">{{ $log['data']['transaction_id'] }}</div>
                                                    </div>
                                                    <div class="detail-row">
                                                        <div class="detail-label">Số tiền:</div>
                                                        <div class="detail-value amount {{ $actionType === 'cancelled' ? 'negative' : 'positive' }}">
                                                            {{ number_format($log['data']['amount']) }} VND
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-timeline">
                                    <i class="ri-history-line empty-timeline-icon"></i>
                                    <div class="empty-timeline-message">Chưa có lịch sử hoạt động</div>
                                    <div class="empty-timeline-submessage">Các hoạt động liên quan đến gói thành viên sẽ xuất hiện tại đây</div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title"><i class="ri-tools-fill"></i> Thao tác</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex flex-wrap">
                                    <a href="#" class="btn btn-sm btn-primary me-2 mb-2">
                                        <i class="ri-refresh-line me-1"></i> Gia hạn gói
                                    </a>
                                    <a href="#" class="btn btn-sm btn-warning me-2 mb-2">
                                        <i class="ri-time-line me-1"></i> Điều chỉnh thời hạn
                                    </a>
                                    <a href="#" class="btn btn-sm btn-success me-2 mb-2">
                                        <i class="ri-mail-send-line me-1"></i> Gửi thông báo
                                    </a>
                                    <a href="#" class="btn btn-sm btn-danger me-2 mb-2">
                                        <i class="ri-close-circle-line me-1"></i> Hủy gói
                                    </a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="payment-info mb-0">
                                    <h6 class="mb-2">Thay đổi kỳ hạn gói:</h6>
                                    <div class="d-flex align-items-center mt-3">
                                        <select class="form-select me-2" style="height: 38px;">
                                            <option value="3">3 tháng</option>
                                            <option value="6" selected>6 tháng</option>
                                            <option value="12">12 tháng</option>
                                        </select>
                                        <button class="btn btn-primary" style="height: 38px; width: 100px">Áp
                                            dụng</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
