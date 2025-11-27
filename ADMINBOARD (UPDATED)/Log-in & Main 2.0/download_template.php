<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$table = $_GET['table'] ?? '';

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

switch($table) {

    case "teachers":
        $sheet->setCellValue('A1', 'TeacherID');
        $sheet->setCellValue('B1', 'Name');
        $sheet->setCellValue('C1', 'Department');
        $filename = "teachers_template.xlsx";
        break;

    case "subjects":
        $sheet->setCellValue('A1', 'SubjectID');
        $sheet->setCellValue('B1', 'SubjectName');
        $sheet->setCellValue('C1', 'SubjectCode');
        $filename = "subjects_template.xlsx";
        break;

    case "rooms":
        $sheet->setCellValue('A1', 'RoomID');
        $sheet->setCellValue('B1', 'RoomName');
        $sheet->setCellValue('C1', 'BuildingID');
        $sheet->setCellValue('D1', 'TimeAvailable');
        $sheet->setCellValue('E1', 'DaysAvailable');
        $sheet->setCellValue('F1', 'DaysOccupied');
        $filename = "rooms_template.xlsx";
        break;

    default:
        die("Invalid table.");
}

header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Cache-Control: max-age=0");

$writer = new Xlsx($spreadsheet);
$writer->save("php://output");
exit;
