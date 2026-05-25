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

$eczane_id = mysqli_real_escape_string($conn, $data["eczane_id"]);
$ilac_id   = mysqli_real_escape_string($conn, $data["ilac_id"]);
$miktar    = mysqli_real_escape_string($conn, $data["miktar"]);
$islem     = mysqli_real_escape_string($conn, $data["islem"]);

// تحقق هل يوجد سجل مخزون
$kontrol = mysqli_query($conn,
    "SELECT id FROM eczane_stok WHERE eczane_id='$eczane_id' AND ilac_id='$ilac_id'");

if (mysqli_num_rows($kontrol) == 0) {
    // أنشئ سجل جديد
    mysqli_query($conn,
        "INSERT INTO eczane_stok (eczane_id, ilac_id, miktar) VALUES ('$eczane_id', '$ilac_id', 0)");
}

if ($islem == "ekle") {
    $sql = "UPDATE eczane_stok SET miktar = miktar + $miktar 
            WHERE eczane_id='$eczane_id' AND ilac_id='$ilac_id'";
} else {
    $sql = "UPDATE eczane_stok SET miktar = miktar - $miktar 
            WHERE eczane_id='$eczane_id' AND ilac_id='$ilac_id' 
            AND miktar >= $miktar";
}

if (mysqli_query($conn, $sql)) {
    if (mysqli_affected_rows($conn) > 0) {
        echo json_encode(["basarili" => true]);
    } else {
        echo json_encode(["basarili" => false, "mesaj" => "Yetersiz stok!"]);
    }
} else {
    echo json_encode(["basarili" => false]);
}
?>