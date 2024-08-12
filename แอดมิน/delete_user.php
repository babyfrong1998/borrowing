<?php
include "../connect.php";

if (isset($_GET['u_id'])) {
    $id = $_GET['u_id'];

    $sql = "DELETE FROM users WHERE u_id='$id'";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: manage_user.php");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
