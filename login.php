<!DOCTYPE html>
<html lang="th">

<?php
include('includes/Header.php');
include('includes/CheckDevice.php');
?>

<style>
    body {
        font-family: 'Prompt', sans-serif;
    }
</style>

<style type="text/css">
    .toggleeye {
        float: right;
        margin-right: 6px;
        margin-top: -20px;
        position: relative;
        z-index: 2;
        color: darkgrey;
    }
    .forgot-password {
        display: block;
        margin-top: 10px;
        text-align: center;
        color: blue;
        text-decoration: none;
    }
    .forgot-password:hover {
        text-decoration: underline;
    }
</style>

<script>
    $(document).ready(function () {
        let username = '<?php if (isset($_COOKIE["username"])) {
            echo $_COOKIE["username"];
        } ?>';
        let password = '<?php if (isset($_COOKIE["password"])) {
            echo $_COOKIE["password"];
        } ?>';
        let remember_chk = '<?php echo $_COOKIE["remember_chk"]?>';

        $("#username").val(username);
        $("#password").val(password);

        if (remember_chk === "check") {
            $("#remember").prop('checked', true); // Checked
        }

    });
</script>

<script>
    $(document).ready(function () {
        $("button").click(function () {
            check_login();
        });
    });
</script>

<script>
    function check_login() {
        let username = $("#username").val();
        let password = $("#password").val();
        let remember = "";

        if ($("#remember").prop("checked")) {
            remember = $("#remember").val();
        }

        if (username != "" && password != "") {
            $.ajax
            ({
                type: 'post',
                url: 'login_process',
                data: {
                    username: username,
                    password: password,
                    remember: remember,
                },
                success: function (response) {
                    if (response !== "0") {
                        window.location.href = response;
                    } else {
                        alert("เข้าระบบไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง");
                        window.location.href = "login";
                    }
                }
            });
        } else {
            alert("Please Fill All The Details");
        }

        return false;
    }
</script>

<script type='text/javascript'>
    $(document).ready(function () {
        $('#togglePassword').click(function () {
            $('#password').attr('type') === 'password' ? $('#password').attr('type', 'text') : $('#password').attr('type', 'password');
        });
    });
</script>

<script>
    $(document).keyup(function(event) {
        if (event.which === 13) {
            check_login();
        }
    });
</script>

<body class="bg-gradient-login">
<!-- Login Content -->
<div class="container-login">
    <div class="row justify-content-center">
        <div class="col-xl-6 col-lg-12 col-md-9">
            <div class="card shadow-sm my-5">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="login-form">
                                <div class="text-center">
                                    <div>
                                        <img src="img/logo/logo text-01.png" class="img-fluid" alt="Logo" style="max-width: 100%; height: auto;">
                                    </div>
                                    <h1 class="h4 text-gray-900 mb-4">ระบบข้อมูลพนักงาน SAC</h1>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="username"
                                           value=""
                                           placeholder="Enter User Name">
                                </div>
                                <div class="form-group">
                                    <input type="password" class="form-control" id="password"
                                           value=""
                                           placeholder="Password">
                                    <span class="far fa-eye toggleeye" id="togglePassword"
                                          style="cursor: pointer;"></span>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="on" id="remember"
                                                   name="remember">
                                            <label class="form-check-label" for="remember">
                                                <?php  if ($_SESSION['deviceType']=='computer') { ?>
                                                    <p style="color:blue;">Remember Me 30 Days</p>
                                                <?php } else { ?>
                                                    <p style="color:red;">Remember Me 30 Days</p>
                                                <?php } ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <button type="button" name="login-submit" id="login-submit" tabindex="4"
                                            class="form-control btn btn-primary">
                                            <span class="spinner">
                                                <i class="icon-spin icon-refresh" id="spinner"></i></span> Log In
                                </div>

                                <!-- Forgot password link -->
                                <a href="change-password-forget" target="_blank" class="forgot-password">ลืมรหัสผ่าน?</a>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
