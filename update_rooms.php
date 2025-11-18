<?php
// update_rooms.php
require_once __DIR__ . '/connect.php';

// PhpSpreadsheet - make sure vendor/autoload.php exists (composer required)
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo "<p style='color:red;'>Invalid request.</p>";
    exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    echo "<p style='color:red;'>No file uploaded or upload error.</p>";
    exit;
}

// Basic validation: extension
$allowedExt = ['xlsx'];
$filename = $_FILES['file']['name'];
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
if (!in_array($ext, $allowedExt)) {
    echo "<p style='color:red;'>Invalid file type. Only .xlsx allowed.</p>";
    exit;
}

// Move file to a temporary location (optional)
$tmpFile = $_FILES['file']['tmp_name'];

try {
    // Load spreadsheet
    $spreadsheet = IOFactory::load($tmpFile);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray(null, true, true, true); // returns 1-indexed columns A,B,C...

    if (count($rows) <= 1) {
        echo "<p style='color:orange;'>Excel file seems empty (no data rows).</p>";
        exit;
    }

    // Start transaction
    $conn->begin_transaction();

    // Prepare statements for update and insert (upsert behavior)
    $updateStmt = $conn->prepare("
        UPDATE rooms 
        SET RoomName = ?, BuildingID = ?, TimeAvailable = ?, DaysAvailable = ?, DaysOccupied = ?
        WHERE RoomID = ?
    ");
    if (!$updateStmt) throw new Exception("Prepare update failed: " . $conn->error);

    $insertStmt = $conn->prepare("
        INSERT INTO rooms (RoomID, RoomName, BuildingID, TimeAvailable, DaysAvailable, DaysOccupied)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    if (!$insertStmt) throw new Exception("Prepare insert failed: " . $conn->error);

    $rowCount = 0;
    $updated = 0;
    $inserted = 0;
    $skipped = 0;
    $errors = [];

    // Determine header row and column mapping (be robust to different header names)
    // We'll expect first row to be header. Map column index by header name lowercased.
    $header = $rows[1];
    $colMap = []; // map normalized header => column letter
    foreach ($header as $col => $value) {
        $norm = strtolower(trim((string)$value));
        if ($norm === '') continue;
        $colMap[$norm] = $col;
    }

    // Expected keys (flexible)
    // Try to find columns by common header names:
    $tryKeys = [
        'roomid' => ['roomid', 'room id', 'id'],
        'roomname' => ['roomname', 'room name', 'name'],
        'buildingid' => ['buildingid', 'building id', 'building'],
        'timeavailable' => ['timeavailable', 'time available', 'time'],
        'daysavailable' => ['daysavailable', 'days available', 'days'],
        'daysoccupied' => ['daysoccupied', 'days occupied', 'occupied']
    ];

    $cols = [];
    foreach ($tryKeys as $key => $variants) {
        $cols[$key] = null;
        foreach ($variants as $v) {
            if (isset($colMap[$v])) {
                $cols[$key] = $colMap[$v];
                break;
            }
        }
    }

    // If some required columns missing, fall back to A-F mapping
    if (!$cols['roomid'] || !$cols['roomname'] || !$cols['buildingid']) {
        // assume columns: A=RoomID, B=RoomName, C=BuildingID, D=TimeAvailable, E=DaysAvailable, F=DaysOccupied
        $cols = [
            'roomid' => 'A',
            'roomname' => 'B',
            'buildingid' => 'C',
            'timeavailable' => 'D',
            'daysavailable' => 'E',
            'daysoccupied' => 'F'
        ];
    }

    // Iterate rows starting from 2 (skip header)
    foreach ($rows as $index => $row) {
        if ($index == 1) continue; // header

        // read by column letter in $cols
        $roomID_raw = isset($row[$cols['roomid']]) ? $row[$cols['roomid']] : null;
        $roomName_raw = isset($row[$cols['roomname']]) ? $row[$cols['roomname']] : null;
        $buildingID_raw = isset($row[$cols['buildingid']]) ? $row[$cols['buildingid']] : null;
        $timeAvail_raw = isset($row[$cols['timeavailable']]) ? $row[$cols['timeavailable']] : '';
        $daysAvail_raw = isset($row[$cols['daysavailable']]) ? $row[$cols['daysavailable']] : '';
        $daysOcc_raw = isset($row[$cols['daysoccupied']]) ? $row[$cols['daysoccupied']] : '';

        // sanitize
        $roomID = is_numeric($roomID_raw) ? intval($roomID_raw) : null;
        $roomName = trim((string)$roomName_raw);
        $buildingID = is_numeric($buildingID_raw) ? intval($buildingID_raw) : null;
        $timeAvail = trim((string)$timeAvail_raw);
        $daysAvail = trim((string)$daysAvail_raw);
        $daysOcc = trim((string)$daysOcc_raw);

        // skip rows with no RoomID or no RoomName
        if (empty($roomID) || $roomName === '') {
            $skipped++;
            continue;
        }

        $rowCount++;

        // Try update first
        $updateStmt->bind_param("sisssi", $roomName, $buildingID, $timeAvail, $daysAvail, $daysOcc, $roomID);
        if (!$updateStmt->execute()) {
            $errors[] = "Row $index update error: " . $updateStmt->error;
            continue;
        }

        if ($updateStmt->affected_rows > 0) {
            $updated++;
        } else {
            // no existing row updated -> insert
            $insertStmt->bind_param("isisss", $roomID, $roomName, $buildingID, $timeAvail, $daysAvail, $daysOcc);
            if (!$insertStmt->execute()) {
                $errors[] = "Row $index insert error: " . $insertStmt->error;
                continue;
            }
            $inserted++;
        }
    }

    // Commit transaction
    $conn->commit();

    $msgParts = [];
    $msgParts[] = "<p style='color:green; font-weight:bold;'>Database update complete.</p>";
    $msgParts[] = "<p>Processed rows: <strong>$rowCount</strong></p>";
    $msgParts[] = "<p>Updated: <strong>$updated</strong> | Inserted: <strong>$inserted</strong> | Skipped: <strong>$skipped</strong></p>";

    if (!empty($errors)) {
        $msgParts[] = "<details><summary>Errors (" . count($errors) . ")</summary><ul>";
        foreach ($errors as $err) {
            $msgParts[] = "<li>" . htmlspecialchars($err) . "</li>";
        }
        $msgParts[] = "</ul></details>";
    }

    echo implode("\n", $msgParts);

} catch (ReaderException $re) {
    if (isset($conn) && $conn->connect_errno === 0) $conn->rollback();
    echo "<p style='color:red;'>Error reading Excel file: " . htmlspecialchars($re->getMessage()) . "</p>";
    exit;
} catch (Exception $e) {
    if (isset($conn) && $conn->connect_errno === 0) $conn->rollback();
    echo "<p style='color:red;'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}
