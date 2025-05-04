@extends('layouts.app')
@push('page-css')
    <!-- plugin css -->
    <link href="{{ asset('assets/libs/jsvectormap/css/jsvectormap.min.css') }}" rel="stylesheet" type="text/css" />
@endpush

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                    <h4 class="mb-sm-0">{{ $title ?? '' }}</h4>

                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">{{ $title ?? '' }}</li>
                        </ol>
                    </div>

                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">{{ $subTitle ?? '' }}</h4>
                        <div class="d-flex gap-2">
                            <div class="search-box ms-2">
                                <input type="text" name="query" id="searchFull" class="form-control search h-75"
                                    placeholder="Tìm kiếm..." data-search value="{{ request()->input('query') ?? '' }}">
                                <button id="search-full" class="h-75 ri-search-line search-icon m-0 p-0 border-0"
                                    style="background: none;"></button>
                            </div>

                            <button class="btn btn-sm btn-primary h-75" id="toggleAdvancedSearch">
                                Tìm kiếm nâng cao
                            </button>
                            <div class="dropdown">
                                <button class="btn btn-sm btn-primary" type="button" id="filterDropdown"
                                    data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ri-filter-2-line"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="filterDropdown"
                                    style="min-width: 500px;">
                                    <div class="container">
                                        <div class="container">
                                            <div class="row">

                                                <li class="col-6">
                                                    <div class="mb-2">
                                                        <label for="startDate" class="form-label">Ngày bình luận</label>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="start_date" id="startDate" data-filter
                                                            value="{{ request()->input('start_date') ?? '' }}">
                                                    </div>
                                                </li>

                                            </div>
                                            <li class="mt-2 d-flex gap-1">
                                                <button class="btn btn-sm btn-success flex-grow-1" type="reset"
                                                    id="resetFilter">Reset</button>
                                                <button class="btn btn-sm btn-primary flex-grow-1" id="applyFilter">Áp
                                                    dụng</button>
                                            </li>

                                        </div>
                                    </div>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Tìm kiếm nâng cao -->
                    <div id="advancedSearch" class="card-header" style="display:none;">
                        <form>
                            <div class="row">
                                <div class="col-md-3">
                                    <label class="form-label">Tên người dùng</label>
                                    <input class="form-control form-control-sm" name="user_name_comment" type="text"
                                        value="{{ request()->input('code') ?? '' }}" placeholder="Nhập tên người dùng..."
                                        data-advanced-filter>
                                </div>

                                <div class="mt-3 text-end">
                                    <button class="btn btn-sm btn-success" type="reset" id="resetFilter">Reset</button>
                                    <button class="btn btn-sm btn-primary" id="applyAdvancedFilter">Áp dụng</button>
                                </div>

                            </div>
                        </form>
                    </div>

                    <div class="card-body" id="item_List">
                        <div class="listjs-table" id="customerList">
                            <div class="table-responsive table-card mt-3 mb-1">
                                <table class="table align-middle table-nowrap">
                                    <thead class="table-light">
                                        <tr>
                                            <th>STT</th>
                                            <th>Người dùng</th>
                                            <th>Bình luận gốc</th>
                                            <th>Vị trí bình luận</th>
                                            <th>Số lượng cảm xúc</th>
                                            <th>Bình luận trả lời</th>
                                            <th>Thời gian bình luận</th>
                                            <th>Hành động</th>
                                        </tr>
                                    </thead>
                                    <tbody class="list form-check-all">
                                        @foreach ($comments as $comment)
                                            <tr>
                                                <th scope="row">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="itemID"
                                                            value="{{ $comment->id }}">
                                                    </div>
                                                </th>
                                                <td class="phone">
                                                    {{ $comment->user->name ?? 'Không xác định' }}
                                                </td>
                                                <td class="id">{{ $comment->content }}</td>

                                                <td class="id">
                                                    {{ $comment->commentable->title ?? 'Nội dung khác' }}
                                                </td>

                                                <td class="id">{{ $comment->total_reactions }}</td>

                                                <td>
                                                    <button class="btn btn-sm btn-primary"
                                                        onclick="loadReplies({{ $comment->id }}, '{{ addslashes($comment->content) }}')"
                                                        data-bs-toggle="modal" data-bs-target="#replyModal"> Xem bình luận
                                                    </button>
                                                </td>
                                                <td>{{ $comment->created_at }}</td>
                                                <td>
                                                    <a href="{{ route('admin.comments.destroy', $comment->id) }}"
                                                        class="sweet-confirm btn btn-sm btn-danger remove-item-btn">
                                                        <span class="ri-delete-bin-7-line"></span>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            {{ $comments->appends(request()->query())->links() }}
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

    <div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="replyModalLabel">Danh sách phản hồi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Hiển thị nội dung bình luận gốc -->
                    <div class="mb-3 p-2 border bg-light">
                        <strong>Bình luận gốc:</strong>
                        <p id="parentCommentContent"></p>
                    </div>

                    <!-- Danh sách các reply -->
                    <ul id="replyList" class="list-group"></ul>
                </div>

            </div>
        </div>
    </div>
