<?php
session_start();
include "../connect.php";

// ตรวจสอบการเชื่อมต่อกับฐานข้อมูล
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
// ดึงข้อมูลประเภทของอุปกรณ์และจำนวน
$sqlTypeStats = "SELECT it.type_name, COUNT(i.ag_type) AS type_count 
                 FROM items_1 i 
                 JOIN item_type it ON i.ag_type = it.type_id 
                 GROUP BY it.type_name";
$resultTypeStats = $conn->query($sqlTypeStats);
$types = [];
$typeCounts = [];
while ($row = $resultTypeStats->fetch_assoc()) {
    $types[] = $row['type_name'];
    $typeCounts[] = $row['type_count'];
}
// ดึงข้อมูลรายการยืมจากหน่วย โดยใช้ตาราง office และ borroww
$sqlBorrowFromAgency = "
SELECT o.Agency, o.User, COUNT(b.number) AS borrow_count 
FROM office o
LEFT JOIN borroww b ON o.number = b.number 
GROUP BY o.Agency, o.User
HAVING borrow_count > 0
";
$resultBorrowFromAgency = $conn->query($sqlBorrowFromAgency);
$agencies = [];
$borrowCounts = [];
while ($row = $resultBorrowFromAgency->fetch_assoc()) {
    $agencies[] = $row['Agency'] . " (" . $row['User'] . ")"; // รวมชื่อหน่วยกับผู้ใช้
    $borrowCounts[] = $row['borrow_count'] ? $row['borrow_count'] : 0; // แสดง 0 หากไม่มีการยืม
}
// สร้างอาร์เรย์สีเพื่อใช้กับหน่วยงานแต่ละหน่วย
$colors = [];
for ($i = 0; $i < count($agencies); $i++) {
    $colors[] = sprintf('rgba(%d, %d, %d, 0.2)', rand(0, 255), rand(0, 255), rand(0, 255));
}
$borderColors = [];
for ($i = 0; $i < count($agencies); $i++) {
    $borderColors[] = sprintf('rgba(%d, %d, %d, 1)', rand(0, 255), rand(0, 255), rand(0, 255));
}
// สถิติการยืมและคืนตามเดือน
$sqlStatsByMonth = "
    SELECT 
        DATE_FORMAT(b_date, '%Y-%m') AS month,
        SUM(CASE WHEN b_status IN ('ST005', 'ST002') THEN 1 ELSE 0 END) AS borrowed,
        SUM(CASE WHEN b_status = 'ST009' THEN 1 ELSE 0 END) AS returned
    FROM borrohistory
    GROUP BY DATE_FORMAT(b_date, '%Y-%m')
    ORDER BY month
