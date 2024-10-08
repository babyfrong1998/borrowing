<?php
session_start();
include "../connect.php";
// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}
// ดึงข้อมูลผู้ใช้งานทั้งหมด
$userQuery = "SELECT * FROM users";
$userResult = $conn->query($userQuery);
// ดึงข้อมูลจากตาราง office
$officeQuery = "SELECT * FROM office";
$officeResult = $conn->query($officeQuery);
// ดึงข้อมูลจากตาราง u_status
$statusQuery = "SELECT * FROM u_status";
$statusResult = $conn->query($statusQuery);
$statusOptions = [];
while ($statusRow = $statusResult->fetch_assoc()) {
    $statusOptions[$statusRow['u_status_id']] = $statusRow['u_status_name'];
}
?>
<style>
    h2 {
        padding-top: 20px;
        padding-bottom: 20px;
        text-align: center;
        width: 100%;
        background-color: turquoise;
        font-weight: bold;
    }

    .btn-back-home {
        display: inline-block;
        padding: 10px 20px;
        background-color: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s ease, box-shadow 0.3s ease;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin: 20px 0;
        font-size: 1em;
        min-width: 400px;
        box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
    }

    table th,
    table td {
        padding: 12px 15px;
        border: 1px solid #ddd;
    }

    table tbody tr:last-of-type {
        border-bottom: 2px solid #009879;
    }

    table tbody tr:hover {
        background-color: #0C71F6FF;
        cursor: pointer;
    }

    .form-control {
        padding: 5px;
        border: 1px solid #ddd;
        border-radius: 4px;
        width: 100%;
    }

    .btn {
        padding: 5px 10px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }

    .btn-success {
        background-color: #28a745;
        color: white;
    }

    .btn-danger {
        background-color: #dc3545;
        color: white;
    }
</style>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../styles.css">
    <script>
        function confirmUpdate() {
            return confirm('คุณแน่ใจหรือไม่ว่าต้องการทำการอัปเดตข้อมูลนี้?');
        }

        function validateForm(form) {
            const usernamePattern = /^[a-zA-Z0-9@_.]+$/;
            const passwordPattern = /^[a-zA-Z0-9]+$/;
            if (!usernamePattern.test(form.u_username.value)) {
                alert("Username can only contain letters, numbers, and the characters @, _, .");
                return false;
            }
            if (!passwordPattern.test(form.u_password.value)) {
                alert("Password can only contain letters and numbers.");
                return false;
            }
            return true;
        }

        function validateForm() {
            const username = document.querySelector('input[name="u_username"]').value;
            const password = document.querySelector('input[name="u_password"]').value;
            // ตรวจสอบความยาว Username
            if (username.length < 4) {
                alert("Username ต้องมีอย่างน้อย 4 ตัวอักษร");
                return false;
            }
            // ตรวจสอบความยาว Password
            if (password.length < 4) {
                alert("Password ต้องมีอย่างน้อย 4 ตัวอักษร");
                return false;
            }
            return true; // ส่งข้อมูลได้หากผ่านการตรวจสอบ
        }
    </script>
