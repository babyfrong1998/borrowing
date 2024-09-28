<?php
include "../connect.php";
if (isset($_POST['type_id']) && isset($_POST['type_name']) && isset($_POST['type_description'])) {
    $type_id = $_POST['type_id'];
    $type_name = $_POST['type_name'];
    $type_description = $_POST['type_description'];
    $sql = "UPDATE item_type SET type_name = ?, type_description = ? WHERE type_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $type_name, $type_description, $type_id);
    if ($stmt->execute()) {
        echo "Updated successfully.";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
$conn->close();
header("Location: manage_items.php");
