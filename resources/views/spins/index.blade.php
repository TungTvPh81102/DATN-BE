@extends('layouts.app')

@push('page-css')
    <style>
        .card {
            margin-bottom: 20px;
            border-radius: 10px;
        }

        .card-header {
            border-radius: 10px 10px 0 0;
        }

        .card-body {
            padding: 20px;
        }

        .table {
            font-size: 14px;
        }

        .form-control-sm {
            max-width: 100px;
        }

        canvas {
            max-width: 100%;
        }

        .row-flex {
            display: flex;
            flex-wrap: wrap;
        }

        .col-70 {
            flex: 0 0 65%;
            max-width: 65%;
            padding: 0 10px;
        }

        .col-30 {
            flex: 0 0 35%;
            max-width: 35%;
            padding: 0 10px;
        }

        .filter-form {
            margin-bottom: 15px;
        }

        .text-muted {
            color: #6c757d !important;
        }

        .list-group-item {
            border-radius: 5px;
            margin-bottom: 5px;
        }

        .bg-light {
            background-color: #f8f9fa !important;
        }

        .fs-4 {
            font-size: 1.5rem;
        }

        .table-scrollable {
            max-height: 425px;
            overflow-y: auto;
            overflow-x: hidden;
            position: relative;
        }

        .table-scrollable table {
            margin-bottom: 0;
        }

        .table-scrollable thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 1;
        }

        .table-scrollable::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        .table-scrollable::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .table-scrollable::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .table-scrollable::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
@endpush

