<?php
include "../connect.php";

if (isset($_POST['ag_id']) && isset($_POST['ag_type']) && isset($_POST['ag_status'])) {
    $ag_id = $_POST['ag_id'];
    $ag_type = $_POST['ag_type'];
    $ag_status = $_POST['ag_status'];

    $sql = "UPDATE items_1 SET ag_type = ?, ag_status = ? WHERE ag_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $ag_type, $ag_status, $ag_id);

    if ($stmt->execute()) {
        echo "Updated successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
}

$conn->close();
header("Location: manage_items.php");
?>
