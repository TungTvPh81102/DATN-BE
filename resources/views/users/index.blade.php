@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Danh sách người dùng</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Dashboard</a></li>
                            <li class="breadcrumb-item active"><a href="{{route('admin.users.index')}}">Danh sách người dùng</a></li>
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
                        <h5 class="card-title">Tổng số khách hàng</h5>
                        <p class="card-text fs-4">{{ $userCounts->total_users ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card text-center h-75">
                    <div class="card-body">
                        <h5 class="card-title">Khách hàng hoạt động</h5>
                        <p class="card-text fs-4 text-success">{{ $userCounts->active_users ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card text-center h-75">
                    <div class="card-body">
                        <h5 class="card-title">Khách hàng không hoạt động</h5>
                        <p class="card-text fs-4 text-warning">{{ $userCounts->inactive_users ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card text-center h-75">
                    <div class="card-body">
                        <h5 class="card-title">Khách hàng bị khóa</h5>
                        <p class="card-text fs-4 text-danger">{{ $userCounts->blocked_users ?? 0 }}</p>
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
                        <h4 class="card-title mb-0">Tổng quan người dùng</h4>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-primary" type="button" id="filterDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-filter-2-line"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown">
                                <div class="container">
                                    <li>
                                        <select class="form-select form-select-sm mb-2">
                                            <option value="">Tất cả trạng thái</option>
                                            <option value="active">Hoạt động</option>
                                            <option value="inactive">Không hoạt động</option>
                                            <option value="banned">Bị khóa</option>
                                        </select>
                                    </li>
                                    <li>
                                        <div class="mb-2">
                                            <label for="startDate" class="form-label">Từ ngày</label>
                                            <input type="date" class="form-control form-control-sm" id="startDate">
                                        </div>
                                    </li>
                                    <li>
                                        <div class="mb-2">
                                            <label for="endDate" class="form-label">Đến ngày</label>
                                            <input type="date" class="form-control form-control-sm" id="endDate">
                                        </div>
                                    </li>
                                    <li>
                                        <button class="btn btn-sm btn-primary w-100">Áp dụng</button>
                                    </li>
                                </div>
                            </ul>
                        </div>
                    </div>
                    <!-- end card header -->
                    <div class="card-body">
                        <div class="listjs-table" id="customerList">
                            <div class="row g-4 mb-3">
                                <div class="col-sm-auto">
                                    <div>
                                        <a href="{{ route('admin.users.create') }}">
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
                                            <input type="text" name="searchUser" class="form-control search"
                                                id="search-options" placeholder="Tìm kiếm..."
                                                value="{{ old('searchUser') }}">
                                            <button class="ri-search-line search-icon m-0 p-0 border-0"
                                                style="background: none;"></button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive table-card mt-3 mb-1">
                                <table class="table align-middle table-nowrap" id="customerTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" style="width: 50px;">
                                                <input type="checkbox" id="checkAll">
                                            </th>
                                            <th>Mã</th>
                                            <th>Tên</th>
                                            <th>Email</th>
                                            <th>Xác Thực</th>
                                            <th>Trạng Thái</th>
                                            <th>Vai Trò</th>
                                            <th>Ngày Tham Gia</th>
                                            <th>Hành Động</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        @foreach ($users as $user)
                                            <tr>
                                                <th scope="row">
                                                    <div class="form-check">
                                                        <input class="form-check-input" id="checkAll" type="checkbox"
                                                            name="userId" value="{{ $user->id }}">
                                                    </div>
                                                </th>
                                                <td class="id"><a
                                                        class="fw-medium link-primary">#{{ $user->code }}</a></td>
                                                <td class="customer_name">{{ $user->name }}</td>
                                                <td class="email">{{ $user->email }}</td>
                                                <td>
                                                    <div class="form-check form-switch form-switch-warning">
                                                        <input class="form-check-input" type="checkbox" role="switch"
                                                            id="SwitchCheck4" disabled @checked($user->email_verified_at != null)>
                                                    </div>
                                                </td>
                                                <td class="status">
                                                    @if ($user->status === 'active')
                                                        <span class="badge bg-success w-100">
                                                            ACTIVE
                                                        </span>
                                                    @elseif($user->status === 'inactive')
                                                        <span class="badge bg-warning w-100">
                                                            INACTIVE
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger w-100">
                                                            BLOCK
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary">User</span>
                                                </td>
                                                <td>{{ $user->created_at != null ? date_format($user->created_at, 'd/m/Y') : 'NULL' }}
                                                </td>
                                                <td>
                                                    <div class="d-flex gap-2">
                                                        <a href="{{ route('admin.users.edit', $user->id) }}">
                                                            <button class="btn btn-sm btn-warning edit-item-btn">
                                                                <span class="ri-edit-box-line"></span>
                                                            </button>
                                                        </a>
                                                        <a href="{{ route('admin.users.show', $user->id) }}">
                                                            <button class="btn btn-sm btn-info edit-item-btn">
                                                                <span class="ri-folder-user-line"></span>
                                                            </button>
                                                        </a>
                                                        <a href="{{ route('admin.users.destroy', $user->id) }}"
                                                            class="sweet-confirm btn btn-sm btn-danger remove-item-btn">
                                                            <span class="ri-delete-bin-7-line"></span>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="row justify-content-end">
                                {{ $users->appends(request()->query())->links() }}
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
    <script src="{{ asset('assets/libs/list.pagination.js/list.pagination.min.js') }}"></script>

    <!-- listjs init -->
    <script src="{{ asset('assets/js/pages/listjs.init.js') }}"></script>
    <script>
        $('#checkAll').on('change', function() {
            const isChecked = $(this).prop('checked');
            $('input[name="userId"]').prop('checked', isChecked);
        });

        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $("#deleteSelected").click(function(event) {
                event.preventDefault();

                var selectedUsers = [];

                $('input[name="userId"]:checked').each(function() {
                    selectedUsers.push($(this).val());
                });

                if (selectedUsers.length == 0) {
                    Swal.fire({
                        title: 'Chọn ít nhất 1 người dùng để xóa',
                        icon: 'warning',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                let deleteUrl = "{{ route('admin.users.destroy', ':userID') }}".replace(':userID',
                    selectedUsers.join(','));

                Swal.fire({
                    title: "Bạn có muốn xóa ?",
                    text: "Bạn sẽ không thể khôi phục dữ liệu khi xoá!!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Đồng ý!!",
                    cancelButtonText: "Huỷ!!"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            type: "DELETE",
                            url: deleteUrl,
                            success: function(data) {
                                if (data.status === 'success') {
                                    Swal.fire({
                                        title: 'Thao tác thành công!',
                                        text: data.message,
                                        icon: 'success'
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            location.reload();
                                        }
                                    });
                                } else if (data.status === 'error') {
                                    Swal.fire({
                                        title: "Thao tác thất bại!",
                                        text: data.message,
                                        icon: 'error'
                                    });
                                }
                            },
                            error: function(data) {
                                console.log('Error:', data);
                                Swal.fire({
                                    title: "Thao tác thất bại!",
                                    text: data.responseJSON.message,
                                    icon: 'error'
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>
@endpush
