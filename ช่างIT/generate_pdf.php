<?php
session_start();
require('fpdf186/fpdf.php');
include "../connect.php";
define('FPDF_FONTPATH', 'fpdf186/font/');

// ตรวจสอบว่าผู้ใช้ล็อกอินแล้วหรือไม่
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

// ดึงข้อมูลผู้ใช้จาก session
$fname = $_SESSION['u_fname'];
$lname = $_SESSION['u_lname'];

// ดึงข้อมูลจากฐานข้อมูล
$BruID = $_GET['BruID']; // รหัสการยืมที่ถูกส่งเข้ามาเมื่อกดแถว
$sql = "SELECT b.BruID, u.u_fname, u.u_lname, b.BrudateRe, i.ag_name, i.ag_id, b.Brunum 
        FROM borroww b 
        JOIN users u ON b.u_id = u.u_id
        JOIN items_1 i ON b.BruID = i.BruID 
        WHERE b.BruID = '$BruID' AND (b.st_id = 'ST002' OR b.st_id = 'ST005')";

$result = mysqli_query($conn, $sql);
$row = mysqli_fetch_assoc($result);

// เช็คว่ามีข้อมูลในฐานข้อมูลหรือไม่
if (!$row) {
    echo "ไม่พบข้อมูลสำหรับการยืมนี้";
    exit();
}

// ตั้งค่าเขตเวลาเป็นเวลาไทย
date_default_timezone_set('Asia/Bangkok');

// ฟอร์แมตวันที่ครบกำหนด
$BrudateRe = date_create($row['BrudateRe']);

// แปลงปี ค.ศ. เป็น พ.ศ. และชื่อเดือนเป็นภาษาไทย
$thaiMonths = [
    1 => "มกราคม",
    2 => "กุมภาพันธ์",
    3 => "มีนาคม",
    4 => "เมษายน",
    5 => "พฤษภาคม",
    6 => "มิถุนายน",
    7 => "กรกฎาคม",
    8 => "สิงหาคม",
    9 => "กันยายน",
    10 => "ตุลาคม",
    11 => "พฤศจิกายน",
    12 => "ธันวาคม",
];

$day = date_format($BrudateRe, "d"); // วัน
$month = intval(date_format($BrudateRe, "m")); // เดือน
$year = intval(date_format($BrudateRe, "Y")) + 543; // ปี (เพิ่ม 543)

$BrudateReFormatted = "$day $thaiMonths[$month] $year"; // วันที่ในฟอร์มไทย

// วันที่ปัจจุบัน
$currentDate = date('d/m/Y'); // กำหนดรูปแบบวันที่เป็น d/m/Y
list($day, $month, $year) = explode('/', $currentDate); // แยกวัน เดือน ปี

// แปลงปี ค.ศ. เป็น พ.ศ.
$year += 543;

// แสดงเดือนเป็นภาษาไทย
$thaiMonth = $thaiMonths[intval($month)];

// เริ่มการสร้าง PDF
class PDF extends FPDF {
    function Header() {
        // ใส่หัวเอกสารถ้าต้องการ
    }

    function Footer() {
        
    }
}

$pdf = new PDF();
$pdf->AddPage();

// ตั้งค่าฟอนต์
$pdf->AddFont('THSarabun', '', 'THSarabun.php');
$pdf->SetFont('THSarabun', '', 14);

// เริ่มกรอกข้อมูลลงใน PDF
$pdf->SetXY(95, 30); // ตำแหน่งสำหรับ "หนังสือแจ้งคืนอุปกรณ์"
$pdf->Write(10, iconv('UTF-8', 'TIS-620', 'หนังสือแจ้งคืนอุปกรณ์'));

$pdf->SetXY(130, 50); // ตำแหน่งสำหรับวันที่
$pdf->Write(10, iconv('UTF-8', 'TIS-620', "(วัน $day เดือน $thaiMonth ปี $year)"));

$pdf->SetXY(30, 70); // ตำแหน่งสำหรับเรื่อง
$pdf->Write(10, iconv('UTF-8', 'TIS-620', 'เรื่อง  แจ้งคืนอุปกรณ์ เลยกำหนดวันคืน'));

