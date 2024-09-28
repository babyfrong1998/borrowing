<?php
session_start();
include "../connect.php";

// ตรวจสอบการเชื่อมต่อกับฐานข้อมูล
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// 1. แสดงจำนวนอุปกรณ์ทั้งหมด
$sqlTotalItems = "SELECT COUNT(*) AS total_items FROM items_1";
$resultTotalItems = $conn->query($sqlTotalItems);
$totalItems = 0;
if ($row = $resultTotalItems->fetch_assoc()) {
    $totalItems = $row['total_items'];
}

// 2. แสดงจำนวนอุปกรณ์ทั้งหมดแบบแยกประเภท (กราฟวงกลม)
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

// 3. แสดงจำนวนรายการยืมทั้งหมด
$sqlTotalBorrows = "SELECT COUNT(*) AS total_borrows FROM borroww";
$resultTotalBorrows = $conn->query($sqlTotalBorrows);
$totalBorrows = 0;
if ($row = $resultTotalBorrows->fetch_assoc()) {
    $totalBorrows = $row['total_borrows'];
}

// 4. แสดงจำนวนรายการยืมแบบแยกหน่วยงาน (กราฟแท่ง)
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
    $agencies[] = $row['Agency'] . " (" . $row['User'] . ")";
    $borrowCounts[] = $row['borrow_count'] ? $row['borrow_count'] : 0;
}

// สร้างสีสำหรับกราฟแท่ง
$colors = [];
$borderColors = [];
for ($i = 0; $i < count($agencies); $i++) {
    $colors[] = sprintf('rgba(%d, %d, %d, 0.2)', rand(0, 255), rand(0, 255), rand(0, 255));
    $borderColors[] = sprintf('rgba(%d, %d, %d, 1)', rand(0, 255), rand(0, 255), rand(0, 255));
}

// 5. ฟอร์มเลือกปีและแสดงข้อมูลยืม-คืนแยกปี
$selected_year = isset($_GET['selected_year']) ? $_GET['selected_year'] : date('Y');

// ดึงเฉพาะปีที่มีข้อมูล
$sqlYears = "SELECT DISTINCT YEAR(b_date) AS year FROM borrohistory ORDER BY year DESC";
$resultYears = $conn->query($sqlYears);
$availableYears = [];
while ($row = $resultYears->fetch_assoc()) {
    $availableYears[] = $row['year'];
}

// สถิติการยืม-คืนแยกตามเดือนในปีที่เลือก
// สถิติการยืม-คืนแยกตามเดือนในปีที่เลือก
$sqlStatsByYear = "
    SELECT 
        DATE_FORMAT(b_date, '%Y-%m') AS month,
        SUM(CASE WHEN b_status IN ('ST005', 'ST002') THEN 1 ELSE 0 END) AS borrowed,
        SUM(CASE WHEN b_status = 'ST009' THEN 1 ELSE 0 END) AS returned,
        COUNT(b_status) AS total_in_month
    FROM borrohistory
    WHERE YEAR(b_date) = '$selected_year'
    GROUP BY month
