<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$conn = mysqli_connect("mysql-1048c291-samehgamilalkaw-8c23.c.aivencloud.com", "avnadmin", "AVNS_kEbMKil4toRW7irfnHO", "pharmacy_system1", 26397);

$data = json_decode(file_get_contents("php://input"), true);
$kullanici = mysqli_real_escape_string($conn, $data["kullanici_adi"]);
$sifre = mysqli_real_escape_string($conn, $data["sifre"]);

$kontrol = mysqli_query($conn, "SELECT * FROM kullanicilar WHERE kullanici_adi='$kullanici'");

if (mysqli_num_rows($kontrol) > 0) {
    echo json_encode(["basarili" => false, "mesaj" => "Kullanıcı zaten var!"]);
} else {
    $sql = "INSERT INTO kullanicilar (kullanici_adi, sifre, rol) VALUES ('$kullanici', '$sifre', 'hasta')";
    if (mysqli_query($conn, $sql)) {
        echo json_encode(["basarili" => true]);
    } else {
        echo json_encode(["basarili" => false, "mesaj" => "Hata!"]);
    }
}
?>
