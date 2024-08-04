<?php
session_start();
include "../connect.php";
// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
if (!isset($_SESSION['u_id'])) {
    echo "Error: User ID is not set in session.";
    exit();
}
// ดึงข้อมูลผู้ใช้จาก Session
$fname = $_SESSION['u_fname'];
$lname = $_SESSION['u_lname'];
$address = $_SESSION['u_address'];
$u_id = $_SESSION['u_id'];

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
    <link rel="stylesheet" href="styles.css">
    <title>ระบบของช่าง IT</title>
</head>
<style>
    body {
        font-family: 'Niramit', sans-serif;
    }

    h2 {
        padding-top: 20px;
        padding-bottom: 20px;
        text-align: center;
        width: 100%;
        background-color: turquoise;
        font-weight: bold;
    }
</style>

<body>
    <h2 id="nav">ระบบของช่าง IT</h2>
    <div class="container">
        <div class="row">
            <div class="col-md-2">
                <div class="row" id="tools">
                    <a href="../index.php">
                        <button type="button" id="menu" class="btn btn-info col-12">ออกจากระบบ</button>
                    </a>
                </div>
            </div>
            <div class="col-md-10">
                <button type="button" class="btn btn-success" onclick="addborrow()" style="margin-bottom: 2%;">เพิ่มอุปกรณ์</button>
                <br>
                
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <?php
                                $sql1 = "SELECT COUNT(*) as 'nb' FROM `items_1` WHERE `ag_type`='T001' AND `ag_status`='ST001'";
                                $result1 = $conn->query($sql1);
                                $nb = 0; // Initialize variable
                                if ($result1->num_rows > 0) {
                                    $row = $result1->fetch_assoc();
                                    $nb = $row['nb'];
                                }
                                $sql2 = "SELECT COUNT(*) as 'nb1' FROM `items_1` WHERE `ag_type`='T001' AND (`ag_status`='ST002' OR `ag_status`='ST005')";
                                $result2 = $conn->query($sql2);
                                $nb1 = 0; // Initialize variable
                                if ($result2->num_rows > 0) {
                                    $row = $result2->fetch_assoc();
                                    $nb1 = $row['nb1'];
                                }

                                ?>
                                <h5 class="card-title">คอมพิวเตอร์</h5>
                                <p class="card-text">จำนวนคงเหลือ <?php echo $nb; ?></p>
                                <p class="card-text">จำนวนที่ถูกยืม <?php echo $nb1; ?></p>
                                <p class="card-text">จำนวนทั้งหมด <?php echo ($nb1 + $nb); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <?php
                                $sql1 = "SELECT COUNT(*) as 'ph' FROM `items_1` WHERE `ag_type`='T002' AND `ag_status`='ST001'";
                                $result1 = $conn->query($sql1);
                                $ph = 0; // Initialize variable
                                if ($result1->num_rows > 0) {
                                    $row = $result1->fetch_assoc();
                                    $ph = $row['ph'];
                                }
                                $sql2 = "SELECT COUNT(*) as 'ph1' FROM `items_1` WHERE `ag_type`='T002' AND (`ag_status`='ST002' OR `ag_status`='ST005')";
                                $result2 = $conn->query($sql2);
                                $ph1 = 0; // Initialize variable
                                if ($result2->num_rows > 0) {
                                    $row = $result2->fetch_assoc();
                                    $ph1 = $row['ph1'];
                                }
                                ?>
                                <h5 class="card-title">หน้าจอ</h5>
                                <p class="card-text">จำนวนคงเหลือ <?php echo $ph; ?></p>
                                <p class="card-text">จำนวนที่ถูกยืม <?php echo $ph1; ?></p>
                                <p class="card-text">จำนวนทั้งหมด <?php echo ($ph1 + $ph); ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <?php
                                // Query to count available items
                                $sql1 = "SELECT COUNT(*) as 'sc' FROM `items_1` WHERE `ag_type`='T003' AND `ag_status`='ST001'";
                                $result1 = $conn->query($sql1);
                                $sc = 0; // Initialize variable
                                if ($result1->num_rows > 0) {
                                    $row = $result1->fetch_assoc();
                                    $sc = $row['sc'];
                                }

                                // Query to count borrowed items
                                $sql2 = "SELECT COUNT(*) as 'sc1' FROM `items_1` WHERE `ag_type`='T003' AND (`ag_status`='ST002' OR `ag_status`='ST005')";
                                $result2 = $conn->query($sql2);
                                $sc1 = 0; // Initialize variable
                                if ($result2->num_rows > 0) {
                                    $row = $result2->fetch_assoc();
                                    $sc1 = $row['sc1'];
                                }
                                ?>
                                <h5 class="card-title">โทรศัพท์</h5>
                                <p class="card-text">จำนวนคงเหลือ <?php echo $sc; ?></p>
                                <p class="card-text">จำนวนที่ถูกยืม <?php echo $sc1; ?></p>
                                <p class="card-text">จำนวนทั้งหมด <?php echo ($sc1 + $sc); ?></p>
                            </div>

                        </div>
                    </div>
                    <div class="col-md-12">
                        <table id="itmes_1" class="table display" style="width:100%;margin-top :20px;">
                            <thead>
                                <tr>
                                    <th>ลำดับ</th>
                                    <th>ชื่อประเภท</th>
                                    <th>รหัสอุปกรณ์</th>
                                    <th>สถานะ</th>
                                    <th>คนยืม</th>
                                    <th>หน่วยงาน</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT i.ag_id, it.type_name, i.ag_status, 
                                                (SELECT CONCAT(u.u_fname, ' ', u.u_lname) 
                                                 FROM borrowing b 
                                                 JOIN users u ON b.b_borower = u.u_id 
                                                 WHERE b.b_name = i.ag_id 
                                                 AND (i.ag_status = 'ST002' OR i.ag_status = 'ST005') LIMIT 1) AS borrower_name,
                                                (SELECT u.u_address 
                                                 FROM borrowing b 
                                                 JOIN users u ON b.b_borower = u.u_id 
                                                 WHERE b.b_name = i.ag_id 
                                                 AND (i.ag_status = 'ST002' OR i.ag_status = 'ST005') LIMIT 1) AS borrower_office 
                                         FROM items_1 i 
                                         JOIN item_type it ON i.ag_type = it.type_id";
                                $result = $conn->query($sql);
                                if ($result->num_rows > 0) {
                                    $i = 1; // Initialize counter for numbering
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $i . "</td>"; // Display the sequence number
                                        echo "<td>" . $row["type_name"] . "</td>";
                                        echo "<td>" . $row["ag_id"] . "</td>";
                                        echo "<td>" . $row["ag_status"] . "</td>";
                                        echo "<td>" . ($row["borrower_name"] ? $row["borrower_name"] : "") . "</td>";
                                        echo "<td>" . ($row["borrower_office"] ? $row["borrower_office"] : "") . "</td>";
                                        echo "<td><button class='return-button' onclick=\"returnItem('" . $row['ag_id'] . "')\">คืน</button></td>";
                                        echo "</tr>";
                                        $i++; // Increment the counter
                                    }
                                } else {
                                    echo "<tr><td colspan='7'>No data found</td></tr>";
                                }
                                $conn->close();
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.12.9/dist/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/i18n/defaults-*.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#itmes_1').DataTable();
        });

        function addborrow() {
            var form = document.getElementById("formdata");
            form.style.display = form.style.display === "none" ? "block" : "none";
        }

        function searchTable() {
            var input, filter, table, tr, td, i, j, txtValue;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("borrow_table");
            tr = table.getElementsByTagName("tr");
            for (i = 1; i < tr.length; i++) {
                tr[i].style.display = "none";
                td = tr[i].getElementsByTagName("td");
                for (j = 0; j < td.length; j++) {
                    if (td[j]) {
                        txtValue = td[j].textContent || td[j].innerText;
                        if (txtValue.toUpperCase().indexOf(filter) > -1) {
                            tr[i].style.display = "";
                            break;
                        }
                    }
                }
            }
        }
        let ch = 0;
        $(document).ready(function() {
            $('.s_select').selectpicker();
            $('#itemstb').dataTable();
        });
    </script>
</body>

</html>