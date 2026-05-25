<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$conn = mysqli_connect("localhost", "root", "", "pharmacy_system1");
$barkod = mysqli_real_escape_string($conn, $_GET["barkod"]);
$sql = "SELECT * FROM ilaclar WHERE barkod='$barkod'";
$result = mysqli_query($conn, $sql);

$ilaclar = [];
while ($row = mysqli_fetch_assoc($result)) {
    $ilaclar[] = [
        "id"   => $row["id"],
        "ad"   => $row["ad"],
        "doz"  => $row["doz"],
        "barkod" => $row["barkod"],
    ];
}
echo json_encode($ilaclar);
?>