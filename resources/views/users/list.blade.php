<div class="card-body">
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
                        <input type="text" name="searchFull" class="form-control search" placeholder="Tìm kiếm...">
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
                            <td class="id"><a class="fw-medium link-primary">{{ $loop->index + 1 }}</a></td>
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
                                    $roleName =
                                        $roleUser['name'] === 'deleted'
                                            ? $user->roles->first()?->name
                                            : $roleUser['name'];
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
