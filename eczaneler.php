<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$conn = mysqli_connect("mysql-1048c291-samehgamilalkaw-8c23.c.aivencloud.com", "avnadmin", "AVNS_kEbMKil4toRW7irfnHO", "pharmacy_system1");

if (!$conn) {
    echo json_encode(["error" => mysqli_connect_error()]);
    exit();
}

$sql = "SELECT * FROM eczaneler";
$result = mysqli_query($conn, $sql);

if (!$result) {
    echo json_encode(["error" => mysqli_error($conn)]);
    exit();
}

$eczaneler = [];
while ($row = mysqli_fetch_assoc($result)) {
    $eczaneler[] = [
        "id" => $row["id"],
        "ad" => $row["ad"],
        "adres" => $row["adres"],
        "telefon" => $row["telefon"],
    ];
}
echo json_encode($eczaneler);
?>