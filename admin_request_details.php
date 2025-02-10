<?php
session_start();
include 'db.php';

// ตรวจสอบการล็อกอินและบทบาทของผู้ใช้
if (!isset($_SESSION['email']) || $_SESSION['type'] != 'admin') {
    header('Location: index.php');
    exit();
}

// ตรวจสอบค่า booking_id
if (!isset($_GET['booking_id']) || empty($_GET['booking_id']) || !is_numeric($_GET['booking_id'])) {
    echo "ไม่มีค่า booking_id ใน URL หรือ booking_id ไม่ถูกต้อง";
    exit();
}

$booking_id = $_GET['booking_id'];

// ดึงข้อมูลคำขอจองรถ
$stmt = $conn->prepare("SELECT * FROM booking_requests WHERE booking_id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $booking_request = $result->fetch_assoc();
} else {
    echo "ไม่พบข้อมูลคำขอจองรถที่ตรงกับ ID ที่ให้มา";
    exit();
}
    // ตรวจสอบว่ามีค่า booking_id ใน URL หรือไม่ และเป็นตัวเลขหรือไม่
    if (!isset($_GET['booking_id']) || empty($_GET['booking_id']) || !is_numeric($_GET['booking_id'])) {
        echo "ไม่มีค่า booking_id ใน URL หรือ booking_id ไม่ถูกต้อง";
        exit();
    }

    $booking_id = $_GET['booking_id'];

// ... (โค้ดส่วนที่เหลือ)

// ตรวจสอบว่ามีการส่งข้อมูลการอนุมัติหรือไม่
if (isset($_POST['approve'])) {
    $update_query = $conn->prepare("UPDATE booking_requests SET approval_status = 'อนุมัติ' WHERE booking_id = ?");
    $update_query->bind_param("i", $booking_id);

    if ($update_query->execute()) {
        echo "อนุมัติคำขอแล้ว!";
        header("Location: admin_requests.php");
        exit();
    } else {
        error_log("Error approving booking request: " . $update_query->error);
        echo "เกิดข้อผิดพลาดในการอนุมัติ";
    }
} elseif (isset($_POST['reject'])) {
    $update_query = $conn->prepare("UPDATE booking_requests SET approval_status = 'ไม่อนุมัติ' WHERE booking_id = ?");
    $update_query->bind_param("i", $booking_id);

    if ($update_query->execute()) {
        echo "ไม่อนุมัติคำขอแล้ว!";
        header("Location: admin_requests.php");
        exit();
    } else {
        error_log("Error rejecting booking request: " . $update_query->error);
        echo "เกิดข้อผิดพลาดในการไม่อนุมัติ";
    }
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายละเอียดคำขอจองรถ</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>

<body>
    <h1>รายละเอียดคำขอจองรถ</h1>

    <table>
        <tr>
            <th>ผู้ขอ</th>
            <td><?php echo $booking_request['first_name'] . ' ' . $booking_request['last_name']; ?></td>
        </tr>
        <tr>
            <th>วันที่ขอ</th>
            <td><?php echo $booking_request['request_date']; ?></td>
        </tr>
        <tr>
            <th>เหตุผล</th>
            <td><?php echo $booking_request['reason']; ?></td>
        </tr>
        <tr>
            <th>สถานที่</th>
            <td><?php echo $booking_request['location']; ?></td>
        </tr>
        <tr>
            <th>จำนวนผู้โดยสาร</th>
            <td><?php echo $booking_request['num_passengers']; ?></td>
        </tr>
        <tr>
            <th>จำนวนอาจารย์/เจ้าหน้าที่</th>
            <td><?php echo $booking_request['num_teachers']; ?></td>
        </tr>
        <tr>
            <th>จำนวนนักศึกษา</th>
            <td><?php echo $booking_request['num_students']; ?></td>
        </tr>
        <tr>
            <th>วันที่เดินทาง</th>
            <td><?php echo $booking_request['start_date']; ?></td>
        </tr>
        <tr>
            <th>เวลาเดินทาง</th>
            <td><?php echo $booking_request['start_time']; ?></td>
        </tr>
        <tr>
            <th>วันสิ้นสุดการเดินทาง</th>
            <td><?php echo $booking_request['end_date']; ?></td>
        </tr>
        <tr>
            <th>เวลาสิ้นสุดการเดินทาง</th>
            <td><?php echo $booking_request['end_time']; ?></td>
        </tr>
        <tr>
            <th>ระยะทาง</th>
            <td><?php echo $booking_request['distance_km']; ?></td>
        </tr>
        <tr>
            <th>ผู้ควบคุม</th>
            <td><?php echo $booking_request['supervisor']; ?></td>
        </tr>
        <tr>
            <th>เอกสารโครงการ</th>
            <td>
                <?php if (!empty($booking_request['document_path'])) : ?>
                    <a href="uploads/<?php echo basename($booking_request['document_path']); ?>" target="_blank">ดู/ดาวน์โหลด</a>
                <?php else : ?>
                    ไม่มี
                <?php endif; ?>
            </td>
        </tr>
    </table>

    <?php if ($booking_request['approval_status'] == 'รออนุมัติ') : ?>
        <form method="post">
            <button type="submit" name="approve">อนุมัติ</button>
            <button type="submit" name="reject">ไม่อนุมัติ</button>
        </form>
    <?php endif; ?>

    <?php if ($booking_request['approval_status'] == 'อนุมัติ' && $booking_request['driver_confirmed'] != 'ยืนยันแล้ว') : ?>
        <p><strong>รอการยืนยันจากคนขับ</strong></p>
    <?php endif; ?>

    <?php if ($booking_request['approval_status'] == 'อนุมัติ' && $booking_request['driver_confirmed'] == 'ยืนยันแล้ว') : ?>
        <p><strong>การจองได้รับการยืนยันแล้ว</strong></p>
    <?php endif; ?>

    <?php if ($booking_request['approval_status'] == 'ไม่อนุมัติ') : ?>
        <p><strong>คำขอถูกปฏิเสธแล้ว</strong></p>
    <?php endif; ?>

</body>

</html>