<?php
session_start();
include "../connect.php";

if (!isset($_SESSION['username'])) {
    http_response_code(401);
    echo "Unauthorized";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $borrowId = $_POST['borrowId'];

    // ดึง b_name จากตาราง borrowing
    $sql = "SELECT b_name FROM borrowing WHERE b_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("i", $borrowId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $b_name = $row['b_name'];

            // อัปเดต ag_status ในตาราง items_1
            $updateSql1 = "UPDATE items_1 SET ag_status = 'ST008' WHERE ag_id = ?";
            $updateStmt1 = $conn->prepare($updateSql1);
            if ($updateStmt1) {
                $updateStmt1->bind_param("s", $b_name);
                if ($updateStmt1->execute()) {
                    
                    // อัปเดต b_status ในตาราง borrowing
                    $updateSql2 = "UPDATE borrowing SET b_status = 'ST008' WHERE b_id = ?";
                    $updateStmt2 = $conn->prepare($updateSql2);
                    if ($updateStmt2) {
                        $updateStmt2->bind_param("i", $borrowId);
                        if ($updateStmt2->execute()) {
                            http_response_code(200);
                            echo "Success";
                        } else {
                            http_response_code(500);
                            echo "Failed to update borrowing status: " . $conn->error;
                        }
                        $updateStmt2->close();
                    } else {
                        http_response_code(500);
                        echo "Failed to prepare update statement for borrowing: " . $conn->error;
                    }
                } else {
                    http_response_code(500);
                    echo "Failed to update item status: " . $conn->error;
                }
                $updateStmt1->close();
            } else {
                http_response_code(500);
                echo "Failed to prepare update statement for items: " . $conn->error;
            }
        } else {
            http_response_code(404);
            echo "Borrow record not found";
        }
        $stmt->close();
    } else {
        http_response_code(500);
        echo "Failed to prepare select statement: " . $conn->error;
    }
} else {
    http_response_code(405);
    echo "Method Not Allowed";
}
$conn->close();
?>
