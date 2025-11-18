<?php
require_once __DIR__ . '/connect.php';
require_once __DIR__ . '/vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { echo "<p style='color:red;'>Invalid request.</p>"; exit; }
$table = $_POST['table'] ?? '';
if (!in_array($table,['rooms','teachers','subjects'])) { echo "<p style='color:red;'>Invalid table selected.</p>"; exit; }

if (!isset($_FILES['file']) || $_FILES['file']['error']!==UPLOAD_ERR_OK) { echo "<p style='color:red;'>No file uploaded or upload error.</p>"; exit; }
$filename = $_FILES['file']['name'];
$ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
if (!in_array($ext,['xlsx'])) { echo "<p style='color:red;'>Invalid file type.</p>"; exit; }

$tmpFile = $_FILES['file']['tmp_name'];

try {
    $spreadsheet = IOFactory::load($tmpFile);
    $rows = $spreadsheet->getActiveSheet()->toArray(null,true,true,true);
    if (count($rows)<=1){ echo "<p style='color:orange;'>Excel file seems empty.</p>"; exit; }

    $conn->begin_transaction();
    $rowCount=0;$updated=0;$inserted=0;$skipped=0;$errors=[];

    switch($table){
        case 'rooms':
            foreach($rows as $i=>$row){ if($i==1) continue;
                $roomID = intval($row['A']??0); $roomName = trim($row['B']??''); $buildingID=intval($row['C']??0);
                $timeAvail=trim($row['D']??''); $daysAvail=trim($row['E']??''); $daysOcc=trim($row['F']??'');
                if(!$roomID||$roomName===''){$skipped++; continue;} $rowCount++;
                $update = $conn->prepare("UPDATE rooms SET RoomName=?, BuildingID=?, TimeAvailable=?, DaysAvailable=?, DaysOccupied=? WHERE RoomID=?");
                $update->bind_param("sisssi",$roomName,$buildingID,$timeAvail,$daysAvail,$daysOcc,$roomID); $update->execute();
                if($update->affected_rows>0){$updated++;} else {
                    $insert = $conn->prepare("INSERT INTO rooms (RoomID,RoomName,BuildingID,TimeAvailable,DaysAvailable,DaysOccupied) VALUES (?,?,?,?,?,?)");
                    $insert->bind_param("isisss",$roomID,$roomName,$buildingID,$timeAvail,$daysAvail,$daysOcc);
                    if(!$insert->execute()) $errors[]="Row $i insert error: ".$insert->error; else $inserted++;
                }
            }
            break;

        case 'teachers':
            foreach($rows as $i=>$row){ if($i==1) continue;
                $teacherID=intval($row['A']??0); $name=trim($row['B']??''); $dept=trim($row['C']??'');
                if(!$teacherID||$name===''){$skipped++; continue;} $rowCount++;
                $update=$conn->prepare("UPDATE teachers SET Name=?, Department=? WHERE TeacherID=?");
                $update->bind_param("ssi",$name,$dept,$teacherID); $update->execute();
                if($update->affected_rows>0){$updated++;} else {
                    $insert=$conn->prepare("INSERT INTO teachers (TeacherID,Name,Department) VALUES (?,?,?)");
                    $insert->bind_param("iss",$teacherID,$name,$dept);
                    if(!$insert->execute()) $errors[]="Row $i insert error: ".$insert->error; else $inserted++;
                }
            }
            break;

        case 'subjects':
            foreach($rows as $i=>$row){ if($i==1) continue;
                $subID=intval($row['A']??0); $subName=trim($row['B']??''); $subCode=trim($row['C']??'');
                if(!$subID||$subName===''){$skipped++; continue;} $rowCount++;
                $update=$conn->prepare("UPDATE subjects SET SubjectName=?, SubjectCode=? WHERE SubjectID=?");
                $update->bind_param("ssi",$subName,$subCode,$subID); $update->execute();
                if($update->affected_rows>0){$updated++;} else {
                    $insert=$conn->prepare("INSERT INTO subjects (SubjectID,SubjectName,SubjectCode) VALUES (?,?,?)");
                    $insert->bind_param("iss",$subID,$subName,$subCode);
                    if(!$insert->execute()) $errors[]="Row $i insert error: ".$insert->error; else $inserted++;
                }
            }
            break;
    }

    $conn->commit();
    echo "<p style='color:green; font-weight:bold;'>Database update complete for <strong>$table</strong>.</p>";
    echo "<p>Processed rows: <strong>$rowCount</strong></p>";
    echo "<p>Updated: <strong>$updated</strong> | Inserted: <strong>$inserted</strong> | Skipped: <strong>$skipped</strong></p>";
    if($errors){ echo "<details><summary>Errors (".count($errors).")</summary><ul>"; foreach($errors as $err){ echo "<li>".htmlspecialchars($err)."</li>"; } echo "</ul></details>"; }

} catch (Exception $e){ 
    if(isset($conn) && $conn->connect_errno===0) $conn->rollback();
    echo "<p style='color:red;'>Error: ".htmlspecialchars($e->getMessage())."</p>"; exit;
}
