$(document).ready(function () {

    let formData = {action: "GET_PERMISSION", sub_action: "GET_SELECT"};
    let dataRecords = $('#TablePermissionList').DataTable({
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
            'url': 'model/manage_permission.php',
            'data': formData
        },
        'columns': [
            {data: 'permission_id'},
            {data: 'permission_detail'},
            {data: 'select'}
        ]
    });
});

$("#TablePermissionList").on('click', '.select', function () {
    let data = this.id.split('@');
    $('#permission_id').val(data[0]);
    $('#permission_detail').val(data[1]);

    let permission_id = data[0];
    let formData = {action: "LOAD_PERMISSION", permission_id: permission_id};

    $.ajax({
        type: "POST",
        url: 'model/manage_permission.php',
        dataType: "json",
        data: formData,
        success: function (response) {
            console.log("Response:", response); // ตรวจสอบข้อมูลที่ได้รับ

            if (!response || response.length === 0) {
                alertify.error("No data received.");
                return;
            }

            let main_menu = response[0].main_menu || "";
            let sub_menu = response[0].sub_menu || "";
            let dashboard_page = response[0].dashboard_page || "";

            $('#dashboard_page').val(dashboard_page);

            let main_menu_array = main_menu.split(",");
            let sub_menu_array = sub_menu.split(",");

            let main_list = document.getElementsByName("menu_main");
            let sub_list = document.getElementsByName("menu_sub");

            // เคลียร์ค่าทุก checkbox ก่อน
            main_list.forEach(item => item.checked = false);
            sub_list.forEach(item => item.checked = false);

            // ตรวจสอบว่า ID มีอยู่จริงก่อนกำหนดค่า checked
            main_menu_array.forEach(m_main => {
                let mainCheckbox = document.getElementById(m_main);
                if (m_main.trim() !== "" && mainCheckbox) {
                    console.log("Checking main menu:", m_main);
                    mainCheckbox.checked = true;
                } else {
                    console.warn("Main menu ID not found:", m_main);
                }
            });

            sub_menu_array.forEach(m_sub => {
                let subCheckbox = document.getElementById(m_sub);
                if (m_sub.trim() !== "" && subCheckbox) {
                    console.log("Checking sub menu:", m_sub);
                    subCheckbox.checked = true;
                } else {
                    console.warn("Sub menu ID not found:", m_sub);
                }
            });

        },
        error: function (xhr, status, error) {
            console.error("AJAX Error: ", status, error);
            alertify.error("Error loading permissions.");
        }
    });

    $('#SearchPermissionModal').modal('hide');
});
