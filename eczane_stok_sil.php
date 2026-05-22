<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "pharmacy_system1");
$data = json_decode(file_get_contents("php://input"), true);

$ilac_id   = mysqli_real_escape_string($conn, $data["ilac_id"]);
$eczane_id = mysqli_real_escape_string($conn, $data["eczane_id"]);

$sql = "DELETE FROM eczane_stok 
        WHERE ilac_id='$ilac_id' AND eczane_id='$eczane_id'";

if (mysqli_query($conn, $sql)) {
    echo json_encode(["basarili" => true]);
} else {
    echo json_encode(["basarili" => false]);
}
?>