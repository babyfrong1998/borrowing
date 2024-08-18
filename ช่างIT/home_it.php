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
    <link rel="stylesheet" href="../style.css?<?php echo time() ?>">
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

    #addItemForm {
        display: none;
        margin-top: 20px;
    }

    #addItemTypeForm,
    #addItemForm {
        display: none;
        margin-top: 20px;
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
                    <hr>
                    <button type="button" class="btn btn-info col-12" onclick="window.location.href='manage_items.php'">จัดการประเภทและอุปกรณ์</button>
                </div>
            </div>
            <div class="col-md-10">
                <button type="button" class="btn btn-success" onclick="toggleForm('addItemTypeForm')" style="margin-bottom: 2%;">เพิ่มประเภทอุปกรณ์</button>
                <!-- ปุ่มสำหรับเปิดฟอร์มเพิ่มอุปกรณ์ -->
                <button type="button" class="btn btn-success" onclick="toggleForm('addItemForm')" style="margin-bottom: 2%;">เพิ่มอุปกรณ์</button>
                <!-- ฟอร์มเพิ่มประเภทอุปกรณ์ -->
                <div id="addItemTypeForm">
                    <form action="add_item_type.php" method="POST">
                        <div class="form-group">
                            <label for="type_name">ชื่อประเภท</label>
                            <input type="text" class="form-control" id="type_name" name="type_name" required>
                        </div>
                        <div class="form-group">
                            <label for="type_description">อธิบายอุปกรณ์</label>
                            <textarea class="form-control" id="type_description" name="type_description" rows="3" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">บันทึกประเภทอุปกรณ์</button>
                    </form>
                </div>
                <!-- ฟอร์มเพิ่มอุปกรณ์ -->
                <div id="addItemForm">
                    <form action="add_item.php" method="POST">
                        <div class="form-group">
                            <label for="ag_type">ประเภท</label>
                            <select class="form-control" id="ag_type" name="ag_type" required>
                                <?php
                                // ดึงข้อมูลประเภทอุปกรณ์จากฐานข้อมูล
                                $sql = "SELECT * FROM item_type";
                                $result = $conn->query($sql);
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<option value='" . $row['type_id'] . "'>" . $row['type_name'] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="ag_id">รหัสอุปกรณ์</label>
                            <input type="text" class="form-control" id="ag_id" name="ag_id" required>
                        </div>
                        <button type="submit" class="btn btn-primary">บันทึกอุปกรณ์</button>
                    </form>
                </div>
                <br>
                <div class="col-md-12">
                </div>
                <br>
                <div class="row">
                    <?php
                    $sql = "SELECT * FROM item_type";
                    if ($res = mysqli_query($conn, $sql)) {
                        while ($row = mysqli_fetch_array($res)) {
                            $type_id = $row['type_id'];
                            $type_name = $row['type_name'];
                            $sql1 = "SELECT COUNT(*) as 'remaining' FROM `items_1` WHERE `ag_type`='$type_id' AND `ag_status`='ST001'";
                            $result1 = $conn->query($sql1);
                            $remaining = 0; // Initialize variable
                            if ($result1->num_rows > 0) {
                                $row1 = $result1->fetch_assoc();
                                $remaining = $row1['remaining'];
                            }
                            $sql2 = "SELECT COUNT(*) as 'borrowed' FROM `items_1` WHERE `ag_type`= '$type_id' AND (`ag_status`='ST002' OR `ag_status`='ST005')";
                            $result2 = $conn->query($sql2);
                            $borrowed = 0; // Initialize variable
                            if ($result2->num_rows > 0) {
                                $row2 = $result2->fetch_assoc();
                                $borrowed = $row2['borrowed'];
                            }
                            $total = $remaining + $borrowed;
                    ?>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($type_name); ?></h5>
                                        <p class="card-text">จำนวนคงเหลือ <?php echo $remaining; ?></p>
                                        <p class="card-text">จำนวนที่ถูกยืม <?php echo $borrowed; ?></p>
                                        <p class="card-text">จำนวนทั้งหมด <?php echo ($total); ?></p>
                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    }
                    ?>
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
                                $sql = "SELECT i.ag_id, it.type_name, i.ag_status,sl.st_name,  
                                (SELECT CONCAT(u.u_fname, ' ', u.u_lname) 
                                 FROM borrowing b 
                                 JOIN users u ON b.b_borower = u.u_id 
                                 WHERE b.b_name = i.ag_id 
                                 AND (i.ag_status = 'ST002' OR i.ag_status = 'ST005'OR i.ag_status = 'ST008' OR i.ag_status = 'ST007') LIMIT 1) AS borrower_name,
                                (SELECT u.u_address 
                                 FROM borrowing b 
                                 JOIN users u ON b.b_borower = u.u_id 
                                 WHERE b.b_name = i.ag_id 
                                 AND (i.ag_status = 'ST002' OR i.ag_status = 'ST005' OR i.ag_status = 'ST008' OR i.ag_status = 'ST007') LIMIT 1) AS borrower_office 
                                 FROM items_1 i 
                         JOIN item_type it ON i.ag_type = it.type_id 
                         JOIN statuslist sl ON i.ag_status = sl.st_id";
                                $result = $conn->query($sql);
                                if ($result->num_rows > 0) {
                                    $i = 1; // Initialize counter for numbering
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $i . "</td>"; // Display the sequence number
                                        echo "<td>" . $row["type_name"] . "</td>";
                                        echo "<td>" . $row["ag_id"] . "</td>";
                                        echo "<td>" . $row["st_name"] . "</td>";
                                        echo "<td>" . ($row["borrower_name"] ? $row["borrower_name"] : "") . "</td>";
                                        echo "<td>" . ($row["borrower_office"] ? $row["borrower_office"] : "") . "</td>";
                                        // Display the "คืน" button if status is ST002 or ST005
                                        if ($row["ag_status"] == 'ST007') {
                                            echo "<td>
                                                    <form method='POST' action='approve_status.php' onsubmit='return confirmApprove()'>
                                                        <input type='hidden' name='ag_id' value='" . $row["ag_id"] . "'>
                                                        <button type='submit' class='btn btn-success'>อนุมัติการยืม</button>
                                                    </form>
                                                  </td>";
                                        } else if ($row["ag_status"] == 'ST008') {
                                            echo "<td>
                                            <form method='POST' action='update_status.php' onsubmit='return confirmReturn()'>
                                                <input type='hidden' name='ag_id' value='" . $row["ag_id"] . "'>
                                                <input type='hidden' name='u_id' value='<?php echo $u_id; ?>'>
                                                <button type='submit' class='btn btn-primary'>ยืนยันการคืนอุปกรณ์</button>
                                            </form>
                                          </td>";
                                        } else {
                                            echo "<td></td>"; // No button displayed
                                        }

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

        function confirmReturn() {
            return confirm("คุณแน่ใจหรือไม่ว่าต้องการคืนอุปกรณ์นี้?");
        }

        function confirmApprove() {
            return confirm("คุณแน่ใจหรือไม่ว่าต้องการอนุมัติการยืมอุปกรณ์นี้?");
        }

        function toggleForm(formId) {
            var form = document.getElementById(formId);
            if (form.style.display === "none" || form.style.display === "") {
                form.style.display = "block";
            } else {
                form.style.display = "none";
            }
        }
    </script>
</body>

</html>