<?php
session_start();
include 'db.php';  // เชื่อมต่อฐานข้อมูล

// ตรวจสอบการล็อกอินและบทบาทของผู้ใช้
if (!isset($_SESSION['email']) || $_SESSION['type'] != 'driver') {
    header('Location: login.php');
    exit();
}

$email = $_SESSION['email'];
$sql = "SELECT first_name, last_name FROM driver_profile WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$driver = $result->fetch_assoc();
$display_name = $driver ? ($driver['first_name'] . ' ' . $driver['last_name']) : "ไม่พบข้อมูล";

// ตรวจสอบการยืนยันคำขอ
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['confirm_booking'])) {
    $booking_id = $_POST['booking_id'];

    $sql = "UPDATE booking_requests SET driver_confirmed = 'ยืนยันแล้ว' WHERE booking_id = ? AND driver_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $booking_id, $email);
    $stmt->execute();
}

// ดึงข้อมูลคำขอจองที่ยังไม่ได้รับการยืนยันจากคนขับ
$requests = $conn->prepare("SELECT * FROM booking_requests WHERE driver_email = ? AND driver_confirmed = 'รอการยืนยัน'");
$requests->bind_param("s", $email);
$requests->execute();
$result = $requests->get_result();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันคำขอจอง</title>
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

        .table-container {
            background-color: white;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #004080;
            color: white;
        }

        .button {
            background-color: #004080;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }

        .button:hover {
            background-color: #003366;
        }

        .s1 {
            margin: 80px 0 20px 300px;
            color: #333;
            font-size: 18px
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
            <img src="uploads/RMUTI_KORAT.png" alt="Logo">
            <h1>ระบบจองรถมหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน วิทยาเขตขอนแก่น</h1>
        </div>
    </header>

    <div class="sidebar">
        <h2>สวัสดี, <?php echo htmlspecialchars($display_name); ?></h2>
        <a href="driver_dashboard.php">หน้าหลัก</a>
        <a href="driver_profile.php">ข้อมูลส่วนตัว</a>
        <a href="driver_confirmation.php">อนุมัติการขับรถ</a>
        <a href="driver_dashboard2.php">สถานะ/รายการต้องขับ</a>
        <a href="change_password3.php">เปลี่ยนรหัสผ่าน</a>
        <a href="logout.php">ออกจากระบบ</a>
    </div>

    <div>
        <h2 class="s1">อนุมัติการขับรถ</h2>
        <div class="tb1">
            <table>
                <thead>
                    <tr>
                        <th>รหัสคำขอ</th>
                        <th>ชื่อผู้ขอ</th>
                        <th>สถานะการยืนยัน</th>
                        <th>ยืนยันการขับ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                            <td><?php echo htmlspecialchars($booking['first_name']) . " " . htmlspecialchars($booking['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['driver_confirmed']); ?></td>
                            <td>
                                <form method="POST" action="driver_confirmation.php">
                                    <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                    <button type="submit" name="confirm_booking" class="button">ยืนยันการจอง</button>
                                </form>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>