<?php
include "../connect.php";
if (isset($_GET['type_id'])) {
    $type_id = $_GET['type_id'];
    $sql = "DELETE FROM item_type WHERE type_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $type_id);
    if ($stmt->execute()) {
        echo "Deleted successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
header("Location: manage_items.php");
