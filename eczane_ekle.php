<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// معالجة طلب OPTIONS (مهم للـ Flutter)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// الاتصال بقاعدة البيانات
$conn = mysqli_connect("sql7.freesqldatabase.com", "sql7827892", "e5UCW2qCwC", "sql7827892");

// التحقق من الاتصال
if (!$conn) {
    echo json_encode([
        "basarili" => false,
        "hata" => "Database connection failed"
    ]);
    exit;
}

// استقبال البيانات من Flutter
$data = json_decode(file_get_contents("php://input"), true);

// تنظيف البيانات
$ad = mysqli_real_escape_string($conn, $data["ad"]);
$adres = mysqli_real_escape_string($conn, $data["adres"]);
$telefon = mysqli_real_escape_string($conn, $data["telefon"]);
$enlem = mysqli_real_escape_string($conn, $data["enlem"]);
$boylam = mysqli_real_escape_string($conn, $data["boylam"]);

// إدخال البيانات في جدول الصيدليات
$sql = "INSERT INTO eczaneler (ad, adres, telefon, enlem, boylam) 
        VALUES ('$ad', '$adres', '$telefon', '$enlem', '$boylam')";

// تنفيذ العملية
if (mysqli_query($conn, $sql)) {
    echo json_encode([
        "basarili" => true
    ]);
} else {
    echo json_encode([
        "basarili" => false,
        "hata" => mysqli_error($conn)
    ]);
}

// إغلاق الاتصال
mysqli_close($conn);
?>