";
$resultStatsByYear = $conn->query($sqlStatsByYear);
$months = [];
$borrowedCounts = [];
$returnedCounts = [];
while ($row = $resultStatsByYear->fetch_assoc()) {
    $months[] = $row['month'];
    $totalInMonth = $row['total_in_month'];

    // คำนวณเปอร์เซ็นต์การยืมและคืน
    $borrowedPercent = ($totalInMonth > 0) ? ($row['borrowed'] / $totalInMonth) * 100 : 0;
    $returnedPercent = ($totalInMonth > 0) ? ($row['returned'] / $totalInMonth) * 100 : 0;

    $borrowedCounts[] = $borrowedPercent;
    $returnedCounts[] = $returnedPercent;
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
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .container {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            position: relative;
        }

        .btn-back-home {
            position: absolute;
            top: 20px;
            left: 20px;
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .btn-print {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
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
            margin: 0;
        }

        .chart-container {
            width: 80%;
            height: 400px;
            margin: auto;
        }

        form {
            margin: 20px 0;
            text-align: center;
        }

        select {
            padding: 5px;
            font-size: 16px;
        }

        @media print {
            body * {
                visibility: hidden;
            }

            .printable-section,
            .printable-section * {
                visibility: visible;
            }

            .printable-section {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
        }

        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.4);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 300px;
            border-radius: 5px;
        }

        label,
        ul,
        li {
            text-align: left !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .close-modal {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .close-modal:hover,
        .close-modal:focus {
            color: black;
            text-decoration: none;
        }

        .modal-buttons {
            text-align: right;
            margin-top: 20px;
        }

        .modal-buttons button {
            padding: 8px 16px;
            margin-left: 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }

        .modal-buttons .btn-cancel {
            background-color: #dc3545;
            color: white;
        }

        .modal-buttons .btn-confirm {
            background-color: #28a745;
            color: white;
        }
    </style>
</head>

<body>
    <div class="container">
        <header>
            <a href="home_admin.php" class="btn-back-home">Back to Admin Home</a>
            <button class="btn-print" id="openPrintModal">Print</button>
            <h1>IT Equipment Statistics</h1>
            <!-- แสดงจำนวนอุปกรณ์ทั้งหมดและจำนวนรายการยืมทั้งหมด -->
            <h2>จำนวนอุปกรณ์ทั้งหมด: <?php echo $totalItems; ?></h2>
            <h2>จำนวนรายการยืมทั้งหมด: <?php echo $totalBorrows; ?></h2>
        </header>
        <!-- ส่วนบนแบ่งเป็น ซ้าย-ขวา -->
        <div class="top-section">
            <!-- ซ้ายบน: กราฟประเภทอุปกรณ์ -->
            <div class="left-top printable-section" id="print-type">
                <h2>ประเภทอุปกรณ์ IT</h2>
                <canvas id="typeChart" class="chart-container"></canvas>
            </div>
            <!-- ขวาบน: กราฟแท่งแสดงรายการยืมจากหน่วย -->
            <div class="right-top printable-section" id="print-borrow">
                <h2>รายการยืมจากหน่วย</h2>
                <canvas id="borrowChart" class="chart-container"></canvas>
            </div>
        </div>
        <!-- ส่วนล่าง: กราฟผู้ยืมคืน -->
        <div class="bottom-section printable-section" id="print-stats">
            <h2>สถิติการยืมและคืนอุปกรณ์ IT (ปี <?php echo $selected_year; ?>)</h2>
            <form method="GET">
                <label for="year">เลือกปี: </label>
                <select name="selected_year" id="year">
                    <?php foreach ($availableYears as $year) : ?>
                        <option value="<?php echo $year; ?>" <?php echo $year == $selected_year ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" value="กรอง">
            </form>

            <canvas id="statsChart" class="chart-container"></canvas>
        </div>
    </div>

    <!-- Modal for Print Options -->
    <div id="printModal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h3>Select Sections to Print</h3>
            <form id="printForm">
                <!-- ส่วนแสดงข้อมูลที่เลือก -->
                <div id="selectedSectionsDisplay" style="margin-bottom: 20px;"></div>

                <!-- หมวดประเภทอุปกรณ์ IT -->
                <input type="checkbox" id="printType" name="sections" value="print-type" checked>
                <label for="printType">ประเภทอุปกรณ์ IT</label>
                <ul>
                    <?php foreach ($types as $index => $type) : ?>
                        <li>
                            <input type="checkbox" class="type-checkbox" id="type_<?php echo $index; ?>" name="type_selected[]" value="<?php echo $type; ?>" checked>
                            <label for="type_<?php echo $index; ?>"><?php echo $type; ?> (<?php echo $typeCounts[$index]; ?>)</label>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <br>

                <!-- หมวดรายการยืมจากหน่วย -->
                <input type="checkbox" id="printBorrow" name="sections" value="print-borrow" checked>
                <label for="printBorrow">รายการยืมจากหน่วย</label>
                <ul>
                    <?php foreach ($agencies as $index => $agency) : ?>
                        <li>
                            <input type="checkbox" class="agency-checkbox" id="agency_<?php echo $index; ?>" name="agency_selected[]" value="<?php echo $agency; ?>" checked>
                            <label for="agency_<?php echo $index; ?>"><?php echo $agency; ?> (<?php echo $borrowCounts[$index]; ?>)</label>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <br>

                <input type="checkbox" id="printBorrowStats" name="sections" value="print-borrow-stats" checked>
                <label for="printBorrowStats">สถิติการยืมและคืนอุปกรณ์ IT (ปี <?php echo $selected_year; ?>)</label><br>
                <div id="monthlyStats" style="margin-top: 10px;">
                    <ul>
                        <?php foreach ($months as $index => $month) : ?>
                            <li><?php echo date('F', strtotime($month)); ?>:
                                Borrowed: <?php echo number_format($borrowedCounts[$index], 2); ?>,
                                Returned: <?php echo number_format($returnedCounts[$index], 2); ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="modal-buttons">
                    <button type="button" class="btn-cancel" id="cancelPrint">Cancel</button>
                    <button type="button" class="btn-confirm" id="confirmPrint">Print</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        // Modal Elements
        const modal = document.getElementById('printModal');
        const btnPrint = document.getElementById('openPrintModal');
        const spanClose = document.getElementsByClassName('close-modal')[0];
        const btnCancel = document.getElementById('cancelPrint');
        const btnConfirm = document.getElementById('confirmPrint');

        // Open Modal
        btnPrint.onclick = function() {
            modal.style.display = "block";
        }

        // Close Modal when clicking on <span> (x)
        spanClose.onclick = function() {
            modal.style.display = "none";
        }

        // Close Modal when clicking on Cancel button
        btnCancel.onclick = function() {
            modal.style.display = "none";
        }

        // Close Modal when clicking outside the modal content
        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }

        // กราฟประเภทอุปกรณ์
        const typeData = {
            labels: <?php echo json_encode($types); ?>,
            datasets: [{
                data: <?php echo json_encode($typeCounts); ?>,
                backgroundColor: ['rgba(75, 192, 192, 0.2)', 'rgba(255, 159, 64, 0.2)', 'rgba(153, 102, 255, 0.2)', 'rgba(255, 99, 132, 0.2)', 'rgba(54, 162, 235, 0.2)'],
                borderColor: ['rgba(75, 192, 192, 1)', 'rgba(255, 159, 64, 1)', 'rgba(153, 102, 255, 1)', 'rgba(255, 99, 132, 1)', 'rgba(54, 162, 235, 1)'],
                borderWidth: 1
            }]
        };
        const pieConfig = {
            type: 'pie',
            data: typeData,
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
        new Chart(document.getElementById('typeChart'), pieConfig);

        // กราฟรายการยืมจากหน่วย
        const borrowData = {
            labels: <?php echo json_encode($agencies); ?>,
            datasets: [{
                label: 'จำนวนการยืม',
                data: <?php echo json_encode($borrowCounts); ?>,
                backgroundColor: <?php echo json_encode($colors); ?>,
                borderColor: <?php echo json_encode($borderColors); ?>,
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
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return this.getLabelForValue(value).split(" ").join("\n");
                            }
                        }
                    },
                    y: {
                        beginAtZero: true
                    }
                },
                plugins: {
                    legend: {
                        display: true,
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
        new Chart(document.getElementById('borrowChart'), borrowConfig);

        // กราฟสถิติการยืมและคืนอุปกรณ์
        const statsData = {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'ยืม (%)',
                data: <?php echo json_encode($borrowedCounts); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }, {
                label: 'คืน (%)',
                data: <?php echo json_encode($returnedCounts); ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        };
        const statsConfig = {
            type: 'bar',
            data: statsData,
            options: {
                responsive: true,
                scales: {
                    x: {
                        beginAtZero: true
                    },
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return value + '%'; // แสดงเป็นเปอร์เซ็นต์
                            }
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.dataset.label + ': ' + tooltipItem.raw.toFixed(2) + '%'; // แสดงเป็นเปอร์เซ็นต์ใน tooltip
                            }
                        }
                    }
                }
            }
        };
        new Chart(document.getElementById('statsChart'), statsConfig);
        document.getElementById('printType').addEventListener('change', function() {
            let checkboxes = document.querySelectorAll('.type-checkbox');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = document.getElementById('printType').checked;
            });
        });

        // ฟังก์ชันติ๊กข้อมูลย่อยทั้งหมดใน "รายการยืมจากหน่วย"
        document.getElementById('printBorrow').addEventListener('change', function() {
            let checkboxes = document.querySelectorAll('.agency-checkbox');
            checkboxes.forEach(function(checkbox) {
                checkbox.checked = document.getElementById('printBorrow').checked;
            });
        });

        btnConfirm.onclick = function() {
            // Create a new window for printing
            const printWindow = window.open('', '_blank');
            printWindow.document.write('<html><head><title>สถิติการยืม-คืนอุปกรณ์</title>');
            printWindow.document.write('<link rel="stylesheet" href="../styles.css">'); // Use existing CSS
            printWindow.document.write(`
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
                padding: 0;
                line-height: 1.5;
            }
            h1, h2 {
                text-align: center;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
                page-break-inside: auto; /* Allow page break inside tables */
            }
            th, td {
                padding: 8px;
                text-align: left;
                border: 1px solid #000;
            }
            th {
                background-color: #f2f2f2;
            }
            @media print {
                body {
                    -webkit-print-color-adjust: exact; /* Preserve colors in print */
                }
            }
        </style>
    `);
            printWindow.document.write('</head><body>');
            printWindow.document.write('<h1>Print Preview</h1>');

            // Create table for showing selected types of equipment
            printWindow.document.write('<h2>ประเภทอุปกรณ์</h2>');
            printWindow.document.write('<table>');
            printWindow.document.write('<thead><tr><th>ประเภทอุปกรณ์</th><th>จำนวน</th></tr></thead><tbody>');

            // Check selected item types
            let typeCheckboxes = document.querySelectorAll('.type-checkbox:checked');
            typeCheckboxes.forEach(function(checkbox) {
                const typeName = checkbox.value;
                const count = checkbox.parentElement.textContent.match(/\((\d+)\)/)[1]; // Extract number from text
                printWindow.document.write(`<tr><td>${typeName}</td><td>${count}</td></tr>`);
            });

            printWindow.document.write('</tbody></table>'); // Close types table

            // Create table for showing selected agencies
            printWindow.document.write('<h2>หน่วยงาน</h2>');
            printWindow.document.write('<table>');
            printWindow.document.write('<thead><tr><th>หน่วยงาน</th><th>จำนวน</th></tr></thead><tbody>');

            // Check selected agencies
            let agencyCheckboxes = document.querySelectorAll('.agency-checkbox:checked');
            agencyCheckboxes.forEach(function(checkbox) {
                const agencyName = checkbox.value;
                const count = checkbox.parentElement.textContent.match(/\((\d+)\)/)[1]; // Extract number from text
                printWindow.document.write(`<tr><td>${agencyName}</td><td>${count}</td></tr>`);
            });

            printWindow.document.write('</tbody></table>'); // Close agencies table

            // Check if borrowing statistics should be included
            if (document.getElementById('printBorrowStats').checked) {
                printWindow.document.write('<h2>สถิติการยืมและคืนอุปกรณ์ IT (ปี <?php echo $selected_year; ?>)</h2>');
                printWindow.document.write('<table>');
                printWindow.document.write('<thead><tr><th>เดือน</th><th>จำนวนยืม</th><th>จำนวนคืน</th></tr></thead><tbody>');

                <?php foreach ($months as $index => $month) : ?>
                    printWindow.document.write(`<tr><td><?php echo date('F', strtotime($month)); ?></td><td><?php echo number_format($borrowedCounts[$index], 2); ?></td><td><?php echo number_format($returnedCounts[$index], 2); ?></td></tr>`);
                <?php endforeach; ?>

                printWindow.document.write('</tbody></table>');
            }

            printWindow.document.write('<button onclick="window.print()">Print this page</button>');
            printWindow.document.write('</body></html>');
            printWindow.document.close(); // Close document
            modal.style.display = "none"; // Hide modal after printing
        }
    </script>
</body>

</html>