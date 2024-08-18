<?php
include "../connect.php";

$type_id = $_GET['type_id'];

// Query to select items with ag_status = 'ST001', 'ST002', 'ST005' and order them accordingly
$sql = "
    SELECT ag_id 
    FROM items_1 
    WHERE ag_type = '$type_id' 
      AND ag_status IN ('ST001', 'ST002', 'ST005') 
    ORDER BY 
      CASE 
        WHEN ag_status = 'ST001' THEN 1 
        WHEN ag_status = 'ST002' THEN 2 
        WHEN ag_status = 'ST005' THEN 3 
        ELSE 4 
      END
";

$result = mysqli_query($conn, $sql);

$items = array();
while ($row = mysqli_fetch_assoc($result)) {
    $items[] = $row;
}

echo json_encode($items);
?>
