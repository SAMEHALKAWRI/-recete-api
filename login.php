<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$conn = mysqli_connect("mysql-1048c291-samehgamilalkaw-8c23.c.aivencloud.com", "avnadmin", "AVNS_kEbMKil4toRW7irfnHO", "pharmacy_system1");
$data = json_decode(file_get_contents("php://input"), true);
$kullanici = mysqli_real_escape_string($conn, $data["kullanici_adi"]);
$sifre = mysqli_real_escape_string($conn, $data["sifre"]);

$sql = "SELECT k.*, e.ad as eczane_adi 
        FROM kullanicilar k
        LEFT JOIN eczaneler e ON k.eczane_id = e.id
        WHERE k.kullanici_adi='$kullanici' AND k.sifre='$sifre'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    echo json_encode([
        "basarili"   => true,
        "rol"        => $row["rol"] ?? "hasta",
        "eczane_id"  => $row["eczane_id"] ?? null,
        "eczane_adi" => $row["eczane_adi"] ?? null,
    ]);
} else {
    echo json_encode(["basarili" => false]);
}
?>