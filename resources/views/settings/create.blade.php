@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Thêm mới settings</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Dashboard</a></li>
                            <li class="breadcrumb-item active"><a href="{{route('admin.settings.index')}}">Danh sách settings</a></li>
                            <li class="breadcrumb-item active"><a href="{{route('admin.settings.create')}}">Thêm mới settings</a></li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Thêm mới settings</h4>
                @if (session()->has('error') && session()->get('error') != null)
                    <span class="badge bg-danger text-end">Thao tác không thành công</span>
                @endif
            </div><!-- end card header -->
            <div class="card-body">
                <div class="live-preview">
                    <form action="{{ route('admin.settings.store') }}" method="post" class="row g-3">
                        @csrf
                        <div class="col-md-12">
                            <label for="key" class="form-label">Key settings</label>
                            <input type="text" class="form-control mb-2" name="key" id="key"
                                placeholder="Nhập key settings" value="{{ old('key') }}">
                            @error('key')
                                <span class="text-danger mt-2">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label for="inputValue" class="form-label">Giá trị</label>
                            <input type="value" class="form-control mb-2" name="value" id="inputValue"
                                placeholder="Nhập giá trị" value="{{ old('value') }}">
                            @error('value')
                                <span class="text-danger mt-2">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-12">
                            <div class="text-end">
                                <a class="btn btn-success" href="{{ route('admin.settings.index') }}">Quay lại</a>
                                <button type="submit" class="btn btn-primary">Thêm</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
