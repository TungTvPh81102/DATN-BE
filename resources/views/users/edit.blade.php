@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Cập nhật người dùng</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a>Quản lý thành viên</a></li>
                            <li class="breadcrumb-item active">Cập nhật người dùng</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Cập nhật người dùng <span
                        class="text-danger">{{ $user->name }}</span></h4>
                @if (session()->has('success') && session()->get('success') == true)
                    <span class="badge bg-primary text-end">Thao tác thành công</span>
                @endif
                @if (session()->has('error') && session()->get('error') != null)
                    <span class="badge bg-danger text-end">Thao tác không thành công</span>
                @endif
            </div><!-- end card header -->

            <div class="card-body">
                <div class="live-preview">

                    {{-- start form --}}
                    <form action="{{ route('admin.users.update', $user->id) }}" method="POST" enctype="multipart/form-data"
                        class="row">
                        @csrf
                        @method('PUT')

                        {{-- Avatar --}}
                        <div class="col-4">
                            <div class="p-2 text-center">
                                <div class="mx-auto mb-3">
                                    <img src="{{ $user->avatar }}" alt="Hình avatar" class="img-fluid rounded-circle">
                                </div>
                                <h6 class="mb-1">Avatar</h6>
                            </div>
                        </div>
                        {{-- end avatar --}}

                        <div class="col-8">
                            <div class="col-md-12 mb-3">
                                <label for="fullname" class="form-label">Họ Và Tên</label>
                                <input type="text" class="form-control" name="name" id="fullname"
                                    placeholder="Nhập họ và tên của bạn" value="{{ $user->name }}">
                                @error('name')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="inputEmail4" class="form-label">Email</label>
                                <input type="email" class="form-control" name="email" id="inputEmail4"
                                    placeholder="Nhập email" value="{{ $user->email }}">
                                @error('email')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Avatar mới</label>
                                <input type="file" name="avatar" id="imageInput" accept="image/*" class="form-control">
                                <img id="imagePreview" style="display: none; max-width: 100%; max-height: 300px;">
                                @error('avatar')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div> <!-- end col -->
                            <div class="col-12">
                                <div class="text-start">
                                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                                    <a class="btn btn-success" href="{{ route('admin.users.index') }}">Quay lại</a>
                                </div>
                            </div>
                        </div>
                    </form>
                    {{-- end form --}}

                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
    <script>
        document.getElementById('imageInput').addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = () => {
                    const preview = document.getElementById('imagePreview');
                    preview.src = reader.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(file);
            }
        });
    </script>
@endpush
