<?php
session_start();
include 'db.php';  // เชื่อมต่อฐานข้อมูล

// ตรวจสอบการล็อกอินและบทบาทของผู้ใช้
if (!isset($_SESSION['email']) || $_SESSION['type'] != 'admin') {
    header('Location: admin_status.php');
    exit();
}
$email = $_SESSION['email'];
$message = ""; // ตัวแปรเก็บข้อความแจ้งเตือน

// ดึงข้อมูลชื่อผู้ดูแลระบบ
$stmt = $conn->prepare("SELECT first_name, last_name FROM admin_profile WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$display_name = ($row) ? $row['first_name'] . ' ' . $row['last_name'] : 'ผู้ดูแลระบบ';


// ตรวจสอบการทำงานของการเปลี่ยนสถานะการจอง
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_booking_status'])) {
    $booking_id = $_POST['booking_id'];
    $approval_status = $_POST['approval_status'];

    // อัปเดตสถานะการอนุมัติ
    $update_query = $conn->prepare("UPDATE booking_requests SET approval_status = ? WHERE booking_id = ?");
    $update_query->bind_param("si", $approval_status, $booking_id);

    if ($update_query->execute()) {
        echo "ข้อมูลการจองถูกอัปเดตสำเร็จ!";
    } else {
        echo "เกิดข้อผิดพลาดในการอัปเดตข้อมูลการจอง.";
    }
}

// การลบคำขอ
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    // ตรวจสอบก่อนว่าคำขอนี้ถูกยืนยันหรือยัง ถ้ายังไม่ยืนยันให้ลบได้
    $stmt = $conn->prepare("SELECT driver_confirmed FROM booking_requests WHERE booking_id = ?");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['driver_confirmed'] == 'รอการยืนยัน') {
        $delete_query = $conn->prepare("DELETE FROM booking_requests WHERE booking_id = ?");
        $delete_query->bind_param("i", $delete_id);

        if ($delete_query->execute()) {
            // รีเฟรชหน้า
            header("Location: admin_status.php");
            exit();
        } else {
            echo "เกิดข้อผิดพลาดในการลบคำขอจอง.";
        }
    } else {
        echo "ไม่สามารถลบคำขอจองนี้ได้ เนื่องจากคนขับได้รับการยืนยันแล้ว.";
    }
}

// ดึงข้อมูลคำขอจองทั้งหมด
$requests = $conn->query("SELECT * FROM booking_requests");

require 'vendor/autoload.php';  // เรียกใช้งาน Dompdf

use Dompdf\Dompdf;
use Dompdf\Options;

// ตั้งค่าตัวเลือก
$options = new Options();
$fontPath = __DIR__ . "/fonts/Sarabun-Regular.ttf";  // ใช้ฟอนต์ Sarabun
$options->set('chroot', __DIR__);

// สร้างตัวแปร Dompdf
$dompdf = new Dompdf($options);

// กำหนดเส้นทางของไฟล์ PDF ที่ต้องการแสดง
$pdfFilePath = 'C:/xampp/htdocs/vehicle_booking_system/ใบขออนุญาตใช้รถราชการ มทท ขอนแก่น.pdf';


// ตรวจสอบว่าไฟล์มีอยู่จริงหรือไม่
if (file_exists($pdfFilePath)) {
    // โหลดไฟล์ PDF
    $dompdf->loadHtml(file_get_contents($pdfFilePath));
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    // ส่งให้ผู้ใช้ดาวน์โหลดหรือดู PDF
    $dompdf->stream("ใบขออนุญาตใช้รถราชการ มทท ขอนแก่น.pdf", array("Attachment" => 0));  // 0 เพื่อให้แสดงในเบราว์เซอร์
} else {
    echo "ไม่พบไฟล์ PDF";
}

