<?php
require 'connect.php';

// 1. Find all active reservations that are already past today
$result = $conn->query("
    SELECT ReservationID, PdfPath 
    FROM Reservations 
    WHERE Date < CURDATE() AND Status = 'active'
");

while ($row = $result->fetch_assoc()) {
    $reservationID = (int)$row['ReservationID'];
    $pdfPath = $row['PdfPath'];

    // 2. Mark the reservation as expired
    $update = $conn->prepare("UPDATE Reservations SET Status='expired' WHERE ReservationID=?");
    $update->bind_param("i", $reservationID);
    $update->execute();

    // 3. Insert into logs with the actual ReservationID
    $insert = $conn->prepare("INSERT INTO TransactionLogs (ReservationID, PDFPath) VALUES (?, ?)");
    $insert->bind_param("is", $reservationID, $pdfPath);
    $insert->execute();
}

echo "Weekly reset completed successfully.";
?>
