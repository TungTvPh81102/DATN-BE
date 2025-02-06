@extends('layouts.app')
@section('title', $title)
@push('page-css')
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Khóa học đã bán</h4>
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active"><a
                                    href="{{ route('admin.invoices.index') }}">{{ $subTitle }}</a></li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->
        <!-- List-customer -->
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Danh sách khóa học đã bán</h4>
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
                            <button class="btn btn-sm btn-success h-75">Export dữ liệu</button>
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
                                                        value="10000" oninput="updateRange()" data-filter>
                                                    <input type="range" class="form-range w-50" id="amountMaxRange"
                                                        name="amount_max" min="50000000" max="99990000" step="10000"
                                                        value="99990000" oninput="updateRange()" data-filter>
                                                </div>
                                            </li>
                                            <div class="row">
                                                <li class="col-6">
                                                    <div class="mb-2">
                                                        <label for="startDate" class="form-label">Ngày mua</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="created_at" id="dateRequest" data-filter
                                                            value="{{ request()->input('created_at') ?? '' }}">
                                                    </div>
                                                </li>
                                                <li class="col-6">
                                                    <div class="mb-2">
                                                        <label for="endDate" class="form-label">Ngày xác nhận</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="updated_at" id="dateComplete" data-filter
                                                            value="{{ request()->input('updated_at') ?? '' }}">
                                                    </div>
                                                </li>
                                            </div>
                                            <li class="mt-2 d-flex gap-1">
                                                <button class="btn btn-sm btn-success flex-grow-1" id="resetInput"
                                                    type="reset">Reset</button>
                                                <button class="btn btn-sm btn-primary flex-grow-1" id="applyFilter">Áp
                                                    dụng</button>
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
                                <div class="col-md-4">
                                    <label class="form-label">Người mua</label>
                                    <input class="form-control form-control-sm" name="user_name_invoice" type="text"
                                        placeholder="Nhập tên người mua khóa học..."
                                        value="{{ request()->input('user_name_invoice') ?? '' }}" data-advanced-filter>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Mã khóa học</label>
                                    <input class="form-control form-control-sm" name="course_code_invoice" type="text"
                                        placeholder="Nhập mã khóa học..."
                                        value="{{ request()->input('course_code_invoice') ?? '' }}" data-advanced-filter>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tên khóa học</label>
                                    <input class="form-control form-control-sm" name="course_name_invoice" type="text"
                                        placeholder="Nhập tên khóa học..."
                                        value="{{ request()->input('course_name_invoice') ?? '' }}" data-advanced-filter>
                                </div>
                                <div class="mt-3 text-end">
                                    <button class="btn btn-sm btn-success" type="reset">Reset</button>
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
                                            <th>Người mua</th>
                                            <th>Mã khóa học</th>
                                            <th>Tên khóa học</th>
                                            <th>Tổng thanh toán</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày mua</th>
                                            <th>Ngày xác nhận mua</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        @foreach ($invoices as $invoice)
                                            <tr>
                                                <td>{{ $loop->index + 1 }}</td>
                                                <td><span class="text-danger fw-bold">{{ $invoice->user->name }}</span>
                                                </td>
                                                <td>{{ $invoice->course->code }}</td>
                                                <td>{{ Str::limit($invoice->course->name, 40) }}</td>
                                                <td>{{ number_format($invoice->final_total) }} VND</td>
                                                <td>
                                                    <span class="badge bg-primary">Hoàn thành</span>
                                                </td>
                                                <td>{{ optional(\Carbon\Carbon::parse($invoice->created_at))->format('d/m/Y') ?? 'NULL' }}
                                                </td>
                                                <td>{{ optional(\Carbon\Carbon::parse($invoice->updated_at))->format('d/m/Y') ?? 'NULL' }}
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
                    </div>
                    <!-- end card -->
                </div>
            </div>
            <!-- end col -->
        </div>
        <!-- end List-customer -->
    </div>
@endsection

@push('page-scripts')
    <script>
        var routeUrlFilter = "{{ route('admin.invoices.index') }}";

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
        $(document).on('click', '#resetInput', function() {
            $('#amountMinRange').val(0);
            $('#amountMaxRange').val(99990000);
            updateRange();
        });
    </script>
    <script src="{{ asset('assets/js/custom/custom.js') }}"></script>
    <script src="{{ asset('assets/js/common/filter.js') }}"></script>
    <script src="{{ asset('assets/js/common/search.js') }}"></script>
    <script src="{{ asset('assets/js/common/handle-ajax-search&filter.js') }}"></script>
@endpush
