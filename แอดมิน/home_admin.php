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
    <title>ระบบแอดมิน</title>
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
    <h2 id="nav">ระบบแอดมิน</h2>
    <div class="container">
        <div class="row">
            <div class="col-md-2">
                <div class="row" id="tools">
                    <a href="../index.php">
                        <button type="button" id="menu" class="btn btn-info col-12">ออกจากระบบ</button>
                    </a>
                    <hr>
                    <button type="button" class="btn btn-info col-12" onclick="window.location.href='manage_user.php'">จัดการชื่อผู้ใช้งาน</button>
                    <hr>
                    <button type="button" class="btn btn-info col-12" onclick="window.location.href='Statistical.php'">สถิติการใช้อุปกรณ์ IT</button>
                </div>
            </div>
            <div class="col-md-10">
                <br>
                <div class="col-md-10">
                </div>
                <br>
                <div class="row">
                    <?php
                    $sql = "SELECT * FROM item_type";
                    if ($res = mysqli_query($conn, $sql)) {
                        $grand_remaining = 0; // ค่าเริ่มต้นของจำนวนคงเหลือทั้งหมด
                        $grand_borrowed = 0; // ค่าเริ่มต้นของจำนวนที่ถูกยืมทั้งหมด

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

                            // คำนวณผลรวมของแต่ละประเภท
                            $grand_remaining += $remaining;
                            $grand_borrowed += $borrowed;
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

                    // แสดงข้อมูลรวมทั้งหมดหลังจากแสดงแบบแยกประเภท
                    $grand_total = $grand_remaining + $grand_borrowed;
                    ?>
                    <div class="col-md-12">
                        <div class="card mt-4">
                            <div class="card-body">
                                <h5 class="card-title">ข้อมูลอุปกรณ์ทั้งหมด</h5>
                                <p class="card-text">จำนวนคงเหลือทั้งหมด <?php echo $grand_remaining; ?></p>
                                <p class="card-text">จำนวนที่ถูกยืมทั้งหมด <?php echo $grand_borrowed; ?></p>
                                <p class="card-text">จำนวนทั้งหมด <?php echo $grand_total; ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <table id="items_1" class="table display" style="width:100%;margin-top :20px;">
                            <thead>
                                <tr>
                                    <th>ลำดับ</th>
                                    <th>ชื่อผู้ยืม</th>
                                    <th>หน่วยงาน</th>
                                    <th>ประเภทอุปกรณ์</th>
                                    <th>จำนวน</th>
                                    <th>วันที่ยืม</th>
                                    <th>วันที่คืน</th>
                                    <th>สถานะ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT b.BruID, u.u_fname, u.u_lname, b.number, b.type_id, b.Brunum, b.BrudateB, b.BrudateRe, b.st_id, b.commen, s.st_name
                                            FROM borroww b 
                                            JOIN users u ON b.u_id = u.u_id
                                            JOIN statuslist s ON b.st_id = s.st_id";
                                $row_number = 1;
                                $result = mysqli_query($conn, $sql);
                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        $type_id = $row['type_id'];
                                        $st_name = $row['st_name']; // Use st_name instead of st_id
                                        $BrudateRe = date_create($row['BrudateRe']);
                                        $BrudateB = date_create($row['BrudateB']);
                                        echo "<tr onclick='toggleDetails($row_number)'>";
                                        echo "<td>" . $row_number . "</td>";
                                        echo "<td>" . $row['u_fname'] . " " . $row['u_lname'] . "</td>";
                                        echo "<td>" . $row['number'] . "</td>";
                                        echo "<td>" . $row['type_id'] . "</td>";
                                        echo "<td>" . $row['Brunum'] . "</td>";
                                        echo "<td>" . date_format($BrudateB, "d/m/") . (date_format($BrudateB, "Y") + 543) . "</td>";
                                        echo "<td>" . date_format($BrudateRe, "d/m/") . (date_format($BrudateRe, "Y") + 543) . "</td>";
                                        echo "<td>" . $st_name . "</td>"; // Display status name instead of id
                                        echo "</tr>";
                                        // เริ่มแสดงผลตามสถานะ
                                        echo "<tr id='details_$row_number' style='display: none;'>";
                                        echo "<td colspan='8'>";
                                        echo "<p><strong>หมายเหตุ:</strong> " . htmlspecialchars($row['commen']) . "</p>"; // แสดงข้อมูล commen
                                        if ($row['st_id'] == 'ST002' || $row['st_id'] == 'ST005') {
                                            // สถานะยืมใช้งานอยู่
                                            echo "<form action='update_borrow.php' method='POST'>";
                                            echo "<input type='hidden' name='BruID' value='" . $row['BruID'] . "'>";
                                            echo "<label for='ag_id_$row_number'>อุปกรณ์ที่ยืม</label>";
                                            $sql_items = "SELECT ag_id, ag_name FROM items_1 WHERE ag_type = '$type_id' AND (ag_status = 'ST002' OR ag_status = 'ST005') AND BruID = '" . $row['BruID'] . "'";
                                            $items_result = $conn->query($sql_items);
                                            if ($items_result->num_rows > 0) {
                                                while ($item = $items_result->fetch_assoc()) {
                                                    echo "<div class='form-group'>";
                                                    // แสดงชื่ออุปกรณ์โดยตรง
                                                    echo "<p>" . htmlspecialchars($item['ag_name']) . "</p>";
                                                    echo "<input type='hidden' name='ag_id[]' value='" . $item['ag_id'] . "'>";
                                                    echo "</div>";
                                                }
                                            }
                                            //echo "<button type='submit' class='btn btn-primary'>บันทึกการเปลี่ยนแปลง</button>";
                                            echo "</form>";
                                        } elseif ($row['st_id'] == 'ST009') {
                                            echo "<input type='hidden' name='BruID' value='" . htmlspecialchars($row['BruID']) . "'>";
                                            echo "<label for='ag_id_$row_number'>ประวัติอุปกรณ์ที่รับคืน</label>";
                                            // ดึงข้อมูลจากตาราง borrohistory ที่ BruID ตรงกัน และ JOIN กับ statuslist เพื่อดึง st_name
                                            $sql_history = "
                                                    SELECT bh.b_items, sl.st_name 
                                                    FROM borrohistory bh 
                                                    JOIN statuslist sl ON bh.b_status = sl.st_id 
                                                    WHERE bh.BruID = ?";
                                            $stmt_history = $conn->prepare($sql_history);
                                            $stmt_history->bind_param('s', $row['BruID']);
                                            $stmt_history->execute();
                                            $history_result = $stmt_history->get_result();
                                            if ($history_result->num_rows > 0) {
                                                while ($history = $history_result->fetch_assoc()) {
                                                    // ดึงชื่ออุปกรณ์จากตาราง items_1 โดยใช้ ag_id ที่ตรงกับ b_items
                                                    $sql_item_name = "SELECT ag_name FROM items_1 WHERE ag_id = ?";
                                                    $stmt_item_name = $conn->prepare($sql_item_name);
                                                    $stmt_item_name->bind_param('s', $history['b_items']);
                                                    $stmt_item_name->execute();
                                                    $item_result = $stmt_item_name->get_result();
                                                    if ($item_result->num_rows > 0) {
                                                        $item = $item_result->fetch_assoc();
                                                        echo "<div class='form-group' style='display: flex; align-items: center; justify-content: space-between;'>";
                                                        echo "<p style='margin-right: 10px;'>ชื่ออุปกรณ์: " . htmlspecialchars($item['ag_name']) . "</p>";
                                                        echo "<p style='margin-right: 10px;'>สถานะ: " . htmlspecialchars($history['st_name']) . "</p>";
                                                        echo "</div>";
                                                    }
                                                }
                                            } else {
                                                echo "<p>ไม่มีประวัติการคืนอุปกรณ์.</p>";
                                            }
                                            echo "</form>";
                                        }
                                        echo "</tr>";
                                        $row_number++;
                                    }
                                } else {
                                    echo "<tr><td colspan='8'>No records found</td></tr>";
                                }
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

        function toggleDetails(rowId) {
            var detailsRow = document.getElementById('details_' + rowId);
            if (detailsRow.style.display === 'none') {
                detailsRow.style.display = '';
            } else {
                detailsRow.style.display = 'none';
            }
        }
    </script>
</body>

</html>