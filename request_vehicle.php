<?php
session_start();
if (!isset($_SESSION['email']) || $_SESSION['type'] != 'faculty') {
    header('Location: index.php');
    exit();
}

// เชื่อมต่อฐานข้อมูล
$host = 'localhost';
$username = 'root';
$password = '';
$dbname = 'university_vehicle_booking';

$conn = new mysqli($host, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ตรวจสอบว่าผู้ใช้ได้เข้าสู่ระบบหรือไม่
if (!isset($_SESSION['email'])) {
    die("Please log in first.");
}



$email = $_SESSION['email']; // กำหนดค่าก่อนใช้

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


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // รับค่าจากฟอร์ม
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $position = $_POST['position'];
    $level = $_POST['level'];
    $reason = $_POST['reason'];
    $location = $_POST['location'];
    $num_passengers = intval($_POST['num_passengers']);
    $num_teachers = intval($_POST['num_teachers']);
    $num_students = intval($_POST['num_students']);
    $request_date = $_POST['request_date'];
    $request_month = $_POST['request_month'];
    $request_year = $_POST['request_year'];
    $start_date = $_POST['start_date'];
    $start_month = $_POST['start_month'];
    $start_year = $_POST['start_year'];
    $start_time = $_POST['start_time'];
    $end_date = $_POST['end_date'];
    $end_month = $_POST['end_month'];
    $end_year = $_POST['end_year'];
    $end_time = $_POST['end_time'];
    $distance_km = floatval($_POST['distance_km']);
    $supervisor = $_POST['supervisor'];

    // การอัปโหลดเอกสาร
    $document_path = "";
    if (isset($_FILES['document_path']) && $_FILES['document_path']['error'] == 0) {
        $document_path = 'uploads/' . basename($_FILES['document_path']['name']);
        move_uploaded_file($_FILES['document_path']['tmp_name'], $document_path);
    }

    // คำสั่ง SQL
    $sql = "INSERT INTO booking_requests (email, first_name, last_name, position, level, reason, location, num_passengers, num_teachers, num_students, request_date, request_month, request_year, start_date, start_month, start_year, start_time, end_date, end_month, end_year, end_time, distance_km, supervisor, document_path) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        die('MySQL prepare error: ' . $conn->error);
    }

    // ผูกพารามิเตอร์กับคำสั่ง SQL
    $stmt->bind_param(
        "ssssssiissssssssssssssds",
        $email,
        $first_name,
        $last_name,
        $position,
        $level,
        $reason,
        $location,
        $num_passengers,
        $num_teachers,
        $num_students,
        $request_date,
        $request_month,
        $request_year,
        $start_date,
        $start_month,
        $start_year,
        $start_time,
        $end_date,
        $end_month,
        $end_year,
        $end_time,
        $distance_km,
        $supervisor,
        $document_path
    );

    if ($stmt->execute()) {
        echo "บันทึกข้อมูลเรียบร้อย";
    } else {
        echo "เกิดข้อผิดพลาด: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>คำขอจองรถมหาวิทยาลัย</title>
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

        /* เนื้อหาหลักที่อยู่ฝั่งขวา */
        .container {
            margin-left: 220px;
            padding: 15px;
            width: calc(100% - 220px);
        }

        /* ฟอร์ม */
        form {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            width: 500px;
            margin: 20px auto;
            font-size: small;
        }

        label {
            display: block;
            font-weight: bold;
            font-size: 13px;
            margin-top: 10px;
            color: #333;
        }

        input,
        select,
        textarea {
            width: 460px;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 10px;
            transition: border 0.3s;
        }

        input:focus,
        select:focus,
        textarea:focus {
            border-color: rgb(0, 244, 57);
            outline: none;
        }

        input[type="submit"] {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 12px;
            margin-top: 15px;
            cursor: pointer;
            font-size: 16px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #218838;
        }

        button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 18px;
            margin-top: 15px;
            cursor: pointer;
            font-size: 12px;
            border-radius: 5px;
            transition: background-color 0.3s, transform 0.2s;
        }

        button[type="submit"] {
            background-color: #28a745;
        }

        button[type="reset"] {
            background-color: #dc3545;
        }

        button:hover {
            background-color: #0056b3;
            transform: scale(1.05);
        }

        button[type="submit"]:hover {
            background-color: #218838;
        }

        button[type="reset"]:hover {
            background-color: #c82333;
        }

        select {
            width: 200px;
            /* ความกว้างของ select */
            padding: 10px;
            /* ระยะห่างภายใน */
            border-radius: 5px;
            /* ขอบโค้ง */
            border: 1px solid #ccc;
            /* ขอบของ select */
            background-color: #f8f9fa;
            /* สีพื้นหลัง */
            font-size: 10px;
            /* ขนาดฟอนต์ */
            color: #333;
            /* สีข้อความ */
        }

        select:focus {
            border-color: rgb(0, 190, 60);
            /* เปลี่ยนสีขอบเมื่อมีการเลือก */
            outline: none;
            /* เอากรอบออกเมื่อเลือก */
        }

        option {
            padding: 10px;
            /* ระยะห่างภายในของ option */
            font-size: 12px;
            /* ขนาดฟอนต์ */
            color: #333;
            /* สีข้อความใน option */
            background-color: #f8f9fa;
            /* สีพื้นหลังของ option */
        }

        option:hover {
            background-color: rgb(0, 255, 26);
            /* สีพื้นหลังของ option เมื่อเอาเมาส์ไปวาง */
            color: white;
            /* สีข้อความใน option เมื่อเอาเมาส์ไปวาง */
        }

        input[type="time"] {
            width: 200px;
            padding: 10px;
            border-radius: 5px;
            /* ขอบโค้ง */
            border: 1px solid #ccc;
            /* ขอบสีเทา */
            background-color: #f8f9fa;
            /* สีพื้นหลัง */
            font-size: 16px;
            /* ขนาดฟอนต์ */
            color: #333;
            /* สีข้อความ */
        }

        input[type="time"]:focus {
            border-color: #004080;
            /* สีขอบเมื่อมีการเลือก */
            outline: none;
            /* เอากรอบออกเมื่อเลือก */
        }

        input[type="time"]::-webkit-outer-spin-button,
        input[type="time"]::-webkit-inner-spin-button {
            -webkit-appearance: none;
            /* เอาปุ่มขึ้น-ลงออก */
            margin: 0;
        }

        input[type="time"]::-webkit-input-placeholder {
            color: #aaa;
            /* สีของ placeholder */
        }

        .ss1 {
            margin: 80px 80px 30px 300px;
            color: #333;
            font-size: 14px
        }
    </style>
