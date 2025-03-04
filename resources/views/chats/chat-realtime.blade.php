@vite(['resources/js/app.js'])
@extends('layouts.app')
@push('page-css')
    <!-- glightbox css -->
    <link rel="stylesheet" href="{{ asset('assets/libs/glightbox/css/glightbox.min.css') }}">
    <link href="{{ asset('assets/css/select2.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('assets/css/cssChat.css') }}" rel="stylesheet" type="text/css" />
@endpush
@php
    $title = 'Chat';
@endphp

@section('content')
    <div class="container-fluid">
        <div class="chat-wrapper d-lg-flex gap-1 mx-n4 mt-n4 p-1">
            <div class="chat-leftsidebar">
                <div class="px-4 pt-4 mb-3">
                    <div class="d-flex align-items-start">
                        <div class="flex-grow-1">
                            <h5 class="mb-4">Chats</h5>
                        </div>
                        <div aria-hidden="true" aria-labelledby="addGroupModalLabel" class="modal fade" id="addGroupModal"
                            role="dialog" tabindex="-1">
                            <div class="modal-dialog modal-lg d-flex align-items-center justify-content-center h-100">
                                <div class="modal-content rounded-3 shadow-lg">
                                    <div class="modal-header bg-primary text-white rounded-top p-3">
                                        <h5 class="modal-title text-white" id="addGroupModalLabel">
                                            Thêm hội thoại
                                        </h5>
                                        <button aria-label="Close" class="close text-white" data-dismiss="modal"
                                            type="button">
                                            <span aria-hidden="true">×</span>
                                        </button>
                                    </div>
                                    <div class="modal-body p-4 bg-light rounded-bottom">
                                        <form id="createGroupChatForm">
                                            @csrf
                                            <div class="form-group mb-3">
                                                <label for="groupType" class="font-weight-bold">Chọn kiểu nhóm</label>
                                                <select class="form-select py-2" name="type" id="groupType">
                                                    <option value="#">Chọn kiểu nhóm</option>
                                                    <option value="1">Personal</option>
                                                    <option value="2">Group</option>
                                                </select>
                                            </div>
                                            <div class="form-group mb-3">
                                                <label for="groupName" class="font-weight-bold">Tên nhóm</label>
                                                <input class="form-control py-2" name="name" id="groupName"
                                                    placeholder="Nhập tên nhóm" type="text" />
                                            </div>
                                            <div class="form-group mb-3">
                                                <label for="groupMembers" class="font-weight-bold">Add Members</label>
                                                <select tabindex="-1" id="groupMembers" name="members[]"
                                                    multiple="multiple">
                                                    @foreach ($data['admins'] as $admin)
                                                        <option value="{{ $admin->id }}">
                                                            {{ $admin->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <button class="btn btn-primary w-100 py-2" type="submit">
                                                Add Group
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
                                <button type="button" class="btn btn-soft-success btn-sm" data-toggle="modal"
                                    data-target="#addGroupModal">
                                    <i class="ri-add-line align-bottom"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="search-box">
                        <input type="text" class="form-control bg-light border-light" placeholder="Search here...">
                        <i class="ri-search-2-line search-icon"></i>
                    </div>
                </div> <!-- .p-4 -->

                <ul class="nav nav-tabs nav-tabs-custom nav-success nav-justified" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#chats" role="tab">
                            Chats
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#contacts" role="tab">
                            Contacts
                        </a>
                    </li>
                </ul>

                <div class="tab-content text-muted">
                    <div class="tab-pane active" id="chats" role="tabpanel">
                        <div class="chat-room-list pt-3" data-simplebar>
                            <div class="d-flex align-items-center px-4 mb-2">
                                <div class="flex-grow-1">
                                    <h4 class="mb-0 fs-11 text-muted text-uppercase">Direct Messages</h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="bottom"
                                        title="New Message">

                                        <!-- Button trigger modal -->
                                        <button type="button" class="btn btn-soft-success btn-sm shadow-none">
                                            <i class="ri-add-line align-bottom"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="chat-message-list">

                                <ul class="list-unstyled chat-list chat-user-list usersList">
                                </ul>
                            </div>

                            <div class="d-flex align-items-center px-4 mt-4 pt-2 mb-2">
                                <div class="flex-grow-1">
                                    <h4 class="mb-0 fs-11 text-muted text-uppercase">Channels</h4>
                                </div>
                                <div class="flex-shrink-0">
                                    <div data-bs-toggle="tooltip" data-bs-trigger="hover" data-bs-placement="bottom"
                                        title="Create group">
                                        <!-- Button trigger modal -->
                                        <button type="button" class="btn btn-soft-success btn-sm">
                                            <i class="ri-add-line align-bottom"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="chat-message-list">

                                <ul class="list-unstyled chat-list chat-user-list mb-0 conversationList">
                                    @foreach ($data['channels'] as $channel)
                                        <li class="">
                                            <a href="#" class="unread-msg-user group-button"
                                                data-channel-id="{{ $channel->id }}">
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0 chat-user-img align-self-center me-2 ms-0">
                                                        <div class="avatar-xxs">
                                                            <div class="avatar-title bg-light rounded-circle text-body">
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

                                                            <img id="imageUser" src=""
                                                                class="rounded-circle avatar-xs" alt="">
                                                            <span class="user-status"></span>
                                                        </div>
                                                        <div class="flex-grow-1 overflow-hidden" id="groupInfo">
                                                            <h5 class="text-truncate mb-0 fs-16">
                                                                <a class="text-reset username name"></a>
                                                            </h5>
                                                            <p class="text-truncate text-muted fs-14 mb-0 userStatus">
                                                                <small class="memberCount"></small>
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
                                                                                                <li><a class="dropdown-item"
                                                                                                        href="javascript:void(0);"><i
                                                                                                            class="ri-inbox-archive-line align-bottom text-muted me-2"></i>Archive</a>
                                                                                                </li>
                                                                                                <li><a class="dropdown-item"
                                                                                                        href="javascript:void(0);"><i
                                                                                                            class="ri-mic-off-line align-bottom text-muted me-2"></i>Muted</a>
                                                                                                </li>
                                                                                                <li><a class="dropdown-item"
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
                                                                        <img src="{{ asset('assets/images/users/multi-user.jpg') }}"
                                                                            alt=""
                                                                            class="avatar-lg img-thumbnail rounded-circle mx-auto profile-img">
                                                                        <div class="mt-3">
                                                                            <h5 class="fs-16 mb-1"><a
                                                                                    href="javascript:void(0);"
                                                                                    class="link-primary username name"></a>
                                                                            </h5>
                                                                            <p class="text-muted"><i
                                                                                    class="ri-checkbox-blank-circle-fill me-1 align-bottom text-success"></i>Online
                                                                            </p>
                                                                        </div>

                                                                        <div class="d-flex gap-2 justify-content-center">

                                                                            <button type="button"
                                                                                class="btn avatar-xs p-0"
                                                                                data-bs-toggle="tooltip"
                                                                                data-bs-placement="top" title="Favourite">
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
                                                                                    <li><a class="dropdown-item"
                                                                                            href="javascript:void(0);"><i
                                                                                                class="ri-delete-bin-5-line align-bottom text-muted me-2"></i>Delete</a>
                                                                                    </li>
                                                                                </ul>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="border-top border-top-dashed p-3">
                                                                        <h5 class="fs-15 mb-3">Danh sách thành viên(<b
                                                                                class="memberCount"></b>)</h5>
                                                                        <ul class="list-group member-list"
                                                                            id="membersList">

                                                                        </ul>
                                                                    </div>
                                                                    <div class="border-top border-top-dashed p-3">
                                                                        <h5 class="fs-15 mb-3">File đã gửi</h5>

                                                                        <div class="vstack gap-2">
                                                                            <div class="border rounded border-dashed p-2"
                                                                                id="sentFilesList">

                                                                            </div>
                                                                            <div class="text-center mt-2">
                                                                                <button id="loadMore" type="button"
                                                                                    class="btn btn-danger">Load more <i
                                                                                        class="ri-arrow-right-fill align-bottom ms-1"></i></button>
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
                                                                            <h5 class="modal-title" id="myModalLabel">Thêm
                                                                                thành viên</h5>
                                                                            <button type="button" class="btn-close"
                                                                                data-bs-dismiss="modal"
                                                                                aria-label="Close"> </button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <form>
                                                                                @csrf
                                                                                <div class="form-group mb-3">
                                                                                    <label for="groupMembers"
                                                                                        class="font-weight-bold">Chọn thành
                                                                                        viên</label>
                                                                                        @foreach ($data['channelsWithAdminsNotInGroup'] as $channelData)
                                                                                    <select tabindex="-1" id="addMembers"
                                                                                        name="members[]"
                                                                                        multiple="multiple">
                                                                                        
                                                                                        @foreach ($channelData['adminsNotInGroup'] as $member)
                                                                                            <option
                                                                                                value="{{ $member->id }}"
                                                                                                class="select-member-option">
                                                                                                {{ $member->name }}
                                                                                            </option>
                                                                                        @endforeach  
                                                                                    </select>
                                                                                    @endforeach
                                                                                </div>

                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-light"
                                                                                data-bs-dismiss="modal">Close</button>
                                                                            <button type="submit"
                                                                                class="btn btn-primary getID"
                                                                                id="addMembersButton"
                                                                                data-conversation-id="">Thêm</button>
                                                                        </div>
                                                                        </form>
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
                                                                        placeholder="Search here..."
                                                                        onkeyup="searchMessages()" id="searchMessage">
                                                                    <i class="ri-search-2-line search-icon"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </li>
                                                <li class="list-inline-item m-0">
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

                                                    <input type="file" name="fileinput" id="fileInput"
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
                                            <!-- Nếu có tính năng trả lời tin nhắn -->
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
    <script>
        var APP_URL = "{{ env('APP_URL') . '/' }}";
        const userId = @json(auth()->id()); // Truyền id người dùng từ Laravel sang JavaScript
    </script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="{{ asset('assets/libs/glightbox/js/glightbox.min.js') }}"></script>
    <script src="{{ asset('assets/libs/fg-emoji-picker/fgEmojiPicker.js') }}"></script>
    <script>
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

                console.log("Hàm initIcons đã chạy thành công!");
            });
        }
        initIcons();

        $(document).ready(function() {
            $("#upload-btn").click(function() {
                $("#fileInput").click();
            });
            document.getElementById("fileInput").addEventListener("change", function() {
                let file = this.files[0];
                let messageInput = document.getElementById("messageInput");
                let previewContainer = document.getElementById("previewContainer");
                let imagePreview = document.getElementById("imagePreview");

                if (file) {
                    // messageInput.value = file.name;

                    // Kiểm tra xem tệp có phải ảnh không
                    if (file.type.startsWith("image/")) {
                        let reader = new FileReader();
                        reader.onload = function(e) {
                            imagePreview.src = e.target.result;
                            previewContainer.style.display = "block"; // Hiển thị khu vực ảnh
                        };
                        reader.readAsDataURL(file);
                    } else {
                        previewContainer.style.display = "none"; // Ẩn nếu không phải ảnh
                    }
                }
            });
        });

        function removeImage() {
            document.getElementById("imagePreview").src = "";
            document.getElementById("previewContainer").style.display = "none";
            document.getElementById("chatinput-form").reset();
        }
    </script>
    <script>
        $(document).ready(function() {
            $('#groupMembers').select2({
                placeholder: "Chọn thành viên thêm vào nhóm",
                allowClear: true,
                dropdownParent: $('#addGroupModal'),
            });
        });
    </script>
    <script>
        var currentConversationId = null;
        $(document).ready(function() {
            $('#createGroupChatForm').submit(function(event) {
                event.preventDefault();

                var formData = $(this).serialize(); // Lấy dữ liệu từ form
                $.ajax({
                    url: "{{ route('admin.chats.create') }}",
                    method: "POST",
                    data: formData,
                    success: function(response) {
                        if (response.status == 'success') {
                            // Cập nhật lại dữ liệu nhóm và admin trên giao diện
                            $('#conversationList').html(response.data.channels);
                            alert(response.message); // Hiển thị thông báo thành công
                            window.location.href = "{{ route('admin.chats.index') }}";
                        } else {
                            alert(response.message); // Hiển thị thông báo lỗi
                        }
                    },
                    error: function() {
                        alert("Có lỗi xảy ra!"); // Hiển thị lỗi
                    }
                });
            });

            $('.conversationList a').click(function(event) {
                event.preventDefault(); // Ngừng hành động mặc định của liên kết

                var channelId = $(this).data('channel-id'); // Lấy ID của nhóm chat

                // Gửi yêu cầu AJAX để lấy thông tin nhóm
                $.ajax({
                    url: "{{ route('admin.chats.getGroupInfo') }}", // Endpoint API để lấy thông tin nhóm
                    method: 'GET',
                    data: {
                        id: channelId
                    },
                    success: function(response) {
                        console.log(response);
                        if (response) {

                            // Cập nhật tên nhóm và số thành viên
                            $('.name').text(response.data.name);
                            $('.memberCount').text(response.data.memberCount);
                            $('#nameUser').text(response.data.direct);
                            $('#imageUser').attr('src', response.data.avatar);
                            $('.getID').attr('data-conversation-id', response.data.channelId);
                            loadMessages(response.data.group.id);
                            loadSentFiles(response.data.group.id);
                            let membersHtml = '';
                            response.data.member.forEach(function(member) {
                                membersHtml += `<li class="list-group-item">
                                                    <div class="d-flex align-items-center">
                                                        <div class="flex-shrink-0">
                                                            <img src="${member.avatar}" alt="" class="avatar-xs rounded-circle">
                                                        </div>
                                                        <div class="flex-grow-1 ms-2">
                                                            ${member.name}
                                                        </div>`;

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
            });
            // Khi người dùng chọn một nhóm
            $('.group-button').click(function() {
                currentConversationId = $(this).data('channel-id'); // Lấy ID nhóm đã chọn
                console.log('Đã chọn nhóm với ID:', currentConversationId);
                window.Echo.private('conversation.' + currentConversationId)
                    .listen('GroupMessageSent', function(event) {
                        $('#messagesList').append(renderMessage(event));
                        scrollToBottom();
                        // alert('Đã nhận tin nhắn mới');
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
                            // Cập nhật giao diện nếu thành viên được thêm thành công
                            alert('Thành viên đã được thêm vào nhóm!');

                            // Bạn có thể cập nhật danh sách thành viên ở frontend, ví dụ như thêm thành viên vào danh sách nhóm chat.

                            // Đóng modal sau khi thêm thành viên
                            $('#myModal').modal('hide');
                        } else {
                            alert('Có lỗi xảy ra khi thêm thành viên.');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.log(xhr.responseText);
                        alert('Đã có lỗi xảy ra. Vui lòng thử lại.');
                    }
                });
            });
            // Khi người dùng nhấn gửi tin nhắn
            $('#sendMessageButton').click(function(e) {
                e.preventDefault();
                let content = $('#messageInput').val();
                let parentId = $('#parentMessageId').val();
                let type = 'text'; // Hoặc 'image', 'file', tùy thuộc vào loại tin nhắn
                let metaData = null; // Nếu có dữ liệu bổ sung (ví dụ: hình ảnh, file...)
                let formData = new FormData();

                // Thêm dữ liệu tin nhắn
                formData.append('conversation_id', currentConversationId);
                formData.append('content', content);
                formData.append('parent_id', parentId || '');
                formData.append('type', type);
                // Kiểm tra nếu có ảnh được chọn
                let fileInput = $('#fileInput')[0].files[0];
                if (fileInput) {
                    formData.append('fileinput', fileInput);
                    type = 'image'; // Đổi type thành image nếu có file ảnh
                    formData.set('type', type);
                }
                if (currentConversationId && content || fileInput) {
                    // Gửi tin nhắn vào nhóm hiện tại
                    $.ajax({
                        url: "{{ route('admin.chats.sendGroupMessage') }}",
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            console.log(response);

                            if (response.status == 'success') {
                                $('#messageInput').val(''); // Xóa nội dung nhập
                                $('#fileInput').val(''); // Reset input file
                                $('#imagePreviewContainer')
                                    .hide(); // Ẩn preview ảnh sau khi gửi


                                $('#messagesList').append(renderMessage(response));
                                scrollToBottom();
                            }
                        },
                        error: function(xhr) {
                            alert("Gửi tin nhắn thất bại, thử lại!");
                            console.error(xhr.responseText);
                        }
                    });
                } else {
                    alert("Vui lòng nhập tin nhắn hoặc chọn ảnh!");
                }
            });
        });

        function loadMessages(conversationId) {
            $.get('http://127.0.0.1:8000/admin/chats/get-messages/' + conversationId, function(response) {
                if (response.status === 'success') {
                    // Lấy tất cả các tin nhắn
                    $('#messagesList').html(''); // Xóa danh sách tin nhắn cũ

                    const messagesHtml = response.messages.map(message => {
                        // console.log(response);

                        // Kiểm tra ID người gửi và người nhận
                        const messageClass = message.sender.id == userId ? 'sender' :
                            'received'; // Xác định lớp tin nhắn   
                        const time = formatTime(message.created_at);
                        let messageContent = '';
                        try {
                            // Kiểm tra nếu có `media` và lấy ảnh đầu tiên
                            if (message.media && message.media.length > 0) {
                                let mediaFile = message.media[0].file_path; // Lấy đường dẫn ảnh
                                messageContent = `
                                    <p>${message.content}</p>
                                    <img src="/storage/${mediaFile}" alt="Hình ảnh" 
                                    style=max-width:350px; border-radius: 8px;">
                                    `;
                            } else {
                                messageContent =
                                    `<p>${message.content}</p>`; // Hiển thị văn bản nếu không có ảnh
                            }
                        } catch (error) {
                            console.error("Lỗi lấy ảnh:", error);
                            messageContent =
                                `<p>${message.content}</p>`; // Nếu lỗi, fallback về content
                        }
                        return `
                            <div class=" message ${messageClass} style="padding-top: 10px">
                                <div class="message-avatar">
                                    <img src="${message.sender.avatar}" alt="avatar">
                                </div>
                                <div class="message-content">
                                    <div class="message-header">
                                        <strong>${message.sender.name}</strong>
                                        <span class="message-time">${time}</span>
                                        </div>
                                        <p>   
                                             ${messageContent}
                                            </p>
                                        </div>
                                </div>`;
                    }).join(''); // Chuyển mảng thành chuỗi HTML

                    $('#elmLoader').hide(); // Ẩn loader khi tải xong tin nhắn
                    $('#messagesList').append(messagesHtml); // Thêm tin nhắn vào danh sách
                    scrollToBottom();
                } else {
                    $('#elmLoader').show(); // Hiển thị loader nếu có lỗi
                }
            });
        }

        function loadSentFiles(conversationId) {
            $.get('http://127.0.0.1:8000/admin/chats/get-sent-files/' + conversationId, function(response) {

                if (response.status === 'success') {
                    $('#sentFilesList').html(''); // Xóa danh sách cũ

                    if (response.files.length === 0) {
                        $('#sentFilesList').html('<p>Chưa có file nào được gửi</p>');
                        return;
                    }
                    let visibleImages = 6; // Số ảnh hiển thị ban đầu
                    let allFiles = response.files; // Lưu toàn bộ danh sách ảnh
                    function renderImages() {
                        $('#sentFilesList').html(''); // Xóa nội dung cũ
                        let filesToShow = allFiles.slice(0, visibleImages); // Lấy số ảnh cần hiển thị

                        const filesHtml = filesToShow.map(file => {
                            return `
                        <div class="gallery-item">
                            <img src="/storage/${file.file_path}" alt="File đã gửi">
                        </div>
                    `;
                        }).join('');

                        $('#sentFilesList').append(filesHtml);

                        if (visibleImages >= allFiles.length) {
                            $('#loadMore').hide(); // Ẩn nút khi không còn ảnh để load
                        } else {
                            $('#loadMore').show(); // Hiển thị nút nếu còn ảnh
                        }
                    }

                    // Render ảnh ban đầu
                    renderImages();

                    // Xử lý khi bấm nút "Xem thêm"
                    $('#loadMore').off('click').on('click', function() {
                        visibleImages += 6;
                        renderImages();
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

            const messageClass = response.message.sender.id == userId ?
                'sender' : 'received';
            const time = formatTime(response.message.created_at);
            let messageContent = '';

            try {
                if (response.message.media && response.message.media
                    .length > 0) {
                    let mediaFile = response.message.media[0]
                        .file_path; // Lấy đường dẫn ảnh
                    messageContent = `
                <p>${response.message.content}</p>
                <img src="/storage/${mediaFile}" alt="Hình ảnh" 
                style="max-width:350px; border-radius: 8px;">
            `;
                } else {
                    messageContent =
                        `<p>${response.message.content}</p>`; // Hiển thị văn bản nếu không có ảnh
                }
            } catch (error) {
                console.error("Lỗi lấy ảnh:", error);
                messageContent =
                    `<p>${response.message.content}</p>`; // Nếu lỗi, fallback về content
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
            </div>
        </div>
    `;
            return messageHtml;
        }
    </script>
@endpush
