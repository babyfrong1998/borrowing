<?php
include "../connect.php";
if (isset($_GET['ag_id'])) {
    $ag_id = $_GET['ag_id'];
    $sql = "DELETE FROM items_1 WHERE ag_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $ag_id);
    if ($stmt->execute()) {
        echo "Deleted successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
header("Location: manage_items.php");
