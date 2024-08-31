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
                            <input type="text" class="form-control" id="type_name" name="type_name" maxlength="50" required>
                        </div>
                        <div class="form-group">
                            <label for="type_description">อธิบายอุปกรณ์</label>
                            <textarea class="form-control" id="type_description" name="type_description" maxlength="50" required></textarea>
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
                            <input type="text" class="form-control" id="ag_id" name="ag_id" maxlength="50" required>
                        </div>
                        <div class="form-group">
                            <label for="ag_name">ชื่ออุปกรณ์</label>
                            <input type="text" class="form-control" id="ag_name" name="ag_name" maxlength="20" required>
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
                    <div class="row">
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

                                            echo "<tr onclick='toggleDetails($row_number)'>";
                                            echo "<td>" . $row_number . "</td>";
                                            echo "<td>" . $row['u_fname'] . " " . $row['u_lname'] . "</td>";
                                            echo "<td>" . $row['number'] . "</td>";
                                            echo "<td>" . $row['type_id'] . "</td>";
                                            echo "<td>" . $row['Brunum'] . "</td>";
                                            echo "<td>" . $row['BrudateB'] . "</td>";
                                            echo "<td>" . $row['BrudateRe'] . "</td>";
                                            echo "<td>" . $st_name . "</td>"; // Display status name instead of id
                                            echo "</tr>";

                                            // เริ่มแสดงผลตามสถานะ
                                            echo "<tr id='details_$row_number' style='display: none;'>";
                                            echo "<td colspan='8'>";
                                            echo "<p><strong>หมายเหตุ:</strong> " . htmlspecialchars($row['commen']) . "</p>"; // แสดงข้อมูล commen

                                            if ($row['st_id'] == 'ST007') {
                                                // สถานะรอยืนยันการยืม
                                                echo "<form id='borrowForm' action='confirm_borrow.php' method='POST' onsubmit='return validateForm()'>";
                                                echo "<input type='hidden' name='BruID' value='" . $row['BruID'] . "'>";
                                                echo "<input type='hidden' name='BrudateRe' value='" . $row['BrudateRe'] . "'>"; // ส่งข้อมูลวันที่คืน

                                                echo "<label for='ag_id_$row_number'>เลือกอุปกรณ์</label>";

                                                // สร้างรายการตัวเลือกทั้งหมดเพียงครั้งเดียว
                                                $sql_items = "SELECT ag_id, ag_name FROM items_1 WHERE ag_type = '$type_id' AND ag_status = 'ST001'";
                                                $items_result = $conn->query($sql_items);
                                                $items_options = [];
                                                if ($items_result->num_rows > 0) {
                                                    while ($item = $items_result->fetch_assoc()) {
                                                        $items_options[] = "<option value='" . $item['ag_id'] . "'>" . $item['ag_name'] . "</option>";
                                                    }
                                                }

                                                // แสดง dropdown สำหรับแต่ละแถว
                                                for ($i = 0; $i < $row['Brunum']; $i++) {
                                                    echo "<div class='form-group'>";
                                                    echo "<select class='form-control ag-select' name='ag_id[]' required>";
                                                    echo implode('', $items_options);
                                                    echo "</select>";
                                                    echo "</div>";
                                                }

                                                echo "<button type='submit' class='btn btn-primary'>ยืนยันการยืม</button>";
                                                echo "</form>";
                                            } elseif ($row['st_id'] == 'ST002' || $row['st_id'] == 'ST005') {
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
                                            } elseif ($row['st_id'] == 'ST006' || $row['st_id'] == 'ST008') {
                                                echo "<form action='confirm_return.php' method='POST'>";
                                                echo "<input type='hidden' name='BruID' value='" . htmlspecialchars($row['BruID']) . "'>";
                                                echo "<label for='ag_id_$row_number'>อุปกรณ์ที่แจ้งคืน</label>";

                                                $sql_items = "SELECT ag_id, ag_name, ag_status FROM items_1 WHERE ag_type = '$type_id' AND (ag_status = 'ST008' OR ag_status = 'ST009'OR ag_status = 'ST006') AND BruID = '" . $row['BruID'] . "'";
                                                $items_result = $conn->query($sql_items);

                                                if ($items_result->num_rows > 0) {
                                                    while ($item = $items_result->fetch_assoc()) {
                                                        echo "<div class='form-group' style='display: flex; align-items: center; justify-content: space-between;'>";

                                                        // แสดงชื่ออุปกรณ์
                                                        echo "<p style='margin-right: 10px;'>" . htmlspecialchars($item['ag_name']) . "</p>";

                                                        // ตรวจสอบสถานะ ag_status
                                                        if ($item['ag_status'] == 'ST009') {
                                                            // ปุ่มแสดงสถานะคืนอุปกรณ์แล้ว และไม่สามารถกดได้
                                                            echo "<button type='button' class='btn btn-success' disabled>รับคืนอุปกรณ์แล้ว</button>";
                                                        } else {
                                                            // ปุ่มยืนยันคืนอุปกรณ์
                                                            echo "<button type='button' id='return-item-" . htmlspecialchars($item['ag_id']) . "' class='btn btn-warning' onclick='returnItem(\"" . htmlspecialchars($item['ag_id']) . "\", \"" . htmlspecialchars($row['BruID']) . "\")'>ยืนยันคืนอุปกรณ์นี้</button>";
                                                        }

                                                        echo "</div>";
                                                    }
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
                $('.borrow-row').on('click', function() {
                    var bid = $(this).data('bid');
                    $('.item-selection').removeClass('show-selection');
                    $('#selection-' + bid).addClass('show-selection');
                });
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

            function toggleDetails(rowId) {
                var detailsRow = document.getElementById('details_' + rowId);
                if (detailsRow.style.display === 'none') {
                    detailsRow.style.display = '';
                } else {
                    detailsRow.style.display = 'none';
                }
            }
            document.addEventListener('DOMContentLoaded', function() {
                const selects = document.querySelectorAll('.ag-select');

                selects.forEach((select, index) => {
                    select.addEventListener('change', function() {
                        // เก็บค่าอุปกรณ์ที่เลือกไว้ในแถวนี้
                        const selectedValues = [];
                        selects.forEach((sel) => {
                            if (sel.value) {
                                selectedValues.push(sel.value);
                            }
                        });

                        // กรองตัวเลือกใน select ของแถวถัดไป
                        selects.forEach((sel, idx) => {
                            if (idx > index) {
                                sel.querySelectorAll('option').forEach((option) => {
                                    if (selectedValues.includes(option.value)) {
                                        option.style.display = 'none';
                                    } else {
                                        option.style.display = '';
                                    }
                                });
                            }
                        });
                    });
                });
            });

            function validateForm() {
                var ag_selects = document.querySelectorAll('.ag-select');
                var selectedValues = [];
                for (var i = 0; i < ag_selects.length; i++) {
                    var value = ag_selects[i].value;
                    if (selectedValues.includes(value)) {
                        alert('คุณเลือกอุปกรณ์ซ้ำกัน กรุณาเลือกอุปกรณ์ที่ไม่ซ้ำกัน');
                        return false; // หยุดการส่งฟอร์ม
                    }
                    selectedValues.push(value);
                }
                return true; // อนุญาตให้ส่งฟอร์มได้
            }

            function returnItem(ag_id) {
                // สร้าง AJAX request เพื่อส่งข้อมูลไปยังเซิร์ฟเวอร์
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "confirm_return.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

                // เมื่อ request สำเร็จ
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        console.log(xhr.responseText);  // เพิ่มการตรวจสอบนี้
                        // เปลี่ยนปุ่มเป็นสถานะคืนอุปกรณ์แล้ว
                        var button = document.getElementById('return-item-' + ag_id);
                        button.innerText = 'คืนอุปกรณ์แล้ว';
                        button.classList.remove('btn-warning');
                        button.classList.add('btn-success');
                        button.disabled = true;
                    }
                };

                // ส่ง ag_id ไปที่เซิร์ฟเวอร์
                xhr.send("ag_id=" + ag_id + "&action=confirm_return");
            }
            
        </script>
</body>

</html>