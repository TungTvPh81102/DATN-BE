@extends('layouts.app')

@push('page-css')
    <style>
        .row.mb-3 {
            border-bottom: 1px solid #f0f0f0;
            /* Đường kẻ phân cách mỗi dòng */
            padding-bottom: 1%;
            /* Khoảng cách dưới mỗi dòng */
            margin-bottom: 1%px;
            /* Khoảng cách giữa các dòng */
        }

        .row.mb-3:last-child {
            border-bottom: none;
            /* Loại bỏ đường kẻ cuối */
        }

        .col-md-3 {
            font-weight: bold;
            /* Làm nổi bật nhãn (label) */
            color: #555;
            /* Màu chữ nhãn nhã nhặn hơn */
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Chi tiết người dùng</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Dashboard</a></li>
                            <li class="breadcrumb-item active"><a href="{{route('admin.users.index')}}">Danh sách người dùng</a></li>
                            <li class="breadcrumb-item active"><a href="{{route('admin.users.show', $user->id)}}">Chi tiết người dùng</a></li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Chi tiết người dùng <span
                        class="text-danger">{{ $user->name }}</span></h4>
            </div><!-- end card header -->

            <div class="card-body px-5">
                <div class="row">
                    <div class="col-4">
                        <div class="p-2 text-center">
                            <div class="mx-auto mb-3">
                                <img src="{{ $user->avatar }}" alt="Hình avatar" class="img-fluid rounded-circle">
                            </div>
                            <h6 class="mb-1">Avatar</h6>
                        </div>
                    </div>

                    <div class="col-8">
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>ID:</strong></div>
                            <div class="col-md-9">{{ $user->id }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Code:</strong></div>
                            <div class="col-md-9">{{ $user->id }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Name:</strong></div>
                            <div class="col-md-9">{{ $user->id }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Email:</strong></div>
                            <div class="col-md-9">{{ $user->id }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Status:</strong></div>
                            <div class="col-md-9">
                                @if ($user->status === 'active')
                                    <span class="badge bg-success">
                                        ACTIVE
                                    </span>
                                @elseif($user->status === 'inactive')
                                    <span class="badge bg-warning">
                                        INACTIVE
                                    </span>
                                @else
                                    <span class="badge bg-danger">
                                        BLOCK
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Email Verified At:</strong></div>
                            <div class="col-md-9">                                                    
                                <div class="form-check form-switch form-switch-warning">
                                <input class="form-check-input" type="checkbox" role="switch"
                                    id="SwitchCheck4" disabled @checked($user->email_verified_at != null)>
                            </div></div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Created At:</strong></div>
                            <div class="col-md-9">{{ $user->created_at }}</div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3"><strong>Updated At:</strong></div>
                            <div class="col-md-9">{{ $user->updated_at }}</div>
                        </div>
                        <div class="text-start mt-5">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Quay lại</a>
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-warning">
                                Chỉnh sửa
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
