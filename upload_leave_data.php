<?php
session_start();
error_reporting(0);
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!doctype html>
    <html>
    <head lang="en">
        <meta charset="utf-8">
        <title>สงวนออโต้คาร์</title>
        <script type="text/javascript" src="js/jquery-1.11.3-jquery.min.js"></script>
        <script type="text/javascript" src="js/script.js"></script>
        <script type="text/javascript" src="js/alertify/alertify.js"></script>
        <link rel="stylesheet" href="js/alertify/css/alertify.css">
        <!--script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
              integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
              crossorigin="anonymous"-->
    </head>
    <body>
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <!-- Container Fluid-->
            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h3 mb-0 text-gray-800"><span id="title"></span></h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                        </li>
                        <li class="breadcrumb-item"><span id="main_menu"></li>
                        <li class="breadcrumb-item active"
                            aria-current="page"><span id="sub_menu"></li>
                    </ol>
                </div>

                <div class="col-md-8">

                    <form method="post" id="my_form" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="modal-body">


                                <div class="form-group row">
                                    <div class="col-sm-6">
                                        <label for="text"
                                               class="control-label">เลขที่เอกสาร</label>
                                        <input type="doc_id" class="form-control"
                                               id="doc_id" name="doc_id"
                                               readonly="true"
                                               required="required"
                                               value=""
                                               placeholder="">
                                    </div>

                                    <div class="col-sm-4">
                                        <label for="doc_date"
                                               class="control-label">วันที่เอกสาร</label>
                                        <i class="fa fa-calendar"
                                           aria-hidden="true"></i>
                                        <input type="text" class="form-control"
                                               id="doc_date"
                                               name="doc_date"
                                               required="required"
                                               value=""
                                               readonly="true"
                                               placeholder="วันที่เอกสาร">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <label for="emp_id"
                                               class="control-label">รหัสพนักงาน</label>
                                        <input type="text" class="form-control"
                                               id="emp_id" name="emp_id"
                                               readonly="true"
                                               required="required"
                                               value=""
                                               placeholder="">
                                    </div>
                                    <div class="col-sm-6">
                                        <label for="full_name"
                                               class="control-label">ชื่อ -
                                            นามสกุล</label>
                                        <input type="text" class="form-control"
                                               id="full_name" name="full_name"
                                               readonly="true"
                                               value=""
                                               placeholder="">
                                    </div>

                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-10">
                                        <label for="leave_type_detail"
                                               class="control-label">ประเภทการลา</label>
                                        <input type="text" class="form-control"
                                               id="leave_type_detail" name="leave_type_detail"
                                               readonly="true"
                                               required="required"
                                               value=""
                                               placeholder="">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-4">
                                        <!--label for="picture"
                                               class="control-label">ชื่อไฟล์</label-->
                                        <input type="hidden" class="form-control"
                                               id="picture" name="picture"
                                               readonly="true"
                                               required="required"
                                               value=""
                                               placeholder="">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-10">
                                        <label for="uploadImage"
                                               class="control-label">เลือกไฟล์รูปภาพที่ต้องการ Upload</label>
                                        <input class="form-control" type="file" id="uploadImage" accept="image/*,.pdf,.doc,.docx"
                                               name="image[]"
                                               onchange="previewImages(event)" multiple/>
                                        <div>Upload File (ไฟล์ .jpg , .png , .pdf , .doc , .docx) ชี้ที่รูปเพื่อขยาย หรือ Click
                                            เพื่อเปิดดูภาพ
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <label class="control-label">เอกสารที่แนบ (Click ที่รูปเพื่อขยาย)</label>
                                        <div id="preview-container" style="display: flex; flex-wrap: wrap; gap: 10px;"></div>
                                    </div>
                                </div>

                            </div>

                            <span class="icon-input-btn">
                                <input class="btn btn-success" type="submit" value="Upload">
                            </span>
                            <button type="button" class="btn btn-danger"
                                    id="btnClose">Close <i
                                        class="fa fa-times"></i>
                            </button>

                        </div>

                        <div class="modal-footer">

                            <div class='preview'>
                                <img class="enlarge" src="" id="img" width="200" height="200"
                                     onclick="openImageInNewTab()">
                            </div>

                            <!--div class='preview'>
                                <img class="enlarge" src="" id="img" width="200" height="200"
                                     onclick="window.open(this.src,'_blank')">
                            </div-->

                            <input type="hidden" name="id" id="id"/>
                            <input type="hidden" name="action" id="action" value=""/>

                        </div>
                    </form>

                </div>
            </div>
        </div>

        </form>

        <div id="err"></div>

    </div>

    <style>

        .preview {
            width: 200px;
            height: 200px;
            border: 0;
            margin: 0 auto;
            background: white;
        }

    </style>

    <script>
        function encodeURL(url) {
            return encodeURIComponent(url);
        }

        function decodeURL(url) {
            return encodeURIComponent(url);
        }
    </script>

    <script>
        function bigImg(x) {
            x.style.height = "100%";
            x.style.width = "100%";
        }

        function normalImg(x) {
            x.style.height = "200px";
            x.style.width = "200px";
        }
    </script>

    <style>
        .enlarge {
            height: 100%;
            width: 100%;
            float: left;
            -webkit-transition: all 0.5s ease-in-out;
            -moz-transition: all 0.5s ease-in-out;
            transition: all 0.5s ease-in-out;
        }

        .enlarge:hover {
            height: 300%;
            width: 300%;
            -webkit-transition: all 0.5s ease-in-out;
            -moz-transition: all 0.5s ease-in-out;
            transition: all 0.5s ease-in-out;
            cursor: pointer;
        }
    </style>

    <script>
        let selectedFiles = [];
        
        function previewImages(event) {
            const container = document.getElementById('preview-container');
            
            const newFiles = Array.from(event.target.files);
            if (newFiles.length === 0) return;
            
            newFiles.forEach((file) => {
                selectedFiles.push(file);
            });
            
            renderPreview();
        }
        
        function renderPreview() {
            const container = document.getElementById('preview-container');
            container.innerHTML = '';
            
            selectedFiles.forEach((file, index) => {
                let reader = new FileReader();
                
                reader.onload = function (e) {
                    let div = document.createElement('div');
                    div.style.position = 'relative';
                    div.style.display = 'inline-block';
                    div.style.textAlign = 'center';
                    div.style.margin = '5px';
                    
                    let img = document.createElement('img');
                    img.src = e.target.result;
                    img.style.maxWidth = '100px';
                    img.style.maxHeight = '100px';
                    img.style.cursor = 'pointer';
                    img.onclick = function() {
                        window.open(this.src, '_blank');
                    };
                    
                    let filename = document.createElement('div');
                    filename.textContent = file.name;
                    filename.style.fontSize = '10px';
                    filename.style.maxWidth = '100px';
                    filename.style.overflow = 'hidden';
                    filename.style.textOverflow = 'ellipsis';
                    filename.style.whiteSpace = 'nowrap';
                    
                    let removeBtn = document.createElement('span');
                    removeBtn.innerHTML = '&times;';
                    removeBtn.style.position = 'absolute';
                    removeBtn.style.top = '-5px';
                    removeBtn.style.right = '-5px';
                    removeBtn.style.background = 'red';
                    removeBtn.style.color = 'white';
                    removeBtn.style.borderRadius = '50%';
                    removeBtn.style.width = '20px';
                    removeBtn.style.height = '20px';
                    removeBtn.style.display = 'flex';
                    removeBtn.style.alignItems = 'center';
                    removeBtn.style.justifyContent = 'center';
                    removeBtn.style.cursor = 'pointer';
                    removeBtn.style.fontSize = '14px';
                    removeBtn.onclick = function() {
                        selectedFiles.splice(index, 1);
                        renderPreview();
                    };
                    
                    div.appendChild(img);
                    div.appendChild(filename);
                    div.appendChild(removeBtn);
                    container.appendChild(div);
                };
                
                reader.readAsDataURL(file);
            });
            
            updateFileInput();
        }
        
        function updateFileInput() {
            const dataTransfer = new DataTransfer();
            selectedFiles.forEach(file => {
                dataTransfer.items.add(file);
            });
            document.getElementById('uploadImage').files = dataTransfer.files;
        }
    </script>


    <script type="text/javascript">
        let queryString = new Array();
        $(function () {
            if (queryString.length == 0) {
                if (window.location.search.split('?').length > 1) {
                    let params = window.location.search.split('?')[1].split('&');
                    for (let i = 0; i < params.length; i++) {
                        let key = params[i].split('=')[0];
                        let value = decodeURIComponent(params[i].split('=')[1]);
                        queryString[key] = value;
                    }
                }
            }

            let data = "<b>" + queryString["title"] + "</b>";
            $("#title").html(data);
            $("#main_menu").html(queryString["main_menu"]);
            $("#sub_menu").html(queryString["sub_menu"]);
            $('#action').val(queryString["action"]);

            if (queryString["id"] != null && queryString["doc_id"] != null) {

                $('#id').val(queryString["id"]);
                $('#doc_id').val(queryString["doc_id"]);
                $('#doc_date').val(queryString["doc_date"]);
                $('#emp_id').val(queryString["emp_id"]);
                $('#full_name').val(queryString["full_name"]);
                $('#date_leave_start').val(queryString["date_leave_start"]);
                $('#date_leave_to').val(queryString["date_leave_to"]);
                $('#leave_type_detail').val(queryString["leave_type_detail"]);

                let picture = queryString["picture"];

                if (picture === null || picture === 'null' || picture === '') {
                    $('.preview').html('<img class="enlarge" src="img_doc/image_doc.png" width="200" height="200" onclick="openImageInNewTab()">');
                } else {
                    let filenames = picture.split(',');
                    let previewHtml = '';
                    
                    filenames.forEach(function(filename) {
                        let fname = filename.trim();
                        if (fname) {
                            previewHtml += '<img class="enlarge" src="img_doc/' + fname + '" width="100" height="100" style="margin: 5px;" onclick="window.open(this.src,\'_blank\')">';
                        }
                    });
                    
                    $('.preview').html(previewHtml);
                }

                $('#picture').val(queryString["picture"]);

            }
        });
    </script>

    <script>

        $(document).ready(function (e) {
            $("#my_form").on('submit', (function (e) {
                e.preventDefault();
                
                let formData = new FormData();
                
                // Add other form fields
                formData.append('id', $('#id').val());
                formData.append('doc_id', $('#doc_id').val());
                formData.append('doc_date', $('#doc_date').val());
                formData.append('emp_id', $('#emp_id').val());
                formData.append('full_name', $('#full_name').val());
                formData.append('leave_type_detail', $('#leave_type_detail').val());
                formData.append('picture', $('#picture').val());
                formData.append('action', $('#action').val());
                
                // Add selected files
                selectedFiles.forEach(file => {
                    formData.append('image[]', file);
                });
                
                $.ajax({
                    url: "upload_ajax.php",
                    type: "POST",
                    data: formData,
                    contentType: false,
                    cache: false,
                    processData: false,
                    beforeSend: function () {
                        $("#err").fadeOut();
                    },
                    success: function (data) {
                        if (data === 'invalid') {
                            $("#err").html("กรุณาเลือกไฟล์ที่ถูกต้องเพื่อ Upload !").fadeIn();
                            alertify.alert('กรุณาเลือกไฟล์ที่ถูกต้องเพื่อ Upload !');
                        } else {
                            // Show all uploaded images
                            let filenames = data.split(',');
                            let previewHtml = '';
                            
                            filenames.forEach(function(filename) {
                                let fname = filename.trim();
                                if (fname) {
                                    previewHtml += '<img class="enlarge" src="img_doc/' + fname + '" width="100" height="100" style="margin: 5px;" onclick="window.open(this.src,\'_blank\')">';
                                }
                            });
                            
                            $('.preview').html(previewHtml);
                            
                            alertify
                                .alert("Upload รูปภาพเรียบร้อยแล้ว", function () {
                                    alertify.message('OK');
                                });
                            $('#picture').val(data);
                            selectedFiles = [];
                            document.getElementById('preview-container').innerHTML = '';
                            document.getElementById('uploadImage').value = '';
                        }
                    },
                    error: function (e) {
                        $("#err").html(e).fadeIn();
                    }
                });
            }));
        });
    </script>

    <script>
        $(document).ready(function () {
            $("#btnClose").click(function () {
                window.opener = self;
                window.close();
            });
        });
    </script>

    <!--script>
        function openImageInNewTab() {
            let imgSrc = document.getElementById('img').src; // ดึง URL ของภาพ
            window.open(imgSrc, '_blank'); // เปิดภาพในแท็บใหม่
        }
    </script-->

    <script>
        function openImageInNewTab() {
            let imgSrc = document.getElementById('img').src; // ดึง URL ของภาพ
            let newTab = window.open("", "_blank"); // เปิดแท็บใหม่

            if (newTab) {
                newTab.document.write(`
                    <html>
                    <head>
                        <title>เอกสารแนบ</title>
                        <link rel="icon" href="img/favicon.ico" type="image/x-icon">
                    </head>
                    <body>
                        <h2>เอกสารแนบ</h2>
                        <img src="${imgSrc}" style="width: 50%;">
                    </body>
                    </html>
                `);
                newTab.document.close();
            } else {
                alert("Pop-up ถูกบล็อก! กรุณาอนุญาตให้เปิดหน้าต่างใหม่");
            }
        }
    </script>



    </body>
    </html>

    <?php
}
?>

