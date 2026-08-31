<?php
session_start();
error_reporting(0);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>ทดสอบ Upload และแปลงไฟล์ HEIC เป็น JPG</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Alertify -->
    <link rel="stylesheet" href="js/alertify/css/alertify.css">
    <link rel="stylesheet" href="js/alertify/css/themes/default.css">
    
    <style>
        body {
            background-color: #f4f6f9;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            color: #333;
        }
        .header-box {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 30px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .dropzone-box {
            border: 2px dashed #4e73df;
            border-radius: 12px;
            background-color: #ffffff;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .dropzone-box:hover, .dropzone-box.dragover {
            background-color: #e8f0fe;
            border-color: #2e59d9;
            transform: translateY(-2px);
        }
        .dropzone-box i {
            font-size: 54px;
            color: #4e73df;
            margin-bottom: 15px;
        }
        .card-custom {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            background: #fff;
            margin-bottom: 25px;
        }
        .preview-card {
            border: 1px solid #e3e6f0;
            border-radius: 10px;
            padding: 15px;
            background: #fff;
            margin-bottom: 15px;
            transition: all 0.2s ease;
        }
        .preview-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .img-thumb-preview {
            width: 100%;
            height: 160px;
            object-fit: cover;
            border-radius: 8px;
            cursor: pointer;
            border: 1px solid #ddd;
        }
        .badge-heic {
            background-color: #e74a3b;
            color: white;
        }
        .badge-jpg {
            background-color: #1cc88a;
            color: white;
        }
        .recent-thumb {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #ddd;
        }
        .loader {
            display: inline-block;
            width: 1.5rem;
            height: 1.5rem;
            vertical-align: text-bottom;
            border: .2em solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: spinner-border .75s linear infinite;
        }
    </style>
</head>
<body>

<div class="container py-4">
    <!-- Header -->
    <div class="header-box">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h2 class="font-weight-bold mb-1"><i class="fas fa-magic mr-2"></i>ระบบทดสอบการแปลงไฟล์ HEIC เป็น JPG</h2>
                <p class="mb-0 text-white-50">ทดสอบการเลือกไฟล์ HEIC จาก iPhone/iPad -> แปลงเป็น JPG อัตโนมัติ -> แสดงตัวอย่างรูปภาพ -> บันทึกขึ้น Server</p>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="manage_leave_document.php" class="btn btn-light btn-sm font-weight-bold mr-2"><i class="fas fa-arrow-left mr-1"></i>หน้าเอกสารการลา</a>
                <a href="upload_leave_data.php" class="btn btn-outline-light btn-sm font-weight-bold"><i class="fas fa-upload mr-1"></i>หน้า Upload เอกสาร</a>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left: Upload & Conversion Zone -->
        <div class="col-lg-7">
            <div class="card card-custom p-4">
                <h5 class="font-weight-bold text-primary mb-3"><i class="fas fa-cloud-upload-alt mr-2"></i>1. เลือกหรือลากไฟล์ภาพ HEIC/JPG มาวางที่นี่</h5>
                
                <!-- Dropzone Area -->
                <div class="dropzone-box" id="dropZone" onclick="document.getElementById('fileInput').click()">
                    <i class="fas fa-images"></i>
                    <h5 class="font-weight-bold">คลิกที่นี่ หรือ ลากไฟล์รูปภาพมาวาง</h5>
                    <p class="text-muted small mb-2">รองรับไฟล์ <strong>.HEIC, .HEIF, .JPG, .PNG, .WEBP</strong> (เลือกได้หลายไฟล์พร้อมกัน)</p>
                    <button type="button" class="btn btn-primary btn-sm px-4 rounded-pill font-weight-bold">
                        <i class="fas fa-folder-open mr-1"></i> เลือกไฟล์รูปภาพ
                    </button>
                    <input type="file" id="fileInput" accept="image/*,.heic,.heif" multiple style="display: none;" onchange="handleFilesSelected(event)">
                </div>

                <!-- Conversion Setting -->
                <div class="mt-3 bg-light p-3 rounded">
                    <div class="d-flex justify-content-between align-items-center">
                        <label class="mb-0 font-weight-bold small text-muted"><i class="fas fa-sliders-h mr-1"></i>คุณภาพของภาพ JPG ที่แปลง (Quality):</label>
                        <span id="qualityVal" class="badge badge-primary font-weight-bold">85%</span>
                    </div>
                    <input type="range" class="custom-range mt-2" id="qualityRange" min="0.5" max="1.0" step="0.05" value="0.85" oninput="document.getElementById('qualityVal').textContent = Math.round(this.value * 100) + '%'">
                </div>

                <!-- Action Buttons -->
                <div class="mt-3 d-flex justify-content-between">
                    <button class="btn btn-outline-danger btn-sm" onclick="clearAllFiles()">
                        <i class="fas fa-trash-alt mr-1"></i> ล้างรายการทั้งหมด
                    </button>
                    <button class="btn btn-success btn-sm px-3 font-weight-bold" id="btnUploadServer" onclick="uploadFilesToServer()" disabled>
                        <i class="fas fa-cloud-upload-alt mr-1"></i> บันทึกรูปภาพขึ้น Server (img_doc/)
                    </button>
                </div>
            </div>

            <!-- Converted / Processed Files List -->
            <div class="card card-custom p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-list-check mr-2"></i>รายการรูปภาพที่แปลงแล้ว (<span id="fileCountBadge">0</span>)</h5>
                    <span id="statusOverall" class="badge badge-secondary">พร้อมเลือกไฟล์</span>
                </div>
                
                <div id="previewList" class="row">
                    <div class="col-12 text-center text-muted py-4" id="emptyNotice">
                        <i class="far fa-image fa-3x mb-2 text-muted"></i>
                        <p class="mb-0">ยังไม่มีไฟล์ที่เลือก กรุณาเลือกไฟล์ .heic หรือรูปภาพด้านบน</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Server Status & Recent Uploads -->
        <div class="col-lg-5">
            <!-- Server Response Card -->
            <div class="card card-custom p-4">
                <h5 class="font-weight-bold text-success mb-3"><i class="fas fa-check-circle mr-2"></i>ผลลัพธ์การบันทึกบน Server</h5>
                <div id="serverResultBox" class="p-3 bg-light rounded text-muted small" style="min-height: 120px;">
                    <p class="mb-0 text-center py-3">ยังไม่มีการ Upload ข้อมูลขึ้น Server</p>
                </div>
            </div>

            <!-- Recent Files in img_doc/ -->
            <div class="card card-custom p-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="font-weight-bold text-dark mb-0"><i class="fas fa-history mr-2"></i>ไฟล์ล่าสุดในโฟลเดอร์ img_doc/</h5>
                    <button class="btn btn-sm btn-outline-secondary" onclick="loadRecentFiles()" title="รีเฟรช"><i class="fas fa-sync-alt"></i></button>
                </div>
                <div id="recentFilesContainer" style="max-height: 420px; overflow-y: auto;">
                    <div class="text-center py-3 text-muted"><div class="loader mr-2"></div> กำลังโหลดรายการ...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal Preview -->
<div class="modal fade" id="imageModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title font-weight-bold" id="imageModalTitle">แสดงรูปภาพ</h6>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center p-2 bg-dark">
                <img id="imageModalSrc" src="" style="max-width: 100%; max-height: 75vh; border-radius: 4px;">
            </div>
            <div class="modal-footer py-2 justify-content-between">
                <span id="imageModalInfo" class="small text-muted"></span>
                <a id="imageModalDownload" href="#" download="" class="btn btn-primary btn-sm"><i class="fas fa-download mr-1"></i> ดาวน์โหลดรูปนี้</a>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="js/jquery-1.11.3-jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/alertify/alertify.js"></script>
<script src="js/heic2any.min.js"></script>
<script src="js/heic_handler.js"></script>

<script>
    let processedFileList = []; // array of { originalFile, convertedFile, previewUrl, isHeic, originalSize, convertedSize, id }

    // Drag and drop handlers
    const dropZone = document.getElementById('dropZone');
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('dragover');
        }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
            processSelectedFiles(Array.from(files));
        }
    });

    function handleFilesSelected(event) {
        const files = Array.from(event.target.files);
        if (files.length > 0) {
            processSelectedFiles(files);
        }
    }

    async function processSelectedFiles(files) {
        document.getElementById('emptyNotice')?.remove();
        document.getElementById('statusOverall').className = 'badge badge-warning';
        document.getElementById('statusOverall').innerHTML = '<div class="loader mr-1" style="width: 10px; height: 10px;"></div> กำลังประมวลผล / แปลงไฟล์...';

        const quality = parseFloat(document.getElementById('qualityRange').value) || 0.85;

        for (let file of files) {
            const isHeic = isHeicFile(file);
            const fileId = 'file_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            
            // Create a placeholder card in UI
            renderLoadingCard(fileId, file.name, formatBytes(file.size), isHeic);

            try {
                let convertedFile = file;
                let previewUrl = '';

                if (isHeic) {
                    // Convert HEIC to JPG
                    if (typeof alertify !== 'undefined' && alertify.message) {
                        alertify.message(`กำลังแปลงไฟล์ ${file.name} เป็น JPG...`);
                    }
                    convertedFile = await convertHeicToJpg(file, quality);
                    previewUrl = URL.createObjectURL(convertedFile);
                } else {
                    // Regular image
                    previewUrl = URL.createObjectURL(file);
                }

                const itemData = {
                    id: fileId,
                    originalFile: file,
                    convertedFile: convertedFile,
                    previewUrl: previewUrl,
                    isHeic: isHeic,
                    originalSize: file.size,
                    convertedSize: convertedFile.size
                };

                processedFileList.push(itemData);
                updateCardWithConvertedData(itemData);

            } catch (err) {
                console.error("Conversion error for", file.name, err);
                updateCardWithError(fileId, file.name, err.message || 'แปลงไฟล์ไม่สำเร็จ');
            }
        }

        updateUIState();
        document.getElementById('fileInput').value = '';
    }

    function renderLoadingCard(fileId, originalName, originalSize, isHeic) {
        const container = document.getElementById('previewList');
        const col = document.createElement('div');
        col.className = 'col-md-6 col-12';
        col.id = fileId;
        col.innerHTML = `
            <div class="preview-card">
                <div class="d-flex align-items-center justify-content-center bg-light rounded" style="height: 140px;">
                    <div class="text-center text-primary">
                        <div class="loader mb-2"></div>
                        <div class="small font-weight-bold">${isHeic ? 'กำลังแปลง HEIC -> JPG...' : 'กำลังโหลดภาพ...'}</div>
                    </div>
                </div>
                <div class="mt-2">
                    <div class="text-truncate font-weight-bold small" title="${originalName}">${originalName}</div>
                    <div class="small text-muted">ขนาดต้นฉบับ: ${originalSize}</div>
                </div>
            </div>
        `;
        container.appendChild(col);
    }

    function updateCardWithConvertedData(data) {
        const cardCol = document.getElementById(data.id);
        if (!cardCol) return;

        const sizeDiff = data.originalSize - data.convertedSize;
        const percentSavings = data.originalSize > 0 ? Math.round((sizeDiff / data.originalSize) * 100) : 0;

        cardCol.innerHTML = `
            <div class="preview-card position-relative">
                <button type="button" class="btn btn-danger btn-sm position-absolute" style="top: 8px; right: 8px; z-index: 10; width: 26px; height: 26px; padding: 0; border-radius: 50%;" onclick="removeFile('${data.id}')" title="ลบ">&times;</button>
                
                <img src="${data.previewUrl}" class="img-thumb-preview mb-2" onclick="openModalPreview('${data.previewUrl}', '${data.convertedFile.name}', '${formatBytes(data.convertedSize)}')">
                
                <div class="d-flex justify-content-between align-items-center mb-1">
                    ${data.isHeic 
                        ? '<span class="badge badge-heic small"><i class="fas fa-apple-alt mr-1"></i>HEIC</span> <i class="fas fa-arrow-right text-muted small mx-1"></i> <span class="badge badge-jpg small"><i class="fas fa-check mr-1"></i>JPG</span>' 
                        : '<span class="badge badge-info small"><i class="far fa-image mr-1"></i>' + data.convertedFile.name.split('.').pop().toUpperCase() + '</span>'}
                    <span class="badge badge-light border text-muted small font-weight-bold">${formatBytes(data.convertedSize)}</span>
                </div>

                <div class="small text-truncate font-weight-bold text-dark" title="${data.convertedFile.name}">
                    ${data.convertedFile.name}
                </div>

                <div class="small text-muted d-flex justify-content-between mt-1 pt-1 border-top">
                    <span>ต้นฉบับ: ${formatBytes(data.originalSize)}</span>
                    <a href="${data.previewUrl}" download="${data.convertedFile.name}" class="text-primary font-weight-bold"><i class="fas fa-download"></i> โหลด JPG</a>
                </div>
            </div>
        `;
    }

    function updateCardWithError(fileId, fileName, errorMsg) {
        const cardCol = document.getElementById(fileId);
        if (!cardCol) return;
        cardCol.innerHTML = `
            <div class="preview-card border-danger">
                <div class="text-danger small font-weight-bold mb-1"><i class="fas fa-exclamation-triangle mr-1"></i> แปลงไฟล์ไม่สำเร็จ</div>
                <div class="small text-truncate text-muted">${fileName}</div>
                <div class="small text-danger mt-1">${errorMsg}</div>
                <button class="btn btn-sm btn-outline-danger mt-2 w-100" onclick="removeFile('${fileId}')">นำออก</button>
            </div>
        `;
    }

    function removeFile(id) {
        processedFileList = processedFileList.filter(item => item.id !== id);
        const el = document.getElementById(id);
        if (el) el.remove();
        updateUIState();
    }

    function clearAllFiles() {
        processedFileList = [];
        document.getElementById('previewList').innerHTML = `
            <div class="col-12 text-center text-muted py-4" id="emptyNotice">
                <i class="far fa-image fa-3x mb-2 text-muted"></i>
                <p class="mb-0">ยังไม่มีไฟล์ที่เลือก กรุณาเลือกไฟล์ .heic หรือรูปภาพด้านบน</p>
            </div>
        `;
        updateUIState();
    }

    function updateUIState() {
        const count = processedFileList.length;
        document.getElementById('fileCountBadge').textContent = count;
        document.getElementById('btnUploadServer').disabled = count === 0;

        if (count > 0) {
            document.getElementById('statusOverall').className = 'badge badge-success';
            document.getElementById('statusOverall').textContent = `พร้อมบันทึก ${count} ไฟล์`;
        } else {
            document.getElementById('statusOverall').className = 'badge badge-secondary';
            document.getElementById('statusOverall').textContent = 'พร้อมเลือกไฟล์';
        }
    }

    function openModalPreview(url, name, size) {
        document.getElementById('imageModalTitle').textContent = name;
        document.getElementById('imageModalSrc').src = url;
        document.getElementById('imageModalInfo').textContent = `ขนาดไฟล์: ${size}`;
        document.getElementById('imageModalDownload').href = url;
        document.getElementById('imageModalDownload').download = name;
        $('#imageModal').modal('show');
    }

    // Upload to server
    function uploadFilesToServer() {
        if (processedFileList.length === 0) return;

        const btn = document.getElementById('btnUploadServer');
        btn.disabled = true;
        btn.innerHTML = '<div class="loader mr-1" style="width: 12px; height: 12px;"></div> กำลัง Upload...';

        const formData = new FormData();
        formData.append('action', 'upload');

        processedFileList.forEach((item) => {
            formData.append('images[]', item.convertedFile, item.convertedFile.name);
        });

        $.ajax({
            url: 'test_heic_process.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function (res) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-cloud-upload-alt mr-1"></i> บันทึกรูปภาพขึ้น Server (img_doc/)';

                if (res.status === 'success') {
                    alertify.alert('บันทึกรูปภาพขึ้น Server เรียบร้อยแล้ว!');
                    renderServerResult(res.files);
                    loadRecentFiles();
                } else {
                    alertify.alert('เกิดข้อผิดพลาด: ' + (res.message || (res.errors && res.errors.join(', '))));
                }
            },
            error: function (xhr, status, error) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-cloud-upload-alt mr-1"></i> บันทึกรูปภาพขึ้น Server (img_doc/)';
                alertify.alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: ' + error);
            }
        });
    }

    function renderServerResult(files) {
        const box = document.getElementById('serverResultBox');
        if (!files || files.length === 0) return;

        let html = `<div class="mb-2 font-weight-bold text-success"><i class="fas fa-check-circle mr-1"></i> อัปโหลดสำเร็จ ${files.length} ไฟล์:</div>`;
        html += `<div class="row">`;
        files.forEach(f => {
            html += `
                <div class="col-12 mb-2 p-2 border bg-white rounded">
                    <div class="d-flex align-items-center">
                        <img src="${f.url}" class="recent-thumb mr-2" onclick="openModalPreview('${f.url}', '${f.saved_name}', '${f.size_formatted}')" style="cursor: pointer;">
                        <div style="flex: 1; min-width: 0;">
                            <div class="font-weight-bold text-truncate text-dark" title="${f.saved_name}">${f.saved_name}</div>
                            <div class="text-muted small">ขนาดบนเซิร์ฟเวอร์: <span class="badge badge-light border">${f.size_formatted}</span></div>
                            <div class="mt-1">
                                <a href="${f.url}" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-2 small"><i class="fas fa-external-link-alt mr-1"></i> เปิดดูรูป</a>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        });
        html += `</div>`;
        box.innerHTML = html;
    }

    function loadRecentFiles() {
        const container = document.getElementById('recentFilesContainer');
        container.innerHTML = '<div class="text-center py-3 text-muted"><div class="loader mr-2"></div> กำลังโหลดรายการ...</div>';

        $.getJSON('test_heic_process.php?action=get_recent_files', function (res) {
            if (res.status === 'success' && res.files.length > 0) {
                let html = '<ul class="list-group list-group-flush">';
                res.files.forEach(f => {
                    html += `
                        <li class="list-group-item px-0 py-2 d-flex align-items-center">
                            ${f.is_image 
                                ? `<img src="${f.url}" class="recent-thumb mr-2" onclick="openModalPreview('${f.url}', '${f.name}', '${f.size_formatted}')" style="cursor: pointer;" alt="${f.name}">` 
                                : `<div class="recent-thumb mr-2 d-flex align-items-center justify-content-center bg-light border"><i class="fas fa-file-alt fa-lg text-secondary"></i></div>`}
                            <div style="flex: 1; min-width: 0;">
                                <div class="font-weight-bold small text-truncate text-dark" title="${f.name}">${f.name}</div>
                                <div class="text-muted small">${f.time} | <span class="badge badge-light border">${f.size_formatted}</span></div>
                            </div>
                            <a href="${f.url}" target="_blank" class="btn btn-sm btn-light ml-1" title="เปิดดู"><i class="fas fa-eye text-primary"></i></a>
                        </li>
                    `;
                });
                html += '</ul>';
                container.innerHTML = html;
            } else {
                container.innerHTML = '<div class="text-center py-3 text-muted">ไม่พบไฟล์ใน img_doc/</div>';
            }
        }).fail(function() {
            container.innerHTML = '<div class="text-center py-3 text-danger">ไม่สามารถโหลดรายการได้</div>';
        });
    }

    function formatBytes(bytes, decimals = 2) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const dm = decimals < 0 ? 0 : decimals;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
    }

    // Initial load
    $(document).ready(function() {
        loadRecentFiles();
    });
</script>

</body>
</html>
