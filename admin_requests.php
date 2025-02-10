<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// Connect to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "university_vehicle_booking";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch admin name from database
$email = $_SESSION['email'];
$stmt = $conn->prepare("SELECT first_name, last_name FROM admin_profile WHERE email = ?");
$stmt->bind_param("s", $email);

if (!$stmt->execute()) {
    die("Error executing query: " . $stmt->error);
}

$result = $stmt->get_result();
$row = $result->fetch_assoc();
$display_name = $row ? $row['first_name'] . ' ' . $row['last_name'] : 'ผู้ดูแลระบบ';

// Fetch all booking requests
$sql = "SELECT * FROM booking_requests";
$result = $conn->query($sql);

if ($result === false) {
    die("Error fetching booking requests: " . $conn->error);
}
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


        /* เพิ่ม margin ให้เนื้อหา */
        .container {
            margin-left: 220px;
            padding: 15px;
            width: calc(100% - 220px);
        }

        table {
            width: 80%;
            /* ใช้ความกว้าง 80% ของหน้าจอ */
            margin-left: 17%;
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
            width: 1200px;
            margin: 20px 0 0 280px;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            font-size: small;
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
        <a href="admin_dashboard.php">หน้าหลัก</a>
        <a href="admin_profile.php">ข้อมูลส่วนตัว</a>
        <a href="admin_requests.php">จัดการคำขอจองรถ</a>
        <a href="add_user.php">เพิ่มข้อมูลผู้ใช้</a>
        <a href="add_vehicle.php">เพิ่มข้อมูลรถ</a>
        <a href="add_driver.php">เพิ่มข้อมูลพนักงานขับรถ</a>
        <a href="admin_status.php">รออนุมัติสถานะการจอง</a>
        <a href="change_password.php">เปลี่ยนรหัสผ่าน</a>
        <a href="logout.php">ออกจากระบบ</a>
    </div>


    <h2 style="margin: 80px 0 20px 300px; color:#333; font-size: 18px">คำขอจองรถทั้งหมด</h2>
    <table class="tb1">
        <tr>
            <th>E-mail</th>
            <th>ชื่อ</th>
            <th>นามสกุล</th>
            <th>ตำแหน่ง</th>
            <th>ระดับ</th>
            <th>ความประสงค์</th>
            <th>วันที่ทำการจอง</th>
            <th>เดือนที่ทำการจอง</th>
            <th>ปีที่ทำการจอง</th>
            <th>เอกสารโครงการ</th>
            <th>เลือกรถ/คนขับ</th>
        </tr>
        <?php if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['position']); ?></td>
                    <td><?php echo htmlspecialchars($row['level']); ?></td>
                    <td><?php echo htmlspecialchars($row['reason']); ?></td>
                    <td><?php echo htmlspecialchars($row['request_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['request_month']); ?></td>
                    <td><?php echo htmlspecialchars($row['request_year']); ?></td>

                    <td>
                        <?php if (!empty($row['document_path'])) { ?>
                            <a class='view-button' href='uploads/<?php echo basename($row['document_path']); ?>' target='_blank'>ดู/ปริ้นไฟล์</a>
                        <?php } else {
                            echo "ไม่มีไฟล์แนบ";
                        } ?>
                    </td>
                    <td><a href="booking_details.php?request_id=<?php echo $row['booking_id']; ?>">เลือกรถ/คนขับ</a></td>
                </tr>
        <?php }
        } else {
            echo "<tr><td colspan='11'>ไม่มีข้อมูลคำขอจองรถ</td></tr>";
        } ?>
    </table>

</body>