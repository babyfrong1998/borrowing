<?php
include "../connect.php";
if (isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $sql = "SELECT o.number, o.agency FROM office o 
            JOIN users u ON u.u_address = o.number 
            WHERE u.u_id = '$user_id'";
    $result = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($result)) {
        $data = array(
            'number' => $row['number'],
            'agency' => $row['agency']
        );
        echo json_encode($data);
    }
}
?>
