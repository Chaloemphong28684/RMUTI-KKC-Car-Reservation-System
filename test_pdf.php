<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

// ตั้งค่าตัวเลือก
$options = new Options();
$fontPath = __DIR__ . "/fonts/Sarabun-Regular.ttf";
$options->set('chroot', __DIR__);

$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);


// HTML ที่ใช้สร้าง PDF
$html = '
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <style>
        @font-face {
            font-family: "Sarabun";
            src: url("fonts/Sarabun-Regular.ttf") format("truetype");
        }
        body {
            font-family: "Sarabun", sans-serif;
        }
    </style>
</head>
<body>
    <p>ทดสอบภาษาไทย</p>
    <p>สวัสดีดอร่า</p>
</body>
</html>
';

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("test.pdf", ["Attachment" => false]);
