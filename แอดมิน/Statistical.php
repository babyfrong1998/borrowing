<?php
session_start();
include "../connect.php";

// ตรวจสอบการเชื่อมต่อกับฐานข้อมูล
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// สถิติการยืม
$sqlBorrowingStats = "SELECT COUNT(*) AS total_borrowed FROM borroww";
$resultBorrowingStats = $conn->query($sqlBorrowingStats);
$borrowingStats = $resultBorrowingStats->fetch_assoc();
$totalBorrowed = $borrowingStats['total_borrowed'];

// สถิติการคืน
$sqlReturnStats = "SELECT COUNT(*) AS total_returned FROM borroww WHERE st_id = 'ST009'";
$resultReturnStats = $conn->query($sqlReturnStats);
$returnStats = $resultReturnStats->fetch_assoc();
$totalReturned = $returnStats['total_returned'];

// จำนวนอุปกรณ์
$sqlItemCount = "SELECT COUNT(*) AS total_items FROM items_1";
$resultItemCount = $conn->query($sqlItemCount);
$itemCount = $resultItemCount->fetch_assoc();
$totalItems = $itemCount['total_items'];

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IT Equipment Statistics</title>
    <link rel="stylesheet" href="../styles.css"> <!-- ใช้ไฟล์ CSS เดียวกับหน้าอื่น ๆ -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Include Chart.js -->
</head>
<body>
    <div class="container">
        <header>
            <h1>IT Equipment Statistics</h1>
            <a href="home_admin.php" class="btn btn-primary">Back to Admin Home</a>
        </header>

        <main>
            <section>
                <h2>Statistics Overview</h2>
                <!-- Canvas elements for Chart.js -->
                <div style="width: 80%; margin: 0 auto;">
                    <canvas id="statsChart"></canvas>
                </div>
            </section>
        </main>
    </div>

    <script>
        // Data for the chart
        const data = {
            labels: ['Total Borrowed', 'Total Returned', 'Total Items'],
            datasets: [{
                label: 'IT Equipment Statistics',
                data: [<?php echo $totalBorrowed; ?>, <?php echo $totalReturned; ?>, <?php echo $totalItems; ?>],
                backgroundColor: ['rgba(54, 162, 235, 0.2)', 'rgba(255, 99, 132, 0.2)', 'rgba(75, 192, 192, 0.2)'],
                borderColor: ['rgba(54, 162, 235, 1)', 'rgba(255, 99, 132, 1)', 'rgba(75, 192, 192, 1)'],
                borderWidth: 1
            }]
        };

        const config = {
            type: 'bar', // You can also use 'line', 'pie', etc.
            data: data,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.dataset.label + ': ' + tooltipItem.raw;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true
                    },
                    y: {
                        beginAtZero: true
                    }
                }
            }
        };

        // Render the chart
        const ctx = document.getElementById('statsChart').getContext('2d');
        new Chart(ctx, config);
    </script>
</body>
</html>
