<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$conn = mysqli_connect("localhost", "root", "", "pharmacy_system1");

$data = json_decode(file_get_contents("php://input"), true);
$kullanici = mysqli_real_escape_string($conn, $data["kullanici_adi"]);
$yeni_sifre = mysqli_real_escape_string($conn, $data["yeni_sifre"]);

$kontrol = mysqli_query($conn, "SELECT * FROM kullanicilar WHERE kullanici_adi='$kullanici'");

if (mysqli_num_rows($kontrol) == 0) {
    echo json_encode(["basarili" => false, "mesaj" => "Kullanıcı bulunamadı!"]);
} else {
    $sql = "UPDATE kullanicilar SET sifre='$yeni_sifre' WHERE kullanici_adi='$kullanici'";
    if (mysqli_query($conn, $sql)) {
        echo json_encode(["basarili" => true]);
    } else {
        echo json_encode(["basarili" => false]);
    }
}
?>