$pdf->SetXY(30, 80); // ตำแหน่งสำหรับชื่อผู้ยืม
$borrowerName = $row['u_fname'] . " " . $row['u_lname'];
$pdf->Write(10, iconv('UTF-8', 'TIS-620', 'เรียน  ' . $borrowerName));

$pdf->SetXY(50, 90); // ตำแหน่งสำหรับวันที่ครบกำหนด
$pdf->Write(10, iconv('UTF-8', 'TIS-620', 'เนื่องจากรายการอุปกรณ์ที่ทำการยืมไปใช้งาน ได้ครบกำหนดไปแล้วในวันที่ ' . $BrudateReFormatted));

$pdf->SetXY(30, 100); // ตำแหน่งสำหรับหัวข้อรายการอุปกรณ์
$pdf->Write(10, iconv('UTF-8', 'TIS-620', 'รายการอุปกรณที่ทำการเรียกเก็บ'));

$pdf->SetXY(50, 120); // ตำแหน่งสำหรับชื่ออุปกรณ์และรหัสอุปกรณ์
$pdf->Write(10, iconv('UTF-8', 'TIS-620', 'ชื่ออุประกรณ์'));
$pdf->SetXY(140, 120); // ตำแหน่งสำหรับชื่ออุปกรณ์และรหัสอุปกรณ์
$pdf->Write(10, iconv('UTF-8', 'TIS-620', 'รหัสอุปกรณ์'));
// คำสั่ง SQL เพื่อดึงข้อมูลอุปกรณ์ที่เกี่ยวข้อง
$sql_items = "SELECT ag_name, ag_id FROM items_1 WHERE BruID = '$BruID'";
$items_result = mysqli_query($conn, $sql_items);
$row_number = 0;
while ($item = mysqli_fetch_assoc($items_result)) {
    $pdf->SetXY(50, 130 + ($row_number * 10)); // ตั้งตำแหน่งสำหรับแต่ละอุปกรณ์ (ปรับ Y ตามจำนวนอุปกรณ์)
    $pdf->Write(10, iconv('UTF-8', 'TIS-620', $item['ag_name']));
    $row_number++;
}
$sql_items = "SELECT ag_name, ag_id FROM items_1 WHERE BruID = '$BruID'";
$items_result = mysqli_query($conn, $sql_items);
$row_number = 0;
while ($item = mysqli_fetch_assoc($items_result)) {
    $pdf->SetXY(140, 130 + ($row_number * 10)); // ตั้งตำแหน่งสำหรับแต่ละอุปกรณ์ (ปรับ Y ตามจำนวนอุปกรณ์)
    $pdf->Write(10, iconv('UTF-8', 'TIS-620', $item['ag_id']));
    $row_number++;
}
// คำลงท้าย
$pdf->SetXY(130, 160 + ($row_number * 10) + 10); // ปรับตำแหน่งหลังจากรายการอุปกรณ์
$pdf->Write(10, iconv('UTF-8', 'TIS-620', '( .............................................. )'));

// ลายเซ็น
$pdf->SetXY(130, 160 + ($row_number * 10) + 20); // ปรับตำแหน่งสำหรับลายเซ็น
$pdf->Write(10, iconv('UTF-8', 'TIS-620', '(ลงชื่อ) ' . $fname . " " . $lname));
// ลายเซ็น
$pdf->SetXY(125, 160 + ($row_number * 10) + 30); // ปรับตำแหน่งสำหรับลายเซ็น
$pdf->Write(10, iconv('UTF-8', 'TIS-620', 'วันที่ ( ......................................... ) ' ));
// ช่องว่างสำหรับหมายเลขโทรศัพท์
$pdf->SetXY(30, 200 + ($row_number * 10) + 30); // ปรับตำแหน่งสำหรับโทรศัพท์
$pdf->Write(10, iconv('UTF-8', 'TIS-620', 'โทร.  ...................................'));

// ส่งออก PDF โดยแสดงผลในเบราว์เซอร์

$pdf->Output('I', 'fromRe.pdf');
?>
