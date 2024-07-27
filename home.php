<?php
session_start();
include "connect.php";
$data = "";
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
</style>

<body>
    <h2 id="nav">ระบบบันทึกการยืม-คืน</h2>
    <div class="container">

        <div class="row">
            <div class="col-md-2">
                <div class="row" id="tools">
                    <a href="index.php">
                        <button type="button" id="menu" class="btn btn-info col-12">ออกจากระบบ</button>
                    </a>
                </div>
            </div>
            <div class="col-md-10">
                <button type="button" class="btn btn-success" onclick="addborrow()" style="margin-bottom: 2%;">ขอยืมครุภัณฑ์</button>
                <br>
                <form action="getSql.php" method="POST" class="row g-3" id="formdata" style="display: none">
                    <div class="row">
                        <label id="headline">ข้อมูลผู้ยืม</label>
                        <hr>
                        <div class="col-md-4">
                            <label for="inputname" class="form-label">ชื่อผู้ยืม</label>
                            <input type="code" class="form-control" onkeyup="submit_btn()" name="borname" id="inputname">
                        </div>
                        <div class="col-md-4" id="office_b">
                            <label for="inputtel" class="form-label">เบอร์หน่วยงาน </label>
                            <select name="office" class="selectpicker w-100" id="s_select" onchange="submit_btn()" data-live-search="true">
                                <option value="">----</option>
                                <?php
                                $office = "SELECT  * FROM `office`  ";
                                if ($rs = mysqli_query($conn, $office)) {
                                    while ($row = mysqli_fetch_assoc($rs)) {
                                ?>
                                        <option value="<?php echo  $row['number'] ?>"><?php echo $row['number'] . " " . $row['Agency'] ?></option>
                                <?php
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="bordate" class="form-label" style="margin-top: 1%;">วันที่ยืม</label>
                            <br>
                            <input type="datetime-local" class="form-control w-100" id="bordate" name="bordate">
                        </div>
                        <label id="headline">ข้อมูลครุภัณฑ์</label>
                        <hr>
                        <div class="col-md-6">
                            <label for="inputcategory" class="form-label">ประเภทครุภัณฑ์</label>
                            <select id="inputcategory" name="item_type" onchange="xml_item('itemselect',this.value),submit_btn()" class="selectpicker s_select w-100" data-live-search="true">
                                <option value="">----</option>
                                <?php
                                $sql = "SELECT DISTINCT `type_name` as type ,`type_id`  FROM item_type; ";
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
                            <select id="itemselect" name="itemselect" onchange="submit_btn()" class="selectpicker s_select w-100" data-live-search="true">
                            </select>
                        </div>
                        <div class="col-md-6">
                        </div>
                    </div>
                    <input type="submit" class="btn btn-light" name="submit" value="บันทึกการยืม" id="submid" disabled>
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
                                    <th>ประเภทอุปกรณ์</th>
                                    <th>วันที่ยืม</th>
                                    <th>วันที่คืน</th>
                                    <th>สถานะ</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT b_id, b_name, b_date, b_return, b_status FROM borrowing";
                                $result = $conn->query($sql);
                                if ($result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<tr>";
                                        echo "<td>" . $row["b_id"] . "</td>";
                                        echo "<td>" . $row["b_name"] . "</td>";
                                        echo "<td>" . $row["b_date"] . "</td>";
                                        echo "<td>" . $row["b_return"] . "</td>";
                                        echo "<td>" . $row["b_status"] . "</td>";
                                        echo "<td></td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td colspan='6'>No data found</td></tr>";
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

        function submit_btn() {
            var check_input = [];
            check_input[0] = document.getElementById('inputname').value;
            check_input[1] = document.getElementById('inputcategory').value;
            check_input[2] = document.getElementById('bordate').value;
            check_input[3] = document.getElementById('s_select').value;
            check_input[4] = document.getElementById('itemselect').value;
            var btn = document.getElementById('submid');
            for (var i = 0; i < check_input.length; i++) {
                if (check_input[i] == "") {
                    btn.setAttribute('class', 'btn btn-light');
                    btn.disabled = true;
                    break;
                } else {
                    btn.setAttribute('class', 'btn btn-success');
                    btn.disabled = false;
                }
            }
        }

        function setdata(checkbox, count) {
            var cb = document.getElementById(checkbox);
            var m_input = document.querySelectorAll('#modal_body' + count + ' input[type="text"]')[0];
            var getdata = document.querySelectorAll('#getdata' + count + ' label');
            var select_r = document.querySelectorAll('#modal_body' + count + ' select')[0];
            var btn = document.querySelectorAll('#exampleModal' + count + ' input[type="submit"]')[0];
            var op = select_r.getElementsByTagName('option');
            if (cb.checked == true) {
                m_input.value = getdata[0].innerHTML;
                btn.disabled = false;
                for (var i = 0; i < op.length; i++) {
                    if (op[i].value == getdata[1].innerHTML) {
                        op[i].selected = true;
                        $('#modal_body' + count + ' .selectpicker').selectpicker('val', getdata[1].innerHTML);
                    }
                }
                console.log(select_r.value);
                $('.s_select').selectpicker('refresh');
            } else {
                m_input.value = "";
                btn.disabled = true
            }
        }

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

        function checkinput(input, btn) {
            var btn = document.getElementById(btn);
            if (input.value != '') {
                btn.disabled = false
            } else {
                btn.disabled = true
            }
        }

        function adddata() {
            var formdata = document.getElementById('formdata');
            var data = formdata.getElementsByTagName('Input');
        }

        function xml_item(selectid, ref) {
            var select = document.getElementById(selectid);
            var xml = new XMLHttpRequest();
            select.innerHTML = "";
            xml.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    select.innerHTML = "";
                    select.innerHTML += this.responseText;
                    $('.s_select').selectpicker('refresh');
                }
            }
            xml.open("GET", "getSql.php?sql=" + ref);
            xml.send();
        }
    </script>
</body>

</html>