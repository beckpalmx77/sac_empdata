<?php
require 'config/connect_db.php'; // เชื่อมต่อฐานข้อมูล
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Manage LINE API">
    <meta name="author" content="SANGUAN AUTO CAR">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <title>สงวนออโต้คาร์ | Manage LINE API</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<div class="container-fluid" id="container-wrapper">
    <h2>Manage LINE API</h2>
    <a href="Dashboard_admin" class="btn btn-outline-danger mb-3">Back to Dashboard</a>

    <form id="alineForm">
        <input type="hidden" id="id" name="id">
        <div class="mb-3">
            <label for="line_api_token" class="form-label">LINE API Token</label>
            <textarea id="line_api_token" name="line_api_token" class="form-control" required></textarea>
        </div>
        <div class="mb-3">
            <label for="doc_type" class="form-label">Document Type</label>
            <input type="text" id="doc_type" name="doc_type" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="detail" class="form-label">Detail</label>
            <input type="text" id="detail" name="detail" class="form-control" required>
        </div>
        <button type="button" id="insert" class="btn btn-success">Insert</button>
        <button type="button" id="update" class="btn btn-warning" disabled>Update</button>
    </form>

    <table class="table table-bordered mt-4">
        <thead>
        <tr>
            <th>ID</th>
            <th>Token</th>
            <th>Document Type</th>
            <th>Detail</th>
            <th>Action</th>
        </tr>
        </thead>
        <tbody id="dataBody"></tbody>
    </table>
</div>

<script>
    $(document).ready(function () {
        loadData();

        $('#insert').click(function () {
            if ($('#alineForm')[0].checkValidity()) {
                $(this).prop('disabled', true);
                $.post('model/aline_api_crud.php', $('#alineForm').serialize() + '&action=insert', function (res) {
                    alert('Insert successful!');
                    loadData();
                    $('#alineForm')[0].reset();
                    $('#insert').prop('disabled', false);
                }).fail(function () {
                    alert('Insert failed!');
                    $('#insert').prop('disabled', false);
                });
            } else {
                $('#alineForm')[0].reportValidity();
            }
        });

        $('#update').click(function () {
            if ($('#alineForm')[0].checkValidity()) {
                $(this).prop('disabled', true);
                $.post('model/aline_api_crud.php', $('#alineForm').serialize() + '&action=update', function (res) {
                    alert('Update successful!');
                    loadData();
                    $('#alineForm')[0].reset();
                    $('#update').prop('disabled', true);
                    $('#insert').prop('disabled', false);
                }).fail(function () {
                    alert('Update failed!');
                    $('#update').prop('disabled', false);
                });
            } else {
                $('#alineForm')[0].reportValidity();
            }
        });

        $(document).on('click', '.edit', function () {
            let row = $(this).closest('tr');
            $('#id').val(row.find('.id').text());
            $('#line_api_token').val(row.find('.token').text());
            $('#doc_type').val(row.find('.doc_type').text());
            $('#detail').val(row.find('.detail').text());

            $('#update').prop('disabled', false);
            $('#insert').prop('disabled', true);
        });

        $(document).on('click', '.delete', function () {
            let id = $(this).data('id');
            if (confirm('Delete this record?')) {
                $.post('model/aline_api_crud.php', {id: id, action: 'delete'}, function () {
                    alert('Delete successful!');
                    loadData();
                }).fail(function () {
                    alert('Delete failed!');
                });
            }
        });
    });

    function loadData() {
        $.get('model/aline_api_crud.php', function (data) {
            $('#dataBody').html(data);
        }).fail(function () {
            alert('Error loading data!');
        });
    }
</script>

</body>
</html>