@endsection
@push('page-scripts')
    <script>
        var routeUrlFilter = "{{ route('admin.comments.index') }}";
        var routeDeleteAll = "{{ route('admin.comments.destroy', ':itemID') }}";

        $(document).on('click', '#resetFilter', function() {
            window.location = routeUrlFilter;
        });
    </script>

    <script src="{{ asset('assets/js/custom/custom.js') }}"></script>
    <script src="{{ asset('assets/js/common/checkall-option.js') }}"></script>
    <script src="{{ asset('assets/js/common/delete-all-selected.js') }}"></script>
    <script src="{{ asset('assets/js/common/restore-all-selected.js') }}"></script>
    <script src="{{ asset('assets/js/common/filter.js') }}"></script>
    <script src="{{ asset('assets/js/common/search.js') }}"></script>
    <script src="{{ asset('assets/js/common/handle-ajax-search&filter.js') }}"></script>


    <script>
        function loadReplies(commentId, content) {
            document.getElementById("parentCommentContent").innerText = content;

            let replyList = document.getElementById("replyList");
            replyList.innerHTML = '<li class="list-group-item">Đang tải...</li>';

            fetch(`/admin/comments/${commentId}/replies`)
                .then(response => response.json())
                .then(data => {
                    replyList.innerHTML = ''; // Xóa nội dung cũ

                    if (data.length === 0) {
                        replyList.innerHTML = '<li class="list-group-item">Không có phản hồi nào.</li>';
                    } else {
                        data.forEach(reply => {
                            let replyItem = document.createElement("li");
                            replyItem.classList.add("list-group-item", "d-flex", "justify-content-between",
                                "align-items-center");
                            replyItem.id = `reply-${reply.id}`;

                            let reactionCount = reply.reactions_count !== undefined ? reply.reactions_count : 0;

                            replyItem.innerHTML = `
                                <div>
                                    <strong>${reply.user_name}:</strong> ${reply.content} <br>
                                    <small class="text-muted">${reply.created_at}</small>
                                </div>
                                <div class="col-3 text-end">
                                    <span class="text-danger">${reactionCount} lượt react</span>
                                    <button class="sweet-confirm btn btn-sm btn-danger remove-item-btn">
                                        <span class="ri-delete-bin-7-line" onclick="deleteReply(${reply.id})"></span>
                                    </button>
                                </div>     
                            `;

                            replyList.appendChild(replyItem);
                        });
                    }
                })
                .catch(error => {
                    console.error("Lỗi khi tải phản hồi:", error);
                    replyList.innerHTML = '<li class="list-group-item text-danger">Lỗi khi tải phản hồi.</li>';
                });
        }


        function deleteReply(replyId) {
            Swal.fire({
                title: "Bạn có muốn xóa?",
                text: "Bạn sẽ không thể khôi phục dữ liệu khi xoá!!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Đồng ý!!",
                cancelButtonText: "Hủy!!"
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`/admin/comments/${replyId}`, {
                            method: "DELETE",
                            headers: {
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    "content"),
                                "Content-Type": "application/json",
                            },
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === "success") {
                                let replyElement = document.getElementById(`reply-${replyId}`);
                                if (replyElement) {
                                    replyElement.remove(); // Xóa chỉ khi phần tử tồn tại
                                    Swal.fire('Đã xóa!', 'Phản hồi đã được xóa.', 'success');
                                } else {
                                    Swal.fire('Lỗi!', 'Không tìm thấy phản hồi để xóa.', 'error');
                                }
                            } else {
                                Swal.fire('Lỗi!', 'Xóa thất bại, vui lòng thử lại.', 'error');
                            }
                        })
                        .catch(error => {
                            console.error("Lỗi khi xóa phản hồi:", error);
                            Swal.fire('Lỗi!', 'Xảy ra lỗi, vui lòng thử lại sau.', 'error');
                        });
                }
            });
        }
    </script>
@endpush
