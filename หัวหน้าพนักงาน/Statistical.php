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
    <link rel="stylesheet" href="../styles.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            height: 400px;
            margin: auto;
        }

        .btn-print {
            margin: 10px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            .print-section,
            .print-section * {
                visibility: visible;
            }

            .print-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <h1>IT Equipment Statistics</h1>
            <a href="home_h.php" class="btn-back-home">Back to Admin Home</a>
        </header>

        <!-- ส่วนบนแบ่งเป็น ซ้าย-ขวา -->
        <div class="top-section">
            <!-- ซ้ายบน: กราฟประเภทอุปกรณ์ -->
            <div class="left-top print-section" id="print-type">
                <h2>ประเภทอุปกรณ์ IT</h2>
                <canvas id="typeChart" class="chart-container"></canvas>
                <button class="btn-print" onclick="printSection('print-type')">Print Type Chart</button>
            </div>

            <!-- ขวาบน: กราฟแท่งแสดงรายการยืมจากหน่วย -->
            <div class="right-top print-section" id="print-borrow">
                <h2>รายการยืมจากหน่วย</h2>
                <canvas id="borrowChart" class="chart-container"></canvas>
                <button class="btn-print" onclick="printSection('print-borrow')">Print Borrow Chart</button>
            </div>
        </div>

        <!-- ส่วนล่าง: กราฟผู้ยืมคืน -->
        <div class="bottom-section print-section" id="print-stats">
            <h2>สถิติการยืมและคืนอุปกรณ์ IT</h2>
            <canvas id="statsChart" class="chart-container"></canvas>
            <button class="btn-print" onclick="printSection('print-stats')">Print Stats Chart</button>
        </div>
    </div>

    <script>
        function printSection(sectionId) {
            const allSections = document.querySelectorAll('.print-section');
            const sectionToPrint = document.getElementById(sectionId);

            // ซ่อนทุกส่วนก่อน
            allSections.forEach(section => {
                if (section !== sectionToPrint) {
                    section.style.display = 'none';
                }
            });

            // เรียกพิมพ์เฉพาะส่วนที่เลือก
            window.print();

            // แสดงทุกส่วนที่ถูกซ่อนกลับมา
            allSections.forEach(section => {
                section.style.display = '';
            });
        }

        // กราฟวงกลมประเภทอุปกรณ์
        const typeLabels = <?php echo json_encode($types); ?>;
        const typeData = <?php echo json_encode($typeCounts); ?>;
        const pieData = {
            labels: typeLabels,
            datasets: [{
                label: 'ประเภทอุปกรณ์',
                data: typeData,
                backgroundColor: ['rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)', 'rgba(255, 206, 86, 0.2)', 'rgba(75, 192, 192, 0.2)', 'rgba(153, 102, 255, 0.2)', 'rgba(255, 159, 64, 0.2)'],
                borderColor: ['rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)', 'rgba(75, 192, 192, 1)', 'rgba(153, 102, 255, 1)', 'rgba(255, 159, 64, 1)'],
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
        const ctxPie = document.getElementById('typeChart').getContext('2d');
        new Chart(ctxPie, pieConfig);

        // กราฟแท่งรายการยืมจากหน่วย
        const borrowAgencyLabels = <?php echo json_encode($agencies); ?>;
        const borrowAgencyData = <?php echo json_encode($borrowCounts); ?>;
        const backgroundColors = <?php echo json_encode($colors); ?>;
        const borderColors = <?php echo json_encode($borderColors); ?>;
        const borrowData = {
            labels: borrowAgencyLabels,
            datasets: [{
                label: 'จำนวนรายการยืม',
                data: borrowAgencyData,
                backgroundColor: backgroundColors,
                borderColor: borderColors,
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
                        beginAtZero: true
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
        const statsData = {
            labels: months,
            datasets: [{
                    label: 'ยืม',
                    data: borrowedCounts,
                    backgroundColor: 'rgba(54, 162, 235, 0.6)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1,
                    stack: 'stack0'
                },
                {
                    label: 'คืน',
                    data: returnedCounts,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                    borderColor: 'rgba(255, 99, 132, 1)',
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
        const ctxStats = document.getElementById('statsChart').getContext('2d');
        new Chart(ctxStats, statsConfig);
    </script>
</body>

</html>