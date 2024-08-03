<?php
include "../connect.php";

if (isset($_GET['type_id'])) {
    $type_id = $_GET['type_id'];
    $sql = "SELECT ag_id FROM items_1 WHERE ag_type = '$type_id'";
    $result = mysqli_query($conn, $sql);
    
    $agIds = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $agIds[] = $row;
    }
    echo json_encode($agIds);
}
?>
