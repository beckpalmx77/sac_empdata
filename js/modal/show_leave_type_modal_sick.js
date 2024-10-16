$(document).ready(function () {
    let formData = {action: "GET_LEAVE_TYPE", sub_action: "GET_SELECT", action_for: "LEAVE" };
    let dataRecords = $('#TableLeaveTypeList').DataTable({
        'lengthMenu': [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
        'language': {
            search: 'ค้นหา', lengthMenu: 'แสดง _MENU_ รายการ',
            info: 'หน้าที่ _PAGE_ จาก _PAGES_',
            infoEmpty: 'ไม่มีข้อมูล',
            zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
            infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
            paginate: {
                previous: 'ก่อนหน้า',
                last: 'สุดท้าย',
                next: 'ต่อไป'
            }
        },
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': {
            'url': 'model/manage_sick_leave_type_process.php',
            'data': formData
        },
        'columns': [
            {data: 'leave_type_id'},
            {data: 'leave_type_detail'},
            {data: 'select'}
        ]
    });
});

$("#TableLeaveTypeList").on('click', '.select', function () {
    let data = this.id.split('@');
    $('#leave_type_id').val(data[0]);
    $('#leave_type_detail').val(data[1]);
    $('#SearchLeaveTypeModal').modal('hide');
});
