<?php
session_start();
include 'db.php';  // เชื่อมต่อฐานข้อมูล

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $booking_id = $_POST['booking_id'];
    $driver_email = $_SESSION['email'];
    $vehicle_license = $_POST['vehicle_license'];

    // อัปเดตสถานะการจอง
    $stmt = $conn->prepare("UPDATE booking_requests SET driver_name = ?, license_plate = ?, status = 'รออนุมัติ' WHERE booking_id = ?");
    $stmt->bind_param("ssi", $driver_email, $vehicle_license, $booking_id);
    if ($stmt->execute()) {
        header("Location: driver_confirmation.php");
    } else {
        echo "เกิดข้อผิดพลาดในการอัปเดตข้อมูล";
    }
}

$booking_id = $_GET['request_id'];
$stmt = $conn->prepare("SELECT * FROM booking_requests WHERE booking_id = ?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$booking_request = $result->fetch_assoc();

// ดึงข้อมูลรถและคนขับ
$vehicles = $conn->query("SELECT license_plate, vehicle_type FROM vehicles");
$drivers = $conn->query("SELECT email, first_name, last_name FROM driver_profile");

$conn->close();
?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <title>ยืนยันการจอง</title>
</head>

<body>
    <h1>ยืนยันการจองของคุณ</h1>
    <form action="confirm_driver.php" method="POST">
        <input type="hidden" name="booking_id" value="<?php echo $booking_request['booking_id']; ?>">

        <p><strong>ชื่อผู้ขอจอง:</strong> <?php echo htmlspecialchars($booking_request['first_name'] . " " . $booking_request['last_name']); ?></p>
        <p><strong>วันที่เริ่มเดินทาง:</strong> <?php echo htmlspecialchars($booking_request['start_date']) . "/" . htmlspecialchars($booking_request['start_month']) . "/" . htmlspecialchars($booking_request['start_year']); ?></p>

        <label for="vehicle_license">เลือกทะเบียนรถ:</label>
        <select name="vehicle_license" id="vehicle_license" required>
            <?php while ($vehicle = $vehicles->fetch_assoc()) { ?>
                <option value="<?php echo $vehicle['license_plate']; ?>"><?php echo $vehicle['license_plate'] . " - " . $vehicle['vehicle_type']; ?></option>
            <?php } ?>
        </select>

        <label for="driver_email">เลือกพนักงานขับรถ:</label>
        <select name="driver_email" id="driver_email" required>
            <?php while ($driver = $drivers->fetch_assoc()) { ?>
                <option value="<?php echo $driver['email']; ?>"><?php echo $driver['first_name'] . " " . $driver['last_name']; ?></option>
            <?php } ?>
        </select>

        <button type="submit">ยืนยันการจอง</button>
    </form>
</body>

</html>