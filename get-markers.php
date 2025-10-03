<?php
session_start();
$host = "localhost";
$dbname = "irkhr_digiwaste";
$username = "irkhr_david";
$password = "david1711#";

// Povezivanje s bazom podataka
$conn = new mysqli($host, $username, $password, $dbname);
if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "Greška u povezivanju s bazom: " . $conn->connect_error]));
}

// Dohvati sve prijavljene lokacije otpada
$query = "SELECT lat, lng, image_url, description, category, created_at, email, active FROM markers2 ORDER BY created_at DESC";
$result = $conn->query($query);

$markers = [];
while ($row = $result->fetch_assoc()) {
    $markers[] = $row;
}

header('Content-Type: application/json');
echo json_encode([
    "success" => true,
    "data" => $markers,
    "unique_emails" => count($emails)
]);
$conn->close();
?>
