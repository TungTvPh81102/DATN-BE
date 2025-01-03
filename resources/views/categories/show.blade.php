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
                    <div class="card-header">
                        <h4 class="card-title mb-0">Chi tiết danh mục: {{ $category->name }}</h4>
                    </div><!-- end card header -->

                    <div class="card-body">
                        <div class="listjs-table" id="customerList">
                            <div class="row g-4 mb-3">
                                <div class="col-sm-auto">
                                    <div>
                                        <a href="{{ route('admin.categories.index') }}" type="button"
                                            class="btn btn-danger add-btn">
                                            Quay lại</a>
                                        
                                    </div>
                                </div>

                            </div>

                            <div class="table-responsive table-card mt-3 mb-1">
                                <table class="table align-middle table-nowrap" id="customerTable">
                                    <thead class="table-light">
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên danh mục</th>
                                            <th>Slug</th>
                                            <th>Danh mục cha</th>
                                            <th>Trạng thái</th>
                                            <th>Biểu tượng</th>
                                            <th>Ngày tạo</th>
                                            <th>Ngày cập nhật</th>
                                        </tr>
                                    </thead>

                                    <tbody class="list form-check-all">
                                        <tr>
                                            
                                            <td>{{ $category->id }}</td>
                                            <td>{{ $category->name }}</td>
                                            <td>{{ $category->slug }}</td>
                                            <td>{{ $category->parent ? $category->parent->name : 'Không có' }}</td>
                                            <td>
                                                @if ($category->status)
                                                    <span
                                                        class="badge bg-success-subtle text-success text-uppercase">Active<span>
                                                        @else
                                                            <span
                                                                class="badge bg-danger-subtle text-success text-uppercase">No
                                                                active<span>
                                                @endif
                                            </td>
                                            <td class="phone">
                                                <img src="{{ $category->icon }}" alt="{{ $category->name }}" width="100">
                                            </td>
                                            <td>{{ $category->created_at }}</td>
                                            <td>{{ $category->updated_at }}</td>
                                        </tr>
                                    </tbody>

                                </table>

                            </div>


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
<div>
    <!-- People find pleasure in different ways. I find it in keeping my mind clear. - Marcus Aurelius -->
</div>
