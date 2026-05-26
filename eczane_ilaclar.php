<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$conn = mysqli_connect("mysql-1048c291-samehgamilalkaw-8c23.c.aivencloud.com", "avnadmin", "AVNS_kEbMKil4toRW7irfnHO", "pharmacy_system1", 26397);
$eczane_id = mysqli_real_escape_string($conn, $_GET["eczane_id"]);

$sql = "SELECT i.id, i.ad, i.barkod, i.doz,
        COALESCE(s.miktar, 0) as miktar FROM ilaclar i
        INNER JOIN eczane_stok s ON i.id = s.ilac_id 
        WHERE s.eczane_id = '$eczane_id'
        ORDER BY i.ad";

$result = mysqli_query($conn, $sql);
$ilaclar = [];
while ($row = mysqli_fetch_assoc($result)) {
    $ilaclar[] = $row;
}
echo json_encode($ilaclar);