</head>

<body>

    <!-- แถบด้านบน -->
    <header>
        <div class="logo-container">
            <img src="uploads/RMUTI_KORAT.png" alt="โลโก้มหาวิทยาลัย">
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

    <div>
        <div class="ss1">
            <h2>ข้อมูลการขอจองรถมหาวิทยาลัย</h2>
        </div>
    </div>
    <!-- ฟอร์มกรอกข้อมูล -->
    <form class="main-content" method="POST" action="" enctype="multipart/form-data">
        <label for="first_name">ชื่อ :</label>
        <input type="text" id="first_name" name="first_name" required><br><br>

        <label for="last_name">นามสกุล :</label>
        <input type="text" id="last_name" name="last_name" required><br><br>

        <label for="position">ตำแหน่ง :</label>
        <input type="text" id="position" name="position" required><br><br>

        <label for="level">ระดับ :</label>
        <input type="text" id="level" name="level" required><br><br>

        <label for="reason">ความประสงค์ในการขอใช้รถ :</label>
        <textarea id="reason" name="reason" required></textarea><br><br>

        <label for="location">สถานที่ :</label>
        <input type="text" id="location" name="location" required><br><br>

        <label for="num_passengers">จำนวนผู้ร่วมเดินทางทั้งหมด :</label>
        <input type="number" id="num_passengers" name="num_passengers" required><br><br>

        <label for="num_teachers">จำนวนอาจารย์-เจ้าหน้าที่ :</label>
        <input type="number" id="num_teachers" name="num_teachers" required><br><br>

        <label for="num_students">จำนวนนักศึกษา :</label>
        <input type="number" id="num_students" name="num_students" required><br><br>

        <label for="request_date">วันที่ทำการจองรถ :</label>
        <select id="request_date" name="request_date" required>
            <option value="01">1</option>
            <option value="02">2</option>
            <option value="03">3</option>
            <option value="04">4</option>
            <option value="05">5</option>
            <option value="06">6</option>
            <option value="07">7</option>
            <option value="08">8</option>
            <option value="09">9</option>
            <option value="10">10</option>
            <option value="11">11</option>
            <option value="12">12</option>
            <option value="13">13</option>
            <option value="14">14</option>
            <option value="15">15</option>
            <option value="16">16</option>
            <option value="17">17</option>
            <option value="18">18</option>
            <option value="19">19</option>
            <option value="20">20</option>
            <option value="21">21</option>
            <option value="22">22</option>
            <option value="23">23</option>
            <option value="24">24</option>
            <option value="25">25</option>
            <option value="26">26</option>
            <option value="27">27</option>
            <option value="28">28</option>
            <option value="29">29</option>
            <option value="30">30</option>
            <option value="31">31</option>
        </select>
        <br><br>

        <label for="request_month">เดือนที่ทำการจองรถ :</label>
        <select id="request_month" name="request_month" required>
            <option value="01">มกราคม</option>
            <option value="02">กุมภาพันธ์</option>
            <option value="03">มีนาคม</option>
            <option value="04">เมษายน</option>
            <option value="05">พฤษภาคม</option>
            <option value="06">มิถุนายน</option>
            <option value="07">กรกฎาคม</option>
            <option value="08">สิงหาคม</option>
            <option value="09">กันยายน</option>
            <option value="10">ตุลาคม</option>
            <option value="11">พฤศจิกายน</option>
            <option value="12">ธันวาคม</option>
        </select>
        <br><br>

        <label for="request_year">ปีที่ทำการจองรถ :</label>
        <select id="request_year" name="request_year" required>
            <option value="2567">2567</option>
            <option value="2568">2568</option>
            <option value="2569">2569</option>
            <option value="2570">2570</option>
            <option value="2571">2571</option>
            <option value="2572">2572</option>
            <option value="2573">2573</option>
            <option value="2574">2574</option>
            <option value="2575">2575</option>
            <option value="2576">2576</option>
            <option value="2577">2577</option>
            <option value="2578">2578</option>

        </select>
        <br><br>

        <label for="start_date">วันที่เริ่มเดินทาง :</label>
        <select id="start_date" name="start_date" required>
            <option value="01">1</option>
            <option value="02">2</option>
            <option value="03">3</option>
            <option value="04">4</option>
            <option value="05">5</option>
            <option value="06">6</option>
            <option value="07">7</option>
            <option value="08">8</option>
            <option value="09">9</option>
            <option value="10">10</option>
            <option value="11">11</option>
            <option value="12">12</option>
            <option value="13">13</option>
            <option value="14">14</option>
            <option value="15">15</option>
            <option value="16">16</option>
            <option value="17">17</option>
            <option value="18">18</option>
            <option value="19">19</option>
            <option value="20">20</option>
            <option value="21">21</option>
            <option value="22">22</option>
            <option value="23">23</option>
            <option value="24">24</option>
            <option value="25">25</option>
            <option value="26">26</option>
            <option value="27">27</option>
            <option value="28">28</option>
            <option value="29">29</option>
            <option value="30">30</option>
            <option value="31">31</option>
        </select>
        <br><br>

        <label for="start_month">เดือนที่เริ่มเดินทาง :</label>
        <select id="start_month" name="start_month" required>
            <option value="01">มกราคม</option>
            <option value="02">กุมภาพันธ์</option>
            <option value="03">มีนาคม</option>
            <option value="04">เมษายน</option>
            <option value="05">พฤษภาคม</option>
            <option value="06">มิถุนายน</option>
            <option value="07">กรกฎาคม</option>
            <option value="08">สิงหาคม</option>
            <option value="09">กันยายน</option>
            <option value="10">ตุลาคม</option>
            <option value="11">พฤศจิกายน</option>
            <option value="12">ธันวาคม</option>
        </select>
        <br><br>

        <label for="start_year">ปีที่เริ่มเดินทาง :</label>
        <select id="start_year" name="start_year" required>
            <option value="2567">2567</option>
            <option value="2568">2568</option>
            <option value="2569">2569</option>
            <option value="2570">2570</option>
            <option value="2571">2571</option>
            <option value="2572">2572</option>
            <option value="2573">2573</option>
            <option value="2574">2574</option>
            <option value="2575">2575</option>
            <option value="2576">2576</option>
            <option value="2577">2577</option>
            <option value="2578">2578</option>

        </select>
        <br><br>

        <label for="start_time">เวลา :</label>
        <input type="time" id="start_time" name="start_time" required><br><br>

        <label for="end_date">วันที่สิ้นสุดการเดินทาง :</label>
        <select id="end_date" name="end_date" required>
            <option value="01">1</option>
            <option value="02">2</option>
            <option value="03">3</option>
            <option value="04">4</option>
            <option value="05">5</option>
            <option value="06">6</option>
            <option value="07">7</option>
            <option value="08">8</option>
            <option value="09">9</option>
            <option value="10">10</option>
            <option value="11">11</option>
            <option value="12">12</option>
            <option value="13">13</option>
            <option value="14">14</option>
            <option value="15">15</option>
            <option value="16">16</option>
            <option value="17">17</option>
            <option value="18">18</option>
            <option value="19">19</option>
            <option value="20">20</option>
            <option value="21">21</option>
            <option value="22">22</option>
            <option value="23">23</option>
            <option value="24">24</option>
            <option value="25">25</option>
            <option value="26">26</option>
            <option value="27">27</option>
            <option value="28">28</option>
            <option value="29">29</option>
            <option value="30">30</option>
            <option value="31">31</option>
        </select>
        <br><br>

        <label for="end_month">เดือนที่สิ้นสุดการเดินทาง :</label>
        <select id="end_month" name="end_month" required>
            <option value="01">มกราคม</option>
            <option value="02">กุมภาพันธ์</option>
            <option value="03">มีนาคม</option>
            <option value="04">เมษายน</option>
            <option value="05">พฤษภาคม</option>
            <option value="06">มิถุนายน</option>
            <option value="07">กรกฎาคม</option>
            <option value="08">สิงหาคม</option>
            <option value="09">กันยายน</option>
            <option value="10">ตุลาคม</option>
            <option value="11">พฤศจิกายน</option>
            <option value="12">ธันวาคม</option>
        </select>
        <br><br>

        <label for="end_year">ปีที่สิ้นสุดการเดินทาง :</label>
        <select id="end_year" name="end_year" required>
            <option value="2567">2567</option>
            <option value="2568">2568</option>
            <option value="2569">2569</option>
            <option value="2570">2570</option>
            <option value="2571">2571</option>
            <option value="2572">2572</option>
            <option value="2573">2573</option>
            <option value="2574">2574</option>
            <option value="2575">2575</option>
            <option value="2576">2576</option>
            <option value="2577">2577</option>
            <option value="2578">2578</option>

        </select>
        <br><br>

        <label for="end_time">เวลา:</label>
        <input type="time" id="end_time" name="end_time" required><br><br>

        <label for="distance_km">ระยะทาง (กิโลเมตร):</label>
        <input type="number" id="distance_km" name="distance_km" step="0.01" required><br><br>

        <label for="supervisor">ผู้ควบคุมรถ :</label>
        <input type="text" id="supervisor" name="supervisor" required><br><br>

        <label for="document_path">เอกสารโครงการ : <p style="color: #c82333;">กรุณาแนบไฟล์ pdf</p></label>
        <input type="file" id="document_path" name="document_path" required><br><br>

        <button type="submit">ยืนยัน</button>
        <button type="reset">ยกเลิก</button>
    </form>