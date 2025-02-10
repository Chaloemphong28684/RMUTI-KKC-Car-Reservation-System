<?php
session_start();
include 'db.php';  // เชื่อมต่อฐานข้อมูล

// ตรวจสอบการล็อกอินและบทบาทของผู้ใช้
if (!isset($_SESSION['email']) || $_SESSION['type'] != 'admin') {
    header('Location: index.php');
    exit();
}

// ตรวจสอบว่าได้มีการส่งค่า request_id มาหรือไม่
if (!isset($_GET['request_id']) || empty($_GET['request_id'])) {
    echo "ไม่มีค่า request_id ใน URL";
    exit();
}

$booking_id = $_GET['request_id']; // รับค่า request_id จาก URL

// ดึงข้อมูลคำขอจองรถ
$stmt = $conn->prepare("SELECT * FROM booking_requests WHERE booking_id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();

// ตรวจสอบผลลัพธ์
if ($result->num_rows > 0) {
    $booking_request = $result->fetch_assoc();
} else {
    echo "ไม่พบข้อมูลคำขอจองรถที่ตรงกับ ID ที่ให้มา";
    exit();
}

// ดึงข้อมูลชื่อผู้ดูแลระบบ
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT first_name, last_name FROM admin_profile WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$display_name = 'ผู้ดูแลระบบ';
if ($row) {
    $display_name = $row['first_name'] . ' ' . $row['last_name'];
}

// ดึงข้อมูลยานพาหนะทั้งหมด
$vehicles = $conn->query("SELECT license_plate, vehicle_type FROM vehicles");

// ดึงข้อมูลคนขับทั้งหมด
$drivers = $conn->query("SELECT email, first_name, last_name FROM driver_profile");

// กำหนดค่าที่เลือกไว้ให้กับฟอร์ม
$selected_vehicle = $booking_request['license_plate'];
$selected_driver = $booking_request['driver_name'];
$selected_driver_email = $booking_request['driver_email'];

// ตรวจสอบว่าได้ส่งข้อมูลจากฟอร์มหรือไม่
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST['driver_email']) && !empty($_POST['vehicle_license']) && !empty($_POST['request_id'])) {
        $booking_id = $_POST['request_id'];  // รับค่า request_id
        $driver_email = $_POST['driver_email'];
        $vehicle_license = $_POST['vehicle_license'];

        // ดึงชื่อคนขับจากอีเมล
        $driver_query = $conn->prepare("SELECT first_name, last_name FROM driver_profile WHERE email = ?");
        $driver_query->bind_param("s", $driver_email);
        $driver_query->execute();
        $driver_result = $driver_query->get_result();

        if ($driver_result->num_rows > 0) {
            $driver_info = $driver_result->fetch_assoc();
            $driver_name = $driver_info['first_name'] . " " . $driver_info['last_name'];

            // อัปเดตข้อมูลการจอง
            $update_query = $conn->prepare("UPDATE booking_requests SET license_plate = ?, driver_name = ?, driver_email = ?, driver_confirmed = 'รอการยืนยัน' WHERE booking_id = ?");
            $update_query->bind_param("sssi", $vehicle_license, $driver_name, $driver_email, $booking_id);

            if ($update_query->execute()) {
                echo "<script>alert('อัปเดตสำเร็จ!'); window.location.href='admin_status.php?request_id=$booking_id';</script>";
                exit();
            } else {
                echo "เกิดข้อผิดพลาดในการอัปเดต: " . $conn->error;
            }
        } else {
            echo "ไม่พบข้อมูลพนักงานขับรถ.";
        }
    } else {
        echo "กรุณาเลือกพนักงานขับรถและทะเบียนรถ";
    }
}

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>รายละเอียดคำขอจองรถ</title>
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
            margin-left: 360px;
            padding: 15px;
            width: 900px;
        }

        .main-content h2 {
            font-size: 1.5em;
            margin-bottom: 20px;
            color: #004080;
        }

        .main-content form {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }

        .main-content input[type="text"],
        .main-content input[type="file"],
        .main-content select {
            width: 90%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1em;
        }

        .main-content button {
            padding: 10px 20px;
            background-color: #004080;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            font-size: 1em;
        }

        .main-content button:hover {
            background-color: rgb(34, 255, 0);
        }

        .main-content a {
            padding: 10px 20px;
            background-color: #ddd;
            text-decoration: none;
            color: #004080;
            border-radius: 4px;
        }

        .main-content a:hover {
            background-color: pink;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #004080;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        table img {
            max-width: 50px;
            height: auto;
            border-radius: 4px;
        }

        .aa1 {
            margin-top: 70px;
            margin-left: 50px;
            margin-bottom: 20px;
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

    <!-- แถบด้านซ้าย -->
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

    <!-- พื้นที่เนื้อหาหลัก -->
    <div class="main-content">
        <h2 style="margin-top: 70px; margin-bottom: 20px; font-size: 18px;">เลือกรถและพนักงานขับรถ</h2>
        <form method="POST" action="">
            <input type="hidden" name="request_id" value="<?php echo htmlspecialchars($booking_id); ?>">

            <p><strong>E-mail</strong> <?php echo htmlspecialchars($booking_request['email']); ?></p>
            <p><strong>ชื่อผู้</strong> <?php echo htmlspecialchars($booking_request['first_name']); ?></p>
            <p><strong>นามสกุล</strong> <?php echo htmlspecialchars($booking_request['last_name']); ?></p>
            <p><strong>ตำแหน่ง</strong> <?php echo htmlspecialchars($booking_request['position']); ?></p>
            <p><strong>ระดับ</strong> <?php echo htmlspecialchars($booking_request['level']); ?></p>
            <p><strong>ความประสงค์ในการขอใช้รถ</strong> <?php echo htmlspecialchars($booking_request['reason']); ?></p>
            <p><strong>สถานที่</strong> <?php echo htmlspecialchars($booking_request['location']); ?></p>
            <p><strong>จำนวนผู้ร่วมเดินทางทั้งหมด</strong> <?php echo htmlspecialchars($booking_request['num_passengers']); ?></p>
            <p><strong>จำนวนอาจารย์-เจ้าหน้าที่</strong> <?php echo htmlspecialchars($booking_request['num_teachers']); ?></p>
            <p><strong>จำนวนนักศึกษา</strong> <?php echo htmlspecialchars($booking_request['num_students']); ?></p>
            <p><strong>วันที่ทำการจอง</strong> <?php echo htmlspecialchars($booking_request['request_date']); ?></p>
            <p><strong>เดือนที่ทำการจอง</strong> <?php echo htmlspecialchars($booking_request['request_month']); ?></p>
            <p><strong>ปีที่ทำการจอง</strong> <?php echo htmlspecialchars($booking_request['request_year']); ?></p>
            <p><strong>วันที่เริ่มเดินทาง</strong> <?php echo htmlspecialchars($booking_request['start_date']); ?></p>
            <p><strong>เดือนที่เริ่มเดินทาง</strong> <?php echo htmlspecialchars($booking_request['start_month']); ?></p>
            <p><strong>ปีที่เริ่มเดินทาง</strong> <?php echo htmlspecialchars($booking_request['start_year']); ?></p>
            <p><strong>เวลา</strong> <?php echo htmlspecialchars($booking_request['start_time']); ?></p>
            <p><strong>วันที่สิ้นสุดการเดินทาง</strong> <?php echo htmlspecialchars($booking_request['end_date']); ?></p>
            <p><strong>เดือนที่สิ้นสุดการเดินทาง</strong> <?php echo htmlspecialchars($booking_request['end_month']); ?></p>
            <p><strong>ปีที่สิ้นสุดการเดินทาง</strong> <?php echo htmlspecialchars($booking_request['end_year']); ?></p>
            <p><strong>เวลา</strong> <?php echo htmlspecialchars($booking_request['end_time']); ?></p>
            <p><strong>ระยะทาง(กิโลเมตร)</strong> <?php echo htmlspecialchars($booking_request['distance_km']); ?></p>
            <p><strong>ชื่อผู้ควบคุม</strong> <?php echo htmlspecialchars($booking_request['supervisor']); ?></p>
            <p><strong>เอกสารโครงการ</strong>
                <?php
                if (!empty($booking_request['document_path'])) {
                    echo "<a class='view-button' href='uploads/" . basename($booking_request['document_path']) . "' target='_blank'>ดู/ปริ้นไฟล์</a>";
                } else {
                    echo "ไม่มีไฟล์แนบ";
                }
                ?>
            </p>
            <p><strong>ทะเบียนรถ</strong></p>
            <select name="vehicle_license" required>
                <option value="">เลือกทะเบียนรถ</option>
                <?php while ($vehicle = $vehicles->fetch_assoc()): ?>
                    <option value="<?php echo $vehicle['license_plate']; ?>" <?php if ($vehicle['license_plate'] == $selected_vehicle) echo 'selected'; ?>>
                        <?php echo $vehicle['license_plate'] . ' - ' . $vehicle['vehicle_type']; ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <p><strong>ชื่อคนขับ</strong></p>
            <select name="driver_email" required>
                <option value="">เลือกชื่อคนขับ</option>
                <?php while ($driver = $drivers->fetch_assoc()): ?>
                    <option value="<?php echo $driver['email']; ?>" <?php if ($driver['email'] == $selected_driver) echo 'selected'; ?>>
                        <?php echo $driver['first_name'] . ' ' . $driver['last_name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <button type="submit">ยืนยันการเลือก</button>
        </form>
    </div>

</body>

</html>