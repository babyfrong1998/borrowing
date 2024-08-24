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

                        <div class="col-md-4">
                            <label for="comment">หมายเหตุ</label>
                            <input type="text" class="form-control w-100" id="comment" name="comment" maxlength="50" placeholder="ระบุข้อมูลการยืมเพิ่มเติม">
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
                <?php
                // ส่วนนี้อยู่ในตำแหน่งที่คุณต้องการให้แสดงกล่องข้อความ
                if (isset($_GET['success']) && $_GET['success'] == 1) {
                    echo  '<div id="alertBox" class="alert alert-success" role="alert">การยืมสำเร็จ!</div>';
                }
                ?>

                <!-- HTML Form for Selecting Return Date -->
                <div id="extend-form-container" style="display: none;">
                    <form id="extend-form">
                        <label for="return-date">เลือกวันที่คืน:</label>
                        <input type="date" id="return-date" name="return-date" required>
                        <button type="button" onclick="submitReturnDate()">ยืนยัน</button>
                        <button type="button" onclick="cancelForm()">ยกเลิก</button>
                    </form>
                </div>
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
                        <table id="borrow_table" class="table display" style="width:100%;margin-top :20px;">
                            <thead>
                                <tr>
                                    <th>ลำดับ</th>
                                    <th>ชื่อประเภท</th>
                                    <th>รหัสอุปกรณ์</th>
                                    <th>วันที่ยืม</th>
                                    <th>วันที่คืน</th>
                                    <th>สถานะ</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Filter records by u_id
                                $sql = "SELECT b.b_id, b.b_name, b.b_date, b.b_return, b.b_status, sl.st_name, it.type_name 
                                FROM borrowing b 
                                JOIN items_1 i ON b.b_name = i.ag_id 
                                JOIN item_type it ON i.ag_type = it.type_id 
                                JOIN statuslist sl ON b.b_status = sl.st_id
                                WHERE b.b_borower = '$u_id'
                                ORDER BY 
                                CASE 
                                WHEN b.b_status = 'ST002' THEN 1
                                WHEN b.b_status = 'ST005' THEN 2
                                WHEN b.b_status = 'ST008' THEN 3
                                WHEN b.b_status = 'ST007' THEN 4
                                ELSE 5
                                END, b.b_status";
                                $result = $conn->query($sql);
                                if ($result->num_rows > 0) {
                                    $i = 1; // Initialize counter for numbering
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $i . "</td>"; // Display the sequence number
                                        echo "<td>" . $row["type_name"] . "</td>";
                                        echo "<td>" . $row["b_name"] . "</td>";
                                        echo "<td>" . $row["b_date"] . "</td>";
                                        echo "<td>" . $row["b_return"] . "</td>";
                                        echo "<td>" . $row["st_name"] . "</td>";
                                        // Initialize empty cell content
                                        $actionCellContent = "";
                                        // Check conditions to set action buttons
                                        if ($row["b_status"] == 'ST002' || $row["b_status"] == 'ST005') {
                                            $actionCellContent = "<button class='return-button' onclick=\"returnItem('" . $row['b_id'] . "')\">แจ้งคืน</button>";
                                        }
                                        if ($row["b_status"] == 'ST005') {
                                            $actionCellContent .= "<button class='extend-button' onclick=\"extendBorrowing('" . $row['b_id'] . "')\">ยืมต่อ</button>";
                                        }
                                        echo "<td>" . $actionCellContent . "</td>"; // Output the action cell content
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

        function addborrow() {
            var btn = document.getElementById('btnaddbor');
            var form = document.getElementById('formdata');
            if (ch == 0) {
                form.style.display = 'block';
                ch = 1;
            } else {
                form.style.display = 'none';
                ch = 0;
            }
        }

        function adddata() {
            var formdata = document.getElementById('formdata');
            var data = formdata.getElementsByTagName('Input');
        }

        function updateItemCodes(typeId) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'getItemCodes.php?type_id=' + typeId, true);
            xhr.onload = function() {
                if (this.status === 200) {
                    const agIds = JSON.parse(this.responseText);
                    let options = '<option value="">-- เลือกรหัสครุภัณฑ์ --</option>';
                    agIds.forEach(function(ag) {
                        options += `<option value="${ag.ag_id}">${ag.ag_id}</option>`;
                    });
                    document.getElementById('itemselect').innerHTML = options;
                    $('.selectpicker').selectpicker('refresh');
                }
            };
            xhr.send();
        }
        document.getElementById('formdata').addEventListener('submit', function(event) {
            var bordate = document.getElementById('bordate').value;
            var itemselect = document.getElementById('itemselect').value;
            // ตรวจสอบข้อมูลในช่อง 'วันที่ยืม' และ 'รหัสครุภัณฑ์'
            if (!bordate || !itemselect) {
                alert('กรุณากรอกข้อมูลให้ครบถ้วน');
                event.preventDefault(); // ป้องกันการส่งฟอร์ม
            }
        });
        document.getElementById('formdata').addEventListener('submit', function(event) {
            var bordate = document.getElementById('bordate').value;
            var returnDate = document.getElementById('returnDate').value;
            var itemselect = document.getElementById('itemselect').value;
            // ตรวจสอบไม่ให้ returnDate น้อยกว่า bordate
            if (new Date(returnDate) < new Date(bordate)) {
                alert('วันกำหนดคืนต้องไม่ต่ำกว่าวันที่ยืม');
                event.preventDefault(); // หยุดการส่งฟอร์ม
                return false;
            }
            // ตรวจสอบว่ามีการเลือกอุปกรณ์
            if (itemselect === '') {
                alert('กรุณาเลือกรหัสครุภัณฑ์');
                event.preventDefault();
                return false;
            }
        });
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

        function submitReturnDate() {
            var form = document.getElementById('extend-form');
            var borrowId = form.dataset.borrowId;
            var returnDate = document.getElementById('return-date').value;
            if (returnDate) {
                var xhr = new XMLHttpRequest();
                xhr.open('POST', 'update_return_date.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        alert(xhr.responseText);
                        location.reload(); // รีเฟรชหน้าเพื่อแสดงข้อมูลที่อัพเดต
                    } else {
                        alert('เกิดข้อผิดพลาดในการอัพเดตข้อมูล');
                    }
                };
                xhr.send('b_id=' + encodeURIComponent(borrowId) + '&return_date=' + encodeURIComponent(returnDate));
            } else {
                alert('กรุณาเลือกวันที่คืน');
            }
        }

        function cancelForm() {
            var formContainer = document.getElementById('extend-form-container');
            formContainer.style.display = 'none';
        }
        // กำหนดเวลาให้ข้อความหายไปหลังจาก 3 วินาที (3000 มิลลิวินาที)
        setTimeout(function() {
            var alertBox = document.getElementById('alertBox');
            if (alertBox) {
                alertBox.style.display = 'none';
            }
        }, 3000);
        $(document).ready(function() {
        $('#inputcategory').on('change', function() {
            var type_id = $(this).val();
            if (type_id) {
                $.ajax({
                    url: 'get_quantity.php', // ไฟล์ PHP สำหรับดึงข้อมูล
                    type: 'POST',
                    data: { type_id: type_id },
                    success: function(response) {
                        $('#inputQuantity').html(response);
                    }
                });
            } else {
                $('#inputQuantity').html('<option value="">--เลือกจำนวนเครื่อง--</option>');
            }
        });
    });
    </script>

</body>

</html>