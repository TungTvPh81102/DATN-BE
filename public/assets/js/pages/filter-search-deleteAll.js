$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).on('change', '#checkAll', function () {
        const isChecked = $(this).prop('checked');
        $('input[name="itemID"]').prop('checked', isChecked);
    });

    $(document).on('click', "#deleteSelected", function (event) {
        event.preventDefault();

        var selectedItems = [];
        $('input[name="itemID"]:checked').each(function () {
            selectedItems.push($(this).val());
        });

        if (selectedItems.length == 0) {
            Swal.fire({
                title: 'Chọn ít nhất 1 lựa chọn để xóa',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }

        let deleteUrl = routeDeleteAll.replace(':itemID',
            selectedItems.join(','));

        Swal.fire({
            title: "Bạn có muốn xóa ?",
            text: "Bạn sẽ không thể khôi phục dữ liệu khi xoá!!",
            icon: "error",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Đồng ý!!",
            cancelButtonText: "Huỷ!!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "DELETE",
                    url: deleteUrl,
                    success: function (data) {
                        if (data.status === 'success') {
                            Swal.fire({
                                title: 'Thao tác thành công!',
                                text: data.message,
                                icon: 'success'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload();
                                }
                            });
                        } else if (data.status === 'error') {
                            Swal.fire({
                                title: "Thao tác thất bại!",
                                text: data.message,
                                icon: 'error'
                            });
                        }
                    },
                    error: function (data) {
                        console.log('Error:', data);
                        Swal.fire({
                            title: "Thao tác thất bại!",
                            text: data.responseJSON.message,
                            icon: 'error'
                        });
                    }
                });
            }
        });
    });

    $(document).on('click', "#restoreSelected", function (event) {
        event.preventDefault();

        var selectedItems = [];
        $('input[name="itemID"]:checked').each(function () {
            selectedItems.push($(this).val());
        });

        if (selectedItems.length == 0) {
            Swal.fire({
                title: 'Chọn ít nhất 1 lựa chọn để khôi phục',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }

        let restoreUrl = routeRestoreUrl.replace(':itemID',
            selectedItems.join(','));

        Swal.fire({
            title: "Bạn có muốn khôi phục ?",
            text: "Bạn sẽ khôi phục dữ liệu đã xoá!!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#DD6B55",
            confirmButtonText: "Đồng ý!!",
            cancelButtonText: "Huỷ!!"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    type: "PUT",
                    url: restoreUrl,
                    success: function (data) {
                        if (data.status === 'success') {
                            Swal.fire({
                                title: 'Thao tác thành công!',
                                text: data.message,
                                icon: 'success'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    location.reload();
                                }
                            });
                        } else if (data.status === 'error') {
                            Swal.fire({
                                title: "Thao tác thất bại!",
                                text: data.message,
                                icon: 'error'
                            });
                        }
                    },
                    error: function (data) {
                        console.log('Error:', data);
                        Swal.fire({
                            title: "Thao tác thất bại!",
                            text: data.responseJSON.message,
                            icon: 'error'
                        });
                    }
                });
            }
        });
    });

    $(document).on('click', '#applyFilter', function (event) {
        event.preventDefault();

        var status = $('#statusItem').val();
        var startDate = $('#startDate').val();
        var endDate = $('#endDate').val();

        $.ajax({
            type: 'GET',
            url: routeUrlFilter,
            data: {
                status: status,
                start_date: startDate,
                end_date: endDate
            },
            success: function (response) {
                $('#item_List').html(response.html);
                $('#statusItem').val(status);
                $('#startDate').val(startDate);
                $('#endDate').val(endDate);
            }
        });
    });

    $(document).on('click', '#search-full', function (event) {
        event.preventDefault();

        var searchFull = $('input[name="searchFull"]').val();

        $.ajax({
            type: 'GET',
            url: routeUrlFilter,
            data: {
                search_full: searchFull
            },
            success: function (response) {
                $('#item_List').html(response.html);
                $('input[name="searchFull"]').val(searchFull);
            }
        });
    });
});