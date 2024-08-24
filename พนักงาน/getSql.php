<?php
include "../connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetching form data
    $u_id = $_POST['u_id'];
    $type_id = $_POST['item_type'];
    $item_quantity = $_POST['item_quantity'];
    $bordate = $_POST['bordate'];
    $returnDate = $_POST['returnDate'] ?? null; // Optional field
    $office_number = $_POST['office'];
    $st_id = $_POST['st_id'];
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    // Get the next BruID
    $sql = "SELECT IFNULL(MAX(BruID), 0) + 1 AS next_id FROM borroww";
    $result = mysqli_query($conn, $sql);
    $row = mysqli_fetch_assoc($result);
    $BruID = $row['next_id'];

    // Insert data into the borroww table
    $sql = "INSERT INTO borroww (BruID, u_id, type_id, Brunum, BrudateB, BrudateRe, number, st_id, commen) 
            VALUES ('$BruID', '$u_id', '$type_id', '$item_quantity', '$bordate', '$returnDate', '$office_number', '$st_id', '$comment')";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: home.php?success=1");
        exit();
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
    mysqli_close($conn);
  
}
?>
