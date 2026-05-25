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
$metin = strtolower($data["metin"] ?? "");

$result = mysqli_query($conn, "SELECT id, ad, doz FROM ilaclar");
$tumIlaclar = [];
while ($row = mysqli_fetch_assoc($result)) {
    $tumIlaclar[] = $row;
}

$bulunanIlaclar = [];
$eklenenIds = [];

foreach ($tumIlaclar as $ilac) {
    $ilacAdi = strtolower($ilac["ad"]);
    
    // البحث بالاسم الكامل
    if (strpos($metin, $ilacAdi) !== false) {
        if (!in_array($ilac["id"], $eklenenIds)) {
            $bulunanIlaclar[] = $ilac;
            $eklenenIds[] = $ilac["id"];
        }
        continue;
    }
    
    // البحث بأول كلمة فقط
    $kelimeler = explode(" ", $ilacAdi);
    $ilkKelime = $kelimeler[0];
    if (strlen($ilkKelime) > 3 && strpos($metin, $ilkKelime) !== false) {
        if (!in_array($ilac["id"], $eklenenIds)) {
            $bulunanIlaclar[] = $ilac;
            $eklenenIds[] = $ilac["id"];
        }
    }
}

echo json_encode([
    "ilaclar" => $bulunanIlaclar,
    "okunan_metin" => $metin
]);