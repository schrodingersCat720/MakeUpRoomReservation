<?php
error_reporting(E_ERROR | E_PARSE);
header('Content-Type: application/json');

require 'connect.php';
require_once __DIR__ . '/vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// Retrieve POST
$instructor = $_POST['instructor'] ?? '';
$subject    = $_POST['subject'] ?? '';
$course     = $_POST['course'] ?? '';
$campus     = $_POST['campus'] ?? '';
$building   = $_POST['building'] ?? '';
$time       = $_POST['time'] ?? '';
$roomID     = $_POST['roomID'] ?? null;

// Normalize date to YYYY-MM-DD
$date = '';
if (!empty($_POST['date'])) {
  $date = date('Y-m-d', strtotime($_POST['date']));
}

if (!$instructor || !$subject || !$course || !$campus || !$building || !$date || !$time || !$roomID) {
  echo json_encode(['success' => false, 'message' => 'Missing required fields']);
  exit;
}

// Prevent duplicate reservation
$check = $conn->prepare("SELECT COUNT(*) FROM Reservations 
                         WHERE RoomID = ? AND Date = ? AND Time = ? AND Status = 'active'");
$check->bind_param("iss", $roomID, $date, $time);
$check->execute();
$check->bind_result($count);
$check->fetch();
$check->close();

if ($count > 0) {
  echo json_encode(['success' => false, 'message' => 'This room is already reserved for that date and time']);
  exit;
}

// Insert reservation
$stmt = $conn->prepare("
  INSERT INTO Reservations 
  (InstructorName, SubjectCode, CourseSection, Campus, Building, Date, Time, RoomID, Status) 
  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')
");
$stmt->bind_param("sssssssi", $instructor, $subject, $course, $campus, $building, $date, $time, $roomID);

if (!$stmt->execute()) {
  echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
  exit;
}

$reservationID = $stmt->insert_id;

// PDF folder
$pdfDir = __DIR__ . '/reservation_slips';
if (!is_dir($pdfDir)) mkdir($pdfDir, 0777, true);

// Generate PDF
$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$html = "
<h2 style='text-align:center;'>Pamantasan ng Lungsod ng Valenzuela</h2>
<h3 style='text-align:center;'>Make-Up Class Reservation Slip</h3>
<table border='1' cellpadding='6' cellspacing='0' width='100%'>
<tr><td><b>Instructor:</b></td><td>$instructor</td></tr>
<tr><td><b>Subject:</b></td><td>$subject</td></tr>
<tr><td><b>Course:</b></td><td>$course</td></tr>
<tr><td><b>Campus:</b></td><td>$campus</td></tr>
<tr><td><b>Building:</b></td><td>$building</td></tr>
<tr><td><b>Date:</b></td><td>$date</td></tr>
<tr><td><b>Time:</b></td><td>$time</td></tr>
</table>
<p style='margin-top:40px;'>Admin Signature: ________________________</p>
<p style='text-align:right;'>Instructor Signature: ________________________</p>
";

$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$pdfName = "reservation_$reservationID.pdf";
file_put_contents("$pdfDir/$pdfName", $dompdf->output());

// Save path
$conn->query("UPDATE Reservations SET PdfPath='reservation_slips/$pdfName' WHERE ReservationID=$reservationID");

// Log transaction
$stmt = $conn->prepare("INSERT INTO TransactionLogs (ReservationID, PDFPath, LoggedAt) VALUES (?, ?, NOW())");
$pdfPath = "reservation_slips/$pdfName";
$stmt->bind_param("is", $reservationID, $pdfPath);
$stmt->execute();

echo json_encode([
  'success' => true,
  'pdf' => $pdfPath
]);
?>
