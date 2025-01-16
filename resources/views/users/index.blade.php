@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Danh sách {{ $roleUser['actor'] }}</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active"><a
                                    href="{{ route('admin.' . $roleUser['role_name'] . '.index') }}">Danh sách
                                    {{ $roleUser['actor'] }}</a></li>
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
                        <h5 class="card-title">Tổng số {{ $roleUser['actor'] }}</h5>
                        <p class="card-text fs-4">{{ $userCounts->total_users ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card text-center h-75">
                    <div class="card-body">
                        <h5 class="card-title">{{ Str::ucfirst($roleUser['actor']) }} hoạt động</h5>
                        <p class="card-text fs-4 text-success">{{ $userCounts->active_users ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card text-center h-75">
                    <div class="card-body">
                        <h5 class="card-title">{{ Str::ucfirst($roleUser['actor']) }} không hoạt động</h5>
                        <p class="card-text fs-4 text-warning">{{ $userCounts->inactive_users ?? 0 }}</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <div class="card text-center h-75">
                    <div class="card-body">
                        <h5 class="card-title">{{ Str::ucfirst($roleUser['actor']) }} bị khóa</h5>
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
                        <h4 class="card-title mb-0">Danh sách {{ $roleUser['actor'] }}</h4>
                        <div class="dropdown">
                            <button class="btn btn-sm btn-primary" type="button" id="filterDropdown"
                                data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ri-filter-2-line"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown">
                                <div class="container">
                                    <li>
                                        <select class="form-select form-select-sm mb-2" name="statusItem" id="statusItem">
                                            <option value="">Tất cả trạng thái</option>
                                            <option value="active">Hoạt động</option>
                                            <option value="inactive">Không hoạt động</option>
                                            <option value="blocked">Bị khóa</option>
                                        </select>
                                    </li>
                                    <li>
                                        <div class="mb-2">
                                            <label for="startDate" class="form-label">Từ ngày</label>
                                            <input type="date" class="form-control form-control-sm" id="startDate"
                                                value="{{ request()->input('start_date') ?? '' }}">
                                        </div>
                                    </li>
                                    <li>
                                        <div class="mb-2">
                                            <label for="endDate" class="form-label">Đến ngày</label>
                                            <input type="date" class="form-control form-control-sm" id="endDate"
                                                value="{{ request()->input('end_date') ?? '' }}">
                                        </div>
                                    </li>
                                    <li>
                                        <button class="btn btn-sm btn-primary w-100" id="applyFilter">Áp dụng</button>
                                    </li>
                                </div>
                            </ul>
                        </div>

                    </div>
                    <!-- end card header -->
                    <div class="card-body" id="item_List">
                        <div class="listjs-table" id="customerList">
                            <div class="row g-4 mb-3">
                                <div class="col-sm-auto">
                                    <div>
                                        @if ($roleUser['name'] === 'deleted')
                                            <button class="btn btn-danger" id="restoreSelected">
                                                <i class=" ri-restart-line"> Khôi phục</i>
                                            </button>
                                        @else
                                            <a href="{{ route('admin.users.create') }}">
                                                <button type="button" class="btn btn-primary add-btn">
                                                    <i class="ri-add-line align-bottom me-1"></i> Thêm mới
                                                </button>
                                            </a>
                                        @endif
                                        <button class="btn btn-danger" id="deleteSelected">
                                            <i class="ri-delete-bin-2-line"> Xóa nhiều</i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-sm">
                                    <div class="d-flex justify-content-sm-end">
                                        <div class="search-box ms-2">
                                            <input type="text" name="searchFull" class="form-control search"
                                                placeholder="Tìm kiếm...">
                                            <button id="search-full" class="ri-search-line search-icon m-0 p-0 border-0"
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
                                            <th>STT</th>
                                            <th>Tên</th>
                                            <th>Email</th>
                                            <th>Xác minh email</th>
                                            <th>Trạng Thái</th>
                                            <th>Vai Trò</th>
                                            <th>Ngày Tham Gia</th>
                                            @if ($roleUser['name'] !== 'deleted')
                                                <th>Hành Động</th>
                                            @else
                                                <th>Thời gian xóa</th>
                                            @endif
                                        </tr>
                                    </thead>
                                    <tbody class="list">
                                        @foreach ($users as $user)
                                            <tr>
                                                <th scope="row">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="itemID"
                                                            value="{{ $user->id }}">
                                                    </div>
                                                </th>
                                                <td class="id"><a
                                                        class="fw-medium link-primary">{{ $loop->index + 1 }}</a></td>
                                                <td class="customer_name">{{ $user->name }}</td>
                                                <td class="email">{{ $user->email }}</td>
                                                <td>
                                                    <div class="form-check form-switch form-switch-warning">
                                                        <input class="form-check-input" type="checkbox" role="switch"
                                                            {{ $roleUser['name'] !== 'deleted' ? 'name=email_verified' : 'disabled' }}
                                                            value="{{ $user->id }}" @checked($user->email_verified_at != null)>
                                                    </div>
                                                </td>
                                                <td class="status">
                                                    @if ($user->status === 'active')
                                                        <span class="badge bg-success w-50">
                                                            Active
                                                        </span>
                                                    @elseif($user->status === 'inactive')
                                                        <span class="badge bg-warning w-50">
                                                            Inactive
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger w-50">
                                                            Block
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $roleName = $roleUser['name'] === 'deleted' ? $user->roles->first()?->name : $roleUser['name'];
                                                        $badgeColor = match ($roleName) {
                                                            'admin' => 'bg-danger',
                                                            'member' => 'bg-primary',
                                                            'instructor' => 'bg-warning',
                                                            default => 'bg-primary',
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $badgeColor }} w-75">
                                                        {{ Str::ucfirst($roleName) }}
                                                    </span>
                                                </td>
                                                <td>
                                                    {{ $user->created_at != null ? date_format($user->created_at, 'd/m/Y') : 'NULL' }}
                                                </td>
                                                <td>
                                                    @if ($roleUser['name'] !== 'deleted')
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
                                                    @else
                                                        {{ $user->deleted_at != null ? date_format($user->deleted_at, 'd/m/Y') : 'NULL' }}
                                                    @endif
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
    <script>
        var routeUrlFilter = "{{ route('admin.' . $actorRole . '.index') }}";
        var routeDeleteAll = "{{ $roleUser['name'] === 'deleted' ? route('admin.users.forceDelete', ':itemID') : route('admin.users.destroy', ':itemID') }}";
        var routeRestoreUrl = "{{ route('admin.users.restoreDelete', ':itemID') }}";
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $(document).on('change', 'input[name="email_verified"]', function() {
                var userID = $(this).val();
                var isChecked = $(this).is(':checked');

                var updateUrl = "{{ route('admin.users.updateEmailVerified', ':userID') }}".replace(
                    ':userID', userID);

                $.ajax({
                    type: "PUT",
                    url: updateUrl,
                    data: {
                        email_verified: isChecked ? userID : ''
                    },
                });
            });
        });
    </script>
    <script src="{{ asset('assets/js/pages/filter-search-deleteAll.js') }}"></script>
@endpush
