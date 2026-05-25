<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$conn = mysqli_connect("sql7.freesqldatabase.com", "sql7827892", "e5UCW2qCwC", "sql7827892");

if (!$conn) {
    echo json_encode(["error" => "DB connection failed: " . mysqli_connect_error()]);
    exit();
}

$barkod = mysqli_real_escape_string($conn, $_GET["barkod"] ?? "");

$ilaclar_sql = "SELECT id, ad, doz FROM ilaclar WHERE barkod='$barkod'";
$ilaclar_result = mysqli_query($conn, $ilaclar_sql);

if (!$ilaclar_result) {
    echo json_encode(["error" => mysqli_error($conn)]);
    exit();
}

$ilaclar = [];
while ($row = mysqli_fetch_assoc($ilaclar_result)) {
    $ilaclar[] = $row;
}

if (empty($ilaclar)) {
    echo json_encode(["ilaclar" => [], "tam_eslesme" => [], "kismi_eslesme" => []]);
    exit();
}

$ilac_ids = array_column($ilaclar, 'id');
$ilac_ids_str = implode(',', $ilac_ids);

$stok_sql = "SELECT s.ilac_id, s.miktar, i.ad as ilac_adi,
             e.id as eczane_id, e.ad, e.adres, e.telefon, e.enlem, e.boylam
             FROM eczane_stok s
             JOIN eczaneler e ON s.eczane_id = e.id
             JOIN ilaclar i ON s.ilac_id = i.id
             WHERE s.ilac_id IN ($ilac_ids_str) AND s.miktar > 0";

$stok_result = mysqli_query($conn, $stok_sql);

if (!$stok_result) {
    echo json_encode(["error" => mysqli_error($conn)]);
    exit();
}

$eczane_ilac_map = [];
$eczane_info = [];
$eczane_ilac_names = [];

while ($row = mysqli_fetch_assoc($stok_result)) {
    $eid = $row['eczane_id'];
    $iid = $row['ilac_id'];

    if (!isset($eczane_ilac_map[$eid])) {
        $eczane_ilac_map[$eid] = [];
        $eczane_ilac_names[$eid] = [];
        $eczane_info[$eid] = [
            "id"      => $eid,
            "ad"      => $row['ad'],
            "adres"   => $row['adres'],
            "telefon" => $row['telefon'],
            "enlem"   => $row['enlem'],
            "boylam"  => $row['boylam'],
        ];
    }
    $eczane_ilac_map[$eid][] = $iid;
    $eczane_ilac_names[$eid][] = $row['ilac_adi'];
}

$tam_eslesme = [];
$kismi_eslesme = [];

foreach ($eczane_ilac_map as $eid => $iids) {
    $info = $eczane_info[$eid];
    $bulunan = count(array_unique($iids));
    $toplam = count($ilaclar);
    $info['bulunan_ilac'] = $bulunan;
    $info['toplam_ilac'] = $toplam;
    $info['mevcut_ilaclar'] = array_values(array_unique($eczane_ilac_names[$eid]));

    if ($bulunan >= $toplam) {
        $info['tip'] = 'tam';
        $tam_eslesme[] = $info;
    } else {
        $info['tip'] = 'kismi';
        $kismi_eslesme[] = $info;
    }
}

echo json_encode([
    "ilaclar"       => $ilaclar,
    "tam_eslesme"   => $tam_eslesme,
    "kismi_eslesme" => $kismi_eslesme,
]);
?>