";
$resultStatsByMonth = $conn->query($sqlStatsByMonth);
$months = [];
$borrowedCounts = [];
$returnedCounts = [];
while ($row = $resultStatsByMonth->fetch_assoc()) {
    $months[] = $row['month'];
    $borrowedCounts[] = $row['borrowed'];
    $returnedCounts[] = $row['returned'];
}
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
    <style>
        /* แบ่งส่วนเว็บเพจ */
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .container {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .top-section {
            display: flex;
            flex: 1;
            padding: 20px;
            background-color: #f0f0f0;
        }

        .bottom-section {
            flex: 1;
            padding: 20px;
            background-color: #fff;
        }

        .left-top,
        .right-top {
            flex: 1;
            margin: 10px;
        }

        .left-top {
            background-color: #e0f7fa;
            padding: 20px;
        }

        .right-top {
            background-color: #ffe0b2;
            padding: 20px;
        }

        h2 {
            padding-top: 20px;
            padding-bottom: 20px;
            text-align: center;
            width: 100%;
            background-color: turquoise;
            font-weight: bold;
        }

        .btn-back-home {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .chart-container {
            width: 80%;
            /* เปลี่ยนเป็นขนาดที่ต้องการ */
            height: 400px;
            /* เปลี่ยนเป็นขนาดที่ต้องการ */
            margin: auto;
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>IT Equipment Statistics</h1>
            <a href="home_admin.php" class="btn-back-home">Back to Admin Home</a>
        </header>
        <!-- ส่วนบนแบ่งเป็น ซ้าย-ขวา -->
        <div class="top-section">
            <!-- ซ้ายบน: กราฟประเภทอุปกรณ์ -->
            <div class="left-top">
                <h2>ประเภทอุปกรณ์ IT</h2>
                <canvas id="typeChart" class="chart-container"></canvas>
            </div>
            <!-- ขวาบน: กราฟแท่งแสดงรายการยืมจากหน่วย -->
            <div class="right-top">
                <h2>รายการยืมจากหน่วย</h2>
                <canvas id="borrowChart" class="chart-container"></canvas>
            </div>
        </div>
        <!-- ส่วนล่าง: กราฟผู้ยืมคืน -->
        <div class="bottom-section">
            <h2>สถิติการยืมและคืนอุปกรณ์ IT</h2>
            <canvas id="statsChart" class="chart-container"></canvas>
        </div>
    </div>
    <script>
        // ตัวอย่างข้อมูลประเภทอุปกรณ์ (อัพเดทด้วยข้อมูลจริง)
        const typeLabels = <?php echo json_encode($types); ?>;
        const typeData = <?php echo json_encode($typeCounts); ?>;
        const originalTypeData = [...typeData]; // Clone the original array
        const pieData = {
            labels: typeLabels,
            datasets: [{
                label: 'ประเภทอุปกรณ์',
                data: typeData,
                backgroundColor: ['rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)', 'rgba(255, 206, 86, 0.2)',
                    'rgba(75, 192, 192, 0.2)', 'rgba(153, 102, 255, 0.2)', 'rgba(255, 159, 64, 0.2)'
                ],
                borderColor: ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)', 'rgba(153, 102, 255, 1)', 'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        };
        const pieConfig = {
            type: 'pie',
            data: pieData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw;
                            }
                        }
                    }
                }
            }
        };
        // กราฟวงกลมประเภทอุปกรณ์
        const ctxPie = document.getElementById('typeChart').getContext('2d');
        new Chart(ctxPie, pieConfig);
        // อัพเดทข้อมูลกราฟแท่งรายการยืมจากหน่วย
        const borrowAgencyLabels = <?php echo json_encode($agencies); ?>; // ใช้ชื่อหน่วยงานเป็น labels
        const borrowAgencyData = <?php echo json_encode($borrowCounts); ?>; // จำนวนการยืมของแต่ละหน่วยงาน
        const backgroundColors = <?php echo json_encode($colors); ?>; // สีพื้นหลังของแต่ละแท่งกราฟ
        const borderColors = <?php echo json_encode($borderColors); ?>; // สีขอบของแต่ละแท่งกราฟ
        const borrowData = {
            labels: borrowAgencyLabels, // ใช้ชื่อหน่วยงานแต่ละอันเป็น labels
            datasets: [{
                label: 'จำนวนรายการยืม', // ชื่อของ datasets
                data: borrowAgencyData, // ข้อมูลจำนวนการยืมของแต่ละหน่วยงาน
                backgroundColor: backgroundColors, // สีพื้นหลังสำหรับแต่ละหน่วยงาน
                borderColor: borderColors, // สีขอบสำหรับแต่ละหน่วยงาน
                borderWidth: 1
            }]
        };
        const borrowConfig = {
            type: 'bar',
            data: borrowData,
            options: {
                responsive: true,
                indexAxis: 'x',
                scales: {
                    x: {
                        beginAtZero: true
                    },
                    y: {
                        beginAtZero: true,
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        };
        const ctxBarBorrow = document.getElementById('borrowChart').getContext('2d');
        new Chart(ctxBarBorrow, borrowConfig);
        // กราฟแท่งสถิติการยืมและคืน
        const months = <?php echo json_encode($months); ?>;
        const borrowedCounts = <?php echo json_encode($borrowedCounts); ?>;
        const returnedCounts = <?php echo json_encode($returnedCounts); ?>;
        // สร้างกราฟแท่งสำหรับการยืมและคืน
        const statsData = {
            labels: months,
            datasets: [{
                    label: 'ยืม',
                    data: borrowedCounts,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)', // สีสำหรับรายการยืม
                    borderColor: 'rgba(54, 162, 235, 1)', // สีขอบสำหรับรายการยืม
                    borderWidth: 1,
                    stack: 'stack0'
                },
                {
                    label: 'คืน',
                    data: returnedCounts,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)', // สีสำหรับรายการคืน
                    borderColor: 'rgba(255, 99, 132, 1)', // สีขอบสำหรับรายการคืน
                    borderWidth: 1,
                    stack: 'stack1'
                }
            ]
        };
        const statsConfig = {
            type: 'bar',
            data: statsData,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top'
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
                        stacked: true,
                        beginAtZero: true
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true
                    }
                }
            }
        };
        // วาดกราฟแท่ง
        const ctxStats = document.getElementById('statsChart').getContext('2d');
        new Chart(ctxStats, statsConfig);
    </script>
</body>

</html>