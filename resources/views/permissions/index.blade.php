@extends('layouts.app')

@section('title', $title)

@push('page-css')
    <link href="{{ asset('assets/css/custom.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .permission-card {
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .permission-card .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .module-header {
            background-color: #f1f5f9;
            padding: 10px 15px;
            font-weight: 600;
            border-left: 4px solid #4b72b2;
            margin-bottom: 0.75rem;
            border-radius: 4px;
        }

        .permission-badges .badge {
            font-size: 0.8rem;
            font-weight: 500;
            margin-right: 4px;
            margin-bottom: 8px;
            padding: 6px 10px;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.2s;
        }

        .permission-badges .badge:hover {
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .permission-badges .badge-view {
            background-color: #e3f2fd;
            color: #0d6efd;
        }

        .permission-badges .badge-create {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .permission-badges .badge-edit {
            background-color: #fff8e1;
            color: #f57c00;
        }

        .permission-badges .badge-delete {
            background-color: #ffebee;
            color: #d32f2f;
        }

        .permission-badges .badge-manage {
            background-color: #e8eaf6;
            color: #3f51b5;
        }

        .search-box .form-control:focus {
            box-shadow: none;
            border-color: #4b72b2;
        }

        .actions-column {
            min-width: 100px;
        }

        .permission-table th {
            background-color: #f8f9fa;
            font-weight: 600;
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
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dasboard</a></li>
                            <li class="breadcrumb-item active">{{ $title ?? '' }}</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card permission-card">
                    <div class="card-header d-flex align-items-center">
                        <h5 class="card-title mb-0 flex-grow-1">
                            <i class="ri-add-line me-1"></i> Thêm quyền mới
                        </h5>
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse"
                            data-bs-target="#newPermissionForm" aria-expanded="false">
                            <i class="ri-arrow-down-s-line"></i>
                        </button>
                    </div>
                    <div class="collapse show" id="newPermissionForm">
                        <div class="card-body">
                            <form action="{{ route('admin.permissions.store') }}" method="POST" class="row g-3">
                                @csrf
                                <div class="col-md-6">
                                    <label class="form-label">Tên quyền</label>
                                    <input type="text" name="name" class="form-control mb-2"
                                        placeholder="Nhập tên quyền..." value="{{ old('name') }}">

                                    @if ($errors->has('name'))
                                        <span class="text-danger">{{ $errors->first('name') }}</span>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    <label for="inputEmail4" class="form-label">Mô tả</label>
                                    <input type="text" class="form-control mb-2" placeholder="Nhập mô tả..."
                                        value="{{ old('description') }}" name="description">

                                    @if ($errors->has('description'))
                                        <span class="text-danger">{{ $errors->first('description') }}</span>
                                    @endif
                                </div>

                                <div class="col-12 d-flex justify-content-end">
                                    <div class="">
                                        <button type="reset" class="btn btn-light me-2">
                                            <i class="ri-refresh-line me-1"></i> Nhập lại
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="ri-save-line me-1"></i> Thêm mới
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">{{ $subTitle ?? '' }}</h4>
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
                            <a class="btn btn-sm btn-success h-75" href="{{ route('admin.permissions.export') }}">Export dữ
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
                                            <div class="row">
                                                <li class="col-6">
                                                    <div class="mb-2">
                                                        <label for="startDate" class="form-label">Từ ngày</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="created_at" id="dateRequest" data-filter>
                                                    </div>
                                                </li>
                                                <li class="col-6">
                                                    <div class="mb-2">
                                                        <label for="endDate" class="form-label">Đến ngày</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="updated_at" id="dateComplete" data-filter>
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
                                <div class="col-md-6">
                                    <label class="form-label">Quyền</label>
                                    <input class="form-control form-control-sm" name="name" type="text"
                                        placeholder="Nhập quyền..." data-advanced-filter>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Mô tả</label>
                                    <input class="form-control form-control-sm" name="description" type="text"
                                        placeholder="Nhập mô tả quyền..." data-advanced-filter>
                                </div>
                                <div class="mt-3 text-end">
                                    <button class="btn btn-sm btn-success" type="reset" id="resetFilter">Reset</button>
                                    <button class="btn btn-sm btn-primary" id="applyAdvancedFilter">Áp dụng</button>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="card-body" id="item_List">
                        <div class="permission-list">
                            @foreach ($permissions as $guardName => $groupedPermissions)
                                <div class="module-group mb-4">
                                    <div class="module-header d-flex justify-content-between align-items-center">
                                        <div>
                                            <i class="ri-shield-keyhole-line me-1"></i>
                                            Module {{ Str::ucfirst($guardName) }}
                                            <span class="badge bg-secondary ms-2">{{ count($groupedPermissions) }}</span>
                                        </div>
                                        <div class="permission-badges d-none d-md-block">
                                            <span class="badge badge-view">view_*</span>
                                            <span class="badge badge-create">create_*</span>
                                            <span class="badge badge-edit">edit_*</span>
                                            <span class="badge badge-delete">delete_*</span>
                                            <span class="badge badge-manage">manage_*</span>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover permission-table">
                                            <thead>
                                                <tr>
                                                    <th width="5%">#</th>
                                                    <th width="25%">Tên quyền</th>
                                                    <th width="45%">Mô tả</th>
                                                    <th width="15%">Ngày tạo</th>
                                                    <th width="10%" class="text-center">Thao tác</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($groupedPermissions as $permission)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>
                                                            {{ $permission->name }}
                                                        </td>
                                                        <td>{{ $permission->description }}</td>
                                                        <td>{{ $permission->created_at->format('d/m/Y H:i') }}</td>
                                                        <td class="text-center actions-column">
                                                            <div class="btn-group">
                                                                <a href="#" class="btn btn-sm btn-outline-info"
                                                                    data-bs-toggle="modal"
                                                                    data-bs-target="#permissionDetailModal"
                                                                    data-id="{{ $permission->id }}"
                                                                    data-name="{{ $permission->name }}"
                                                                    data-description="{{ $permission->description }}"
                                                                    data-created_at="{{ $permission->created_at->format('d/m/Y H:i') }}">
                                                                    <i class="ri-eye-line"></i>
                                                                </a>
                                                                <a href="{{ route('admin.permissions.destroy', $permission->id) }}"
                                                                    class="sweet-confirm btn btn-sm btn-outline-danger">
                                                                    <i class="ri-delete-bin-line"></i>
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class=" justify-content-between align-items-center flex-wrap mt-3">
                            {{ $permissions->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!-- Modal chi tiết và chỉnh sửa quyền -->
    <div class="modal fade" id="permissionDetailModal" tabindex="-1" aria-labelledby="permissionDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="permissionDetailModalLabel">Chi tiết quyền</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="updatePermissionForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <input type="hidden" name="id" id="permissionId">
                        <div class="mb-3">
                            <label for="permissionName" class="form-label">Tên quyền</label>
                            <input type="text" class="form-control" id="permissionName" name="name" required>
                        </div>
                        <div class="mb-3">
                            <label for="permissionDescription" class="form-label">Mô tả</label>
                            <input type="text" class="form-control" id="permissionDescription" name="description">
                        </div>
                        <div class="mb-3">
                            <label for="permissionCreatedAt" class="form-label">Ngày tạo</label>
                            <input type="text" class="form-control" id="permissionCreatedAt" readonly>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                        <button type="submit" class="btn btn-primary">Cập nhật</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endSection
@push('page-scripts')
    <script>
        $(document).on('click', '[data-bs-target="#permissionDetailModal"]', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var description = $(this).data('description');
            var created_at = $(this).data('created_at');

            // Điền dữ liệu vào form trong modal
            $('#permissionId').val(id);
            $('#permissionName').val(name);
            $('#permissionDescription').val(description);
            $('#permissionCreatedAt').val(created_at);
        });

        // Xử lý submit form trong modal
        $('#updatePermissionForm').on('submit', function(e) {
            e.preventDefault();

            var form = $(this);
            var url = '{{ route('admin.permissions.update', ':id') }}'.replace(':id', $('#permissionId').val());
            var data = form.serialize();

            $.ajax({
                url: url,
                type: 'PUT',
                data: data,
                success: function(response) {
                            if (response.success) {
                                // Hiển thị thông báo thành công
                                showToast('success', response.message ||
                                    'Cập nhật thành công!');
                                    $('#permissionDetailModal').modal('hide');
                                    window.location.href = '{{ route("admin.permissions.index") }}';
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
        });
    </script>
    <script>
        var routeUrlFilter = "{{ route('admin.permissions.index') }}";
        $(document).on('click', '#resetFilter', function() {
            handleSearchFilter('');
        });
    </script>
    <script src="{{ asset('assets/js/custom/custom.js') }}"></script>
    <script src="{{ asset('assets/js/common/filter.js') }}"></script>
    <script src="{{ asset('assets/js/common/search.js') }}"></script>
    <script src="{{ asset('assets/js/common/handle-ajax-search&filter.js') }}"></script>
@endpush
