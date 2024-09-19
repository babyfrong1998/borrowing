<?php
    $host="localhost";
    $username="root";
    $password ="";
    $database = "items";

    $conn = mysqli_connect($host,$username,$password,$database) or die("Error : ".mysqli_error($con));
    function mysqlFetch($conn,$sql){
        $data = [];
        if ($rs = mysqli_query($conn, $sql)) {
            while ($r = mysqli_fetch_assoc($rs)) {
                $data[] = $r;
            }
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }
        return $data;
    }
?>