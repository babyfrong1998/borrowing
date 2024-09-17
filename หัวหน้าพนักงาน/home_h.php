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
// ดึงข้อมูลเบอร์หน่วยงานจากฐานข้อมูล
$sql = "SELECT number, Agency FROM office WHERE number = '$address'";
$result = mysqli_query($conn, $sql);
$office_data = mysqli_fetch_assoc($result);
$office_number = $office_data['number'];
$office_agency = $office_data['Agency'];

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
    <title>ระบบยืมคืนอุปกรณ์ IT</title>
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

    .return-button {
        background-color: #FF5900;
        color: white;
        border: none;
        padding: 10px 20px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
        margin: 4px 2px;
        cursor: pointer;
        border-radius: 4px;
    }

    .extend-button {
        background-color: #4CAF50;
        color: white;
        border: none;
        padding: 10px 20px;
        text-align: center;
        text-decoration: none;
        display: inline-block;
        font-size: 16px;
        margin: 4px 2px;
        cursor: pointer;
        border-radius: 4px;
    }


    #extend-form-container {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        padding: 20px;
        background-color: white;
        border: 1px solid #ccc;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        z-index: 1000;
    }

    .alert-success {
        color: #155724;
        background-color: #d4edda;
        border-color: #c3e6cb;
        padding: 10px;
        margin-top: 20px;
        border-radius: 5px;
    }
</style>

