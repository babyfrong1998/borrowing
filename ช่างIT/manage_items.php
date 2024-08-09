<?php
session_start();
include "../connect.php";

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// ดึงข้อมูลประเภทอุปกรณ์ทั้งหมด
$typeQuery = "SELECT * FROM item_type";
$typeResult = $conn->query($typeQuery);

// ดึงข้อมูลอุปกรณ์ทั้งหมด
$itemQuery = "SELECT * FROM items_1";
$itemResult = $conn->query($itemQuery);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Items</title>
    <link rel="stylesheet" href="styles.css"> <!-- ใช้ไฟล์ CSS เดียวกับ home_it.php -->
</head>

<body>
    <div class="container">
        <header>
            <h1>Manage Equipment Types and Items</h1>
            <a href="home_it.php" class="btn btn-primary">Back to Home</a>
        </header>

        <main>
            <!-- แก้ไขประเภทอุปกรณ์ -->
            <section class="section">
                <h2>Edit/Delete Equipment Type</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Type ID</th>
                            <th>Type Name</th>
                            <th>Type Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $typeResult->fetch_assoc()) { ?>
                            <tr>
                                <form method="POST" action="update_type.php">
                                    <td><?php echo htmlspecialchars($row['type_id']); ?></td>
                                    <td><input type="text" name="type_name" value="<?php echo htmlspecialchars($row['type_name']); ?>" class="form-control"></td>
                                    <td><input type="text" name="type_description" value="<?php echo htmlspecialchars($row['type_description']); ?>" class="form-control"></td>
                                    <td>
                                        <input type="hidden" name="type_id" value="<?php echo htmlspecialchars($row['type_id']); ?>">
                                        <button type="submit" class="btn btn-success">Update</button>
                                        <a href="delete_type.php?type_id=<?php echo urlencode($row['type_id']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                    </td>
                                </form>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </section>

            <!-- แก้ไขอุปกรณ์ -->
            <section class="section">
                <h2>Edit/Delete Equipment</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Equipment ID</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $itemResult->fetch_assoc()) { ?>
                            <tr>
                                <form method="POST" action="update_item.php">
                                    <td><?php echo htmlspecialchars($row['ag_id']); ?></td>
                                    <td><input type="text" name="ag_type" value="<?php echo htmlspecialchars($row['ag_type']); ?>" class="form-control"></td>
                                    <td><input type="text" name="ag_status" value="<?php echo htmlspecialchars($row['ag_status']); ?>" class="form-control"></td>
                                    <td>
                                        <input type="hidden" name="ag_id" value="<?php echo htmlspecialchars($row['ag_id']); ?>">
                                        <button type="submit" class="btn btn-success">Update</button>
                                        <a href="delete_item.php?ag_id=<?php echo urlencode($row['ag_id']); ?>" class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
                                    </td>
                                </form>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </section>
        </main>
    </div>
</body>

</html>

<?php
$conn->close();
?>
