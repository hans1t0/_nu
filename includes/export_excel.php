<?php
require '../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Configuración BD
define('DB_HOST', 'localhost');
define('DB_NAME', 'guarderia_matinal');
define('DB_USER', 'root');
define('DB_PASS', 'hans');

// Recibir filtro de colegio si existe
$data = json_decode(file_get_contents('php://input'), true);
$colegio = isset($data['colegio']) ? $data['colegio'] : '';

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Preparar consulta SQL
    $sql = "
        SELECT 
            r.nombre as responsable,
            r.dni,
            r.email,
            r.telefono,
            r.forma_pago,
            r.iban,
            h.nombre as alumno,
            DATE_FORMAT(h.fecha_nacimiento, '%d/%m/%Y') as fecha_nacimiento,
            c.nombre as colegio,
            h.curso,
            TIME_FORMAT(h.hora_entrada, '%H:%i') as hora_entrada,
            CASE WHEN h.desayuno = 1 THEN 'Sí' ELSE 'No' END as desayuno,
            r.observaciones
        FROM responsables r
        JOIN hijos h ON h.responsable_id = r.id
        JOIN colegios c ON h.colegio_id = c.id
    ";

    if ($colegio) {
        $sql .= " WHERE c.codigo = :colegio";
    }
    $sql .= " ORDER BY c.nombre, r.nombre, h.nombre";

    $stmt = $pdo->prepare($sql);
    if ($colegio) {
        $stmt->bindParam(':colegio', $colegio);
    }
    $stmt->execute();
    $inscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Crear nuevo documento Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Inscripciones');

    // Establecer encabezados
    $headers = [
        'A1' => 'Responsable',
        'B1' => 'DNI',
        'C1' => 'Email',
        'D1' => 'Teléfono',
        'E1' => 'Forma de Pago',
        'F1' => 'IBAN',
        'G1' => 'Alumno',
        'H1' => 'Fecha Nacimiento',
        'I1' => 'Colegio',
        'J1' => 'Curso',
        'K1' => 'Hora Entrada',
        'L1' => 'Desayuno',
        'M1' => 'Observaciones'
    ];

    // Aplicar estilo a encabezados
    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
        $sheet->getStyle($cell)->getFont()->setBold(true);
        $sheet->getStyle($cell)->getFill()
            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
            ->getStartColor()->setRGB('CCCCCC');
    }

    // Llenar datos
    $row = 2;
    foreach ($inscripciones as $inscripcion) {
        $sheet->setCellValue('A' . $row, $inscripcion['responsable']);
        $sheet->setCellValue('B' . $row, $inscripcion['dni']);
        $sheet->setCellValue('C' . $row, $inscripcion['email']);
        $sheet->setCellValue('D' . $row, $inscripcion['telefono']);
        $sheet->setCellValue('E' . $row, $inscripcion['forma_pago']);
        $sheet->setCellValue('F' . $row, $inscripcion['iban']);
        $sheet->setCellValue('G' . $row, $inscripcion['alumno']);
        $sheet->setCellValue('H' . $row, $inscripcion['fecha_nacimiento']);
        $sheet->setCellValue('I' . $row, $inscripcion['colegio']);
        $sheet->setCellValue('J' . $row, $inscripcion['curso']);
        $sheet->setCellValue('K' . $row, $inscripcion['hora_entrada']);
        $sheet->setCellValue('L' . $row, $inscripcion['desayuno']);
        $sheet->setCellValue('M' . $row, $inscripcion['observaciones']);
        $row++;
    }

    // Autoajustar columnas
    foreach (range('A', 'M') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Configurar cabeceras HTTP para descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="inscripciones.xlsx"');
    header('Cache-Control: max-age=0');

    // Crear archivo Excel y enviarlo al navegador
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}
