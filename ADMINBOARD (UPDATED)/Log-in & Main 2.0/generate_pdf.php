<?php
require 'connect.php';

$id = $_GET['id'] ?? null;
if (!$id) die("No reservation ID.");

$stmt = $conn->prepare("SELECT * FROM Reservations WHERE ReservationID = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();
if (!$res) die("Reservation not found.");

$pdfPath = $res['PdfPath'];
if (!$pdfPath || !file_exists($pdfPath)) {
    die("PDF not found.");
}

header("Content-type: application/pdf");
readfile($pdfPath);
?>