?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการคำขอจองรถ</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: rgb(241, 241, 241);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* แถบด้านบน (Header) */
        header {
            background-color: #004080;
            color: white;
            padding: 10px 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo-container img {
            height: 35px;
            margin-right: 10px;
        }

        .logo-container h1 {
            margin: 0;
            font-size: 1.2em;
            white-space: nowrap;
        }

        /* แถบด้านข้าง (Sidebar) */
        .sidebar {
            background-color: #f4f4f4;
            width: 200px;
            height: calc(100vh - 50px);
            position: fixed;
            top: 50px;
            left: 0;
            padding: 15px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            overflow-y: auto;
        }

        .sidebar h2 {
            margin-bottom: 15px;
            font-size: 1em;
            text-align: center;
        }

        .sidebar a {
            text-decoration: none;
            color: #004080;
            display: block;
            padding: 8px 5px;
            font-size: 0.9em;
        }

        .sidebar a:hover {
            background-color: #e0e0e0;
            border-radius: 5px;
        }

        /* พื้นที่หลัก (Main Content) */
        .main-content {
            margin-left: 220px;
            padding: 15px;
            width: calc(100% - 220px);
        }

        .main-content h2 {
            font-size: 1.2em;
            margin-bottom: 15px;
        }

        table {
            width: 80%;
            margin-left: 2%;
            margin-top: 2%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
            font-size: 0.9em;
        }

        th {
            background-color: #004080;
            color: white;
            text-transform: uppercase;
            font-weight: bold;
            font-size: 1em;
        }

        td {
            color: #333;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .view-button {
            background-color: #3498DB;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.9em;
            transition: all 0.3s ease;
        }

        .status {
            font-weight: bold;
        }

        .approve-button,
        .reject-button {
            padding: 8px 16px;
            border: none;
            cursor: pointer;
            font-size: 1.1em;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .approve-button {
            background-color: #28a745;
            color: white;
            box-shadow: 0 4px 6px rgba(0, 128, 0, 0.2);
        }

        .approve-button:hover {
            background-color: #218838;
        }

        .reject-button {
            background-color: #dc3545;
            color: white;
            box-shadow: 0 4px 6px rgba(220, 53, 69, 0.2);
        }

        .reject-button:hover {
            background-color: #c82333;
        }

        .delete-button {
            background-color: #e0e0e0;
            color: black;
            padding: 5px 10px;
            border: none;
            cursor: pointer;
            font-size: 1em;
        }

        .print-button {
            background-color: #ff9800;
            color: white;
            padding: 8px 16px;
            border: none;
            cursor: pointer;
            font-size: 0.9em;
            border-radius: 5px;
            transition: all 0.3s ease;
        }

        .print-button:hover {
            background-color: #f57c00;
        }

        .completed-status {
            background-color: #f8d7da;
            /* สีแดงอ่อน */
            color: #721c24;
            /* ตัวอักษรสีแดงเข้ม */
        }

        .tb1 {
            width: 1050px;
            height: 100px;
            margin: 20px 0 0 360px;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            font-size: smaller;
        }
    </style>
</head>

<body>

    <header>
        <div class="logo-container">
            <img src="uploads/RMUTI_KORAT.png" alt="โลโก้มหาวิทยาลัย">
            <h1>ระบบจองรถมหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน วิทยาเขตขอนแก่น</h1>
        </div>
    </header>

    <div class="sidebar">
        <h2>สวัสดี, <?php echo htmlspecialchars($display_name); ?></h2>
        <a href="admin_dashboard.php">หน้าหลัก</a>
        <a href="admin_profile.php">ข้อมูลส่วนตัว</a>
        <a href="admin_requests.php">จัดการคำขอจองรถ</a>
        <a href="add_user.php">เพิ่มข้อมูลผู้ใช้</a>
        <a href="add_vehicle.php">เพิ่มข้อมูลรถ</a>
        <a href="add_driver.php">เพิ่มข้อมูลพนักงานขับรถ</a>
        <a href="admin_status.php">รออนุมัติสถานะการจอง</a>
        <a href="change_password.php">เปลี่ยนรหัสผ่าน</a>
        <a href="login.php">ออกจากระบบ</a>
    </div>

    <div>
        <h2 style="margin: 60px 0 20px 300px; color:#333; font-size: 18px">จัดการสถานะการจอง</h2>
        <table class="tb1">
            <thead>
                <tr>
                    <th>รหัสคำขอ</th>
                    <th>ชื่อผู้ขอ</th>
                    <th>สถานะการอนุมัติ</th>
                    <th>ชื่อ-สกุลพนักงานขับรถ</th>
                    <th>สถานะการยืนยันคนขับ</th>
                    <th>การอนุมัติ</th>
                    <th>เอกสารคำขอ</th>
                    <th>การสิ้นสุด</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($booking = $requests->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                        <td><?php echo htmlspecialchars($booking['first_name']) . " " . htmlspecialchars($booking['last_name']); ?></td>
                        <td class="status"><?php echo htmlspecialchars($booking['approval_status']); ?></td>
                        <td class="status"><?php echo htmlspecialchars($booking['driver_name']); ?></td>
                        <td class="status"><?php echo htmlspecialchars($booking['driver_confirmed']); ?></td>

                        <td>
                            <?php if ($booking['approval_status'] != 'อนุมัติ' || $booking['driver_confirmed'] == 'รอการยืนยัน') { ?>
                                <form method="POST" action="admin_status.php">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                    <select name="approval_status">
                                        <option value="รออนุมัติ" <?php echo $booking['approval_status'] == 'รออนุมัติ' ? 'selected' : ''; ?>>รออนุมัติ</option>
                                        <option value="อนุมัติ" <?php echo $booking['approval_status'] == 'อนุมัติ' ? 'selected' : ''; ?>>อนุมัติ</option>
                                        <option value="ไม่อนุมัติ" <?php echo $booking['approval_status'] == 'ไม่อนุมัติ' ? 'selected' : ''; ?>>ไม่อนุมัติ</option>
                                    </select>

                                    <button type="submit" name="update_booking_status" class="approve-button">อัปเดต</button>
                                </form>
                            <?php } else { ?>
                                <span class="status">อนุมัติแล้ว</span>
                            <?php } ?>
                        </td>

                        <td>
                            <a href="print_booking.php?booking_id=<?php echo $booking['booking_id']; ?>" class="print-button">พิมพ์คำขอจองรถ</a>
                        </td>
                        <td class="status <?php echo $booking['job_completed'] == 'สิ้นสุดแล้ว' ? 'completed-status' : ''; ?>">
                            <?php echo htmlspecialchars($booking['job_completed']); ?>
                        </td>

                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

</body>

</html>