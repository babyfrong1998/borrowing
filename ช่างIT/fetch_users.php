<?php
include "../connect.php";
if (isset($_POST['query'])) {
    $query = $_POST['query'];
    $sql = "SELECT u_id, CONCAT(u_fname, ' ', u_lname) AS fullname FROM users WHERE CONCAT(u_fname, ' ', u_lname) LIKE '%$query%'";
    $result = mysqli_query($conn, $sql);
    $output = '<ul class="list-group">';
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $output .= '<li class="list-group-item" data-id="' . $row['u_id'] . '">' . $row['fullname'] . '</li>';
        }
    } else {
        $output .= '<li class="list-group-item">ไม่พบข้อมูล</li>';
    }
    $output .= '</ul>';
    echo $output;
}
?>