<?php
session_start();
include "connect.php";
if (isset($_SESSION['fname']) && isset($_SESSION['lname'])) {
    header("Location:index.php");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Niramit:wght@200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?<?php echo time() ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css" />
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" rel="stylesheet" />
    <title>ระบบยืมคืนอุปกรณ์ IT</title>
</head>
<style>
    body {
        background-image: url(img/bg.png);
        background-attachment: fixed;
        background-repeat: no-repeat;
    }
</style>

<body>
    <div class="container">
        <div class="row justify-content-center align-items-center g-2">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <form action="con1.php" method="post">
                            <div class="container">
                                <div class="row justify-content-center align-items-center g-2">
                                    <div class="col-md-12">
                                        <center>
                                            <h1>ระบบยืมคืนอุปกรณ์ IT</h1>
                                        </center>
                                        <center><label for="" style="color:red;">
                                                <?php
                                                if (isset($_GET['t']) && $_GET['t'] == 1) {
                                                    echo "ชื่อผู้ใช้งานหรือรหัสผ่านไม่ถูกต้อง โปรดลองอีกครั้ง";
                                                }
                                                ?>
                                            </label></center>
                                    </div>
                                    <div class="col-md-12">
                                        <label for="">ชื่อผู้เข้าใช้งาน</label>
                                        <input type="text" name="username" class="form-control w-100" placeholder="username">
                                    </div>
                                    <div class="col-md-12">
                                        <label for="">รหัสผ่าน</label>
                                        <input type="password" name="password" class="form-control w-100" placeholder="password" ]>
                                    </div>
                                    <div class="col-md-12">
                                        <input type="submit" name="login" value="ล็อกอิน" class="fbtn btn-primary w-100">
                                    </div>
                                </div>
                            </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3"></div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.js"></script>
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>
    <!-- (Optional) Latest compiled and minified JavaScript translation files -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/i18n/defaults-*.min.js"></script>
</body>

</html>