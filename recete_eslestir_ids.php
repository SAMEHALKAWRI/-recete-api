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
$ilac_ids = $data["ilac_ids"] ?? [];

if (empty($ilac_ids)) {
    echo json_encode(["tam_eslesme" => [], "kismi_eslesme" => []]);
    exit();
}

$ids_str = implode(',', array_map('intval', $ilac_ids));
$toplam = count($ilac_ids);

$sql = "SELECT s.ilac_id, i.ad as ilac_adi,
        e.id as eczane_id, e.ad, e.adres, e.telefon, e.enlem, e.boylam
        FROM eczane_stok s
        JOIN eczaneler e ON s.eczane_id = e.id
        JOIN ilaclar i ON s.ilac_id = i.id
        WHERE s.ilac_id IN ($ids_str) AND s.miktar > 0";

$result = mysqli_query($conn, $sql);

$eczane_map = [];
$eczane_names = [];

while ($row = mysqli_fetch_assoc($result)) {
    $eid = $row['eczane_id'];
    if (!isset($eczane_map[$eid])) {
        $eczane_map[$eid] = [];
        $eczane_names[$eid] = [];
        $eczane_info[$eid] = [
            "id" => $eid,
            "ad" => $row['ad'],
            "adres" => $row['adres'],
            "telefon" => $row['telefon'],
            "enlem" => $row['enlem'],
            "boylam" => $row['boylam'],
        ];
    }
    if (!in_array($row['ilac_id'], $eczane_map[$eid])) {
        $eczane_map[$eid][] = $row['ilac_id'];
        $eczane_names[$eid][] = $row['ilac_adi'];
    }
}

$tam = [];
$kismi = [];

foreach ($eczane_map as $eid => $iids) {
    $info = $eczane_info[$eid];
    $info['bulunan_ilac'] = count($iids);
    $info['toplam_ilac'] = $toplam;
    $info['mevcut_ilaclar'] = array_values(array_unique($eczane_names[$eid]));

    if (count($iids) >= $toplam) {
        $info['tip'] = 'tam';
        $tam[] = $info;
    } else {
        $info['tip'] = 'kismi';
        $kismi[] = $info;
    }
}

echo json_encode([
    "tam_eslesme" => $tam,
    "kismi_eslesme" => $kismi,
]);