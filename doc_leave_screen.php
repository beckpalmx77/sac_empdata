<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Leave Date</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

<form id="leaveForm">
    <label for="leave_type_id">Leave Type ID:</label>
    <input type="text" id="leave_type_id" name="leave_type_id" required>

    <label for="leaves_requested">Leaves Requested:</label>
    <input type="number" id="leaves_requested" name="leaves_requested" required>

    <label for="leave_date">Leave Date:</label>
    <input type="date" id="leave_date" name="leave_date" required>

    <button type="submit">Submit</button>
</form>

<div id="result"></div>

<script>
    $(document).ready(function () {
        $("#leaveForm").submit(function (event) {
            event.preventDefault();
            let leaveTypeId = $('#leave_type_id').val();
            let leavesRequested = $('#leaves_requested').val();
            let leaveDate = $('#leave_date').val();

            $.ajax({
                url: 'check_leave_date.php',
                type: 'POST',
                data: {
                    leave_type_id: leaveTypeId,
                    leaves_requested: leavesRequested,
                    leave_date: leaveDate
                },
                success: function (response) {
                    $('#result').html(response);
                },
                error: function (xhr, status, error) {
                    console.error(xhr.responseText);
                }
            });
        });
    });
</script>

</body>
</html>

