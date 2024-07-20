<?php 
include 'connect.php';
if(isset($_POST['username']) && isset($_POST['password'])){
    $username = $_POST['username'];
    $password = $_POST['password'];
    $sql = "SELECT u_username, u_password from users where u_username = '$username' and u_password = '$password'";

    if($result = mysqli_query($conn, $sql)){
            header("location: home.php");
        }else{
            //header("location: login1.php?t=1");
        }
    }


?>