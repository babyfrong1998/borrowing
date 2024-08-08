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
    <title>ระบบยืมคืนครุภัณฑ์ IT</title>
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
        background-color: orange;
        color: white;
        border: none;
        padding: 5px 10px;
        cursor: pointer;
    }

    .return-button:hover {
        background-color: darkorange;
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
                        <div class="col-md-4">
                            <label for="bordate" class="form-label" style="margin-top: 1%;">วันที่ยืม</label>
                            <br>
                            <input type="datetime-local" class="form-control w-100" id="bordate" name="bordate">
                        </div>
                        <div class="col-md-4">
                            <label for="returnDate" class="form-label" style="margin-top: 1%;">กำหนดวันคืน</label>
                            <br>
                            <input type="datetime-local" class="form-control w-100" id="returnDate" name="returnDate">
                        </div>
                        <label id="headline">ข้อมูลครุภัณฑ์</label>
                        <hr>
                        <div class="col-md-6">
                            <label for="inputcategory" class="form-label">ประเภทครุภัณฑ์</label>
                            <select id="inputcategory" name="item_type" class="selectpicker s_select w-100" data-live-search="true" onchange="updateItemCodes(this.value)">
                                <option value="">----</option>
                                <?php
                                $sql = "SELECT DISTINCT `type_name` as type ,`type_id` FROM item_type;";
                                if ($result = mysqli_query($conn, $sql)) {
                                    while ($r = mysqli_fetch_assoc($result)) {
                                ?>
                                        <option value="<?php echo $r['type_id'] ?>"><?php echo $r['type'] ?></option>
                                <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6 mt-2">
                            <label for="inputcode" class="form-label">รหัสครุภัณฑ์</label>
                            <select id="itemselect" name="itemselect" class="selectpicker s_select w-100" data-live-search="true">
                            </select>
                        </div>
                        <div class="col-md-6"></div>
                    </div>
                    <input type="hidden" name="u_id" value="<?php echo $u_id; ?>">
                    <input type="submit" class="btn btn-light" name="submit" value="บันทึกการยืม" id="submid">
                </form>
                <div class="row">
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <?php
                                $sql = "SELECT COUNT(*) as 'NB' FROM `items_1` WHERE `ag_type`='T001'AND `ag_status`='ST001'";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()) {
                                    $NB = $row['NB'];
                                }
                                ?>
                                <h5 class="card-title">คอมพิวเตอร์</h5>
                                <p class="card-text">จำนวนคงเหลือ <?php echo $NB
                                                                    ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <?php
                                $sql = "SELECT COUNT(*) as 'ph' FROM `items_1` WHERE `ag_type`='T002'AND `ag_status`='ST001'";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()) {
                                    $ph = $row['ph'];
                                }
                                ?>
                                <h5 class="card-title">หน้าจอ</h5>
                                <p class="card-text">จำนวนคงเหลือ <?php echo $ph
                                                                    ?></p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-body">
                                <?php
                                $sql = "SELECT COUNT(*) as 'sc' FROM `items_1` WHERE `ag_type`='T003'AND `ag_status`='ST001'";
                                $result = $conn->query($sql);
                                while ($row = $result->fetch_assoc()) {
                                    $sc = $row['sc'];
                                }
                                ?>
                                <h5 class="card-title">โทรศัพท์</h5>
                                <p class="card-text">จำนวนคงเหลือ <?php echo $sc
                                                                    ?></p>
                            </div>
                        </div>
                    </div>
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
        WHERE b.b_borower = '$u_id'";


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

                                        if ($row["b_status"] == 'ST002' || $row["b_status"] == 'ST005') {
                                            echo "<td><button class='return-button' onclick=\"returnItem('" . $row['b_id'] . "')\">คืน</button></td>";
                                        } else {
                                            echo "<td></td>"; // Empty cell if condition is not met
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
            $('#borrow_table').DataTable();
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

        function returnItem(borrowId) {
            console.log('Borrow ID: ', borrowId);
            if (confirm('คุณต้องการคืนอุปกรณ์นี้หรือไม่?')) {
                const xhr = new XMLHttpRequest();
                xhr.open('POST', 'returnItem.php', true);
                xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                xhr.onload = function() {
                    console.log('Response status: ', this.status);
                    console.log('Response text: ', this.responseText);
                    if (this.status === 200) {
                        alert('การคืนสำเร็จ');
                        location.reload();
                    } else {
                        alert('การคืนล้มเหลว: ' + this.responseText);
                    }
                };
                xhr.send('borrowId=' + borrowId);
            }
        }
    </script>
</body>

</html>