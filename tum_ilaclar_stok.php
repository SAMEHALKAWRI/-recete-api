<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$conn = mysqli_connect("sql7.freesqldatabase.com", "sql7827892", "e5UCW2qCwC", "sql7827892");

$result = mysqli_query($conn, "
    SELECT i.id, i.ad, i.barkod, i.doz,
           COALESCE(SUM(s.miktar), 0) AS toplam_stok
    FROM ilaclar i
    LEFT JOIN eczane_stok s ON i.id = s.ilac_id
    GROUP BY i.id, i.ad, i.barkod, i.doz
    ORDER BY i.ad ASC
");

$ilaclar = [];
while ($row = mysqli_fetch_assoc($result)) {
    $ilaclar[] = $row;
}

echo json_encode($ilaclar);
mysqli_close($conn);
?>