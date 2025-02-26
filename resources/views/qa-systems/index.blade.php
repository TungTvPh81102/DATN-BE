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
                            <li class="breadcrumb-item active"><a href="">{{ $subTitle ?? '' }}</a></li>
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
                        <h4 class="card-title mb-0">{{ $subTitle ?? '' }}</h4>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                data-bs-target="#importModal">Import dữ liệu</button>

                            <a href="{{ route('admin.withdrawals.export') }}" class="btn btn-sm btn-success h-75">Export
                                dữ
                                liệu</a>
                            <button class="btn btn-sm btn-primary h-75" id="toggleAdvancedSearch">
                                Tìm kiếm nâng cao
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-primary" type="button" id="filterDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-filter-2-line"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown"
                                    style="min-width: 500px;">
                                    <div class="container">
                                        <div class="row">
                                            <li class="col-6">
                                                <div class="mb-2">
                                                    <label for="startDate" class="form-label">Ngày bắt đầu</label>
                                                    <input type="date" class="form-control form-control-sm"
                                                        name="startDate" id="dateRequest" data-filter
                                                        value="{{ request()->input('startDate') ?? '' }}">
                                                </div>
                                            </li>
                                            <li class="col-6">
                                                <div class="mb-2">
                                                    <label for="endDate" class="form-label">Ngày kết thúc</label>
                                                    <input type="date" class="form-control form-control-sm"
                                                        name="endDate" id="dateComplete" data-filter
                                                        value="{{ request()->input('endDate') ?? '' }}">
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
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- end card header -->

                    <!-- Tìm kiếm nâng cao -->
                    <div id="advancedSearch" class="card-header" style="display:none;">
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">Tiêu đề</label>
                                <input class="form-control form-control-sm" name="title" type="text"
                                    placeholder="Nhập tiêu đề..." value="{{ request()->input('title') ?? '' }}"
                                    data-advanced-filter>
                            </div>
                            <div class="col-md-4">
                                <label for="answer_type" class="form-label">Loại câu hỏi</label>
                                <select class="form-select form-select-sm" name="answer_type" id="answer_type"
                                    data-advanced-filter>
                                    <option value="">Chọn loại câu hỏi</option>
                                    <option value="single" @selected(request()->input('answer_type') === 'single')>
                                        Chọn một
                                    </option>
                                    <option value="multiple" @selected(request()->input('answer_type') === 'multiple')>Đang
                                        Chọn nhiều
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="statusItem" class="form-label">Trạng thái</label>
                                <select class="form-select form-select-sm" name="status" id="statusItem"
                                    data-advanced-filter>
                                    <option value="">Chọn trạng thái</option>
                                    <option value="0" @selected(request()->input('status') === '0')>
                                        Không hoạt động
                                    </option>
                                    <option value="1" @selected(request()->input('status') === '1')>
                                        Hoạt động
                                    </option>
                                </select>
                            </div>
                            <div class="mt-3 text-end">
                                <button class="btn btn-sm btn-success" type="reset" id="resetFilter">Reset</button>
                                <button class="btn btn-sm btn-primary" id="applyAdvancedFilter">Áp dụng</button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="listjs-table" id="customerList">
                            <div class="row g-4 mb-3">
                                <div class="col-sm-auto">
                                    <div>
                                        <a href="{{ route('admin.qa-systems.create') }}">
                                            <button type="button" class="btn btn-primary add-btn">
                                                <i class="ri-add-line align-bottom me-1"></i> Thêm mới
                                            </button>
                                        </a>
                                        <button class="btn btn-danger" id="deleteSelected">
                                            <i class="ri-delete-bin-2-line"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-sm">
                                    <div class="d-flex justify-content-sm-end">
                                        <div class="search-box ms-2">

                                            <input type="text" name="search_full" class="form-control search h-75"
                                                placeholder="Tìm kiếm..." data-search>
                                            <button id="search-full"
                                                class="h-75 ri-search-line search-icon m-0 p-0 border-0"
                                                style="background: none;"></button>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive table-card mt-3 mb-1" id="item_List">
                                <table class="table align-middle table-nowrap" id="customerTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 50px;">
                                                <input type="checkbox" id="checkAll">
                                            </th>
                                            <th>#</th>
                                            <th>Tiêu đề</th>
                                            <th>Loại câu hỏi</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày tạo</th>
                                            <th>Hành Động</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        @foreach ($qaSystems as $qaSystem)
                                            <tr>
                                                <th scope="row">0
                                                    <div class="form-check">
                                                        <input class="form-check-input" id="checkAll" type="checkbox"
                                                            name="itemID" value="{{ $qaSystem->id }}">

                                                    </div>
                                                </th>
                                                <td>{{ $loop->iteration }}</td>
                                                <td>{{ $qaSystem->title ?? '' }}</td>
                                                <td>
                                                    @if ($qaSystem->answer_type === 'multiple')
                                                        <span class="badge bg-primary">
                                                            Chọn nhiều
                                                        </span>
                                                    @else
                                                        <span class="badge bg-info">
                                                            Chọn một
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($qaSystem->status === 1)
                                                        <span class="badge bg-success">
                                                            Hoạt động
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger">
                                                            Không hoạt động
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>{{ $qaSystem->created_at->format('d/m/Y') ?? '' }}</td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('admin.qa-systems.edit', $qaSystem->id) }}">
                                                            <button class="btn btn-sm btn-warning edit-item-btn">
                                                                <span class="ri-edit-box-line"></span>
                                                            </button>
                                                        </a>
                                                        <a href="{{ route('admin.qa-systems.destroy', $qaSystem->id) }}"
                                                            class="sweet-confirm btn btn-sm btn-danger remove-item-btn">
                                                            <span class="ri-delete-bin-7-line"></span>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="row justify-content-end">
                                    {{ $qaSystems->appends(request()->query())->links() }}
                                </div>
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

    <div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importModalLabel">Import Dữ Liệu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">

                    <form id="importForm" action="{{ route('admin.qa-systems.import') }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="importFile" class="form-label">Chọn file để import:</label>
                            <input type="file" class="form-control" name="file" id="file"
                                accept=".xlsx,.xls,.csv" required>
                        </div>
                        <button type="submit" class="btn btn-success">Tiến hành Import</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
    <script>
        var routeUrlFilter = "{{ route('admin.qa-systems.index') }}";
        $(document).on('click', '#resetFilter', function() {
            window.location = routeUrlFilter;
        });
    </script>
    <script src="{{ asset('assets/js/custom/custom.js') }}"></script>
    <script src="{{ asset('assets/js/common/checkall-option.js') }}"></script>
    <script src="{{ asset('assets/js/common/filter.js') }}"></script>
    <script src="{{ asset('assets/js/common/search.js') }}"></script>
    <script src="{{ asset('assets/js/common/handle-ajax-search&filter.js') }}"></script>
@endpush