</head>
<body>
    <div class="container">
        <header>
            <h2>ระบบจัดการข้อมูลผู้ใช้งาน</h2>
            <a href="home_admin.php" class="btn-back-home">Back to Admin Home</a>
        </header>
        <div class="row">
            <main>
                <!-- แก้ไข/ลบผู้ใช้งาน -->
                <section>
                    <h2>จัดการและแก้ไขผู้ใช้งาน</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Username</th>
                                <th>Password</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $userResult->fetch_assoc()) { ?>
                                <tr>
                                    <form method="POST" action="update_user.php" onsubmit="return validateForm(this)">
                                        <td><input type="text" name="u_fname" value="<?php echo $row['u_fname']; ?>" class="form-control"></td>
                                        <td><input type="text" name="u_lname" value="<?php echo $row['u_lname']; ?>" class="form-control"></td>
                                        <td><input type="email" name="u_email" value="<?php echo $row['u_email']; ?>" class="form-control"></td>
                                        <td>
                                            <select name="u_address" class="form-control">
                                                <?php
                                                $officeResult->data_seek(0);
                                                while ($officeRow = $officeResult->fetch_assoc()) { ?>
                                                    <option value="<?php echo $officeRow['number']; ?>" <?php if ($row['u_address'] == $officeRow['number']) echo 'selected'; ?>>
                                                        <?php echo $officeRow['number'] . " - " . $officeRow['Agency']; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                        <!-- จำกัดการกรอก Username -->
                                        <td>
                                            <input type="text" name="u_username" value="<?php echo $row['u_username']; ?>"
                                                class="form-control"
                                                pattern="[a-zA-Z0-9@_.]+"
                                                title="Username can only contain letters (a-z, A-Z), numbers (0-9), and the characters @, _, .">
                                        </td>
                                        <!-- จำกัดการกรอก Password -->
                                        <td>
                                            <input type="text" name="u_password" value="<?php echo $row['u_password']; ?>"
                                                class="form-control"
                                                pattern="[a-zA-Z0-9]+"
                                                title="Password can only contain letters (a-z, A-Z) and numbers (0-9)">
                                        </td>
                                        <td>
                                            <select name="u_status_id" class="form-control">
                                                <?php foreach ($statusOptions as $statusId => $statusName) { ?>
                                                    <option value="<?php echo $statusId; ?>" <?php if ($row['u_status_id'] == $statusId) echo 'selected'; ?>>
                                                        <?php echo $statusName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="hidden" name="u_id" value="<?php echo $row['u_id']; ?>">
                                            <button type="submit" class="btn btn-success" onclick="return confirmUpdate()">Update</button>
                                            <a href="delete_user.php?u_id=<?php echo $row['u_id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                        </td>
                                    </form>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </section>
                <section>
                    <h2>เพิ่มผู้ใช้งาน</h2>
                    <!-- เพิ่มการเว้นขอบให้กับฟอร์ม -->
                    <form method="POST" action="add_user.php" style="padding: 20px; border: 1px solid #ddd; border-radius: 10px; max-width: 600px; margin: auto;" onsubmit="return validateForm()">
                        <!-- ชื่อและนามสกุลอยู่แถวเดียวกัน -->
                        <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                            <input type="text" name="u_fname" placeholder="First Name" required class="form-control" style="width: 50%;">
                            <input type="text" name="u_lname" placeholder="Last Name" required class="form-control" style="width: 50%;">
                        </div>
                        <!-- อีเมล (ช่องเดียว) -->
                        <div style="margin-bottom: 10px;">
                            <input type="email" name="u_email" placeholder="Email" required class="form-control" style="width: 100%;">
                        </div>
                        <!-- ที่อยู่ -->
                        <select name="u_address" required class="form-control" style="margin-bottom: 10px;">
                            <option value="" disabled selected>Select Address</option>
                            <?php
                            // รีเซ็ตตัวชี้ผลลัพธ์ของ office
                            $officeResult->data_seek(0);
                            while ($officeRow = $officeResult->fetch_assoc()) { ?>
                                <option value="<?php echo $officeRow['number']; ?>">
                                    <?php echo $officeRow['number'] . " - " . $officeRow['Agency']; ?>
                                </option>
                            <?php } ?>
                        </select>
                        <!-- Username และ Password อยู่แถวเดียวกัน -->
                        <div style="display: flex; gap: 10px; margin-bottom: 10px;">
                            <!-- Username: อย่างน้อย 4 ตัวอักษร และใช้ได้เฉพาะ (A-Z, a-z, 0-9, @, _, .) -->
                            <input type="text" name="u_username" placeholder="Username" required class="form-control" pattern="^[a-zA-Z0-9@_.]{4,}$" title="Username ต้องมีอย่างน้อย 4 ตัวอักษร และใช้ได้เฉพาะตัวอักษร (A-Z, a-z), ตัวเลข (0-9), และสัญลักษณ์ @, _, ." style="width: 50%;">
                            <!-- Password: อย่างน้อย 4 ตัวอักษร และใช้ได้เฉพาะ (A-Z, a-z, 0-9) -->
                            <input type="password" name="u_password" placeholder="Password" required class="form-control" pattern="^[a-zA-Z0-9]{4,}$" title="Password ต้องมีอย่างน้อย 4 ตัวอักษร และใช้ได้เฉพาะตัวอักษร (A-Z, a-z), ตัวเลข (0-9)" style="width: 50%;">
                        </div>
                        <!-- สถานะ -->
                        <select name="u_status_id" required class="form-control" style="margin-bottom: 10px;">
                            <?php foreach ($statusOptions as $statusId => $statusName) { ?>
                                <option value="<?php echo $statusId; ?>"><?php echo $statusName; ?></option>
                            <?php } ?>
                        </select>
                        <!-- ปุ่มเพิ่มผู้ใช้ -->
                        <button type="submit" class="btn btn-success">เพิ่มผู้ใช้</button>
                    </form>
                </section>
        </div>
        </main>
    </div>
</body>
</html>
<?php
$conn->close();
?>