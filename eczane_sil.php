<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$conn = mysqli_connect("sql7.freesqldatabase.com", "sql7827892", "e5UCW2qCwC", "sql7827892");

$data = json_decode(file_get_contents("php://input"), true);
$id = mysqli_real_escape_string($conn, $data["id"]);

$sql = "DELETE FROM eczaneler WHERE id='$id'";

if (mysqli_query($conn, $sql)) {
    echo json_encode(["basarili" => true]);
} else {
    echo json_encode(["basarili" => false, "hata" => mysqli_error($conn)]);
}
?>