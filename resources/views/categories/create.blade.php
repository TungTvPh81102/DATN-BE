@extends('layouts.app')


@section('content')
    <div class="container-fluid">

        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Listjs</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="javascript: void(0);">Tables</a></li>
                            <li class="breadcrumb-item active">Listjs</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header align-items-center d-flex">
                        <h4 class="card-title mb-0 flex-grow-1">Thêm mới danh mục</h4>
                        <div class="flex-shrink-0">
                            
                            
                            <div class="form-check form-switch form-switch-right form-switch-md">
                                <label for="form-grid-showcode" class="form-label text-muted">Show Code</label>
                                <input class="form-check-input code-switcher" type="checkbox" id="form-grid-showcode">
                            </div>
                        </div>
                    </div>

                    <!-- end card header -->
                    <form action="{{ route('admin.categories.store') }}" method="post" enctype="multipart/form-data">

                        @csrf

                        <div class="card-body">
                            <div class="live-preview">
                                <div class="row gy-4">
                                    <div class="col-xxl-6 col-md-6">
                                        <div>
                                            <label for="placeholderInput" class="form-label">Tên danh mục</label>
                                            <input type="text" class="form-control" id="placeholderInput"
                                                placeholder="Nhập tên danh mục" name="name" value="{{ old('name') }}">
                                        </div>
                                        @if ($errors->has('name'))
                                            <span class="text-danger">{{ $errors->first('name') }}</span>
                                        @endif
                                    </div>
                                    <!--end col-->
                                    <div class="col-xxl-6 col-md-6">
                                        <div>
                                            <label for="placeholderInput" class="form-label">URL thân thiện</label>
                                            <input type="text" class="form-control" id="placeholderInput"
                                                placeholder="Nhập URL thân thiện" name="slug"
                                                value="{{ old('slug') }}">
                                        </div>
                                        @if ($errors->has('slug'))
                                            <span class="text-danger">{{ $errors->first('slug') }}</span>
                                        @endif
                                    </div>
                                    <!--end col-->

                                    <div class="col-xxl-6 col-md-6">
                                        <div>
                                            <label for="placeholderInput" class="form-label">Danh mục gốc</label>
                                            <input type="text" class="form-control" id="placeholderInput"
                                                placeholder="Nhập id của danh mục" name="parent_id" value="{{ old('parent_id') }}">
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-xxl-6 col-md-6">
                                        <div>
                                            <label for="placeholderInput" class="form-label">Biểu tượng</label>
                                            <input type="file" class="form-control" id="formFile"
                                                placeholder="Chọn file cần nhập" name="icon" value="{{ old('icon') }}">
                                        </div>
                                    </div>
                                    <!--end col-->
                                    <div class="col-xxl-6 col-md-6">
                                        <div>
                                            <label for="placeholderInput" class="form-label">Trạng thái</label>
                                            <div class="form-check form-check-secondary mb-3">
                                                <input class="form-check-input" type="checkbox" id="formCheck7"
                                                    name="status" value="1" checked>
                                                <label class="form-check-label" for="formCheck7">
                                                    Active
                                                </label>
                                            </div>

                                        </div>
                                    </div>
                                    <!--end col-->
                                </div>

                                <div class="mt-3">
                                    <button type="submit" class="btn btn-success waves-effect waves-light">Thêm
                                        mới</button>
                                    <a href="{{ route('admin.categories.index') }}" type="button" class="btn btn-danger waves-effect waves-lightm ">Quay lại</a>
                                </div>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
            <!--end col-->
        </div>
        <!-- end row -->





    </div>
@endsection
<div>
    <!-- People find pleasure in different ways. I find it in keeping my mind clear. - Marcus Aurelius -->
</div>

<div>
    <!-- Waste no more time arguing what a good man should be, be one. - Marcus Aurelius -->
</div>
