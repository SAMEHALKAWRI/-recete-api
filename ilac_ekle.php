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

$ad        = mysqli_real_escape_string($conn, $data["ad"]);
$barkod    = mysqli_real_escape_string($conn, $data["barkod"]);
$doz       = mysqli_real_escape_string($conn, $data["doz"]);
$miktar    = intval($data["miktar"] ?? 0);
$eczane_id = mysqli_real_escape_string($conn, $data["eczane_id"] ?? "1");

// تحقق هل الدواء موجود بنفس الباركود والجرعة
$kontrol = mysqli_query($conn,
    "SELECT id FROM ilaclar WHERE barkod='$barkod' AND doz='$doz'");

if (mysqli_num_rows($kontrol) > 0) {
    // الدواء موجود → زد المخزون لهذه الصيدلية فقط
    $row = mysqli_fetch_assoc($kontrol);
    $ilac_id = $row["id"];

    $stok = mysqli_query($conn,
        "SELECT id FROM eczane_stok 
         WHERE eczane_id='$eczane_id' AND ilac_id='$ilac_id'");

    if (mysqli_num_rows($stok) > 0) {
        mysqli_query($conn,
            "UPDATE eczane_stok 
             SET miktar = miktar + $miktar 
             WHERE eczane_id='$eczane_id' AND ilac_id='$ilac_id'");
    } else {
        mysqli_query($conn,
            "INSERT INTO eczane_stok (eczane_id, ilac_id, miktar) 
             VALUES ('$eczane_id', '$ilac_id', $miktar)");
    }

    echo json_encode([
        "basarili" => true,
        "mesaj"    => "Stok güncellendi! "
    ]);
} else {
    // دواء جديد → أضفه لـ ilaclar
    $sql = "INSERT INTO ilaclar (ad, barkod, doz) 
            VALUES ('$ad', '$barkod', '$doz')";

    if (mysqli_query($conn, $sql)) {
        $ilac_id = mysqli_insert_id($conn);

        // أضفه لمخزون هذه الصيدلية
        mysqli_query($conn,
            "INSERT INTO eczane_stok (eczane_id, ilac_id, miktar) 
             VALUES ('$eczane_id', '$ilac_id', $miktar)");

        echo json_encode([
            "basarili" => true,
            "mesaj"    => "Yeni ilaç eklendi! "
        ]);
    } else {
        echo json_encode(["basarili" => false]);
    }
}
?>