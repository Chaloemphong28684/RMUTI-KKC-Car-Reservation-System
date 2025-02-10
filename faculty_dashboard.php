<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['type'] != 'faculty') {
    header('Location: faculty_dashboard.php');
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

// ดึงข้อมูลการจองจากฐานข้อมูล
$events = [];
$query = "SELECT license_plate, start_datetime, end_datetime FROM calendar_bookings";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $events[] = [
            'title' => 'ทะเบียน: ' . $row['license_plate'],
            'start' => $row['start_datetime'],
            'end' => $row['end_datetime']
        ];
    }
} else {
    die("Query Failed: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แดชบอร์ดอาจารย์/เจ้าหน้าที่</title>
    <link rel="stylesheet" href="css/styles.css">
    <link href='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.css' rel='stylesheet' />
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@5.11.0/main.min.js'></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: rgb(241, 241, 241);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

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

        .main-content {
            margin-left: 220px;
            padding: 15px;
            width: calc(100% - 220px);
        }

        .main-content h2 {
            font-size: 1.2em;
            margin-bottom: 15px;
        }

        #calendar {
            max-width: 55%;
            margin: 0 auto;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 10px;
            padding: 8px;
            background-color: #fff;
            font-size: 0.9em;
        }

        .ss1 {
            margin-top: 70px;
            margin-left: 50px;
            margin-bottom: 20px;
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
        <a href="faculty_dashboard.php">หน้าหลัก</a>
        <a href="faculty_profile.php">ข้อมูลส่วนตัว</a>
        <a href="request_vehicle.php">คำขอจองรถ</a>
        <a href="user_booking_status.php">สถานะการจองรถ</a>
        <a href="change_password2.php">เปลี่ยนรหัสผ่าน</a>
        <a href="logout.php">ออกจากระบบ</a>
    </div>

    <div class="main-content">
        <div class="ss1">
            <h2>ปฏิทินการจองรถของมหาวิทยาลัย</h2>
        </div>
        <div id='calendar'></div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var events = <?php echo json_encode($events); ?>;

            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'th',
                events: events,
                editable: false,
                selectable: false
            });

            calendar.render();
        });
    </script>
</body>

</html>