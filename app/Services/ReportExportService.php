<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportExportService
{
    public static function excel(string $filename, string $title, string $period, array $columns, array $rows): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $school = ReportService::schoolInfo();

        $sheet->setCellValue('A1', $school['school_name'] ?? 'Sistem Presensi Sekolah');
        $sheet->setCellValue('A2', $title);
        $sheet->setCellValue('A3', $period);
        $sheet->setCellValue('A4', 'Dicetak: ' . date('d/m/Y H:i'));

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A2')->getFont()->setBold(true);

        $headerRow = 6;
        $col = 'A';
        foreach ($columns as $header) {
            $sheet->setCellValue($col . $headerRow, $header);
            $sheet->getStyle($col . $headerRow)->getFont()->setBold(true);
            $col++;
        }

        $rowNum = $headerRow + 1;
        foreach ($rows as $row) {
            $col = 'A';
            foreach ($row as $value) {
                $sheet->setCellValue($col . $rowNum, self::sanitizeCell($value));
                $col++;
            }
            $rowNum++;
        }

        foreach (range('A', $col) as $columnId) {
            $sheet->getColumnDimension($columnId)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Prevents CSV/Excel formula injection: a cell value starting with
     * =, +, -, or @ can be interpreted as a formula by spreadsheet
     * software when the file is opened.
     */
    private static function sanitizeCell(mixed $value): mixed
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@'], true)) {
            return "'" . $value;
        }

        return $value;
    }

    public static function pdf(string $filename, string $html): void
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'Helvetica');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($filename . '.pdf', ['Attachment' => false]);
        exit;
    }

    public static function buildPdfHtml(string $title, string $period, array $columns, array $rows): string
    {
        $school = ReportService::schoolInfo();
        $schoolName = htmlspecialchars($school['school_name'] ?? 'Sistem Presensi Sekolah', ENT_QUOTES);
        $address = htmlspecialchars($school['address'] ?? '', ENT_QUOTES);
        $titleEsc = htmlspecialchars($title, ENT_QUOTES);
        $periodEsc = htmlspecialchars($period, ENT_QUOTES);

        $thead = '<tr>';
        foreach ($columns as $c) {
            $thead .= '<th>' . htmlspecialchars($c, ENT_QUOTES) . '</th>';
        }
        $thead .= '</tr>';

        $tbody = '';
        foreach ($rows as $row) {
            $tbody .= '<tr>';
            foreach ($row as $value) {
                $tbody .= '<td>' . htmlspecialchars((string) $value, ENT_QUOTES) . '</td>';
            }
            $tbody .= '</tr>';
        }

        $printedAt = date('d/m/Y H:i');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
    body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #18231B; }
    .header { text-align: center; border-bottom: 2px solid #18231B; padding-bottom: 8px; margin-bottom: 12px; }
    .header h1 { font-size: 16px; margin: 0; }
    .header p { font-size: 10px; margin: 2px 0; color: #444; }
    .title { font-size: 13px; font-weight: bold; margin-bottom: 2px; }
    .period { font-size: 11px; margin-bottom: 10px; color: #444; }
    table { width: 100%; border-collapse: collapse; }
    th, td { border: 1px solid #999; padding: 4px 6px; font-size: 10px; text-align: left; }
    th { background: #E8F8EB; }
    .footer { margin-top: 16px; font-size: 9px; color: #666; text-align: right; }
</style>
</head>
<body>
    <div class="header">
        <h1>{$schoolName}</h1>
        <p>{$address}</p>
    </div>
    <div class="title">{$titleEsc}</div>
    <div class="period">Periode: {$periodEsc}</div>
    <table>
        <thead>{$thead}</thead>
        <tbody>{$tbody}</tbody>
    </table>
    <div class="footer">Dicetak pada {$printedAt}</div>
</body>
</html>
HTML;
    }
}
