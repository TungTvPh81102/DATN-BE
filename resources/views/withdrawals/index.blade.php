@extends('layouts.app')
@push('page-css')
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">{{ $title ?? '' }}</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active"><a
                                    href="{{ route('admin.withdrawals.index') }}">{{ $subTitle ?? '' }}</a></li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <!-- social-customer -->
        <div class="row mb-2">
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card text-center h-75">
                    <div class="card-body">
                        <h5 class="card-title">Tổng số yêu cầu rút tiền</h5>
                        <p class="card-text fs-4">{{ $countWithdrawals->total_withdrawals ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card text-center h-75">
                    <div class="card-body">
                        <h5 class="card-title">Yêu cầu rút tiền đang chờ duyệt</h5>
                        <p class="card-text fs-4 text-success">{{ $countWithdrawals->completed_withdrawals ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card text-center h-75">
                    <div class="card-body">
                        <h5 class="card-title">Yêu cầu rút tiền thành công</h5>
                        <p class="card-text fs-4 text-warning">{{ $countWithdrawals->pending_withdrawals ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card text-center h-75">
                    <div class="card-body">
                        <h5 class="card-title">Yêu cầu rút tiền thất bại</h5>
                        <p class="card-text fs-4 text-danger">{{ $countWithdrawals->failed_withdrawals ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- End social-customer -->

        <!-- List-customer -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Danh sách yêu cầu rút tiền</h4>
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
                            <a href="{{ route('admin.withdrawals.export') }}" class="btn btn-sm btn-success h-75">Export
                                dữ
                                liệu</a>
                            <button class="btn btn-sm btn-primary h-75" id="toggleAdvancedSearch">
                                Tìm kiếm nâng cao
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-primary h-75" type="button" id="filterDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-filter-2-line"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown"
                                    style="min-width: 500px;">
                                    <form>
                                        <div class="container">
                                            <li>
                                                <label for="amountRange" class="form-label">Số tiền</label>

                                                <div class="d-flex justify-content-between">
                                                    <span id="amountMin">10,000 VND</span>
                                                    <span id="amountMax">99,999,999 VND</span>
                                                </div>

                                                <div class="d-flex justify-content-between">
                                                    <input type="range" class="form-range w-50" id="amountMinRange"
                                                        name="amount_min" min="10000" max="49990000" step="10000"
                                                        value="{{ request()->input('amount_min') ?? 10000 }}"
                                                        oninput="updateRange()" data-filter>
                                                    <input type="range" class="form-range w-50" id="amountMaxRange"
                                                        name="amount_max" min="50000000" max="99990000" step="10000"
                                                        value="{{ request()->input('amount_max') ?? 99990000 }}"
                                                        oninput="updateRange()" data-filter>
                                                </div>
                                            </li>
                                            <div class="row">
                                                <li class="col-6">
                                                    <div class="mb-2">
                                                        <label for="startDate" class="form-label">Ngày yêu cầu</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="request_date" id="dateRequest" data-filter
                                                            value="{{ request()->input('request_date') ?? '' }}">
                                                    </div>
                                                </li>
                                                <li class="col-6">
                                                    <div class="mb-2">
                                                        <label for="endDate" class="form-label">Ngày xác nhận</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="completed_date" id="dateComplete" data-filter
                                                            value="{{ request()->input('completed_date') ?? '' }}">
                                                    </div>
                                                </li>
                                            </div>
                                            <li class="mt-2 d-flex gap-1">
                                                <button class="btn btn-sm btn-success flex-grow-1" type="reset"
                                                    id="resetFilter">Reset
                                                </button>
                                                <button class="btn btn-sm btn-primary flex-grow-1" id="applyFilter">Áp
                                                    dụng
                                                </button>
                                            </li>
                                        </div>
                                    </form>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- Tìm kiếm nâng cao -->
                    <div id="advancedSearch" class="card-header" style="display:none;">
                        <form>
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Tên chủ tài khoản</label>
                                    <input class="form-control form-control-sm" name="account_holder" type="text"
                                        placeholder="Nhập tên chủ tài khoản..."
                                        value="{{ request()->input('account_holder') ?? '' }}" data-advanced-filter>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Số tài khoản</label>
                                    <input class="form-control form-control-sm" name="account_number" type="text"
                                        placeholder="Nhập tên số tài khoản..."
                                        value="{{ request()->input('account_number') ?? '' }}" data-advanced-filter>
                                </div>
                                <div class="col-md-3">
                                    <label for="statusItem" class="form-label">Trạng thái</label>
                                    <select class="form-select form-select-sm" name="status" id="statusItem"
                                        data-advanced-filter>
                                        <option value="">Chọn trạng thái</option>
                                        <option value="Hoàn thành" @selected(request()->input('status') == 'Hoàn thành')>
                                           Hoàn thành
                                        </option>
                                        <option value="Đang xử lý" @selected(request()->input('status') == 'Đang xử lý')>
                                            Đang xử lý
                                        </option>
                                        <option value="Chờ xác nhận lại" @selected(request()->input('status') == 'Chờ xác nhận lại')>
                                            Chờ xác nhận lại
                                        </option>
                                        <option value="Đã xử lý" @selected(request()->input('status') == 'Đã xử lý')>
                                          Đã xử lý
                                        </option>
                                        <option value="Từ chối" @selected(request()->input('status') == 'Từ chối')>
                                            Từ chối
                                        </option>
                                        <option value="Không xác định" @selected(request()->input('status') == 'Không xác định')>
                                            Không xác định
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label for="bankName" class="form-label">Ngân hàng</label>
                                    <select class="form-select form-select-sm" name="bank_name" id="bankName"
                                        data-advanced-filter>
                                        <option value="">Chọn ngân hàng</option>
                                        @foreach ($supportedBank as $bank)
                                            <option value="{{ $bank->name }}" @selected(request()->input('bank_name') === $bank->name)>
                                                {{ $bank->name . ' (' . $bank->short_name . ')' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mt-3 text-end">
                                    <button class="btn btn-sm btn-success" type="reset" id="resetFilter">Reset</button>
                                    <button class="btn btn-sm btn-primary" id="applyAdvancedFilter">Áp dụng</button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <!-- end card header -->
                    <div class="card-body" id="item_List">
                        <div class="listjs-table" id="customerList">
                            <div class="table-responsive table-card mt-3 mb-1">
                                <table class="table align-middle table-nowrap" id="customerTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>STT</th>
                                            <th>Ngân hàng</th>
                                            <th>Tên chủ tài khoản</th>
                                            <th>Số tài khoản</th>
                                            <th>Số tiền</th>
                                            <th>QR</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày yêu cầu</th>
                                            <th>Ngày xác nhận</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        @foreach ($withdrawals as $withdrawal)
                                            <tr>
                                                <td>{{ $loop->index + 1 }}</td>
                                                <td>{{ \Illuminate\Support\Str::limit($withdrawal->bank_name ?? 'Không có thông tin', 40) }}
                                                </td>
                                                <td>{{ $withdrawal->account_holder ?? 'Không có thông tin' }}</td>
                                                <td><span
                                                        class="text-danger">{{ $withdrawal->account_number ?? 'Không có thông tin' }}</span>
                                                </td>
                                                <td>{{ number_format($withdrawal->amount ?? 0) }} VND</td>
                                                <td>
                                                    <img id="thumbnail-{{ $withdrawal->id }}"
                                                        class="img-thumbnail img-preview" width="50" height="50"
                                                        src="{{ \Illuminate\Support\Facades\Storage::url($withdrawal->qr_code ?? '') }}"
                                                        alt="QR Code {{ $withdrawal->id }}" style="cursor: pointer;" />
                                                </td>
                                                <td>
                                                    @if ($withdrawal->status === 'Hoàn thành')
                                                        <span class="badge bg-success w-100">Hoàn thành</span>
                                                    @elseif($withdrawal->status === 'Đang xử lý')
                                                        <span class="badge bg-warning w-100">Đang xử lý</span>
                                                    @elseif($withdrawal->status === 'Chờ xác nhận lại')
                                                        <span class="badge bg-primary w-100">Chờ xác nhận lại</span>
                                                    @elseif($withdrawal->status === 'Đã xử lý')
                                                        <span class="badge bg-info w-100">Đã xử lý</span>
                                                    @elseif($withdrawal->status === 'Từ chối')
                                                        <span class="badge bg-danger w-100">Từ chối</span>
                                                    @else
                                                        <span class="badge bg-secondary w-100">Không xác định</span>
                                                    @endif
                                                </td>
                                                <td>{!! $withdrawal->request_date
                                                    ? \Carbon\Carbon::parse($withdrawal->request_date)->format('d/m/Y')
                                                    : '<span class="btn btn-sm btn-soft-warning">Không có thông tin</span>' !!}
                                                </td>
                                                <td>{!! $withdrawal->completed_date
                                                    ? \Carbon\Carbon::parse($withdrawal->completed_date)->format('d/m/Y')
                                                    : '<span class="btn btn-sm btn-soft-warning">Chưa xác nhận</span>' !!}
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.withdrawals.show', $withdrawal->id) }}">
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
                                {{ $withdrawals->appends(request()->query())->links() }}
                            </div>
                        </div>
                    </div>
                    <!-- end card -->
                </div>
            </div>
            <!-- end col -->
        </div>
        <!-- end List-customer -->
    </div>

    <div id="imagePreview"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); z-index: 1050; justify-content: center; align-items: center;">
        <img id="previewImage" src="" class="img-fluid"
            style="max-width: 90%; max-height: 90%; border: 3px solid #fff; border-radius: 8px;" />
    </div>
@endsection

@push('page-scripts')
    <script>
        var routeUrlFilter = "{{ route('admin.withdrawals.index') }}";

        function updateRange() {
            var minValue = $('#amountMinRange').val();
            var maxValue = $('#amountMaxRange').val();
            document.getElementById('amountMin').textContent = formatCurrency(minValue) + ' VND';
            document.getElementById('amountMax').textContent = formatCurrency(maxValue) + ' VND';
        }

        function formatCurrency(value) {
            return value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        }

        updateRange();

        $(document).on('click', '#resetFilter', function() {
            window.location = routeUrlFilter;
        });

        $(document).ready(function() {
            $('.img-preview').on('click', function() {
                const imageUrl = $(this).attr('src');
                $('#previewImage').attr('src', imageUrl);
                $('#imagePreview').fadeIn();
            });

            $('#imagePreview').on('click', function() {
                $(this).fadeOut();
            });
        });
    </script>
    <script src="{{ asset('assets/js/custom/custom.js') }}"></script>
    <script src="{{ asset('assets/js/common/filter.js') }}"></script>
    <script src="{{ asset('assets/js/common/search.js') }}"></script>
    <script src="{{ asset('assets/js/common/handle-ajax-search&filter.js') }}"></script>
@endpush
