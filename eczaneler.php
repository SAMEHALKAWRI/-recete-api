<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");

$conn = mysqli_connect("sql7.freesqldatabase.com", "sql7827892", "e5UCW2qCwC", "sql7827892");
$sql = "SELECT * FROM eczaneler";
$result = mysqli_query($conn, $sql);

$eczaneler = [];
while ($row = mysqli_fetch_assoc($result)) {
    $eczaneler[] = [
        "id" => $row["id"],
        "ad" => $row["ad"],
        "adres" => $row["adres"],
        "telefon" => $row["telefon"],
    ];
}
echo json_encode($eczaneler);
?>