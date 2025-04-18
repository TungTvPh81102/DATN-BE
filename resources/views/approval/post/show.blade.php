@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="profile-foreground position-relative mx-n4 mt-n4">
            <div class="profile-wid-bg">
                <img src="{{ asset('assets/images/profile-bg.jpg') }}" alt="" class="profile-wid-img" />
            </div>
        </div>
        <div class="pt-4 mb-4 mb-lg-3 pb-lg-4 profile-wrapper">
            <div class="row g-4">
                <div class="col-md-auto">
                    <div class="avatar-md">
                        <div class="avatar-title bg-white rounded-circle">
                            <img src="{{ Storage::url($approval->approvable->thumbnail) ?? asset('assets/images/no-photo.jpg') }}"
                                alt="" class="rounded-circle img-fluid h-100 object-fit-cover">
                        </div>
                    </div>
                </div>
                <div class="col">
                    <div class="p-2">
                        <h3 class="text-white mb-1">
                            {{ $approval->approvable->title ?? 'Không có tiêu đề' }}
                            @switch($approval->status)
                                @case('pending')
                                    <span class="badge badge-label bg-warning">
                                        <i class="mdi mdi-circle-medium"></i> Chờ phê duyệt
                                    </span>
                                @break

                                @case('approved')
                                    <span class="badge badge-label bg-success">
                                        <i class="mdi mdi-circle-medium"></i> Đã duyệt
                                    </span>
                                @break

                                @default
                                    <span class="badge badge-label bg-danger">
                                        <i class="mdi mdi-circle-medium"></i> Đã từ chối
                                    </span>
                            @endswitch
                        </h3>
                        <div class="hstack gap-3 flex-wrap mt-3 text-white">
                            <div>
                                <i class="ri-map-pin-user-line me-1"></i>
                                Tác giả: {{ $approval->approvable->user->name ?? '' }}
                            </div>
                            <div class="vr"></div>
                            <div>
                                <i class="ri-building-line align-bottom me-1"></i>
                                Danh mục: {{ $approval->approvable->category->name ?? '' }}
                            </div>
                            <div class="vr"></div>
                            <div>Ngày tạo: <span class="fw-medium">{{ $approval->approvable->created_at ?? '' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-auto order-last order-lg-0">
                    @switch($approval->status)
                        @case('pending')
                            <div class="d-flex gap-1">
                                <form action="{{ route('admin.approvals.posts.approve', $approval->id) }}" method="POST"
                                    id="approveForm">
                                    @csrf
                                    @method('PUT')
                                    <button class="btn btn-primary approve" type="button">Phê duyệt</button>
                                </form>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                                    Từ chối
                                </button>
                            </div>

                            <div id="rejectModal" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel"
                                aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title" id="myModalLabel">Từ chối bài viết</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                aria-label="Close"></button>
                                        </div>
                                        <form id="rejectForm" action="{{ route('admin.approvals.posts.reject', $approval->id) }}"
                                            method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label for="rejectReason" class="form-label">Lý do từ chối</label>
                                                    <textarea placeholder="Nhập lý do từ chối..." class="form-control" id="rejectNote" name="note" rows="3"></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                                                    Huỷ
                                                </button>
                                                <button type="button" class="btn btn-primary" id="submitRejectForm">
                                                    Xác nhận
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @break

                        @case('rejected')
                            <button type="button" class="btn btn-danger">
                                Bài viết không đủ điều kiện
                            </button>
                        @break

                        @case('approved')
                            <button type="button" class="btn btn-success">
                                Bài viết đã được phê duyệt
                            </button>
                        @break

                        @default
                            <button type="button" class="btn btn-secondary">
                                Trạng thái không xác định
                            </button>
                    @endswitch
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div>
                    <div class="d-flex profile-wrapper">
                        <ul class="nav nav-pills animation-nav profile-nav gap-2 gap-lg-3 flex-grow-1" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link fs-14 active" data-bs-toggle="tab" href="#overview-tab" role="tab">
                                    <i class="ri-airplay-fill d-inline-block d-md-none"></i> <span
                                        class="d-none d-md-inline-block">Tổng quan</span>
                                </a>
                            </li>
                            @if (!empty($approval->approval_logs))
                                <li class="nav-item">
                                    <a class="nav-link fs-14" data-bs-toggle="tab" href="#approval_logs" role="tab">
                                        <i class="ri-folder-4-line d-inline-block d-md-none"></i> <span
                                            class="d-none d-md-inline-block">Lịch sử kiểm duyệt</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-content pt-4 text-muted">
            <div class="tab-pane active" id="overview-tab" role="tabpanel">
                <div class="row">
                    <div class="col-xxl-9">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="mb-3">Mô tả</h5>
                                <p class="text-muted mb-4">{!! $approval->approvable->description !!}</p>

                                <div>
                                    <h5 class="mb-3">Nội dung</h5>
                                    <div class="text-muted">{!! $approval->approvable->content !!}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-3">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Thông tin bài viết</h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive table-card">
                                    <table class="table mb-0">
                                        <tbody>
                                            <tr>
                                                <td class="fw-medium">Lượt xem</td>
                                                <td>{{ $approval->approvable->views ?? 0 }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-medium">Bài viết nổi bật</td>
                                                <td>
                                                    @if ($approval->approvable->is_hot)
                                                        <span class="badge bg-success">Có</span>
                                                    @else
                                                        <span class="badge bg-danger">Không</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-medium">Ngày xuất bản</td>
                                                <td>
                                                    @if ($approval->approvable->published_at)
                                                        {{ \Carbon\Carbon::parse($approval->approvable->published_at)->format('d/m/Y') }}
                                                    @else
                                                        <span class="badge bg-warning">Chưa xuất bản</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="tab-pane" id="approval_logs" role="tabpanel">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Lịch sử kiểm duyệt</h5>
                            </div>
                            <div class="card-body">
                                @php
                                    $approval_logs = collect(json_decode($approval->approval_logs, true))
                                        ->sortByDesc('action_at')
                                        ->values()
                                        ->all();
                                @endphp

                                @if (!empty($approval_logs))
                                    @foreach ($approval_logs as $log)
                                        <div
                                            class="card mb-3 shadow-sm border-start border-4 
                                    @switch($log['status'])
                                        @case('approved') border-success @break
                                        @case('rejected') border-danger @break
                                        @default border-secondary
                                    @endswitch
                                ">
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <h6 class="mb-0">{{ $log['name'] }}</h6>
                                                    <small
                                                        class="text-muted">{{ \Carbon\Carbon::parse($log['action_at'])->format('d/m/Y H:i') }}</small>
                                                </div>

                                                <p class="mb-1">
                                                    <strong>Trạng thái: </strong>
                                                    @switch($log['status'])
                                                        @case('approved')
                                                            <span class="badge bg-success">Duyệt</span>
                                                        @break

                                                        @case('rejected')
                                                            <span class="badge bg-danger">Từ chối</span>
                                                        @break

                                                        @default
                                                            <span class="badge bg-secondary">{{ ucfirst($log['status']) }}</span>
                                                    @endswitch
                                                </p>

                                                @if (!empty($log['note']))
                                                    <p class="mb-1"><strong>Ghi chú:</strong> {{ $log['note'] }}</p>
                                                @endif

                                                @if (!empty($log['reason']))
                                                    <p class="mb-0"><strong>Lý do:</strong> {{ $log['reason'] }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="d-flex justify-content-center align-items-center" style="height: 150px;">
                                        <p class="text-muted fs-5 mb-0">Chưa có lịch sử kiểm duyệt</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('page-scripts')
    <script>
        $(document).ready(function() {
            $(".approve").click(function(event) {
                event.preventDefault();

                Swal.fire({
                    title: "Phê duyệt bài viết?",
                    text: "Bạn có chắc chắn muốn phê duyệt bài viết này?",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#DD6B55",
                    confirmButtonText: "Phê duyệt",
                    cancelButtonText: "Huỷ"
                }).then((result) => {
                    if (result.isConfirmed) {
                        $("#approveForm").submit();
                    }
                });
            });

            $('#submitRejectForm').on('click', function() {
                const note = $('#rejectNote').val();

                if (note.trim() === '') {
                    Swal.fire({
                        text: "Vui lòng nhập lý do từ chối.",
                        icon: 'warning'
                    });
                    return;
                }

                $.ajax({
                    type: 'POST',
                    url: $('#rejectForm').attr('action'),
                    data: {
                        _method: 'PUT',
                        note,
                    },
                    success: function(response) {
                        Swal.fire({
                            title: 'Thao tác thành công!',
                            text: 'Lý do từ chối đã được ghi nhận.',
                            icon: 'success'
                        }).then(() => {
                            $('#rejectModal').modal('hide');
                            location.reload();
                        });
                    },
                    error: function(error) {
                        Swal.fire({
                            title: 'Thao tác thất bại!',
                            text: 'Đã có lỗi xảy ra. Vui lòng thử lại.',
                            icon: 'error'
                        });
                    }
                });
            });
        });
    </script>
@endpush
