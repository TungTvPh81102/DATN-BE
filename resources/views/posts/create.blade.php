@extends('layouts.app')

@push('page-css')
    <link rel="stylesheet" href="{{ asset('vendor/laraberg/css/laraberg.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
    <div class="container-fluid">

        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">{{ $subTitle ?? '' }}</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dasboard</a></li>
                            <li class="breadcrumb-item active">{{ $title ?? '' }}</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>
        <form action="{{ route('admin.posts.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">
                                Thông tin bài viết
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Tiêu đề</label>
                                <input type="name" class="form-control mb-2" placeholder="Nhập tiêu đề..."
                                    value="{{ old('name') }}" name="name">
                                @error('name')
                                    <span class="text-danger mt-2">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Hình ảnh</label>
                                <input type="file" name="thumbnail" id="imageInput" accept="image/*"
                                    class="form-control">
                                <img class="mt-2" id="imagePreview"
                                    style="display: none; max-width: 100%; max-height: 300px;">
                                @error('thumbnail')
                                    <span class="text-danger mt-2">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Mô tả bài viết</label>
                                <textarea id="ckeditor-classic" name="description" class="form-control" id="" cols="30" rows="10">{{ old('description') }}</textarea>
                            </div>
                            <div class="col-md-12 mb-2">
                                <label for="formGroupExampleInput">Nội dung</label>
                                <textarea class="mb-3" id="laraberg" name="content" hidden>{{ old('content') }}</textarea>
                                @error('content')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">
                                Tuỳ chỉnh
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="col-md-12 mb-2">
                                <label class="form-label">Ngày xuất bản</label>
                                <div class="input-group">
                                    <input type="datetime-local" name="published_at" id="datepicker-publish-input"
                                        class="form-control" placeholder="yy/mm/dd hh:mm" data-provider="flatpickr"
                                        data-date-format="Y/m/d" data-enable-time value="{{ old('published_at') }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">
                                Danh mục
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="col-md-12 mb-2">
                                <select class="select2-categories form-control" name="categories[]"
                                    data-placeholder="Chọn danh mục" multiple="multiple">
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ in_array($category->id, old('categories', [])) ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header align-items-center d-flex">
                            <h4 class="card-title mb-0 flex-grow-1">
                                Tags
                            </h4>
                        </div>
                        <div class="card-body">
                            <div class="col-md-12 mb-2">
                                <select class="select2-tags form-control" name="tags[]" data-placeholder="Chọn tags"
                                    multiple="multiple">
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}"
                                            {{ in_array($category->id, old('categories', [])) ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="text-end">
                        <button type="submit" class="btn btn-primary ">Xuất bản</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@push('page-scripts')
    <script src="{{ asset('assets/libs/@ckeditor/ckeditor5-build-classic/build/ckeditor.js') }}"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="https://unpkg.com/react@17.0.2/umd/react.production.min.js"></script>
    <script src="https://unpkg.com/react-dom@17.0.2/umd/react-dom.production.min.js"></script>

    <script src="{{ asset('vendor/laraberg/js/laraberg.js') }}"></script>
    <script>
        $(document).ready(function() {
            Laraberg.init('laraberg');

            $('#imageInput').on('change', function(e) {
                const file = e.target.files[0];
                if (file && file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function() {
                        $('#imagePreview').attr('src', reader.result).show();
                    };
                    reader.readAsDataURL(file);
                }
            });

            $('form').on('submit', function() {
                var content = Laraberg.getContent('laraberg');
                $('textarea[name="content"]').val(content);
            });

            $('.select2-categories').select2({
                placeholder: 'Chọn danh mục'
            });

            $('.select2-tags').select2({
                tags: true,
                tokenSeparators: [','],
                placeholder: 'Chọn thẻ đính kèm'
            });

            ClassicEditor.create($('#ckeditor-classic')[0])
                .then(editor => {
                    editor.ui.view.editable.element.style.height = "200px";
                })
                .catch(console.error);
        });
    </script>
@endpush
