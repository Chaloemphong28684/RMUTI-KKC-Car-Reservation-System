<?php
session_start();
include 'db.php';  // เชื่อมต่อฐานข้อมูล

// ตรวจสอบการล็อกอินและบทบาทของผู้ใช้
if (!isset($_SESSION['email']) || $_SESSION['type'] != 'admin') {
    header('Location: index.php');
    exit();
}

// ดึงข้อมูลคำขอจองรถทั้งหมด
$stmt = $conn->prepare("SELECT * FROM booking_requests");
$stmt->execute();
$result = $stmt->get_result();

// ปิดการเชื่อมต่อฐานข้อมูล
$conn->close();
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>จัดการคำขอจองรถ - ระบบจองรถมหาวิทยาลัย</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            background-color: #f4f4f4;
        }

        header {
            background-color: #004080;
            color: white;
            padding: 10px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: fixed;
            top: 0;
            width: 100%;
            z-index: 1000;
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
            font-size: 1.3em;
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
            margin-left: 300px;
            margin-top: 80px;
            padding: 20px;
            width: calc(100% - 270px);
        }

        .main-content h2 {
            font-size: 1.5em;
            margin-bottom: 20px;
            color: #004080;
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
        <a href="manage_bookings.php">จัดการคำขอจองรถ</a>
        <a href="add_user.php">เพิ่มข้อมูลผู้ใช้</a>
        <a href="add_vehicle.php">เพิ่มข้อมูลรถ</a>
        <a href="add_driver.php">เพิ่มข้อมูลพนักงานขับรถ</a>
        <a href="admin_status.php">รออนุมัติสถานะการจอง</a>
        <a href="change_password.php">เปลี่ยนรหัสผ่าน</a>
        <a href="login.php">ออกจากระบบ</a>
    </div>

    <!-- พื้นที่เนื้อหาหลัก -->
    <div class="main-content">
        <h2>จัดการคำขอจองรถ</h2>
        <table>
            <thead>
                <tr>
                    <th>รหัสคำขอ</th>
                    <th>ชื่อผู้ขอจอง</th>
                    <th>สถานะการอนุมัติ</th>
                    <th>สถานะการยืนยันของคนขับ</th>
                    <th>ดูรายละเอียด</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['booking_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['first_name']) . " " . htmlspecialchars($row['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['approval_status']); ?></td>
                        <td><?php echo htmlspecialchars($row['driver_confirmed']); ?></td>
                        <td><a href="admin_request_details.php?request_id=<?php echo $row['booking_id']; ?>">ดูรายละเอียด</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>

</html>