@php
    $title = 'Quản lý Vòng Quay May Mắn';
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
                            <li class="breadcrumb-item active"><a
                                    href="{{ route('admin.spins.index') }}">{{ $title ?? '' }}</a></li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal cảnh báo không đủ phần thưởng hoặc quà hiện vật -->
        @if ($showConfigWarning)
            <div class="modal fade" id="configWarningModal" tabindex="-1" aria-labelledby="configWarningModalLabel"
                aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title fw-bold" id="configWarningModalLabel">
                                <i class="bi bi-exclamation-triangle me-2"></i>Cảnh báo: Cấu hình không hợp lệ
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-warning mb-3" role="alert">
                                <ul class="list-unstyled mb-0">
                                    @if (!$hasEnoughSpinConfigTypes)
                                        <li class="mb-2">
                                            <i class="bi bi-dash-circle text-danger me-2"></i>
                                            Vòng quay chưa đủ 3 loại phần thưởng. Thiếu:
                                            <ul class="list-group list-group-flush mt-1">
                                                @foreach ($missingTypeNames as $typeName)
                                                    <li class="list-group-item border-0 ps-4">{{ $typeName }}</li>
                                                @endforeach
                                            </ul>
                                        </li>
                                    @endif
                                    @if (!$hasEnoughGifts)
                                        <li class="mb-2">
                                            <i class="bi bi-dash-circle text-danger me-2"></i>
                                            Số lượng quà hiện vật chưa đủ:
                                            <span class="badge bg-info ms-1">{{ $currentSelectedGiftsCount }}</span> /
                                            <span class="badge bg-success">{{ $requiredGifts }}</span>
                                            (Cần thêm {{ $requiredGifts - $currentSelectedGiftsCount }} quà)
                                        </li>
                                    @endif
                                    @if (!$isProbabilityValid)
                                        <li class="mb-2">
                                            <i class="bi bi-dash-circle text-danger me-2"></i>
                                            Tổng tỷ lệ trúng thưởng hiện tại:
                                            <span class="badge bg-danger">{{ number_format($totalProbability, 2) }}%</span>
                                            (Yêu cầu: 100%)
                                        </li>
                                    @endif
                                </ul>
                            </div>
                            <p class="text-muted fst-italic">Vui lòng hoàn thiện cấu hình để vòng quay có thể hoạt động.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                <i class="bi bi-x-circle me-1"></i>Đóng
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        <!-- Cấu hình tỷ lệ trúng và Danh sách quà hiện vật -->
        <div class="row row-flex">
            <!-- Cấu hình tỷ lệ trúng -->
            <div class="col-70">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h3>Cấu hình tỷ lệ trúng (Tổng: {{ $totalProbability }}%)</h3>
                        <form action="{{ route('admin.spins.toggle-status') }}" method="POST">
                            @csrf
                            <div class="form-check form-switch form-switch-success">
                                <input class="form-check-input" role="switch"
                                       type="checkbox" 
                                       name="status" 
                                       value="active"
                                       onchange="this.form.submit()"
                                       {{ $spinSetting->status === 'active' ? 'checked' : '' }}>
                                <label class="form-check-label">Kích hoạt vòng quay</label>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <!-- Nút thêm ô quà trong vòng quay -->
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#addSpinConfigModal">
                                Thêm ô quà trong vòng quay
                            </button>
                        </div>
                        <table class="table align-middle table-nowrap"
                            style="border-left: 1px solid #dee2e6; border-right: 1px solid #dee2e6;">
                            <thead class="table-light">
                                <tr>
                                    <th>Loại</th>
                                    <th>Tên</th>
                                    <th>Số ô</th>
                                    <th>Tỷ lệ</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($spinConfigs as $config)
                                    <tr data-id="{{ $config->id }}" data-type="spin_config">
                                        <td>{{ $config->type }}</td>
                                        <td>{{ $config->name }}</td>
                                        <td>{{ $config->cells ?? '1' }}</td>
                                        <td>{{ number_format($config->probability, 0) }}%</td>
                                        <td>
                                            <form action="{{ route('admin.spins.spin-config.update', $config->id) }}"
                                                method="POST" class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="number" name="probability" value="{{ number_format($config->probability, 0) }}"
                                                    step="0.01" min="0" max="100"
                                                    class="form-control form-control-sm d-inline" required>
                                                <button type="submit" class="btn btn-primary btn-sm">Cập nhật</button>
                                            </form>
                                            <a href="{{ route('admin.spins.deleteSpinConfig', $config->id) }}"
                                                type="button" class="btn btn-danger btn-sm delete-spin-config">
                                                <span class="ri-delete-bin-7-line"></span>
                                            </a>


                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                                @foreach ($gifts as $gift)
                                    <tr data-id="{{ $gift->id }}" data-type="gift">
                                        <td>Gift</td>
                                        <td>{{ $gift->name }}</td>
                                        <td>{{ $gift->cells ?? '1' }}</td>
                                        <td>{{ number_format($gift->probability, 0) }}%</td>
                                        <td>
                                            <form action="{{ route('admin.spins.gift.update', $gift->id) }}" method="POST"
                                                class="d-inline">
                                                @csrf
                                                @method('PUT')
                                                <input type="number" name="probability" value="{{ $gift->probability }}"
                                                    step="0.01" min="0" max="100"
                                                    class="form-control form-control-sm d-inline" required>
                                                <input type="hidden" name="name" value="{{ $gift->name }}">
                                                <input type="hidden" name="stock" value="{{ $gift->stock }}">
                                                <input type="hidden" name="description" value="{{ $gift->description }}">
                                                <input type="hidden" name="image_url" value="{{ $gift->image_url }}">
                                                <input type="hidden" name="is_active" value="{{ $gift->is_active }}">
                                                <button type="submit" class="btn btn-primary btn-sm">Cập nhật</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Danh sách quà hiện vật -->
            <div class="col-30">
                <div class="card">
                    <div class="card-header">
                        <h3>Danh sách quà hiện vật</h3>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <!-- Nút thêm quà hiện vật -->
                            <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                data-bs-target="#addGiftModal">
                                Thêm quà hiện vật
                            </button>
                        </div>
                        <table class="table align-middle"
                            style="border-left: 1px solid #dee2e6; border-right: 1px solid #dee2e6;">
                            <thead class="table-light">
                                <tr>
                                    <th>Tên quà</th>
                                    <th>Số lượng</th>
                                    <th>Hình ảnh</th>
                                    <th>Kích hoạt</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($giftsAll as $gift)
                                    <tr>
                                        <td>{{ $gift->name }}</td>
                                        <td>{{ $gift->stock }}</td>
                                        <td>
                                            @if ($gift->thumbnail)
                                                <img src="{{ Storage::url($gift->thumbnail) }}"
                                                    alt="{{ $gift->name }}" style="max-width: 50px;">
                                            @else
                                                Không có ảnh
                                            @endif
                                        </td>
                                        <td>
                                            <div class="form-check form-switch form-switch-warning">
                                                <input class="form-check-input popular-course-toggle" 
                                                       role="switch"
                                                       type="checkbox" 
                                                       data-gift-id="{{ $gift->id }}"
                                                       {{ $gift->is_selected ? 'checked' : '' }}>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.spins.gift.delete', $gift->id) }}" type="button"
                                                class="btn btn-sm btn-danger delete-gift-form"
                                                data-id="{{ $gift->id }}">
                                                <span class="ri-delete-bin-7-line"></span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal thêm quà hiện vật -->
        <div class="modal fade" id="addGiftModal" tabindex="-1" aria-labelledby="addGiftModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addGiftModalLabel">Thêm quà hiện vật</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('admin.spins.gift.store') }}" method="POST" class="row g-3"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="col-md-12">
                                <label for="name" class="form-label">Tên quà</label>
                                <input type="text" name="name" value="{{ old('name') }}" class="form-control"
                                    placeholder="Tên quà" required>
                            </div>
                            <div class="col-md-6">
                                <label for="stock" class="form-label">Số lượng</label>
                                <input type="number" name="stock" value="{{ old('stock') }}" class="form-control"
                                    placeholder="Số lượng" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label for="probability" class="form-label">Tỷ lệ (%)</label>
                                <input type="number" name="probability" value="{{ old('probability') }}"
                                    class="form-control" placeholder="Tỷ lệ (%)" step="0.01" min="0"
                                    max="100" required>
                            </div>
                            <div class="col-md-12">
                                <label for="image_url" class="form-label">Ảnh</label>
                                <input type="file" name="image" class="form-control"
                                    placeholder="chọn ảnh hiện vật" id="imageInput">
                            </div>
                            <div class="image-preview-container" style="display:none;">
                                <img id="imagePreview" src="" alt="Image preview" class="img-fluid mt-2 w-25" />
                            </div>
                            <div class="col-md-12">
                                <label for="description" class="form-label">Mô tả</label>
                                <textarea name="description" class="form-control" placeholder="Mô tả">{{ old('description') }}</textarea>
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary w-100">Thêm quà</button>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal thêm ô quà trong vòng quay -->
        <div class="modal fade" id="addSpinConfigModal" tabindex="-1" aria-labelledby="addSpinConfigModalLabel"
            aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addSpinConfigModalLabel">Thêm ô quà trong vòng quay</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form action="{{ route('admin.spins.spin-config.store') }}" method="POST" class="row g-3">
                            @csrf
                            <div class="col-md-12">
                                <label for="type" class="form-label">Loại</label>
                                <select name="type" value="{{ old('type') }}" class="form-select">
                                    <option value="">Chọn loại quà</option>
                                    @foreach ($spinTypes as $spinType)
                                        <option value="{{ $spinType->name }}">{{ $spinType->display_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-12">
                                <label for="probability" class="form-label">Tỷ lệ (%)</label>
                                <input type="number" name="probability" value="{{ old('probability') }}"
                                    class="form-control" placeholder="Tỷ lệ (%)" step="0.01" min="0"
                                    max="100" required>
                            </div>
                            <div class="col-md-12">
                                <label for="cells" class="form-label">Số ô quà</label>
                                <input type="int" name="cells" value="{{ old('cells') }}"
                                    class="form-control" placeholder="Số ô quà trong vòng quay">
                            </div>
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary w-100">Thêm ô quà</button>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tổng số lượt quay và Danh sách người trúng -->
        <div class="row row-flex">
            <!-- Tổng số lượt quay -->
            <!-- Tổng số lượt quay -->
            <div class="col-70">
                <div class="card">
                    <div class="card-header text-white">
                        <h3 class="mb-0">Thống kê tổng quan</h3>
                    </div>
                    <div class="card-body">
                        <!-- Bộ lọc -->
                        <div class="row">
                            <!-- Thống kê tổng quan -->
                            <div class="col-md-4 mb-4">
                                <div class="d-flex flex-column"> <!-- Thay đổi từ 'row' thành 'd-flex flex-column' -->
                                    <div class="mb-3">
                                        <!-- Thay 'col-md-3' bằng 'mb-3' để thêm khoảng cách giữa các thẻ -->
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Tổng số lượt quay</h6>
                                                <p class="card-text fs-4">{{ $totalSpins }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Tổng số lượt trúng</h6>
                                                <p class="card-text fs-4">{{ $totalWinners }}</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body text-center">
                                                <h6 class="card-title">Tỷ lệ trúng thưởng</h6>
                                                <p class="card-text fs-4">{{ number_format($winRate, 2) }}%</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- Biểu đồ phân bố quà trúng -->
                            <div class="col-md-8 mb-4">
                                <h5 class="text-muted">Phân bố quà đã trúng</h5>
                                <div class="card">
                                    <div class="card-body">
                                        <canvas id="winDistributionChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Biểu đồ -->
                        <div class="row">
                            <!-- Biểu đồ số lượt quay -->
                            <div class="mb-4">
                                <h5 class="text-muted">Bộ lọc</h5>
                                <form method="GET" action="{{ route('admin.spins.index') }}"
                                    class="filter-form row g-3">
                                    <div class="col-md-3">
                                        <label for="filter_type" class="form-label">Loại lọc</label>
                                        <select name="filter_type" id="filter_type" class="form-control"
                                            onchange="this.form.submit()">
                                            <option value="day"
                                                {{ request('filter_type', 'day') === 'day' ? 'selected' : '' }}>Theo ngày
                                            </option>
                                            <option value="month"
                                                {{ request('filter_type') === 'month' ? 'selected' : '' }}>Theo tháng
                                            </option>
                                            <option value="year"
                                                {{ request('filter_type') === 'year' ? 'selected' : '' }}>Theo năm</option>
                                        </select>
                                    </div>
                                    @if (request('filter_type', 'day') === 'day')
                                        <div class="col-md-3">
                                            <label for="filter_date" class="form-label">Chọn ngày</label>
                                            <input type="date" name="filter_date"
                                                value="{{ request('filter_date', now()->format('Y-m-d')) }}"
                                                class="form-control" onchange="this.form.submit()">
                                        </div>
                                    @elseif (request('filter_type') === 'month')
                                        <div class="col-md-3">
                                            <label for="filter_month" class="form-label">Chọn tháng</label>
                                            <input type="month" name="filter_month"
                                                value="{{ request('filter_month', now()->format('Y-m')) }}"
                                                class="form-control" onchange="this.form.submit()">
                                        </div>
                                    @elseif (request('filter_type') === 'year')
                                        <div class="col-md-3">
                                            <label for="filter_year" class="form-label">Chọn năm</label>
                                            <select name="filter_year" class="form-control"
                                                onchange="this.form.submit()">
                                                @for ($year = now()->year; $year >= now()->year - 5; $year--)
                                                    <option value="{{ $year }}"
                                                        {{ request('filter_year', now()->year) == $year ? 'selected' : '' }}>
                                                        {{ $year }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                    @endif
                                </form>
                            </div>
                            <div class="">
                                <h5 class="text-muted">Số lượt quay theo thời gian</h5>
                                <div class="card">
                                    <div class="card-body">
                                        <canvas id="spinChart"></canvas>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>

            <!-- Danh sách người trúng quà hiện vật -->
            <div class="col-30">
                <div class="card">
                    <div class="card-header">
                        <h3>Danh sách người trúng quà hiện vật</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Tên người dùng</th>
                                    <th>Quà trúng</th>
                                    <th>Thời gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($giftWinners as $winner)
                                    <tr>
                                        <td>{{ $winner->user->name ?? 'Không xác định' }}</td>
                                        <td>{{ $winner->reward_name }}</td>
                                        <td>{{ $winner->spun_at }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- Ô Lịch sử quay của người chơi (mới) -->
                <div class="card mb-4">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0 text-white">Lịch sử quay của người chơi</h5>
                    </div>
                    <div class="card-body">
                        <!-- Form tìm kiếm -->
                        <form method="GET" action="{{ route('admin.spins.index') }}" class="mb-3">
                            <div class="input-group">
                                <input type="text" name="search" class="form-control"
                                    placeholder="Tìm kiếm theo tên người chơi hoặc phần thưởng..."
                                    value="{{ request('search') }}">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-search"></i> Tìm kiếm
                                </button>
                                @if (request('search'))
                                    <a href="{{ route('admin.spins.index') }}" class="btn btn-secondary ms-2">
                                        <i class="bi bi-x"></i> Xóa bộ lọc
                                    </a>
                                @endif
                            </div>
                        </form>

                        <!-- Bảng lịch sử quay -->
                        @if ($spinHistories->isEmpty())
                            <p class="text-muted">Chưa có lịch sử quay.</p>
                        @else
                            <div class="table-responsive table-card table-scrollable">
                                <table class="table align-middle table-hover">
                                    <thead>
                                        <tr>
                                            <th>STT</th>
                                            <th>Thời gian</th>
                                            <th>Người chơi</th>
                                            <th>Loại phần thưởng</th>
                                            <th>Phần thưởng</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($spinHistories as $index => $history)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ \Carbon\Carbon::parse($history->spun_at)->format('d/m/Y H:i:s') }}
                                                </td>
                                                <td>{{ $history->user ? $history->user->name : 'N/A' }}</td>
                                                <td>
                                                    @switch($history->reward_type)
                                                        @case('gift')
                                                            <span class="badge bg-success">Quà hiện vật</span>
                                                        @break

                                                        @case('coupon')
                                                            <span class="badge bg-info">Mã giảm giá</span>
                                                        @break

                                                        @case('spin')
                                                            <span class="badge bg-warning">Lượt quay</span>
                                                        @break

                                                        @case('no_reward')
                                                            <span class="badge bg-secondary">Không trúng</span>
                                                        @break

                                                        @default
                                                            <span class="badge bg-secondary">{{ $history->reward_type }}</span>
                                                    @endswitch
                                                </td>
                                                <td>{{ $history->reward_name }}</td>

                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        // Lắng nghe sự kiện thay đổi khi người dùng chọn tệp
        document.getElementById("imageInput").addEventListener("change", function(event) {
            const file = event.target.files[0]; // Lấy tệp đã chọn

            if (file) {
                // Tạo URL cho tệp được chọn
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Lấy URL của ảnh
                    const imageUrl = e.target.result;

                    // Cập nhật ảnh xem trước
                    const imagePreview = document.getElementById("imagePreview");
                    imagePreview.src = imageUrl;

                    // Hiển thị phần tử ảnh xem trước
                    document.querySelector(".image-preview-container").style.display = "block";
                };
                reader.readAsDataURL(file); // Đọc tệp ảnh dưới dạng URL
            } else {
                // Nếu không có tệp nào được chọn, ẩn ảnh xem trước
                document.querySelector(".image-preview-container").style.display = "none";
            }
        });
        document.addEventListener('DOMContentLoaded', function() {
            @if ($showConfigWarning)
                const configWarningModal = new bootstrap.Modal(document.getElementById('configWarningModal'), {
                    backdrop: 'static', // Không cho phép đóng modal bằng cách nhấp ra ngoài
                    keyboard: false // Không cho phép đóng modal bằng phím Esc
                });
                configWarningModal.show();
            @endif
        });
        // Hàm đóng modal thủ công
        function forceCloseModal(modal) {
            try {
                const bsModal = bootstrap.Modal.getInstance(modal);
                if (bsModal) {
                    bsModal.hide();
                    console.log('Modal closed using Bootstrap Modal instance');
                } else {
                    console.warn('Bootstrap Modal instance not found, closing manually');
                }

                modal.classList.remove('show');
                modal.style.display = 'none';
                document.body.classList.remove('modal-open');
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => backdrop.remove());
                document.body.style.overflow = 'auto';
                document.body.style.paddingRight = '0px';
                console.log('Modal closed successfully');
            } catch (error) {
                console.error('Error while closing modal:', error);
            }
        }

        // Biểu đồ tổng số lượt quay
        const filterType = "{{ request('filter_type', 'day') }}";
        let labels = [];
        let data = [];

        if (filterType === 'day') {
            labels = [
                @foreach ($spinStatsDay as $stat)
                    "{{ $stat->date }}",
                @endforeach
            ];
            data = [
                @foreach ($spinStatsDay as $stat)
                    {{ $stat->spins }},
                @endforeach
            ];
        } else if (filterType === 'month') {
            labels = [
                @foreach ($spinStatsMonth as $stat)
                    "{{ $stat->month }}/{{ $stat->year }}",
                @endforeach
            ];
            data = [
                @foreach ($spinStatsMonth as $stat)
                    {{ $stat->spins }},
                @endforeach
            ];
        } else if (filterType === 'year') {
            labels = [
                @foreach ($spinStatsYear as $stat)
                    "{{ $stat->year }}",
                @endforeach
            ];
            data = [
                @foreach ($spinStatsYear as $stat)
                    {{ $stat->spins }},
                @endforeach
            ];
        }

        const spinChart = new Chart(document.getElementById('spinChart'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Số lượt quay',
                    data: data,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
        // Biểu đồ phân bố quà trúng
        const winDistributionLabels = [
            @foreach ($groupedWinners as $type => $count)
                "{{ ucfirst($type) }}",
            @endforeach
        ];
        const winDistributionData = [
            @foreach ($groupedWinners as $type => $count)
                {{ $count }},
            @endforeach
        ];
        const winDistributionChart = new Chart(document.getElementById('winDistributionChart'), {
            type: 'pie',
            data: {
                labels: winDistributionLabels.map(label => `${label}`),
                datasets: [{
                    label: 'Phân bố quà trúng',
                    data: winDistributionData,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.6)',
                        'rgba(54, 162, 235, 0.6)',
                        'rgba(255, 206, 86, 0.6)',
                        'rgba(75, 192, 192, 0.6)',
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)',
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        align: 'center',
                        labels: {
                            boxWidth: 20,
                            padding: 15
                        }
                    }
                }
            }

        });


        $('.delete-gift-form').on('click', function(e) {
            e.preventDefault(); // Ngăn form submit mặc định

            const button = $(this);
            const url = button.attr('href'); // Lấy URL từ href
            const row = button.closest('tr'); // Lấy hàng trong bảng
            const id = button.data('id'); // Lấy ID từ data-id

            // Hiển thị SweetAlert để xác nhận
            Swal.fire({
                title: 'Bạn có chắc chắn?',
                text: 'Bạn có muốn xóa quà hiện vật này không?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Gửi yêu cầu AJAX
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                // Hiển thị thông báo thành công
                                showToast('success', response.message ||
                                    'Xóa quà hiện vật thành công!');
                                row.remove();
                            } else {
                                // Hiển thị thông báo lỗi từ server (nếu success: false)
                                showToast('error', response.message ||
                                    'Có lỗi xảy ra, vui lòng thử lại');
                            }
                        },
                        error: function(xhr, status, error) {
                            // Lấy thông điệp lỗi từ phản hồi của server
                            const errorMessage = xhr.responseJSON && xhr.responseJSON.message ?
                                xhr.responseJSON.message :
                                'Có lỗi xảy ra, vui lòng thử lại';

                            // Hiển thị thông báo lỗi
                            showToast('error', errorMessage);
                        }
                    });
                }
            });
        });

        // Xử lý xóa phần thưởng trong vòng quay (delete-spin-config)
        $('.delete-spin-config').on('click', function(e) {
            e.preventDefault(); // Ngăn hành vi mặc định của thẻ <a>

            const button = $(this);
            const url = button.attr('href'); // Lấy URL từ href
            const row = button.closest('tr'); // Lấy hàng trong bảng
            const probability = parseFloat(row.find('td:eq(2)').text()); // Lấy tỷ lệ trúng từ cột thứ 3
            const id = button.data('id'); // Lấy ID từ data-id

            // Hiển thị SweetAlert để xác nhận
            Swal.fire({
                title: 'Bạn có chắc chắn?',
                text: 'Bạn có muốn xóa ô quà này khỏi vòng quay không?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Gửi yêu cầu AJAX
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                // Hiển thị thông báo thành công
                                showToast('success', response.message ||
                                    'Xóa ô quà khỏi vòng quay thành công!');

                                // Xóa hàng khỏi bảng
                                row.remove();

                                // Cập nhật tổng tỷ lệ trúng
                                const totalProbabilityElement = $('.card-header h3');
                                const currentTotal = parseFloat(totalProbabilityElement.text()
                                    .match(/Tổng: ([\d.]+)%/)[1]);
                                const newTotal = currentTotal - probability;
                                totalProbabilityElement.text(
                                    `Cấu hình tỷ lệ trúng (Tổng: ${newTotal.toFixed(4)}%)`);
                            } else {
                                // Hiển thị thông báo lỗi từ server (nếu success: false)
                                showToast('error', response.message ||
                                    'Có lỗi xảy ra, vui lòng thử lại');
                            }
                        },
                        error: function(xhr, status, error) {
                            // Lấy thông điệp lỗi từ phản hồi của server
                            const errorMessage = xhr.responseJSON && xhr.responseJSON.message ?
                                xhr.responseJSON.message :
                                'Có lỗi xảy ra, vui lòng thử lại';

                            // Hiển thị thông báo lỗi
                            showToast('error', errorMessage);

                            // Ghi log để debug (tùy chọn)
                            console.error('Error:', error);
                            console.error('Status:', status);
                            console.error('Response:', xhr.responseJSON);
                        }
                    });
                }
            });
        });
        $('.popular-course-toggle').on('change', function() {
                var giftId = $(this).data('gift-id');
                var isSelected = $(this).is(':checked') ? 1 : 0;

                $.ajax({
                    url: '{{ route("admin.spins.toggle-selection", ["type" => "gift", "id" => "__ID__"]) }}'.replace('__ID__', giftId),
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        is_selected: isSelected
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message); // Sử dụng toastr hoặc alert
                        } else {
                            // Nếu thất bại, đảo ngược trạng thái checkbox
                            $(this).prop('checked', !isSelected);
                            toastr.error(response.message);
                        }
                    }.bind(this), // Bind this để giữ context của checkbox
                    error: function(xhr) {
                        $(this).prop('checked', !isSelected); // Đảo ngược nếu lỗi
                        toastr.error('Có lỗi xảy ra: ' + (xhr.responseJSON?.message || 'Không xác định'));
                    }.bind(this)
                });
            });
    </script>
@endpush