<body>
    <h2 id="nav">ระบบบันทึกการยืม-คืน</h2>
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
                <button type="button" class="btn btn-success" onclick="addborrow()" style="margin-bottom: 2%;">ขอยืมอุปกรณ์ IT</button>
                <button type="button" class="btn btn-success" onclick="window.location.href='Statistical.php'">สถิติการใช้อุปกรณ์ IT</button>
                <br>
                <form action="getSql.php" method="POST" class="row g-3" id="formdata" style="display: none">
                    <div class="row">
                        <hr>
                        <label id="headline">ข้อมูลผู้ยืม</label>
                        <hr>
                        <div class="col-md-4">
                            <label for="inputname" class="form-label">ชื่อผู้ยืม</label>
                            <input type="text" class="form-control" name="borname" id="inputname" value="<?php echo $fname . ' ' . $lname; ?>" readonly>
                        </div>
                        <div class="col-md-4" id="office_b">
                            <label for="inputtel" class="form-label">เบอร์หน่วยงาน</label>
                            <input type="text" class="form-control" name="office" id="inputtel" value="<?php echo $office_number . ' ' . $office_agency; ?>" readonly>
                            <input type="hidden" name="office" value="<?php echo $office_number; ?>">
                        </div>
                        <hr>
                        <label id="headline">ข้อมูลอุปกรณ์</label>
                        <hr>
                        <div class="col-md-4">
                            <label for="inputcategory" class="form-label">ประเภทอุปกรณ์</label>
                            <select id="inputcategory" name="item_type" class="selectpicker s_select w-100" data-live-search="true">
                                <option value="">--เลือกประเภทอุปกรณ์--</option>
                                <?php
                                $sql = "SELECT DISTINCT `type_name`, `type_id` FROM item_type;";
                                if ($result = mysqli_query($conn, $sql)) {
                                    while ($r = mysqli_fetch_assoc($result)) {
                                ?>
                                        <option value="<?php echo $r['type_id'] ?>"><?php echo $r['type_name'] ?></option>
                                <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="inputQuantity" class="form-label">จำนวนเครื่อง</label>
                            <select id="inputQuantity" name="item_quantity" class="form-control" required>
                                <option value="">--เลือกจำนวนเครื่อง--</option>
                                <!-- Option จะถูกเติมโดย JavaScript -->
                            </select>
                        </div>
                        <hr>
                        <div class="col-md-12">
                            <label for="comment">หมายเหตุ</label>
                            <textarea class="form-control w-100" id="comment" name="comment" rows="4" maxlength="80" placeholder="ระบุข้อมูลการยืมเพิ่มเติม "></textarea>
                        </div>
                        <div class="col-md-4">
                            <label for="bordate" class="form-label" style="margin-top: 1%;">วันที่ยืม</label>
                            <br>
                            <input type="datetime-local" class="form-control w-100" id="bordate" name="bordate" min="<?php echo date('Y-m-d\TH:i'); ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="returnDate" class="form-label" style="margin-top: 1%;">กำหนดวันคืน</label>
                            <br>
                            <input type="datetime-local" class="form-control w-100" id="returnDate" name="returnDate">
                        </div>
                        <div class="col-md-6"></div>
                        <input type="hidden" name="st_id" value="ST007">
                    </div>
                    <input type="hidden" name="u_id" value="<?php echo $u_id; ?>">
                    <hr>
                    <input type="submit" class="extend-button" name="submit" value="บันทึกการยืม" id="submid">
                </form>
                <hr>
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
                    ?>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($type_name); ?></h5>
                                        <p class="card-text">จำนวนคงเหลือ <?php echo $remaining; ?></p>
                                        <p class="card-text">จำนวนที่ถูกยืม <?php echo $borrowed; ?></p>

                                    </div>
                                </div>
                            </div>
                    <?php
                        }
                    }
                    ?>
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
                JOIN statuslist s ON b.st_id = s.st_id
                WHERE b.u_id = '$u_id'";
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
                                        echo "<td>" . $st_name . "</td>";
                                        echo "</tr>";
                                        echo "<tr id='details_$row_number' style='display: none;'>";
                                        echo "<td colspan='8'>";
                                        echo "<p><strong>หมายเหตุ:</strong> " . htmlspecialchars($row['commen']) . "</p>";
                                        if ($row['st_id'] == 'ST002' || $row['st_id'] == 'ST005' || $row['st_id'] == 'ST008' || $row['st_id'] == 'ST006') {
                                            echo "<form id='return-all-form' action='updateAllStatus.php' method='POST' style='display:none;'>";
                                            echo "<input type='hidden' name='BruID' value='" . htmlspecialchars($row['BruID']) . "'>";
                                            echo "</form>";

                                            echo "<form action='updateStatus.php' method='POST'>";
                                            echo "<input type='hidden' name='BruID' value='" . htmlspecialchars($row['BruID']) . "'>";
                                            echo "<label for='ag_id_$row_number'>อุปกรณ์ที่ยืม</label>";

                                            $sql_items = "SELECT ag_id, ag_status FROM items_1 WHERE ag_type = '$type_id' AND BruID = '" . htmlspecialchars($row['BruID']) . "'";
                                            $items_result = $conn->query($sql_items);

                                            $status_count = 0;
                                            if ($items_result->num_rows > 0) {
                                                while ($item = $items_result->fetch_assoc()) {
                                                    if ($item['ag_status'] == 'ST002' || $item['ag_status'] == 'ST005') {
                                                        $status_count++;
                                                    }
                                                }

                                                $items_result->data_seek(0);

                                                while ($item = $items_result->fetch_assoc()) {
                                                    echo "<div class='form-group' style='display: flex; align-items: center;'>";
                                                    echo "<p style='flex: 1; margin: 0;'>" . htmlspecialchars($item['ag_id']) . "</p>";
                                                    echo "<input type='hidden' name='ag_id[]' value='" . htmlspecialchars($item['ag_id']) . "'>";

                                                    if ($item['ag_status'] == 'ST002' || $item['ag_status'] == 'ST005') {
                                                        if ($status_count == 1) {
                                                            echo "<button type='button' id='return-all' class='btn btn-warning' onclick='returnAllItems(\"" . htmlspecialchars($row['BruID']) . "\")'>แจ้งคืนทั้งหมด</button>";
                                                        } else {
                                                            echo "<button type='button' id='return-item-" . htmlspecialchars($item['ag_id']) . "' class='btn btn-warning' onclick='returnItem(\"" . htmlspecialchars($item['ag_id']) . "\", \"" . htmlspecialchars($row['BruID']) . "\")'>แจ้งคืนอุปกรณ์นี้</button>";
                                                        }
                                                    } else {
                                                        echo "<button type='button' id='return-item-" . htmlspecialchars($item['ag_id']) . "' class='btn btn-secondary' disabled>อุปกรณ์นี้แจ้งคืนแล้ว</button>";
                                                    }
                                                    echo "</div>";
                                                }
                                            } else {
                                                echo "<p>ไม่มีอุปกรณ์ที่ยืมอยู่ในสถานะปัจจุบัน</p>";
                                            }

                                            if ($row['st_id'] == 'ST002' || $row['st_id'] == 'ST005' || $row['st_id'] == 'ST006') {
                                                echo "<button type='button' id='return-all' class='btn btn-primary' onclick='returnAllItems(\"" . htmlspecialchars($row['BruID']) . "\")'>แจ้งคืนทั้งหมด</button>";
                                            } else if ($row['st_id'] == 'ST008') {
                                                echo "<button type='submit' class='btn btn-primary' name='return_all' disabled>แจ้งคืนทั้งหมด</button>";
                                            }

                                            echo "</form>";
                                        }


                                        echo "</td>";
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#borrow_table').DataTable();
            // กำหนดวันที่ปัจจุบันให้กับ input ที่มี id 'bordate'
            var today = new Date().toISOString().split('T')[0];
            document.getElementById('bordate').setAttribute('min', today);
        });

        function addborrow() {
            var form = document.getElementById("formdata");
            form.style.display = form.style.display === "none" ? "block" : "none";
        }

        $(document).ready(function() {
            var today = new Date().toISOString().slice(0, 16);
            document.getElementById("bordate").min = today;
            document.getElementById("returnDate").min = today;
            $('#bordate').on('change', function() {
                var selectedBorrowDate = new Date(this.value).toISOString().slice(0, 16);
                document.getElementById("returnDate").min = selectedBorrowDate;
            });
        });

        function extendBorrow(borrowId) {
            document.getElementById('borrowId').value = borrowId;
            $('#extendBorrowingModal').modal('show');
        }

        function extendBorrowing(borrowId, borrowDate) {
            // แสดงฟอร์มให้ผู้ใช้เลือกวันที่คืน
            var formContainer = document.getElementById('extend-form-container');
            formContainer.style.display = 'block';
            // เก็บ borrowId และ borrowDate ไว้ใน form เพื่อส่งไปด้วย
            var form = document.getElementById('extend-form');
            form.dataset.borrowId = borrowId;
            form.dataset.borrowDate = borrowDate;
            // ตั้งค่าขอบเขตวันที่ให้เลือกได้
            var returnDateInput = document.getElementById('return-date');
            var today = new Date();
            var minDate = today.toISOString().split('T')[0]; // วันต้องไม่ต่ำกว่าวันนี้
            // วันที่ยืมจะต้องเป็นวันที่ขั้นต่ำ
            var minReturnDate = new Date(borrowDate);
            minReturnDate.setDate(minReturnDate.getDate() + 1); // ต้องมากกว่าหนึ่งวันจากวันที่ยืม
            minReturnDate = minReturnDate.toISOString().split('T')[0];
            // เลือกวันที่ที่มากกว่าทั้งสองวัน
            var finalMinDate = (minDate > minReturnDate) ? minDate : minReturnDate;
            returnDateInput.setAttribute('min', finalMinDate);
        }
        $(document).ready(function() {
            $('#inputcategory').on('change', function() {
                var type_id = $(this).val();
                if (type_id) {
                    $.ajax({
                        url: 'get_quantity.php', // ไฟล์ PHP สำหรับดึงข้อมูล
                        type: 'POST',
                        data: {
                            type_id: type_id
                        },
                        success: function(response) {
                            $('#inputQuantity').html(response);
                        }
                    });
                } else {
                    $('#inputQuantity').html('<option value="">--เลือกจำนวนเครื่อง--</option>');
                }
            });
        });
        document.addEventListener("DOMContentLoaded", function() {
            const urlParams = new URLSearchParams(window.location.search);
            const success = urlParams.get('success');
            if (success === '1') {
                alert('เพิ่มข้อมูลเรียบร้อยแล้ว');
            }
        });

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
    

        function returnItem(ag_id, BruID) {
            if (confirm("คุณต้องการแจ้งคืนอุปกรณ์นี้หรือไม่?")) {
                // ทำการส่ง AJAX request ไปยังสคริปต์ PHP เพื่ออัพเดทสถานะ
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "updateStatus.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        alert("อัพเดทสถานะสำเร็จแล้ว");
                        location.reload(); // รีโหลดหน้าเพื่อแสดงผลการอัปเดต
                    }
                };
                xhr.send("ag_id=" + ag_id + "&BruID=" + BruID);
            }
        }

        function returnAllItems(BruID) {
            if (confirm("คุณต้องการแจ้งคืนอุปกรณ์ทั้งหมดหรือไม่?")) {
                // ทำการส่ง AJAX request ไปยังสคริปต์ PHP เพื่ออัพเดทสถานะ
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "updateAllStatus.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState == 4 && xhr.status == 200) {
                        alert("อัพเดทสถานะสำเร็จแล้ว");
                        location.reload(); // รีโหลดหน้าเพื่อแสดงผลการอัปเดต
                    }
                };
                xhr.send("BruID=" + BruID);
            }
        }
    </script>

</body>

</html>