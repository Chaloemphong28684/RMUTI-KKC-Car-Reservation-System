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

// ดึงข้อมูลคำขอจองที่ได้รับการยืนยันแล้ว
$sql = "SELECT * FROM booking_requests 
        WHERE driver_email = ? AND driver_confirmed = 'ยืนยันแล้ว' 
        ORDER BY 
        CASE 
            WHEN job_completed = 'ยังไม่สิ้นสุด' THEN 1 
            ELSE 2 
        END, 
        start_year DESC, start_month DESC, start_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();


?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รถที่ต้องขับ</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        /* รวมสไตล์ที่เหมือนกันจากหน้าเดิม */
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
            width: 95%;
            
            border-collapse: collapse;
            margin-left: 2%;
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
            width: 1250px;
            height: 500px;
            margin: 20px 0 0 270px;
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
        <h2 class="s1">สถานะ/รายการที่ต้องขับ</h2>
        <div class="tb1">
            <table>
                <thead>
                    <tr>
                        <th>รหัสคำขอ</th>
                        <th>ชื่อผู้ขอ</th>
                        <th>สถานที่</th>
                        <th>จำนวนผู้โดยสาร</th>
                        <th>เริ่มต้น</th>
                        <th>สิ้นสุด</th>
                        <th>ทะเบียนรถ</th>
                        <th>ชื่อคนขับ</th>
                        <th>สถานะการอนุมัติ</th>
                        <th>สิ้นสุดการขับรถ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = $result->fetch_assoc()) { ?>
                        <tr style="background-color: <?php echo ($booking['job_completed'] == 'สิ้นสุดแล้ว') ? '#d4edda' : 'white'; ?>;">
                            <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                            <td><?php echo htmlspecialchars($booking['first_name']) . " " . htmlspecialchars($booking['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($booking['location']); ?></td>
                            <td><?php echo htmlspecialchars($booking['num_passengers']); ?></td>
                            <td><?php echo htmlspecialchars($booking['start_date']) . "/" . htmlspecialchars($booking['start_month']) . "/" . htmlspecialchars($booking['start_year']) . " " . htmlspecialchars($booking['start_time']); ?></td>
                            <td><?php echo htmlspecialchars($booking['end_date']) . "/" . htmlspecialchars($booking['end_month']) . "/" . htmlspecialchars($booking['end_year']) . " " . htmlspecialchars($booking['end_time']); ?></td>
                            <td><?php echo htmlspecialchars($booking['license_plate']); ?></td>
                            <td><?php echo htmlspecialchars($booking['driver_name']); ?></td>
                            <td style="color: 
                <?php
                        if ($booking['approval_status'] == 'อนุมัติ') {
                            echo 'green';
                        } elseif ($booking['approval_status'] == 'ไม่อนุมัติ') {
                            echo 'red';
                        } else {
                            echo 'black';
                        }
                ?>; font-weight: bold;">
                                <?php echo htmlspecialchars($booking['approval_status']); ?>
                            </td>
                            <td>
                                <?php if ($booking['job_completed'] == 'ยังไม่สิ้นสุด') { ?>
                                    <form action="complete_job.php" method="POST">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                        <button type="submit" class="button">สิ้นสุดการขับรถ</button>
                                    </form>
                                <?php } else { ?>
                                    <span style="color: red; font-weight: bold;">สิ้นสุดแล้ว</span>
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>


            </table>
        </div>
    </div>

</body>

</html>