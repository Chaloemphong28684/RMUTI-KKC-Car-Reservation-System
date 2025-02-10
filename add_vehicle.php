<?php
// เริ่มต้น Session
session_start();

// เชื่อมต่อกับฐานข้อมูล
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "university_vehicle_booking"; // ชื่อฐานข้อมูลใหม่

$conn = new mysqli($servername, $username, $password, $dbname);

// ตรวจสอบการเชื่อมต่อ
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// ตรวจสอบการล็อกอิน
if (!isset($_SESSION['email'])) {
    header("Location: login.php");
    exit();
}

// รับอีเมลจาก Session
$email = $_SESSION['email'];

// ดึงข้อมูลชื่อผู้ดูแลระบบ
$stmt = $conn->prepare("SELECT first_name, last_name FROM admin_profile WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$display_name = ($row) ? $row['first_name'] . ' ' . $row['last_name'] : 'ผู้ดูแลระบบ';

// รับค่าจากฟอร์ม
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $license_plate = $_POST['license_plate'];
    $vehicle_type = $_POST['vehicle_type'];
    $seating_capacity = $_POST['seating_capacity'];
    $fuel_type = $_POST['fuel_type'];
    $faculty = $_POST['faculty'];

    // จัดการการอัปโหลดภาพ
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["vehicle_image"]["name"]);
    move_uploaded_file($_FILES["vehicle_image"]["tmp_name"], $target_file);

    // เพิ่มข้อมูลเข้าสู่ฐานข้อมูล
    $sql = "INSERT INTO vehicles (license_plate, vehicle_type, seating_capacity, fuel_type, faculty, vehicle_image) 
            VALUES ('$license_plate', '$vehicle_type', '$seating_capacity', '$fuel_type', '$faculty', '$target_file')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('เพิ่มข้อมูลรถสำเร็จ!');
                window.location.href = 'add_vehicle.php';
              </script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// ดึงข้อมูลรถทั้งหมดจากฐานข้อมูล
$vehicle_sql = "SELECT * FROM vehicles";
$vehicle_result = $conn->query($vehicle_sql);
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เพิ่มรถ - ระบบจองรถ</title>
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


        h2 {
            margin-bottom: 20px;
        }

        /* ฟอร์มการเพิ่มข้อมูล */
        form {
            width: 500px;
            background-color: white;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }

        label {
            display: block;
            margin-top: 10px;
            font-size: 1em;
            color: #333;
        }

        input,
        select,
        button {
            width: 360px;
            padding: 10px;
            margin-top: 8px;
            font-size: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }

        .buttons {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
        }

        button[type="submit"],
        button[type="reset"] {
            width: 150px;
            background-color: #004080;
            color: white;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        button[type="submit"]:hover {
            background-color: rgb(0, 255, 38);
        }

        button[type="reset"]:hover {
            background-color: rgb(255, 0, 0);
        }

        /* ปรับตารางให้ขยับมาทางขวา */
        .content {
            margin-left: 690px;
            margin-bottom: 10px;
            padding: 15px;
            width: 360px;
            height: auto;
            font-size: small;
        }

        /* ปรับขนาดคอลัมน์ในตารางให้เท่ากัน */
        table {
            width: 100%;
            border-collapse: collapse;;
            margin-bottom: 10px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        table th,
        table td {
            padding: 12px;
            text-align: left;
            border: 1px solid #ddd;
            width: 16%;
            /* กำหนดความกว้างของคอลัมน์ให้เท่ากัน */
        }

        table th {
            background-color: #004080;
            color: white;
        }

        table img {
            width: 80px;
            height: auto;
            border-radius: 4px;
        }

        h3 {
            margin-left: 5%;
        }

        .g1 {
            margin: 70px 0 10px 310px;
            margin-top: 5%;
            font-size: 19px;
        }

        .tb2 {
            margin-left: 300px;
            padding: 5px;
            width: 1200px;
            font-size: small;

        }
    </style>
</head>

<body>
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
        <a href="logout.php">ออกจากระบบ</a>
    </div>

    <!-- เนื้อหาหลักที่อยู่ฝั่งขวา -->
    <div>
        <h2 class="g1">เพิ่มข้อมูลรถ</h2>
        <form class="content" action="add_vehicle.php" method="POST" enctype="multipart/form-data">
            <label for="license_plate">หมายเลขทะเบียนรถ:</label>
            <input type="text" name="license_plate" required>

            <label for="vehicle_type">ประเภทของรถ:</label>
            <select name="vehicle_type" required>
                <option value="รถตู้">รถตู้</option>
                <option value="รถกระบะ">รถกระบะ</option>
                <option value="รถเก๋ง">รถเก๋ง</option>
                <option value="มินิบัส">มินิบัส</option>
                <option value="รถสองแถว">รถสองแถว</option>
                <option value="รถมอเตอร์ไซต์">รถมอเตอร์ไซต์</option>
                <option value="รถมอเตอร์ไซต์">รถบัส</option>
            </select>

            <label for="seating_capacity">ความจุของที่นั่ง:</label>
            <input type="number" name="seating_capacity" required min="1" max="20">

            <label for="fuel_type">ประเภทเชื้อเพลิง:</label>
            <select name="fuel_type" required>
                <option value="น้ำมันเบนซิน">น้ำมันเบนซิน</option>
                <option value="แก็สโซฮอล 91">แก็สโซฮอล 91</option>
                <option value="แก็สโซฮอล 95">แก็สโซฮอล 95</option>
                <option value="ดีเซล">ดีเซล</option>
                <option value="ไฟฟ้า">ไฟฟ้า</option>
                <option value="ไฮบริด">ไฮบริด</option>
            </select>

            <label for="faculty">คณะที่ใช้บริการ:</label>
            <select name="faculty" required>
                <option value="คณะวิทยาศาสตร์และเทคโนโลยี">คณะครุศาสตร์อุตสาหกรรม</option>
                <option value="คณะวิศวกรรมศาสตร์">คณะวิศวกรรมศาสตร์</option>
                <option value="คณะบริหารธุรกิจ">คณะบริหารธุรกิจ</option>
                <option value="คณะบริหารธุรกิจ">สำนักวิทยาเขต</option>
            </select>

            <label for="vehicle_image">อัปโหลดภาพรถ:</label>
            <input type="file" name="vehicle_image" accept="image/*" required>

            <div class="buttons">
                <button type="submit" name="add_vehicle">เพิ่มข้อมูลรถ</button>
                <button type="reset">ลบข้อมูล</button>
            </div>
        </form>

        <h3>ข้อมูลรถที่เพิ่ม:</h3>
        <table class="tb2">
            <thead>
                <tr>
                    <th>หมายเลขทะเบียน</th>
                    <th>ประเภทของรถ</th>
                    <th>ความจุที่นั่ง</th>
                    <th>ประเภทเชื้อเพลิง</th>
                    <th>คณะที่ใช้บริการ</th>
                    <th>ภาพรถ</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($vehicle_row = $vehicle_result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($vehicle_row['license_plate']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle_row['vehicle_type']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle_row['seating_capacity']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle_row['fuel_type']); ?></td>
                        <td><?php echo htmlspecialchars($vehicle_row['faculty']); ?></td>
                        <td><img src="<?php echo htmlspecialchars($vehicle_row['vehicle_image']); ?>" alt="Vehicle Image"></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>

</html>