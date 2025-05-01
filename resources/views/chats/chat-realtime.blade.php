@vite(['resources/js/app.js'])
@extends('layouts.app')
@push('page-css')
    <!-- glightbox css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/glightbox/css/glightbox.min.css') }}">
    <link href="{{ asset('assets/css/select2.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/cssChat.css') }}" rel="stylesheet" type="text/css" />
    <style>
        .online {
            color: green;
        }

        .offline {
            color: gray;
        }
    </style>
@endpush
@php
    $title = 'Chat';
@endphp

@section('content')
    <div class="container-fluid">
        <div class="chat-wrapper d-lg-flex gap-1 mx-n4 mt-n4 p-1">
            <div class="chat-leftsidebar" style="height: auto !important">
                <div class="px-4 pt-4 mb-3">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="mb-4">Phòng chat</h5>
                        </div>
                        <!-- Modal -->
                        <div class="modal fade" id="addGroupModal" tabindex="-1" aria-labelledby="addGroupModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content rounded-3 shadow-lg border-0">
                                    <!-- Header -->
                                    <div class="modal-header bg-primary text-white rounded-top">
                                        <h5 class="modal-title text-white fw-bold">
                                            <i class="ri-group-line me-2"></i> Thêm Hội Thoại
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>

                                    <!-- Body -->
                                    <div class="modal-body p-4 bg-light">
                                        <form id="createGroupChatForm">
                                            @csrf

                                            <!-- Nhập tên nhóm -->
                                            <div class="mb-3">
                                                <label for="groupName" class="fw-semibold mb-2">Tên nhóm</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white">
                                                        <i class="ri-chat-3-line"></i>
                                                    </span>
                                                    <input class="form-control py-2 shadow-sm" name="name" id="groupName"
                                                        placeholder="Nhập tên nhóm" type="text" required />
                                                </div>
                                            </div>

                                            <!-- Chọn thành viên -->
                                            <div class="mb-3">
                                                <label for="groupMembers" class="fw-semibold mb-2">Thêm Thành
                                                    Viên</label>
                                                <select id="groupMembers" name="members[]" class="form-select shadow-sm"
                                                    multiple="multiple">
                                                    @foreach ($data['admins'] as $admin)
                                                        <option value="{{ $admin->id }}"
                                                            data-avatar="{{ $admin->avatar }}">
                                                            {{ $admin->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <!-- Nút Thêm -->
                                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                                                <i class="ri-add-circle-line me-1"></i> Tạo Nhóm
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal -->
                        <div class="modal fade" id="addChatPrivateModal" tabindex="-1" aria-labelledby="addGroupModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content rounded-3 shadow-lg border-0">
                                    <!-- Header -->
                                    <div class="modal-header bg-primary text-white rounded-top">
                                        <h5 class="modal-title text-white fw-bold">
                                            <i class="ri-chat-new-line me-2"></i> Thêm Trò Chuyện
                                        </h5>
                                        <button type="button" class="btn-close btn-close-white" data-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>

                                    <!-- Body -->
                                    <div class="modal-body p-4 bg-light">
                                        <form id="createPrivateChatForm">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="received" class="fw-semibold mb-2">Chọn người muốn trò
                                                    chuyện</label>
                                                <div class="input-group">
                                                    <span class="input-group-text bg-white">
                                                        <i class="ri-user-add-line"></i>
                                                    </span>
                                                    <select id="received" name="user_id" class="form-select shadow-sm">
                                                        <option value="" disabled selected>Chọn thành viên...
                                                        </option>
                                                        @foreach ($data['users'] as $user)
                                                            <option value="{{ $user->id }}"
                                                                data-avatar="{{ $user->avatar }}">
                                                                {{ $user->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                                                <i class="ri-add-line me-1"></i> Thêm
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex-shrink-0">
                            <div data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="bottom"
                                title="Add Contact">

                                <!-- Button trigger modal -->

                            </div>
                        </div>
                    </div>
                    <div class="search-box">
                        <input type="text" class="form-control bg-light border-light" placeholder="Tìm kiếm...">
                        <i class="ri-search-2-line search-icon"></i>
                    </div>
                </div> <!-- .p-4 -->

                <ul class="nav nav-tabs nav-tabs-custom nav-success nav-justified" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#chats" role="tab">
                            Trò chuyện
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#contacts" role="tab">
                            Liên hệ gần nhất
                        </a>
                    </li>
                </ul>

                <div class="tab-content text-muted">
                    <div class="tab-pane active" id="chats" role="tabpanel">
                        <div class="chat-room-list pt-3" data-simplebar>
                            <div class="d-flex align-items-center px-4 mb-2">
                                <div class="flex-grow-1">
                                    <h4 class="mb-0 fs-13">Tin nhắn trực tiếp</h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="bottom"
                                        title="New Message">

                                        <!-- Button trigger modal -->
                                        <button type="button" class="btn btn-soft-success btn-sm shadow-none"
                                            data-toggle="modal" data-target="#addChatPrivateModal">
                                            <i class="ri-add-line align-bottom"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="chat-message-list">

                                <ul class="list-unstyled chat-list chat-user-list usersList">

                                    @foreach ($data['channels'] as $channel)
                                        @if ($channel->type == 'direct')
                                            <li class="">
                                                <a href="#" class="unread-msg-user private-button"
                                                    data-private-id="{{ $channel->id }}">
                                                    <div class="d-flex align-items-center">
                                                        <div
                                                            class="flex-shrink-0 chat-user-img align-self-center me-2 ms-0">
                                                            <div class="avatar-xxs">
                                                                <div
                                                                    class="avatar-title bg-light rounded-circle text-body">
                                                                    @php
                                                                        $otherUser = $channel->users
                                                                            ->where('id', '<>', auth()->id())
                                                                            ->first();
                                                                    @endphp
                                                                    <img class="rounded-5"
                                                                        src="{{ $otherUser->avatar ?? url('assets/images/users/multi-user.jpg') }}"
                                                                        alt="" width="30px">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1 overflow-hidden">
                                                            <p class="text-truncate mb-0">
                                                                {{ $otherUser->name ?? 'Người dùng không xác định' }}
                                                            </p>

                                                        </div>
                                                        <button class="btn avatar-xs p-0" type="button"
                                                            data-bs-toggle="dropdown" aria-haspopup="true"
                                                            aria-expanded="false"
                                                            data-conversation-id="{{ $channel->id }}"
                                                            onclick="deleteConversation(this)">
                                                            <span class="avatar-title bg-light text-body rounded">
                                                                <i
                                                                    class="ri-delete-bin-5-line align-bottom text-muted"></i>
                                                            </span>
                                                        </button>
                                                    </div>

                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>

                            <div class="d-flex align-items-center px-4 mt-4 pt-2 mb-2">
                                <div class="flex-grow-1">
                                    <h4 class="mb-0 fs-13">Nhóm</h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="bottom"
                                        title="New Message">
                                        <!-- Button trigger modal -->
                                        <button type="button" class="btn btn-soft-success btn-sm" data-toggle="modal"
                                            data-target="#addGroupModal">
                                            <i class="ri-add-line align-bottom"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="chat-message-list">

                                <ul class="list-unstyled chat-list chat-user-list mb-0 conversationList">

                                    @foreach ($data['channels'] as $channel)
                                        @if ($channel->type == 'group')
                                            <li class="">
                                                <a href="#" class="unread-msg-user group-button"
                                                    data-group-id="{{ $channel->id }}">
                                                    <div class="d-flex align-items-center">
                                                        <div
                                                            class="flex-shrink-0 chat-user-img align-self-center me-2 ms-0">
                                                            <div class="avatar-xxs">
                                                                <div
                                                                    class="avatar-title bg-light rounded-circle text-body">
                                                                    #
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="flex-grow-1 overflow-hidden">
                                                            <p class="text-truncate mb-0">
                                                                {{ $channel->name }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </a>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>
                            <!-- End chat-message-list -->
                        </div>
                    </div>
                    <div class="tab-pane" id="contacts" role="tabpanel">
                        <div class="chat-room-list pt-3" data-simplebar>
                            <div class="sort-contact">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end tab contact -->
            </div>
            <!-- end chat leftsidebar -->
            <!-- Start User chat -->
            <div class="user-chat w-100 overflow-hidden">

                <div class="chat-content d-lg-flex">
                    <!-- start chat conversation section -->
                    <div class="w-100 overflow-hidden position-relative">
                        <!-- conversation user -->
                        <div class="position-relative">


                            <div class="position-relative" id="users-chat">
                                <div class="p-3 user-chat-topbar">
                                    <div class="row align-items-center">
                                        <div class="col-sm-4 col-8">
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0 d-block d-lg-none me-3">
                                                    <a href="javascript: void(0);" class="user-chat-remove fs-18 p-1"><i
                                                            class="ri-arrow-left-s-line align-bottom"></i></a>
                                                </div>
                                                <div class="flex-grow-1 overflow-hidden">
                                                    <div class="d-flex align-items-center">
                                                        <div
                                                            class="flex-shrink-0 chat-user-img online user-own-img align-self-center me-3 ms-0">

                                                            <img src=""
                                                                class="rounded-circle avatar-xs imageUser avatar"
                                                                alt="">
                                                            <span class="text-success show-status-user"></span>
                                                        </div>
                                                        <div class="flex-grow-1 overflow-hidden" id="groupInfo">
                                                            <h5 class="text-truncate mb-0 fs-16">
                                                                <a class="text-reset name nameUser"></a>
                                                            </h5>
                                                            <p class="text-truncate text-muted fs-14 mb-0 userStatus">
                                                                <small id="memberCount"></small>
                                                            </p>
                                                            <!-- Modal chi tiết nhóm -->
                                                            <div class="offcanvas offcanvas-end border-0" tabindex="-1"
                                                                id="userProfileCanvasExample">
                                                                <!--end offcanvas-header-->
                                                                <div class="offcanvas-body profile-offcanvas p-0">
                                                                    <div class="team-cover">
                                                                        <img src="{{ asset('assets/images/small/img-9.jpg') }}"
                                                                            alt="" class="img-fluid" />
                                                                    </div>
                                                                    <div class="p-1 pb-4 pt-0">
                                                                        <div class="team-settings">
                                                                            <div class="row g-0">
                                                                                <div class="col">
                                                                                    <div class="btn nav-btn">
                                                                                        <button type="button"
                                                                                            class="btn-close btn-close-white"
                                                                                            data-bs-dismiss="offcanvas"
                                                                                            aria-label="Close"></button>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-auto">
                                                                                    <div class="user-chat-nav d-flex">
                                                                                        <button type="button"
                                                                                            class="btn nav-btn favourite-btn active">
                                                                                            <i class="ri-star-fill"></i>
                                                                                        </button>

                                                                                        <div class="dropdown">
                                                                                            <a class="btn nav-btn"
                                                                                                href="javascript:void(0);"
                                                                                                data-bs-toggle="dropdown"
                                                                                                aria-expanded="false">
                                                                                                <i
                                                                                                    class="ri-more-2-fill"></i>
                                                                                            </a>
                                                                                            <ul
                                                                                                class="dropdown-menu dropdown-menu-end">
                                                                                                <li>
                                                                                                    <a class="dropdown-item"
                                                                                                        href="javascript:void(0);"><i
                                                                                                            class="ri-inbox-archive-line align-bottom text-muted me-2"></i>Archive</a>
                                                                                                </li>
                                                                                                <li>
                                                                                                    <a class="dropdown-item"
                                                                                                        href="javascript:void(0);"><i
                                                                                                            class="ri-mic-off-line align-bottom text-muted me-2"></i>Muted</a>
                                                                                                </li>
                                                                                                <li>
                                                                                                    <a class="dropdown-item"
                                                                                                        href="javascript:void(0);"><i
                                                                                                            class="ri-delete-bin-5-line align-bottom text-muted me-2"></i>Delete</a>
                                                                                                </li>
                                                                                            </ul>
                                                                                        </div>
                                                                                    </div>

                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <!--end col-->
                                                                    </div>
                                                                    <div class="p-3 text-center">
                                                                        <img src="" alt=""
                                                                            class="avatar-lg img-thumbnail rounded-circle mx-auto profile-img imageUser avatar">
                                                                        <div class="mt-3">
                                                                            <h5 class="fs-16 mb-1"><a
                                                                                    href="javascript:void(0);"
                                                                                    class="link-primary nameUser name"></a>
                                                                            </h5>
                                                                            <p class="text-muted"><i
                                                                                    class="ri-checkbox-blank-circle-fill me-1 align-bottom text-success"></i>Online
                                                                            </p>
                                                                        </div>

                                                                        <div class="d-flex gap-2 justify-content-center">

                                                                            <button type="button"
                                                                                class="btn avatar-xs p-0 getID"
                                                                                data-bs-toggle="tooltip"
                                                                                data-bs-placement="top" title="Favourite"
                                                                                data-conversation-id="">
                                                                                <span
                                                                                    class="avatar-title rounded bg-light text-body">
                                                                                    <i class="ri-star-line"></i>
                                                                                </span>
                                                                            </button>

                                                                            <button type="button"
                                                                                class="btn avatar-xs p-0"
                                                                                data-bs-toggle="tooltip"
                                                                                data-bs-placement="top" title="Phone">
                                                                                <span
                                                                                    class="avatar-title rounded bg-light text-body">
                                                                                    <i class="ri-phone-line"></i>
                                                                                </span>
                                                                            </button>
                                                                            <button type="button"
                                                                                class="btn avatar-xs p-0 getID"
                                                                                title="Rời nhóm" data-conversation-id=""
                                                                                onclick="leaveConversation(this)">
                                                                                <span
                                                                                    class="avatar-title rounded bg-light text-body">
                                                                                    <i
                                                                                        class="ri-delete-bin-5-line align-bottom"></i>
                                                                                </span>
                                                                            </button>
                                                                            <div class="dropdown">
                                                                                <button class="btn avatar-xs p-0"
                                                                                    type="button"
                                                                                    data-bs-toggle="dropdown"
                                                                                    aria-haspopup="true"
                                                                                    aria-expanded="false">
                                                                                    <span
                                                                                        class="avatar-title bg-light text-body rounded">
                                                                                        <i class="ri-more-fill"></i>
                                                                                    </span>
                                                                                </button>

                                                                                <ul
                                                                                    class="dropdown-menu dropdown-menu-end">
                                                                                    <li><a class="dropdown-item"
                                                                                            href="javascript:void(0);"><i
                                                                                                class="ri-inbox-archive-line align-bottom text-muted me-2"></i>Archive</a>
                                                                                    </li>
                                                                                    <li><a class="dropdown-item"
                                                                                            href="javascript:void(0);"><i
                                                                                                class="ri-mic-off-line align-bottom text-muted me-2"></i>Muted</a>
                                                                                    </li>
                                                                                    <li><a class="dropdown-item getID"
                                                                                            href="#"
                                                                                            data-conversation-id=""
                                                                                            onclick="dissolveGroup(this)"><i
                                                                                                class="las la-skull-crossbones align-bottom text-muted me-2"></i>Giải
                                                                                            tán nhóm</a>
                                                                                    </li>
                                                                                </ul>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <ul class="nav nav-tabs" id="myTab"
                                                                        role="tablist">
                                                                        <div class="row w-100" id="addClass">
                                                                            <li class="nav-item col-6" id="OnetoOne"
                                                                                role="presentation">
                                                                                <button class="nav-link active w-100"
                                                                                    id="members-tab" data-bs-toggle="tab"
                                                                                    data-bs-target="#members"
                                                                                    type="button" role="tab"
                                                                                    aria-controls="members"
                                                                                    aria-selected="true">
                                                                                    Danh sách thành viên
                                                                                </button>
                                                                            </li>
                                                                            <li class="nav-item col-6" id="filetofile"
                                                                                role="presentation">
                                                                                <button class="nav-link w-100"
                                                                                    id="files-tab" data-bs-toggle="tab"
                                                                                    data-bs-target="#files" type="button"
                                                                                    role="tab" aria-controls="files"
                                                                                    aria-selected="false">
                                                                                    File đã gửi
                                                                                </button>
                                                                            </li>
                                                                        </div>
                                                                    </ul>

                                                                    <!-- Nội dung tabs chính -->
                                                                    <div class="tab-content" id="myTabContent">
                                                                        <!-- Danh sách thành viên -->
                                                                        <div class="tab-pane fade show active border-top border-top-dashed p-3 memberLists"
                                                                            id="members" role="tabpanel"
                                                                            aria-labelledby="members-tab">
                                                                            <ul class="list-group member-list"
                                                                                id="membersList"></ul>
                                                                        </div>

                                                                        <!-- File đã gửi -->
                                                                        <div class="files-chat-message tab-pane fade border-top border-top-dashed p-3"
                                                                            id="files" role="tabpanel"
                                                                            aria-labelledby="files-tab">
                                                                            <!-- Tabs con -->
                                                                            <ul class="nav nav-pills mb-3" id="fileSubTab"
                                                                                role="tablist">
                                                                                <div class="row w-100">
                                                                                    <li class="nav-item col-6"
                                                                                        role="presentation">
                                                                                        <button
                                                                                            class="nav-link active w-100"
                                                                                            id="media-tab"
                                                                                            data-bs-toggle="tab"
                                                                                            data-bs-target="#media"
                                                                                            type="button" role="tab"
                                                                                            aria-controls="media"
                                                                                            aria-selected="true">
                                                                                            Ảnh & Video
                                                                                        </button>
                                                                                    </li>
                                                                                    <li class="nav-item col-6"
                                                                                        role="presentation">
                                                                                        <button class="nav-link w-100"
                                                                                            id="documents-tab"
                                                                                            data-bs-toggle="tab"
                                                                                            data-bs-target="#documents"
                                                                                            type="button" role="tab"
                                                                                            aria-controls="documents"
                                                                                            aria-selected="false">
                                                                                            File
                                                                                        </button>
                                                                                    </li>
                                                                                </div>
                                                                            </ul>

                                                                            <!-- Nội dung tabs con -->
                                                                            <div class="tab-content"
                                                                                id="fileSubTabContent">
                                                                                <!-- Ảnh & Video -->
                                                                                <div class="tab-pane fade show active"
                                                                                    id="media" role="tabpanel"
                                                                                    aria-labelledby="media-tab">
                                                                                    <div class="border rounded border-dashed p-2"
                                                                                        id="mediaFilesList"></div>
                                                                                    <div class="text-center mt-2">
                                                                                        <button id="loadMoreMedia"
                                                                                            type="button"
                                                                                            class="btn btn-danger">
                                                                                            Load more <i
                                                                                                class="ri-arrow-right-fill align-bottom ms-1"></i>
                                                                                        </button>
                                                                                    </div>
                                                                                </div>

                                                                                <!-- File -->
                                                                                <div class="tab-pane fade" id="documents"
                                                                                    role="tabpanel"
                                                                                    aria-labelledby="documents-tab">
                                                                                    <div class="border rounded border-dashed p-2"
                                                                                        id="documentFilesList">
                                                                                    </div>
                                                                                    <div class="text-center mt-2">
                                                                                        <button id="loadMoreDocuments"
                                                                                            type="button"
                                                                                            class="btn btn-danger">
                                                                                            Load more <i
                                                                                                class="ri-arrow-right-fill align-bottom ms-1"></i>
                                                                                        </button>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                </div>
                                                                <!--end offcanvas-body-->
                                                            </div>
                                                            <!-- Modal thêm thành viên -->
                                                            <div id="myModal" class="modal fade" tabindex="-1"
                                                                aria-labelledby="myModalLabel" aria-hidden="true"
                                                                style="display: none;">
                                                                <div class="modal-dialog">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header">
                                                                            <h5 class="modal-title" id="myModalLabel">
                                                                                Thêm
                                                                                thành viên</h5>
                                                                            <button type="button" class="btn-close"
                                                                                data-bs-dismiss="modal"
                                                                                aria-label="Close"></button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <form>
                                                                                @csrf
                                                                                <div class="form-group mb-3">
                                                                                    <label for="groupMembers"
                                                                                        class="font-weight-bold">Chọn
                                                                                        thành
                                                                                        viên</label>
                                                                                    <select tabindex="-1" id="addMembers"
                                                                                        name="members[]"
                                                                                        multiple="multiple">

                                                                                        @foreach ($data['admins'] as $member)
                                                                                            <option
                                                                                                value="{{ $member->id }}"
                                                                                                class="select-member-option">
                                                                                                {{ $member->name }}
                                                                                            </option>
                                                                                        @endforeach
                                                                                    </select>
                                                                                </div>
                                                                                <div class="modal-footer">
                                                                                    <button type="button"
                                                                                        class="btn btn-light"
                                                                                        data-bs-dismiss="modal">
                                                                                        Close
                                                                                    </button>
                                                                                    <button type="submit"
                                                                                        class="btn btn-primary getID"
                                                                                        id="addMembersButton"
                                                                                        data-conversation-id="">Thêm
                                                                                    </button>
                                                                                </div>
                                                                            </form>
                                                                        </div>
                                                                    </div><!-- /.modal-content -->
                                                                </div><!-- /.modal-dialog -->
                                                            </div><!-- /.modal -->
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-sm-8 col-4">
                                            <ul class="list-inline user-chat-nav text-end mb-0">
                                                <!-- Kiểm tra nếu có cuộc trò chuyện 'direct' -->
                                                <li class="list-inline-item m-0">
                                                    <div class="dropdown">
                                                        <button class="btn btn-ghost-secondary btn-icon" type="button"
                                                            data-bs-toggle="dropdown" aria-haspopup="true"
                                                            aria-expanded="false">
                                                            <i class="lab la-sistrix"
                                                                style="font-size: 20px;color:black"></i>
                                                        </button>
                                                        <div class="dropdown-menu p-0 dropdown-menu-end dropdown-menu-lg">
                                                            <div class="p-2">
                                                                <div class="search-box">
                                                                    <input type="text"
                                                                        class="form-control bg-light border-light"
                                                                        placeholder="Tìm kiếm..."
                                                                        onkeyup="searchMessages()" id="searchMessage">
                                                                    <i class="ri-search-2-line search-icon"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="list-inline-item m-0" id="showadd" style="display: none;">
                                                    <button type="button" class="btn btn-ghost-secondary btn-icon"
                                                        title="Thêm thành viên" data-bs-toggle="modal"
                                                        data-bs-target="#myModal">
                                                        <i class="las la-user-plus"
                                                            style="font-size: 20px;color:black"></i>
                                                    </button>
                                                </li>
                                                <li class="list-inline-item d-none d-lg-inline-block m-0">
                                                    <button type="button" class="btn btn-ghost-secondary btn-icon"
                                                        data-bs-toggle="offcanvas"
                                                        data-bs-target="#userProfileCanvasExample"
                                                        aria-controls="userProfileCanvasExample">
                                                        <i class="las la-users-cog"
                                                            style="font-size: 20px;color:black"></i>
                                                    </button>
                                                </li>
                                            </ul>


                                        </div>
                                    </div>

                                </div>
                                <!-- end chat user head -->
                                <div class="chat-conversation p-3 p-lg-4 " id="chatBox" data-simplebar>
                                    <div id="elmLoader">
                                        <div class="spinner-border text-primary avatar-sm" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                    <ul class="list-unstyled chat-conversation-list" id="messagesList">

                                    </ul>
                                    <!-- end chat-conversation-list -->
                                </div>
                                <div class="alert alert-warning alert-dismissible copyclipboard-alert px-4 fade show "
                                    id="copyClipBoard" role="alert">
                                    Message copied
                                </div>
                            </div>
                            <!-- Default Modals -->
                            <!-- end chat-conversation -->

                            <div class="chat-input-section p-3 p-lg-4">

                                <form id="chatinput-form" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row g-0 align-items-center">
                                        <div id="previewContainer"
                                            style="display: none; align-items: center; gap: 8px; max-width: 100%; padding: 5px; border-radius: 8px; position: relative;">
                                            <img id="imagePreview" src="" alt="Image Preview"
                                                style="max-width: 100px; max-height: 100px; border-radius: 5px; object-fit: cover;">
                                            <button type="button" onclick="removeImage()"
                                                style="border: none; background: red; color: white; padding: 5px; border-radius: 50%; font-size: 12px; width: 20px; height: 20px; cursor: pointer; position: absolute; top: -5px; right: -5px;">
                                                ✖
                                            </button>
                                        </div>
                                        <div class="col-auto">
                                            <div class="chat-input-links me-2">

                                                <div class="links-list-item">
                                                    <button type="button"
                                                        class="btn btn-link text-decoration-none emoji-btn"
                                                        id="emoji-btn">
                                                        <i class="bx bx-smile align-middle"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-link text-decoration-none"
                                                        id="upload-btn">
                                                        <i class="bx bx-paperclip align-middle"></i>
                                                    </button>

                                                    <input type="file" name="input_file[]" id="fileInput" multiple
                                                        style="display: none;">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col">

                                            <div class="chat-input-feedback">
                                                Please Enter a Message
                                            </div>
                                            <input type="text" class="form-control chat-input bg-light border-light"
                                                id="messageInput" placeholder="Type your message..." autocomplete="off">
                                            <input type="hidden" id="parentMessageId">
                                        </div>
                                        <div class="col-auto">
                                            <div class="chat-input-links ms-2">
                                                <div class="links-list-item">
                                                    <button type="submit" id="sendMessageButton"
                                                        class="btn btn-success chat-send waves-effect waves-light">
                                                        <i class="ri-send-plane-2-fill align-bottom"></i>
                                                    </button>
                                                </div>
                                            </div>

                                        </div>

                                    </div>
                                </form>
                            </div>
                            <div class="replyCard">
                                <div class="card mb-0">
                                    <div class="card-body py-3">
                                        <div class="replymessage-block mb-0 d-flex align-items-start">
                                            <div class="flex-grow-1">
                                                <h5 class="conversation-name"></h5>
                                                <p class="mb-0"></p>
                                            </div>
                                            <div class="flex-shrink-0">
                                                <button type="button" id="close_toggle"
                                                    class="btn btn-sm btn-link mt-n2 me-n3 fs-18">
                                                    <i class="bx bx-x align-middle"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- end chat-wrapper -->
    </div>
@endsection

@push('page-scripts')
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="{{ asset('assets/libs/glightbox/js/glightbox.min.js') }}"></script>
    <script src="{{ asset('assets/libs/fg-emoji-picker/fgEmojiPicker.js') }}"></script>

    <script>
        var APP_URL = "{{ env('APP_URL') . '/' }}";
        const userId = "{{ auth()->id() }}"; // Truyền id người dùng từ Laravel sang JavaScript
        var COUNTWEB = 1;
        const firstChanelId = "{{ $data['firstChanel']['id'] }}";
        const firstChanelType = "{{ $data['firstChanel']['type'] }}";

        document.addEventListener("DOMContentLoaded", function() {
            const selectElement = document.getElementById("received");

            selectElement.addEventListener("change", function() {
                const selectedOption = selectElement.options[selectElement.selectedIndex];
                const avatarUrl = selectedOption.getAttribute("data-avatar") ||
                    'default-avatar.png'; // Hình mặc định nếu không có avatar

                // Thêm avatar vào dropdown
                const container = document.querySelector('.custom-select-container');
                let avatarElement = container.querySelector(".avatar");

                if (!avatarElement) {
                    avatarElement = document.createElement("img");
                    avatarElement.classList.add("avatar");
                    container.prepend(avatarElement);
                }

                avatarElement.src = avatarUrl;
            });
        });

        function initIcons() {
            document.addEventListener("DOMContentLoaded", function() {
                let emojiButton = document.getElementById("emoji-btn");
                if (!emojiButton) {
                    console.error("Không tìm thấy nút emoji-btn!");
                    return;
                }

                let emojiPicker = new FgEmojiPicker({
                    trigger: [".emoji-btn"],
                    removeOnSelection: false,
                    closeButton: true,
                    position: ["top", "right"],
                    preFetch: true,
                    dir: "assets/js/pages/plugins/json",
                    insertInto: document.querySelector(".chat-input"),
                });

                emojiButton.addEventListener("click", function() {
                    setTimeout(function() {
                        let pickerEl = document.querySelector(".fg-emoji-picker");
                        if (pickerEl) {
                            let leftPos = parseInt(window.getComputedStyle(pickerEl).left) || 0;
                            pickerEl.style.left = `${leftPos - 40}px`;
                        } else {
                            console.error("Không tìm thấy phần tử fg-emoji-picker!");
                        }
                    }, 100);
                });
            });
        }

        initIcons();

        $(document).ready(function() {

            $("#upload-btn").click(function() {
                $("#fileInput").click();
            });

            $("#fileInput").change(function() {
                let files = this.files;
                let previewContainer = $("#previewContainer");
                previewContainer.html("");

                if (!files.length) return;

                Array.from(files).forEach(file => {
                    let fileType = file.type;
                    let fileExt = file.name.split('.').pop().toLowerCase();
                    let reader = new FileReader();

                    reader.onload = function(e) {
                        let previewHtml = "";

                        if (fileType.startsWith("image/")) {
                            previewHtml = `<img src="${e.target.result}" class="thumbnail"
                    style="max-width:150px; border-radius: 8px; margin:5px;">`;
                        } else if (fileType.startsWith("video/")) {
                            previewHtml = `<video class="thumbnail" controls
                    style="max-width:150px; border-radius: 8px; margin:5px;">
                    <source src="${e.target.result}" type="${fileType}">
                  </video>`;
                        } else if (fileType === "application/pdf") {
                            previewHtml = `<embed src="${e.target.result}" class="thumbnail"
                    style="width:100px; height:100px; border-radius: 8px; margin:5px;">`;
                        } else {
                            let fileThumbnail = getFileThumbnail(fileExt);
                            previewHtml = `<div class="file-thumbnail" style="display:inline-block; text-align:center; margin:5px;">
                    <img src="${fileThumbnail}" style="width:50px; height:50px;">
                    <p style="font-size:12px;">${file.name}</p>
                  </div>`;
                        }

                        previewContainer.append(previewHtml);
                    };

                    reader.readAsDataURL(file);
                });

                previewContainer.show();
            });

            function getFileThumbnail(ext) {
                return `/assets/images/icons/${ext}.png`;
            }

            function removeImage() {
                document.getElementById("imagePreview").src = "";
                document.getElementById("previewContainer").style.display = "none";
                document.getElementById("chatinput-form").reset();
            }
        });

        $(document).ready(function() {
            $('#groupMembers').select2({
                placeholder: "Chọn thành viên thêm vào nhóm",
                allowClear: true,
                dropdownParent: $('#addGroupModal'),
                templateResult: formatUser, // Hiển thị trong danh sách
                templateSelection: formatUserSelection // Hiển thị sau khi chọn
            });

            function formatUser(user) {
                if (!user.id) {
                    return user.text; // Trả về văn bản nếu không có ID
                }

                var avatar = $(user.element).data('avatar'); // Lấy avatar từ data-avatar
                var $user = $(
                    `<div class="d-flex align-items-center">
                <img src="${avatar}" class="rounded-circle me-2" width="30" height="30" />
                <span>${user.text}</span>
            </div>`
                );

                return $user;
            }

            function formatUserSelection(user) {
                if (!user.id) {
                    return user.text;
                }

                var avatar = $(user.element).data('avatar');
                return $(
                    `<div class="d-flex align-items-center">
                <img src="${avatar}" class="rounded-circle me-2" width="25" height="25" />
                <span>${user.text}</span>
            </div>`
                );
            }
        });
        $(document).ready(function() {
            $('#received').select2({
                placeholder: "Chọn thành viên thêm vào nhóm",
                allowClear: true,
                dropdownParent: $('#addChatPrivateModal'),
                templateResult: formatUser, // Hiển thị trong danh sách
                templateSelection: formatUserSelection // Hiển thị sau khi chọn
            });

            function formatUser(user) {
                if (!user.id) {
                    return user.text; // Trả về văn bản nếu không có ID
                }

                var avatar = $(user.element).data('avatar'); // Lấy avatar từ data-avatar
                var $user = $(
                    `<div class="d-flex align-items-center">
                <img src="${avatar}" class="rounded-circle me-2" width="30" height="30" />
                <span>${user.text}</span>
            </div>`
                );

                return $user;
            }

            function formatUserSelection(user) {
                if (!user.id) {
                    return user.text;
                }

                var avatar = $(user.element).data('avatar');
                return $(
                    `<div class="d-flex align-items-center">
                <img src="${avatar}" class="rounded-circle me-2" width="25" height="25" />
                <span>${user.text}</span>
            </div>`
                );
            }
        });
        // Tạo chat đơn
        var currentConversationId = null;
        $(document).ready(function() {
            $('#createPrivateChatForm').submit(function(event) {
                event.preventDefault();

                var formData = $(this).serialize(); // Lấy dữ liệu từ form
                $.ajax({
                    url: "{{ route('admin.chats.createOnetoOne') }}",
                    method: "POST",
                    data: formData,
                    success: function(response) {
                        console.log(response);

                        if (response.status == 'success') {
                            // Cập nhật lại dữ liệu nhóm và admin trên giao diện
                            $('#usersList').html(response.data.channels);
                            // alert(response.message); // Hiển thị thông báo thành công
                            Toastify({
                                text: "Thêm cuộc hội thoại thành công!",
                                backgroundColor: "green",
                                duration: 3000, // Thời gian hiển thị thông báo (3 giây)
                                close: true
                            }).showToast();
                            window.location.href = "{{ route('admin.chats.index') }}";
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText); // Log lỗi để debug

                        let errorMessage = "Đã có lỗi xảy ra. Vui lòng thử lại.";

                        if (xhr.status === 400) {
                            // 📌 Nếu API trả về lỗi 400 (cuộc trò chuyện đã tồn tại)
                            let res = JSON.parse(xhr.responseText);
                            errorMessage = res.message || "Cuộc trò chuyện đã tồn tại!";
                        } else if (xhr.status === 500) {
                            // 📌 Nếu API gặp lỗi hệ thống (500 Internal Server Error)
                            errorMessage = "Lỗi hệ thống, vui lòng thử lại sau!";
                        }

                        Toastify({
                            text: errorMessage,
                            backgroundColor: "red",
                            duration: 3000,
                            close: true
                        }).showToast();
                    }
                });
            });

            $(document).on('click', '.usersList a', function(event) {
                event.preventDefault(); // Ngừng hành động mặc định của liên kết

                var channelId = $(this).data('private-id'); // Lấy ID của nhóm chat

                // Gửi yêu cầu AJAX để lấy thông tin nhóm
                getUserInfo(channelId);
            });

            // Khi người dùng chọn một nhóm
            $(document).on('click', '.private-button', function() {
                if (window.Echo) {
                    window.Echo.leave('conversation.' + currentConversationId);
                }

                const data = new FormData();
                data.append('conversationId', currentConversationId);
                data.append('_token', $('meta[name="csrf-token"]').attr('content'));

                navigator.sendBeacon("{{ route('admin.clear-currency-conversation') }}", data);
                currentConversationId = $(this).data('private-id');
                $('#showadd').hide();

                window.Echo.join('conversation.' + currentConversationId)
                    .listen('.MessageSent', function(event) {
                        console.log(event);
                        $('#messagesList').append(renderMessageRealTime(event));
                        scrollToBottom();
                    }).listen('.UserStatusChanged', function(event) {
                        $('.show-status-user').text(
                            event.is_online == 'online' ? '🟢' : '🔴'
                        );
                    });
            });
        });

        // tạo chat nhóm
        $(document).ready(function() {
            $('#createGroupChatForm').submit(function(event) {
                event.preventDefault();

                var formData = $(this).serialize(); // Lấy dữ liệu từ form
                $.ajax({
                    url: "{{ route('admin.chats.create') }}",
                    method: "POST",
                    data: formData,
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#conversationList').html(response.data.channels);

                            toastr.success(response.message, "Thành công!");

                            setTimeout(() => {
                                window.location.href =
                                    "{{ route('admin.chats.index') }}";
                            }, 1500);
                        } else {
                            toastr.error(response.message, "Lỗi!");
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            let errors = xhr.responseJSON.errors;
                            let errorMessage = "\n";

                            $.each(errors, function(field, messages) {
                                errorMessage += messages.join(", ") + "\n";
                            });

                            toastr.error(errorMessage);
                        } else {
                            toastr.error("Có lỗi xảy ra, vui lòng thử lại!", "Lỗi!");
                        }
                    }
                });
            });

            $('.conversationList a').click(function(event) {
                event.preventDefault(); // Ngừng hành động mặc định của liên kết

                var channelId = $(this).data('group-id'); // Lấy ID của nhóm chat

                // Gửi yêu cầu AJAX để lấy thông tin nhóm
                getGroupInfo(channelId);
            });
            // Khi người dùng chọn một nhóm
            $('.group-button').click(function() {
                if (window.Echo) {
                    window.Echo.leave('conversation.' + currentConversationId);
                }

                const data = new FormData();
                data.append('conversationId', currentConversationId);
                data.append('_token', $('meta[name="csrf-token"]').attr('content'));

                navigator.sendBeacon("{{ route('admin.clear-currency-conversation') }}", data);

                currentConversationId = $(this).data('group-id');

                $('#showadd').show();

                window.Echo.join('conversation.' + currentConversationId)
                    .listen('.GroupMessageSent', function(event) {
                        $('#messagesList').append(renderMessageRealTime(event));
                        scrollToBottom();
                    });
            });

            $('#addMembers').select2();
            document.getElementById('addMembersButton').addEventListener('click', function(e) {
                e.preventDefault(); // Ngăn chặn việc gửi form mặc định

                // Lấy danh sách thành viên đã chọn từ select
                let selectedMembers = $('#addMembers').val();

                // Kiểm tra xem có thành viên nào được chọn không
                if (selectedMembers.length === 0) {
                    alert("Vui lòng chọn ít nhất một thành viên");
                    return;
                }
                var channelId = $(this).data('conversation-id'); // Lấy ID của nhóm chat
                console.log('Id nhóm:', channelId);

                // Gửi yêu cầu AJAX tới backend
                $.ajax({
                    url: 'http://127.0.0.1:8000/admin/chats/add-members-to-group', // URL backend của bạn (cập nhật theo route của bạn)
                    type: 'POST',
                    data: {
                        members: selectedMembers, // Danh sách thành viên đã chọn
                        group_id: channelId, // ID nhóm chat (thêm vào tham số nếu cần thiết)
                    },
                    success: function(response) {
                        if (response.success) {
                            toastr.success("Thành viên đã được thêm vào nhóm!", "Thành công!");

                            // Đóng modal sau khi thêm thành viên
                            $('#myModal').modal('hide');
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 400) {
                            let res = JSON.parse(xhr.responseText);

                            if (res.success === false && res.duplicate_members) {
                                let existingMembersList = res.duplicate_members.join(', ');
                                toastr.warning(
                                    `Thành viên đã tồn tại trong nhóm: ${existingMembersList}`,
                                    "Cảnh báo!");
                            } else {
                                toastr.error(res.message || "Có lỗi xảy ra, vui lòng thử lại.",
                                    "Lỗi!");
                            }
                        } else {
                            toastr.error("Đã có lỗi xảy ra. Vui lòng thử lại.", "Lỗi!");
                        }
                    }
                });
            });
            // Khi người dùng nhấn gửi tin nhắn
            $('#sendMessageButton').click(function(e) {
                e.preventDefault();
                let content = $('#messageInput').val();
                let parentId = $('#parentMessageId').val();
                let metaData = null;
                let formData = new FormData();
                let messageTypes = [];

                formData.append('conversation_id', currentConversationId);
                formData.append('content', content);
                formData.append('parent_id', parentId || '');

                let fileInput = $('#fileInput')[0].files;

                if (fileInput.length > 0) {
                    for (let i = 0; i < fileInput.length; i++) {
                        let file = fileInput[i];
                        let fileType = fileInput[i].type;

                        if (fileType.startsWith('image/')) {
                            if (!messageTypes.includes('image')) messageTypes.push('image');
                        } else if (fileType.startsWith('video/')) {
                            if (!messageTypes.includes('video')) messageTypes.push('video');
                        } else if (fileType.startsWith('audio/')) {
                            if (!messageTypes.includes('audio')) messageTypes.push('audio');
                        } else {
                            if (!messageTypes.includes('file')) messageTypes.push('file');
                        }

                        formData.append('input_file[]', fileInput[i]);
                    }
                }

                let messageTypeString = messageTypes.join(',');

                if (messageTypeString === '') {
                    messageTypeString = 'text';
                }

                formData.append('type', messageTypeString);

                if (currentConversationId && content || fileInput) {

                    $.ajax({
                        url: "{{ route('admin.chats.sendGroupMessage') }}",
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            console.log(response);

                            if (response.status == 'success') {

                                $('#messageInput').val('');
                                $('#fileInput').val('');
                                $('#previewContainer').hide();
                                $('#messagesList').append(renderMessage(response));
                                scrollToBottom();

                                if (response.message.meta_data && response.message.meta_data
                                    .length > 0) {
                                    const mediaHtml = [];
                                    const documentHtml = [];

                                    response.message.meta_data.forEach(
                                        media => {
                                            let fileName = media.file_name;
                                            let mediaFile = media.file_path;
                                            let fileType = media.file_type;
                                            let fileExt = fileName.split('.').pop()
                                                .toLowerCase();
                                            let fileSize = formatFileSize(media.file_size);
                                            let fileIcon = getFileThumbnail(fileExt);
                                            let storagePath = `/storage/${mediaFile}`;

                                            if (fileType.startsWith('image')) {
                                                mediaHtml.push(`
                                                    <div class="gallery-item">
                                                        <div class="file-container border rounded bg-light p-2" style="max-width: 400px; height: 200px; position: relative">
                                                            <a href="${storagePath}" download class="download-btn btn btn-white btn-lg mt-2"
                                                                style="position: absolute; top: 10px; right: 15px; border-radius: 5px; padding: 5px 10px;">
                                                                <i class='bx bx-download'></i>
                                                            </a>
                                                            <img src="${storagePath}" alt="Hình ảnh" style="max-width:100%; height:100%; border-radius: 8px; object-fit:cover;">
                                                        </div>
                                                    </div>`);
                                            } else if (fileType.startsWith('video')) {
                                                mediaHtml.push(`
                                                    <div class="gallery-item">
                                                        <div class="file-container d-flex flex-column p-2 border rounded bg-light" style="max-width: 400px; min-height: 100px;">
                                                            <video controls style="max-width:100%; height:200px; border-radius: 8px;">
                                                                <source src="${storagePath}" type="${fileType}">
                                                            </video>
                                                        </div>
                                                    </div>`);
                                            } else {
                                                documentHtml.push(`
                                                    <div class="gallery-item">
                                                        <div class="file-container d-flex align-items-center p-2 border rounded bg-light" style="max-width: 400px; min-height: 100px;">
                                                            <img src="${fileIcon}" class="me-2 file-icon" style="width: 50px; height: 50px;">
                                                            <div class="flex-grow-1 text-truncate d-flex justify-content-between align-items-center">
                                                                <div class="col-9">
                                                                    <p class="mb-1 small text-truncate" style="max-width: 250px;">${fileName}</p>
                                                                    <p class="text-muted">${fileSize}</p>
                                                                </div>
                                                                <div class="col-2 d-flex align-items-center justify-content-center">
                                                                    <a href="${storagePath}" download class="card btn btn-light btn-sm py-2 my-auto">
                                                                        <i class='fs-4 bx bx-download'></i>
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>`);
                                            }
                                        });

                                    $('#mediaFilesList').prepend(mediaHtml.join(''));
                                    $('#documentFilesList').prepend(documentHtml.join(''));
                                }
                            }
                        },
                        error: function(xhr) {
                            if (xhr.status === 422) {
                                let errors = xhr.responseJSON.errors;

                                Object.values(errors).forEach(err => {
                                    toastr.error(err[0]);
                                });
                            } else {
                                toastr.error("Gửi tin nhắn thất bại, vui lòng thử lại!");
                            }
                        }
                    });
                } else {
                    alert("Vui lòng nhập tin nhắn hoặc chọn ảnh!");
                }
            });
        });

        function kickUser(button) {
            let groupId = button.getAttribute("data-conversation-id");
            let userId = button.getAttribute("data-user-id");

            if (!groupId || !userId) {
                Toastify({
                    text: "Lỗi: Không tìm thấy ID nhóm hoặc ID người dùng.",
                    backgroundColor: "red",
                    duration: 3000,
                    close: true
                }).showToast();
                return;
            }
            if (!confirm("Bạn có chắc chắn muốn xóa người này khỏi nhóm không ?")) return;
            $.ajax({
                url: 'http://127.0.0.1:8000/admin/chats/kick-member',
                type: 'POST',
                data: {
                    group_id: groupId,
                    user_id: userId
                },
                success: function(response) {
                    if (response.success) {
                        Message = "Xóa thành công";
                        showToast('success', Message);
                    } else {
                        Toastify({
                            text: response.message,
                            backgroundColor: "red",
                            duration: 3000,
                            close: true
                        }).showToast();
                    }
                },
                error: function(xhr) {
                    let errorMessage = "Đã có lỗi xảy ra!";
                    if (xhr.status === 403) {
                        errorMessage = "Bạn không có quyền kick người này!";
                    }
                    if (xhr.status === 422) {
                        errorMessage = "Nhóm phải có ít nhất 2 thành viên. Không thể tiếp tục xóa thêm.";
                    }
                    showToast('error', errorMessage);
                }
            });
        }

        function dissolveGroup(a) {
            let groupId = a.getAttribute("data-conversation-id");

            if (!groupId) {
                Toastify({
                    text: "Lỗi: Không tìm thấy nhóm",
                    backgroundColor: "red",
                    duration: 3000,
                    close: true
                }).showToast();
                return;
            }
            if (!confirm("Bạn có chắc chắn muốn giải tán nhóm này?")) return;

            $.ajax({
                url: 'http://127.0.0.1:8000/admin/chats/dissolve-group',
                type: 'POST',
                data: {
                    group_id: groupId
                },
                success: function(response) {
                    if (response.success) {
                        message = "Giải tán nhóm thành công";
                        showToast('success', message);
                        window.location.reload();
                    } else {
                        errorMessage = "Giải tán nhóm thất bại";
                        showToast('error', errorMessage);
                    }
                },
                error: function(xhr) {
                    let errorMessage = "Đã có lỗi xảy ra!";
                    if (xhr.status === 403) {
                        errorMessage = "Bạn không có quyền giải tán nhóm!";
                        showToast('error', errorMessage);
                    }
                }
            });
        }

        function deleteConversation(button) {
            const conversationId = button.getAttribute('data-conversation-id');

            if (confirm("Bạn có chắc chắn muốn xóa cuộc trò chuyện này?")) {
                fetch(`http://127.0.0.1:8000/admin/chats/conversation/${conversationId}/delete/`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            message = "Xóa cuộc trò chuyện thành công";
                            showToast('success', message);
                            location
                                .reload(); // Hoặc bạn có thể xóa phần tử khỏi giao diện nếu không muốn tải lại trang
                        } else {
                            alert(data.message);
                        }
                    })
                    .catch(error => {
                        errormessage = "Có lỗi xảy ra, vui lòng thử lại";
                        showToast('error', errormessage);
                    });
            }
        }

        function leaveConversation(button) {
            const conversationId = button.getAttribute('data-conversation-id');

            if (confirm("Bạn có chắc chắn muốn rời nhóm này?")) {
                fetch(`http://127.0.0.1:8000/admin/chats/conversation/${conversationId}/leave/`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                'content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            message = "Rời nhóm thành công";
                            showToast('success', message);
                            location
                                .reload(); // Hoặc bạn có thể xóa phần tử khỏi giao diện nếu không muốn tải lại trang
                        } else {
                            errormessage = "Có lỗi xảy ra, vui lòng thử lại";
                            showToast('error', errormessage);
                        }
                    })
                    .catch(error => {
                        errormessage = "Có lỗi xảy ra, vui lòng thử lại";
                        showToast('error', errormessage);
                    });
            }
        }

        function getFileThumbnail(ext) {
            return `/assets/images/icons/${ext}.png`;
        }

        function loadMessages(conversationId) {
            $.get('http://127.0.0.1:8000/admin/chats/get-messages/' + conversationId, function(response) {
                console.log(response);

                if (response.status === 'success') {

                    // Lấy tất cả các tin nhắn
                    $('#messagesList').html(''); // Xóa danh sách tin nhắn cũ

                    const messagesHtml = response.messages.flatMap(message => {
                        const messageClass = message.sender.id == userId ? 'sender' : 'received';
                        const time = formatTime(message.created_at);
                        const messageContent = `<p>${message.content || ''}</p>`;
                        const mediaPreview = (message?.meta_data || []).map(media => {
                            const {
                                file_name: fileName,
                                file_path: filePath,
                                file_type: fileType,
                                file_size: fileSize
                            } = media;
                            const fileExt = fileName.split('.').pop().toLowerCase();
                            const formattedSize = formatFileSize(fileSize);
                            const fileIcon = getFileThumbnail(fileExt);
                            const storagePath = `/storage/${filePath}`;

                            if (fileType.startsWith('image')) {
                                return `
                                <div class="file-container border rounded bg-light p-2" style="max-width: 400px; min-height: 100px; position: relative">
                                    <a href="${storagePath}" download class="download-btn btn btn-white btn-lg mt-2"
                                        style="position: absolute; top: 10px; right: 15px; border-radius: 5px; padding: 5px 10px;">
                                        <i class='bx bx-download'></i>
                                    </a>
                                    <img src="${storagePath}" alt="Hình ảnh" style="max-width:100%; border-radius: 8px;">
                                </div>`;
                            }
                            if (fileType.startsWith('video')) {
                                return `
                                <div class="file-container d-flex flex-column p-2 border rounded bg-light" style="max-width: 400px; min-height: 100px;">
                                    <video controls style="max-width:100%; border-radius: 8px;">
                                        <source src="${storagePath}" type="${fileType}">
                                    </video>
                                </div>`;
                            }
                            if (fileType === 'application/pdf') {
                                return `
                                <div class="file-container d-flex flex-column p-2 border rounded bg-light" style="max-width: 400px; min-height: 100px;">
                                    <embed src="${storagePath}" type="application/pdf" style="width:100%; height:300px; border-radius: 8px;">
                                </div>`;
                            }
                            return `
                            <div class="file-container d-flex align-items-center p-2 border rounded bg-light" style="max-width: 400px; min-height: 100px;">
                                <img src="${fileIcon}" class="me-2 file-icon" style="width: 50px; height: 50px;">
                                <div class="flex-grow-1 text-truncate d-flex justify-content-between align-items-center">
                                    <div class="col-9">
                                        <p class="mb-1 small text-truncate" style="max-width: 250px;">${fileName}</p>
                                        <p class="text-muted">${formattedSize}</p>
                                    </div>
                                    <div class="col-2 d-flex align-items-center justify-content-center">
                                        <a href="${storagePath}" download class="card btn btn-light btn-sm py-2 my-auto">
                                            <i class='fs-4 bx bx-download'></i>
                                        </a>
                                    </div>
                                </div>
                            </div>`;
                        }).join('');

                        return `
                            <div class="message ${messageClass}" style="padding-top: 10px">
                                <div class="message-avatar">
                                    <img src="${message.sender.avatar}" alt="avatar">
                                </div>
                                <div class="message-content">
                                    <div class="message-header">
                                        <strong>${message.sender.name}</strong>
                                        <span class="message-time">${time}</span>
                                    </div>
                                    ${messageContent}
                                    ${mediaPreview}
                                </div>
                            </div>`;
                    }).join('');

                    $('#elmLoader').hide(); // Ẩn loader khi tải xong tin nhắn
                    $('#messagesList').append(messagesHtml); // Thêm tin nhắn vào danh sách
                    scrollToBottom();
                } else {
                    $('#elmLoader').show(); // Hiển thị loader nếu có lỗi
                }
            });
        }

        function loadSentFiles(conversationId) {
            $.get(`http://127.0.0.1:8000/admin/chats/get-sent-files/${conversationId}`, function(response) {
                if (response.status === 'success') {
                    $('#sentFilesList, #documentFilesList, #mediaFilesList').html('');

                    if (response.files.length === 0) {
                        $('#sentFilesList, #documentFilesList, #mediaFilesList').html(
                            '<p>Chưa có file nào được gửi</p>');
                        return;
                    }

                    let visibleImages = 6; // Số ảnh hiển thị ban đầu
                    let allFiles = response.files; // Lưu toàn bộ danh sách file

                    function renderFiles() {
                        $('#sentFilesList, #documentFilesList, #mediaFilesList').html('');

                        let filesToShow = allFiles.slice(0, visibleImages);

                        const mediaHtml = [];
                        const documentHtml = [];

                        filesToShow.flatMap(file => (file.meta_data || [])).forEach(media => {
                            let fileName = media.file_name;
                            let mediaFile = media.file_path;
                            let fileType = media.file_type;
                            let fileExt = fileName.split('.').pop().toLowerCase();
                            let fileSize = formatFileSize(media.file_size);
                            let fileIcon = getFileThumbnail(fileExt);
                            let storagePath = `/storage/${mediaFile}`;

                            if (fileType.startsWith('image')) {
                                mediaHtml.push(`
                        <div class="gallery-item">
                            <div class="file-container border rounded bg-light p-2" style="max-width: 400px; height: 200px; position: relative">
                                <a href="${storagePath}" download class="download-btn btn btn-white btn-lg mt-2"
                                    style="position: absolute; top: 10px; right: 15px; border-radius: 5px; padding: 5px 10px;">
                                    <i class='bx bx-download'></i>
                                </a>
                                <img src="${storagePath}" alt="Hình ảnh" style="max-width:100%; height:100%; border-radius: 8px; object-fit:cover;">
                            </div>
                        </div>`);
                            } else if (fileType.startsWith('video')) {
                                mediaHtml.push(`
                        <div class="gallery-item">
                            <div class="file-container d-flex flex-column p-2 border rounded bg-light" style="max-width: 400px; min-height: 100px;">
                                <video controls style="max-width:100%; height:200px; border-radius: 8px;">
                                    <source src="${storagePath}" type="${fileType}">
                                </video>
                            </div>
                        </div>`);
                            } else {
                                documentHtml.push(`
                        <div class="gallery-item">
                            <div class="file-container d-flex align-items-center p-2 border rounded bg-light" style="max-width: 400px; min-height: 100px;">
                                <img src="${fileIcon}" class="me-2 file-icon" style="width: 50px; height: 50px;">
                                <div class="flex-grow-1 text-truncate d-flex justify-content-between align-items-center">
                                    <div class="col-9">
                                        <p class="mb-1 small text-truncate" style="max-width: 250px;">${fileName}</p>
                                        <p class="text-muted">${fileSize}</p>
                                    </div>
                                    <div class="col-2 d-flex align-items-center justify-content-center">
                                        <a href="${storagePath}" download class="card btn btn-light btn-sm py-2 my-auto">
                                            <i class='fs-4 bx bx-download'></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>`);
                            }
                        });

                        $('#mediaFilesList').append(mediaHtml.join(''));
                        $('#documentFilesList').append(documentHtml.join(''));

                        if (visibleImages >= allFiles.length) {
                            $('#loadMore').hide(); // Ẩn nút nếu không còn ảnh
                        } else {
                            $('#loadMore').show(); // Hiển thị nút nếu còn ảnh
                        }
                    }

                    renderFiles();

                    $('#loadMore').off('click').on('click', function() {
                        visibleImages += 6;
                        renderFiles();
                    });
                }
            });
        }

        function scrollToBottom() {
            let chatBox = document.getElementById("chatBox");
            let simpleBarInstance = SimpleBar.instances.get(chatBox);

            if (simpleBarInstance) {
                requestAnimationFrame(() => {
                    simpleBarInstance.getScrollElement().scrollTop = simpleBarInstance
                        .getScrollElement()
                        .scrollHeight;
                });
            }
        }

        function formatTime(dateString) {
            const date = new Date(dateString);

            // Sử dụng toLocaleTimeString() để xử lý múi giờ và định dạng theo yêu cầu (giờ và phút)
            const options = {
                hour: '2-digit',
                minute: '2-digit',
                timeZone: 'Asia/Ho_Chi_Minh', // Chỉnh múi giờ về Việt Nam (hoặc múi giờ khác nếu cần)
            };

            return date.toLocaleTimeString('vi-VN', options); // Sử dụng 'vi-VN' để định dạng tiếng Việt
        }

        // function addReaction(event) {
        //     const reactionContainer = event.target.closest('.message').querySelector('.reaction-container');
        //     const reaction = document.createElement('div');
        //     reaction.classList.add('reaction');
        //     reaction.innerHTML = event.target.innerHTML; // Thêm ký hiệu reaction (❤️ hoặc 👍)

        //     // Vị trí ngẫu nhiên trên tin nhắn
        //     const xOffset = Math.random() * 20 - 10; // Xê dịch ngẫu nhiên
        //     const yOffset = Math.random() * 20 - 10;

        //     // Đặt vị trí reaction
        //     reaction.style.left = `${xOffset}px`;
        //     reaction.style.top = `${yOffset}px`;

        //         // Thêm reaction vào container
        //         reactionContainer.appendChild(reaction);

        //         // Sau khi animation kết thúc, xóa reaction
        //         setTimeout(() => {
        //             reaction.remove();
        //         }, 1000); // Thời gian hiệu ứng hoạt hình (1 giây)
        //     }
        function renderMessage(response) {

            const messageClass = response.message.sender.id == userId ? 'sender' : 'received';
            const time = formatTime(response.message.created_at);
            let messageContent = `<p>${response.message.content || ''}</p>`;
            let mediaPreview = '';

            try {
                if (response?.message.meta_data && response?.message.meta_data.length > 0) {
                    response?.message.meta_data.forEach(media => {
                        let fileName = media.file_name;
                        let mediaFile = media.file_path;
                        let fileType = media.file_type;
                        let fileExt = fileName.split('.').pop().toLowerCase();
                        let fileSize = formatFileSize(media.file_size);
                        let fileIcon = getFileThumbnail(fileExt);

                        if (fileType.startsWith('image')) {
                            mediaPreview += `
                        <div class="file-container border rounded bg-light p-2" style="max-width: 400px; min-height: 100px;">
                            <img src="/storage/${mediaFile}" alt="Hình ảnh" style="max-width:100%; border-radius: 8px;">
                        </div>`;
                        } else if (fileType.startsWith('video')) {
                            mediaPreview += `
                        <div class="file-container d-flex flex-column p-2 border rounded bg-light" style="max-width: 400px; min-height: 100px;">
                            <video controls style="max-width:100%; border-radius: 8px;">
                                <source src="/storage/${mediaFile}" type="${fileType}">
                            </video>
                        </div>`;
                        } else if (fileType === 'application/pdf') {
                            mediaPreview += `
                        <div class="file-container d-flex flex-column p-2 border rounded bg-light" style="max-width: 400px; min-height: 100px;">
                            <embed src="/storage/${mediaFile}" type="application/pdf" style="width:100%; height:300px; border-radius: 8px;">
                            <a href="/storage/${mediaFile}" download class="btn btn-primary btn-sm mt-2">
                                <i class='bx bx-download'></i>
                            </a>
                        </div>`;
                        } else {
                            mediaPreview += `
                        <div class="file-container d-flex align-items-center p-2 border rounded bg-light" style="max-width: 400px; min-height: 100px;">
                            <img src="${fileIcon}" class="me-2 file-icon" style="width: 50px; height: 50px;">
                            <div class="flex-grow-1 text-truncate d-flex justify-content-between align-items-center">
                                <div class="col-9">
                                    <p class="mb-1 small text-truncate" style="max-width: 250px;">${fileName}</p>
                                    <p class="text-muted">${fileSize}</p>
                                </div>
                                <div class="col-2 d-flex align-items-center justify-content-center">
                                    <a href="/storage/${mediaFile}" download class="card btn btn-light btn-sm py-2 my-auto">
                                        <i class='fs-4 bx bx-download'></i>
                                    </a>
                                </div>
                            </div>
                        </div>`;
                        }
                    });
                }
            } catch (error) {
                messageContent = `<p>${message.content}</p>`;
            }

            let messageHtml = `
        <div class="message ${messageClass}" style="padding-top: 10px">
            <div class="message-avatar">
                <img src="${response.message.sender.avatar}" alt="avatar">
            </div>
            <div class="message-content">
                <div class="message-header">
                    <strong>${response.message.sender.name}</strong>
                    <span class="message-time">${time}</span>
                </div>
                ${messageContent}
                ${mediaPreview} <!-- Chứa tất cả file -->
            </div>
        </div>
    `;

            return messageHtml;
        }

        function renderMessageRealTime(response) {

            const messageClass = response.sender.id == userId ? 'sender' : 'received';
            const time = formatTime(response.sent_at);
            let messageContent = `<p>${response.content || ''}</p>`;
            let mediaPreview = '';

            try {
                if (response?.meta_data && response?.meta_data.length > 0) {

                    if (typeof response?.meta_data === 'object' &&
                        'read' in response.meta_data &&
                        'send_at' in response.meta_data) {
                        return;
                    }

                    response?.meta_data.forEach(media => {
                        let fileName = media.file_name;
                        let mediaFile = media.file_path;
                        let fileType = media.file_type;
                        let fileExt = fileName.split('.').pop().toLowerCase();
                        let fileSize = formatFileSize(media.file_size);
                        let fileIcon = getFileThumbnail(fileExt);

                        if (fileType.startsWith('image')) {
                            mediaPreview += `
            <div class="file-container border rounded bg-light p-2" style="max-width: 400px; min-height: 100px;">
                <img src="/storage/${mediaFile}" alt="Hình ảnh" style="max-width:100%; border-radius: 8px;">
            </div>`;
                        } else if (fileType.startsWith('video')) {
                            mediaPreview += `
            <div class="file-container d-flex flex-column p-2 border rounded bg-light" style="max-width: 400px; min-height: 100px;">
                <video controls style="max-width:100%; border-radius: 8px;">
                    <source src="/storage/${mediaFile}" type="${fileType}">
                </video>
            </div>`;
                        } else if (fileType === 'application/pdf') {
                            mediaPreview += `
            <div class="file-container d-flex flex-column p-2 border rounded bg-light" style="max-width: 400px; min-height: 100px;">
                <embed src="/storage/${mediaFile}" type="application/pdf" style="width:100%; height:300px; border-radius: 8px;">
                <a href="/storage/${mediaFile}" download class="btn btn-primary btn-sm mt-2">
                    <i class='bx bx-download'></i>
                </a>
            </div>`;
                        } else {
                            mediaPreview += `
            <div class="file-container d-flex align-items-center p-2 border rounded bg-light" style="max-width: 400px; min-height: 100px;">
                <img src="${fileIcon}" class="me-2 file-icon" style="width: 50px; height: 50px;">
                <div class="flex-grow-1 text-truncate d-flex justify-content-between align-items-center">
                    <div class="col-9">
                        <p class="mb-1 small text-truncate" style="max-width: 250px;">${fileName}</p>
                        <p class="text-muted">${fileSize}</p>
                    </div>
                    <div class="col-2 d-flex align-items-center justify-content-center">
                        <a href="/storage/${mediaFile}" download class="card btn btn-light btn-sm py-2 my-auto">
                            <i class='fs-4 bx bx-download'></i>
                        </a>
                    </div>
                </div>
            </div>`;
                        }
                    });
                }
            } catch (error) {
                messageContent = `<p>${response.content}</p>`;
            }

            let messageHtml = `
<div class="message ${messageClass}" style="padding-top: 10px">
<div class="message-avatar">
    <img src="${response.sender.avatar}" alt="avatar">
</div>
<div class="message-content">
    <div class="message-header">
        <strong>${response.sender.name}</strong>
        <span class="message-time">${time}</span>
    </div>
    ${messageContent}
    ${mediaPreview} <!-- Chứa tất cả file -->
</div>
</div>
`;

            return messageHtml;
        }

        function formatFileSize(bytes) {
            if (bytes < 1024) return bytes + " B";
            const sizes = ["KB", "MB", "GB", "TB"];
            let i = Math.floor(Math.log(bytes) / Math.log(1024));
            let size = (bytes / Math.pow(1024, i)).toFixed(2);
            return `${size} ${sizes[i - 1]}`;
        }

        function updateUserStatus(userId, status) {
            let userElement = $('.show-status-user[data-user-id="' + userId + '"]');

            if (userElement.length) {
                userElement.text(status == 'online' ? '🟢' : '🔴');
            }
        }

        function getUserInfo(channelId) {
            $.ajax({
                url: "{{ route('admin.chats.getUserInfo') }}",
                method: 'GET',
                data: {
                    id: channelId
                },
                success: function(response) {
                    if (response) {

                        // Cập nhật tên nhóm và số thành viên
                        $('.nameUser').text(response.data.nameUser);
                        $('.imageUser').attr('src', response.data.avatarUser);
                        $('.getID').attr('data-conversation-id', response.data.channelId);
                        $('#memberCount').empty();
                        $('#membersList').empty();
                        $('#OnetoOne').hide();
                        $('#filetofile').removeClass('col-6');
                        $('#filetofile').addClass('col-12');
                        $('.files-chat-message').addClass('active show');
                        $('#media-tab').addClass('active');
                        $('#documents-tab').removeClass('active');
                        $('#files-tab').addClass('active');
                        $('#members-tab').removeClass('active');

                        $('.show-status-user').text(
                            response.data.other_user_status == 'online' ? '🟢' : '🔴'
                        );

                        loadMessages(response.data.direct.id);
                        loadSentFiles(response.data.direct.id);
                    } else {
                        alert('Không thể lấy thông tin nhóm');
                    }
                },
                error: function() {
                    alert('Có lỗi xảy ra trong quá trình lấy dữ liệu');
                }
            });
        }

        function getGroupInfo(channelId) {

            $.ajax({
                url: "{{ route('admin.chats.getGroupInfo') }}",
                method: 'GET',
                data: {
                    id: channelId
                },
                success: function(response) {
                    if (response) {

                        // Cập nhật tên nhóm và số thành viên
                        $('.name').text(response.data.name);
                        $('#memberCount').text(response.data.memberCount);
                        $('.avatar').attr('src', response.data.avatar);
                        $('.getID').attr('data-conversation-id', response.data.channelId);
                        $('#OnetoOne').show();
                        $('#filetofile').removeClass('col-12');
                        $('#filetofile').addClass('col-6');
                        $('#files-tab').removeClass('active');
                        $('#members-tab').addClass('active');
                        $('.files-chat-message').removeClass('active show');
                        $('#media-tab').addClass('active');
                        $('#documents-tab').removeClass('active');

                        loadMessages(response.data.group.id);
                        loadSentFiles(response.data.group.id);
                        let membersHtml = '';
                        response.data.member.forEach(function(member) {
                            membersHtml += ` <li class="list-group-item">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0">
                                                            <img src="${member.avatar}" alt="" class="avatar-xs rounded-circle">
                                                        </div>
                                                        <div class="flex-grow-1 ms-2">
                                                            ${member.name}
                                                        </div>
                                                        <button class="btn avatar-xs p-0 getID" type="button"
                                                            data-bs-toggle="dropdown" aria-haspopup="true"
                                                            aria-expanded="false"
                                                            data-conversation-id="${channelId}"
                                                            data-user-id="${member.user_id}"
                                                            onclick="kickUser(this)">
                                                            <span class="avatar-title bg-light text-body rounded">
                                                                <i
                                                                    class="ri-delete-bin-5-line align-bottom text-muted"></i>
                                                            </span>
                                                        </button>`;
                            // Kiểm tra nếu người dùng là trưởng nhóm
                            if (member.user_id == response.data.group.owner_id) {
                                membersHtml +=
                                    `<p style="padding-top:12px">Trưởng nhóm</p>`; // Thêm dòng "Trưởng nhóm" nếu đúng
                            }

                            membersHtml += `</div>
                                                </li>`;

                        });
                        $('#membersList').html(
                            membersHtml); // Cập nhật danh sách thành viên vào giao diện

                    } else {
                        alert('Không thể lấy thông tin nhóm');
                    }
                },
                error: function() {
                    alert('Có lỗi xảy ra trong quá trình lấy dữ liệu');
                }
            });
        }

        function sendActiveUsersToServer(users = null, type) {

            $.ajax({
                url: "{{ route('admin.clear-currency-conversation') }}",
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    conversation_id: currentConversationId,
                    active_users: users,
                    type: type,
                },
                success: function(response) {
                    console.log('Success:', response);
                },
                error: function(xhr) {
                    console.log('Error:', xhr);
                }
            });
        }

        window.addEventListener('beforeunload', function(e) {
            const data = new FormData();
            data.append('conversationId', currentConversationId);
            data.append('_token', $('meta[name="csrf-token"]').attr('content'));

            navigator.sendBeacon("{{ route('admin.clear-currency-conversation') }}", data);
        });

        if (window.location.pathname.startsWith('/chat-room')) {
            document.addEventListener('visibilitychange', function() {
                if (document.hidden) {
                    const data = new FormData();
                    data.append('conversationId', currentConversationId);
                    data.append('_token', $('meta[name="csrf-token"]').attr('content'));

                    navigator.sendBeacon("{{ route('admin.clear-currency-conversation') }}", data);
                }
            });
        }


        if (firstChanelId && firstChanelType && COUNTWEB == 1) {

            function getUrlParam(name) {
                const urlParams = new URLSearchParams(window.location.search);
                return urlParams.get(name);
            }

            let roomParam = getUrlParam('conversation');
            let decodedRoomId = null;

            if (roomParam) {
                try {
                    decodedRoomId = atob(roomParam).trim();

                    if (isNaN(decodedRoomId)) throw new Error("");
                    if (!decodedRoomId) throw new Error("");
                } catch (e) {
                    decodedRoomId = null;
                }
            }

            currentConversationId = decodedRoomId ?? firstChanelId;
            console.log(currentConversationId);

            if (firstChanelType == 'group') {
                getGroupInfo(currentConversationId);
                $(document).ready(function() {
                    $('#showadd').show();

                    window.Echo.join('conversation.' + currentConversationId)
                        .here(users => {
                            $(`.show-status-user`).text('🟢');
                        })
                        .listen('.GroupMessageSent', function(event) {
                            $('#messagesList').append(renderMessageRealTime(event));
                            scrollToBottom();
                        });
                });
            } else {
                getUserInfo(currentConversationId);
                $(document).ready(function() {
                    $('#showadd').show();

                    window.Echo.join('conversation.' + currentConversationId)
                        .listen('.MessageSent', function(event) {
                            console.log(event);

                            $('#messagesList').append(renderMessageRealTime(event));
                            scrollToBottom();
                        }).listen('.UserStatusChanged', function(event) {
                            console.log(event);
                            $('.show-status-user').text(
                                event.is_online == 'online' ? '🟢' : '🔴'
                            );
                        });
                });
            }
            COUNTWEB++;
        }
    </script>
@endpush
