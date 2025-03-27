<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ส่งอีเมล</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">

<h2>ส่งอีเมล</h2>
<form action="engine/send_email.php" method="post">
    <div class="mb-3">
        <label for="to_email" class="form-label">อีเมลผู้รับ:</label>
        <input type="email" name="to_email" id="to_email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="subject" class="form-label">หัวข้อ:</label>
        <input type="text" name="subject" id="subject" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="message" class="form-label">ข้อความ:</label>
        <textarea name="message" id="message" class="form-control" rows="5" required></textarea>
    </div>
    <button type="submit" class="btn btn-primary">ส่งอีเมล</button>
</form>

</body>
</html>