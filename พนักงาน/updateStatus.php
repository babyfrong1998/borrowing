<?php
include "../connect.php";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ag_id = $_POST['ag_id'];
    $BruID = $_POST['BruID'];

    // อัพเดทสถานะ ag_status เป็น ST008 ในตาราง items_1
    $sql = "UPDATE items_1 SET ag_status = 'ST008' WHERE ag_id = ? AND BruID = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ss", $ag_id, $BruID);

        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "error: " . $conn->error;
    }
    
    $conn->close();
}
?>