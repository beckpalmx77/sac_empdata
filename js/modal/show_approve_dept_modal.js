$(document).ready(function () {
    let formData = {action: "GET_DEPT_APPROVE", sub_action: "GET_SELECT", action_for: "DEPT_ID" };
    let dataRecords = $('#TableApproveList').DataTable({
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
            'url': 'model/get_approve_dept_process.php',
            'data': formData
        },
        'columns': [
            {data: 'dept_id_approve'},
            {data: 'approve_name'},
            {data: 'select'}
        ]
    });
});

$("#TableApproveList").on('click', '.select', function () {
    let data = this.id.split('@');
    $('#dept_id_approve').val(data[0]);
    $('#id_dept_approve').val(data[1]);
    $('#SearchApproveModal').modal('hide');
});
