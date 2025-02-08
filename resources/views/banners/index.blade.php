@extends('layouts.app')
@push('page-css')
    <!-- plugin css -->
    <link href="{{ asset('assets/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
@endpush
@php
    $title = 'Danh sách banner';
@endphp
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Quản lí banner</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Danh sách banner</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Danh sách Banner</h4>
                        <div class="d-flex gap-2">
                            <button class="btn btn-sm btn-danger">Import dữ liệu</button>
                            <button class="btn btn-sm btn-success">Export dữ liệu</button>
                            <button class="btn btn-sm btn-primary" id="toggleAdvancedSearch">
                                Tìm kiếm nâng cao
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-primary" type="button" id="filterDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-filter-2-line"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown"
                                    style="min-width: 500px;">
                                    <form>
                                        <div class="container">
                                            <div class="container">
                                                <div class="row">
                                                    <li class="col-6">
                                                        <div class="mb-2">
                                                            <label for="startDate" class="form-label">Ngày bắt đầu</label>
                                                            <input type="date" class="form-control form-control-sm"
                                                                name="created_at" id="startDate" data-filter
                                                                value="{{ request()->input('created_at') ?? '' }}">
                                                        </div>
                                                    </li>
                                                    <li class="col-6">
                                                        <div class="mb-2">
                                                            <label for="endDate" class="form-label">Ngày kết thúc</label>
                                                            <input type="date" class="form-control form-control-sm"
                                                                name="updated_at" id="endDate" data-filter
                                                                value="{{ request()->input('updated_at') ?? '' }}">
                                                        </div>
                                                    </li>
                                                </div>
                                                <li class="mt-2 d-flex gap-1">
                                                    <button class="btn btn-sm btn-success flex-grow-1" type="reset"
                                                        id="resetFilter">Reset</button>
                                                    <button class="btn btn-sm btn-primary flex-grow-1" id="applyFilter">Áp
                                                        dụng</button>
                                                </li>
                                            </div>
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
                                    <label class="form-label">Mã banner</label>
                                    <input class="form-control form-control-sm" name="id" type="text"
                                        value="{{ request()->input('id') ?? '' }}" placeholder="Nhập mã banner..."
                                        data-advanced-filter>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Tiêu đề</label>
                                    <input class="form-control form-control-sm" name="title" type="text"
                                        value="{{ request()->input('title') ?? '' }}" placeholder="Nhập tiêu đề..."
                                        data-advanced-filter>
                                </div>
                                <div class="col-md-4">
                                    <label for="statusItem" class="form-label">Trạng thái</label>
                                    <select class="form-select form-select-sm" name="status" id="statusItem"
                                        data-advanced-filter>
                                        <option value="">Chọn trạng thái</option>
                                        <option @selected(request()->input('status') === '1') value="1">Hoạt động</option>
                                        <option @selected(request()->input('status') === '0') value="0">Không hoạt động</option>
                                    </select>
                                </div>
                                <div class="mt-3 text-end">
                                    <button class="btn btn-sm btn-success" type="reset" id="resetFilter">Reset</button>
                                    <button class="btn btn-sm btn-primary" id="applyAdvancedFilter">Áp dụng</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="card-body" id="item_List">
                        <div class="listjs-table" id="customerList">
                            <div class="row g-4 mb-3">
                                <div class="col-sm-auto">
                                    <div>
                                        <a href="{{ route('admin.banners.create') }}" class="btn btn-primary add-btn"><i
                                                class="ri-add-line align-bottom me-1"></i> Thêm mới</a>
                                        <button class="btn btn-danger" id="deleteSelected">
                                            <i class="ri-delete-bin-2-line"> Xóa nhiều</i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-sm">
                                    <div class="d-flex justify-content-sm-end">
                                        <div class="search-box ms-2">
                                            <form action="{{ route('admin.banners.index') }}" method="get">
                                                <input type="text" name="query" class="form-control search"
                                                    placeholder="Search..." value="{{ old('query') }}">
                                                <i class="ri-search-line search-icon"></i>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

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
                                            <th>Mã banner</th>
                                            <th>Tiêu đề</th>
                                            <th>Ảnh</th>
                                            <th>Thứ tự</th>
                                            <th>Trạng thái</th>
                                            <th>Ngày tạo</th>
                                            <th>Ngày cập nhật</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list form-check-all">
                                        @foreach ($banners as $banner)
                                            <tr>
                                                <th scope="row">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="chk_child"
                                                            value="option1">
                                                    </div>
                                                </th>

                                                <td class="id">{{ $banner->id }}</td>
                                                <td class="customer_name">{{ $banner->title }}</td>
                                                <td class="phone">
                                                    @if ($banner->image)
                                                        <img src="{{ $banner->image }}" alt="" width="100px">
                                                    @else
                                                        <p>Không có ảnh</p>
                                                    @endif

                                                </td>
                                                <td class="date">{{ $banner->order }}</td>
                                                @if ($banner->status)
                                                    <td class="status"><span class="badge bg-success-subtle text-success">
                                                            Hoạt động
                                                        </span></td>
                                                @else
                                                    <td class="status"><span class="badge bg-danger-subtle text-danger">
                                                            Không hoạt động
                                                        </span></td>
                                                @endif

                                                <td class="date">{{ $banner->created_at }}</td>
                                                <td class="date">{{ $banner->updated_at }}</td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <div class="remove">
                                                            <a href="{{ route('admin.banners.edit', $banner->id) }}">
                                                                <button class="btn btn-sm btn-warning edit-item-btn">
                                                                    <span class="ri-edit-box-line"></span>
                                                                </button>
                                                            </a>
                                                        </div>
                                                        <div class="edit">
                                                            <a href="{{ route('admin.banners.show', $banner->id) }}">
                                                                <button class="btn btn-sm btn-info edit-item-btn">
                                                                    <span class="ri-folder-user-line"></span>
                                                                </button>
                                                            </a>
                                                        </div>
                                                        <div class="remove">
                                                            <a href="{{ route('admin.banners.destroy', $banner->id) }}"
                                                                class="btn btn-sm btn-danger sweet-confirm">
                                                                <span class="ri-delete-bin-7-line"></span>
                                                            </a>
                                                        </div>

                                                    </div>
                                                </td>


                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="noresult" style="display: none">
                                    <div class="text-center">
                                        <lord-icon src="https://cdn.lordicon.com/msoeawqm.json" trigger="loop"
                                            colors="primary:#121331,secondary:#08a88a"
                                            style="width:75px;height:75px"></lord-icon>
                                        <h5 class="mt-2">Sorry! No Result Found</h5>
                                        <p class="text-muted mb-0">We've searched more than 150+ Orders We did not find any
                                            orders for you search.</p>
                                    </div>
                                </div>
                            </div>

                            {{ $banners->appends(request()->query())->links() }}
                        </div>
                    </div><!-- end card -->
                </div>
                <!-- end col -->
            </div>
            <!-- end col -->
        </div>
        <!-- end row -->

    </div>
@endsection
@push('page-scripts')
    <script>
        var routeUrlFilter = "{{ route('admin.banners.index') }}";
        $(document).on('click', '#resetFilter', function() {
            window.location = routeUrlFilter;
        });
    </script>
    <script src="{{ asset('assets/js/custom/custom.js') }}"></script>
    <script src="{{ asset('assets/js/common/checkall-option.js') }}"></script>
    <script src="{{ asset('assets/js/common/delete-all-selected.js') }}"></script>
    <script src="{{ asset('assets/js/common/restore-all-selected.js') }}"></script>
    <script src="{{ asset('assets/js/common/filter.js') }}"></script>
    <script src="{{ asset('assets/js/common/search.js') }}"></script>
    <script src="{{ asset('assets/js/common/handle-ajax-search&filter.js') }}"></script>
@endpush
