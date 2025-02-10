<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['type'] != 'faculty') {
    header('Location: user_booking_status.php');
    exit();
}

$email = $_SESSION['email'];

// การเชื่อมต่อกับฐานข้อมูล
$servername = "localhost"; // ชื่อเซิร์ฟเวอร์
$username = "root"; // ชื่อผู้ใช้ฐานข้อมูล
$password = ""; // รหัสผ่านฐานข้อมูล
$dbname = "university_vehicle_booking"; // ชื่อฐานข้อมูลที่ถูกต้อง

// สร้างการเชื่อมต่อ
$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ดึงข้อมูลชื่อจากฐานข้อมูล
$stmt = $conn->prepare("SELECT first_name, last_name FROM user_profile WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

// ตั้งค่าชื่อที่จะแสดง
if ($row) {
    $display_name = $row['first_name'] . ' ' . $row['last_name'];
} else {
    $display_name = 'ผู้ขอจองรถมหาวิทยาลัย';
}

// ดึงข้อมูลคำขอจองรถของอีเมลปัจจุบัน
$sql = "SELECT * FROM booking_requests WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();


?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>คำขอจองรถทั้งหมด</title>
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

        table {
            width: 80%;
            margin: 2% auto;
            margin-left: 16%;
            margin-top: 4%;
            /* กำหนด margin ให้ตารางอยู่กลาง */
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
            padding-right: 20px;
            /* เพิ่ม padding ด้านขวาของเซลล์ */
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

        .view-button:hover {
            background-color: #217DBB;
        }

        a {
            color: #3498DB;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
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

        .g1 {
            margin: 70px 0 10px 310px;
            margin-top: 5%;
            font-size: 19px;
        }
    </style>
</head>

<body>
    <header>
        <div class="logo-container">
            <img src="uploads/RMUTI_KORAT.png" alt="ตราสัญลักษณ์">
            <h1>ระบบจองรถมหาวิทยาลัยเทคโนโลยีราชมงคลอีสาน วิทยาเขตขอนแก่น</h1>
        </div>
    </header>

    <div class="sidebar">
        <h2>สวัสดี, <?php echo htmlspecialchars($display_name); ?></h2>
        <a href="faculty_dashboard.php">หน้าหลัก</a>
        <a href="faculty_profile.php">ข้อมูลส่วนตัว</a>
        <a href="request_vehicle.php">คำขอจองรถ</a>
        <a href="user_booking_status.php">สถานะการจองรถ</a>
        <a href="change_password2.php">เปลี่ยนรหัสผ่าน</a>
        <a href="logout.php">ออกจากระบบ</a>
    </div>

    <h2 class="g1">สถานะการจองรถ</h2>
    <table class="tb1">
        <tr>
            <th>ชื่อ-นามสกุล</th>
            <th>วัน</th>
            <th>เดือน</th>
            <th>ปี</th>
            <th>ความประสงค์</th>
            <th>หมายเลขทะเบียนรถ</th>
            <th>พนักงานขับรถ</th>
            <th>สถานะ</th>
        </tr>
        <?php if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['request_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['request_month']); ?></td>
                    <td><?php echo htmlspecialchars($row['request_year']); ?></td>
                    <td><?php echo htmlspecialchars($row['reason']); ?></td>
                    <td><?php echo htmlspecialchars($row['license_plate']); ?></td>
                    <td><?php echo htmlspecialchars($row['driver_name']); ?></td>
                    <td><?php
                        // ตรวจสอบสถานะการอนุมัติ
                        if (isset($row['approval_status']) && !empty($row['approval_status'])) {
                            echo htmlspecialchars($row['approval_status']);
                        } else {
                            echo "ไม่มีสถานะ"; // หากไม่มีสถานะในฐานข้อมูล
                        }
                        ?></td>
                </tr>
        <?php }
        } else {
            echo "<tr><td colspan='6'>ไม่มีข้อมูลคำขอจองรถ</td></tr>";
        } ?>
    </table>
</body>

</html>