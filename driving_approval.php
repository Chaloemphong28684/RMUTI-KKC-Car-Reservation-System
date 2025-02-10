<?php
session_start();
include 'db.php';

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['email']) || $_SESSION['type'] != 'driver') {
    header('Location: index.php');
    exit();
}

$email = $_SESSION['email'];

// ดึงข้อมูลการจองจากฐานข้อมูล
$stmt = $conn->prepare("SELECT booking_id, first_name, last_name, start_date, start_month, start_year, end_date, end_month, end_year, start_time, end_time, location, license_plate, driver_status 
                         FROM booking_requests AS br
                         INNER JOIN drivers AS d ON driver_id = driver_id
                         WHERE email = ?"); // เปลี่ยนเงื่อนไข WHERE

if (!$stmt) {
    die("การเตรียมคำสั่ง SQL ล้มเหลว: " . $conn->error);
}

// ผูกตัวแปรกับคำสั่ง SQL
$stmt->bind_param("s", $email);

// การดำเนินการต่อไป
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อนุมัติการขับรถ</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: rgb(241, 241, 241);
            margin: 0;
            padding: 0;
        }

        header {
            background-color: #004080;
            color: white;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo-container {
            display: flex;
            align-items: center;
        }

        .logo-container img {
            height: 50px;
            margin-right: 15px;
        }

        .logo-container h1 {
            margin: 0;
            font-size: 1.5em;
        }

        .sidebar {
            background-color: #f4f4f4;
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 70px;
            left: 0;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
        }

        .sidebar h2 {
            margin-bottom: 20px;
            font-size: 1.2em;
        }

        .sidebar a {
            text-decoration: none;
            color: #004080;
            display: block;
            padding: 10px 0;
            font-size: 1em;
        }

        .sidebar a:hover {
            background-color: #e0e0e0;
            border-radius: 5px;
        }

        .main-content {
            margin-left: 270px;
            padding: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f2f2f2;
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
        <a href="driving_approval.php">อนุมัติการขับรถ</a>
        <a href="change_password3.php">เปลี่ยนรหัสผ่าน</a>
        <a href="logout.php">ออกจากระบบ</a>
    </div>

    <div class="main-content">
        <h2>อนุมัติการขับรถ</h2>

        <table>
            <thead>
                <tr>
                    <th>ลำดับ</th>
                    <th>ชื่อผู้จอง</th>
                    <th>วันที่ไป</th>
                    <th>วันที่กลับ</th>
                    <th>เวลาไป</th>
                    <th>เวลาคืน</th>
                    <th>สถานที่</th>
                    <th>หมายเลขทะเบียนรถ</th>
                    <th>สถานะ</th>
                    <th>อนุมัติ</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result && $result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['booking_id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['start_date']) . " " . htmlspecialchars($row['start_month']) . " " . htmlspecialchars($row['start_year']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['end_date']) . " " . htmlspecialchars($row['end_month']) . " " . htmlspecialchars($row['end_year']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['start_time']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['end_time']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['location']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['license_plate']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['booking_status']) . "</td>";
                        echo "<td><a href='approve_booking.php?booking_id=" . htmlspecialchars($row['booking_id']) . "' class='button'>อนุมัติ</a></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='9'>ไม่พบข้อมูลการจอง</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</body>

</html>

<?php
// ปิดการเชื่อมต่อฐานข้อมูล
$stmt->close();
$conn->close();
?>