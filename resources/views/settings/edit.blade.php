@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <!-- start page title -->
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">Cập nhật setting</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active"><a href="{{ route('admin.settings.index') }}">Danh sách settings</a></li>
                            <li class="breadcrumb-item active"><a href="{{ route('admin.settings.edit', $setting->id) }}">Cập
                                    nhật setting</a></li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <!-- end page title -->
        <div class="card">
            <div class="card-header align-items-center d-flex">
                <h4 class="card-title mb-0 flex-grow-1">Cập nhật setting <span
                        class="text-danger">{{ $setting->key }}</span></h4>
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
                    <form action="{{ route('admin.settings.update', $setting->id) }}" method="POST" class="row">
                        @csrf
                        @method('PUT')
                        <div class="col-md-12">
                            <label for="key" class="form-label">Key setting</label>
                            <input type="text" class="form-control mb-2" name="key" id="key"
                                placeholder="Nhập key setting" value="{{ $setting->key }}">
                            @error('key')
                                <span class="text-danger mt-2">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-md-12">
                            <label for="inputValue" class="form-label">Giá trị</label>
                            <input type="value" class="form-control mb-2" name="value" id="inputValue"
                                placeholder="Nhập giá trị" value="{{ $setting->value }}">
                            @error('value')
                                <span class="text-danger mt-2">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="col-12">
                            <div class="text-start">
                                <button type="submit" class="btn btn-primary">Cập nhật</button>
                                <a class="btn btn-success" href="{{ route('admin.settings.index') }}">Quay lại</a>
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
