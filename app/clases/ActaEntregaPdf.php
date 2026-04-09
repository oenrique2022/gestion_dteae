<?php
declare(strict_types=1);

require_once __DIR__ . '/Database.php';

/**
 * Genera el PDF "Acta de recepción de bienes" (formato MINED) para una entrega.
 */
class ActaEntregaPdf
{
    private const FILAS_TABLA = 10;

    public static function obtenerDatos(int $idEntrega): ?array
    {
        $conn = Database::getInstance()->getConnection();

        $sql = "SELECT 
                    e.id_entrega,
                    e.id_contrato,
                    e.id_institucion,
                    e.fecha_entrega,
                    e.estado,
                    e.firma_responsable,
                    e.comentarios,
                    c.numero_contrato,
                    c.nombre_contrato,
                    COALESCE(ff.nombre, '') AS fuente_financiamiento_nombre,
                    ce.centro_id,
                    ce.codigo_infraestructura,
                    ce.nombre_ce
                FROM entregas e
                INNER JOIN contratos c ON e.id_contrato = c.id
                LEFT JOIN fuentes_financiamiento ff ON c.fuente_financiamiento_id = ff.id
                LEFT JOIN centros_educativos ce ON e.id_institucion = ce.centro_id
                WHERE e.id_entrega = :id_entrega
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->execute([':id_entrega' => $idEntrega]);
        $cabecera = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$cabecera) {
            return null;
        }

        // Columnas opcionales si existen en la tabla (no rompe si no están)
        $municipio = '';
        $departamento = '';
        try {
            $stCe = $conn->prepare('SELECT municipio, departamento FROM centros_educativos WHERE centro_id = :id LIMIT 1');
            $stCe->execute([':id' => $cabecera['id_institucion']]);
            $extra = $stCe->fetch(PDO::FETCH_ASSOC);
            if ($extra) {
                $municipio = trim((string) ($extra['municipio'] ?? ''));
                $departamento = trim((string) ($extra['departamento'] ?? ''));
            }
        } catch (Throwable $t) {
            // Columnas inexistentes: ignorar
        }

        $sqlDet = "SELECT 
                        ed.cantidad,
                        COALESCE(eq.nombre_equipo, CONCAT('Equipo #', ed.id_equipo)) AS nombre_equipo,
                        COALESCE(eq.codigo_equipo, '') AS codigo_equipo
                    FROM entregas_detalle ed
                    LEFT JOIN equipos eq ON ed.id_equipo = eq.id_equipo
                    WHERE ed.id_entrega = :id_entrega
                    ORDER BY ed.id_equipo";

        $stDet = $conn->prepare($sqlDet);
        $stDet->execute([':id_entrega' => $idEntrega]);
        $lineas = $stDet->fetchAll(PDO::FETCH_ASSOC);

        return [
            'cabecera' => $cabecera,
            'municipio' => $municipio,
            'departamento' => $departamento,
            'lineas' => $lineas,
        ];
    }

    public static function fechaTextoEspanol(?string $fechaSql): string
    {
        if ($fechaSql === null || $fechaSql === '') {
            return strtoupper(date('d \d\e ') . self::mesEspanol((int) date('n')) . date(' \d\e Y'));
        }
        $ts = strtotime(substr($fechaSql, 0, 10));
        if ($ts === false) {
            return '';
        }
        $d = (int) date('j', $ts);
        $m = (int) date('n', $ts);
        $y = (int) date('Y', $ts);

        return strtoupper(sprintf('%02d DE %s DE %d', $d, self::mesEspanol($m), $y));
    }

    private static function mesEspanol(int $mes): string
    {
        $meses = [
            1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
            5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
            9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE',
        ];

        return $meses[$mes] ?? '';
    }

    public static function construirHtml(array $datos): string
    {
        $c = $datos['cabecera'];
        $municipio = $datos['municipio'] !== '' ? htmlspecialchars($datos['municipio'], ENT_QUOTES, 'UTF-8') : '________________';
        $depto = $datos['departamento'] !== '' ? htmlspecialchars($datos['departamento'], ENT_QUOTES, 'UTF-8') : '________________';
        $codigo = htmlspecialchars((string) ($c['codigo_infraestructura'] ?? $c['id_institucion'] ?? ''), ENT_QUOTES, 'UTF-8');
        $nombreCe = htmlspecialchars((string) ($c['nombre_ce'] ?? ''), ENT_QUOTES, 'UTF-8');
        $fechaTxt = self::fechaTextoEspanol($c['fecha_entrega'] ?? null);

        $numContrato = trim((string) ($c['numero_contrato'] ?? ''));
        $nombreContrato = trim((string) ($c['nombre_contrato'] ?? ''));
        $fuenteNombre = trim((string) ($c['fuente_financiamiento_nombre'] ?? ''));

        $hNum = htmlspecialchars($numContrato, ENT_QUOTES, 'UTF-8');
        $hNombre = htmlspecialchars($nombreContrato, ENT_QUOTES, 'UTF-8');
        $hFuente = htmlspecialchars($fuenteNombre, ENT_QUOTES, 'UTF-8');

        $partesAdquisicion = [];
        if ($numContrato !== '') {
            $partesAdquisicion[] = 'NÚMERO DE CONTRATO ' . $hNum;
        }
        if ($nombreContrato !== '') {
            $partesAdquisicion[] = 'NOMBRE DEL CONTRATO ' . $hNombre;
        }
        if ($fuenteNombre !== '') {
            $partesAdquisicion[] = 'FUENTE DE FINANCIAMIENTO ' . $hFuente;
        }
        $textoAdquisicion =
            count($partesAdquisicion) > 0
                ? 'ADQUIRIDOS SEGÚN ' . implode(', ', $partesAdquisicion)
                : 'ADQUIRIDOS SEGÚN EL CONTRATO VIGENTE';

        $logoFs = PROJECT_ROOT . '/public/assets/img/logo.png';
        $logoSrc = '';
        if (is_readable($logoFs)) {
            $logoSrc = 'data:image/png;base64,' . base64_encode((string) file_get_contents($logoFs));
        }

        $filasHtml = '';
        $lineas = $datos['lineas'];
        for ($i = 0; $i < self::FILAS_TABLA; $i++) {
            $n = $i + 1;
            if (isset($lineas[$i])) {
                $row = $lineas[$i];
                $desc = htmlspecialchars((string) $row['nombre_equipo'], ENT_QUOTES, 'UTF-8');
                if (!empty($row['codigo_equipo'])) {
                    $desc .= ' // ' . htmlspecialchars((string) $row['codigo_equipo'], ENT_QUOTES, 'UTF-8');
                }
                $cant = (int) $row['cantidad'];
            } else {
                $desc = '....................................................................................................';
                $cant = '';
            }
            $filasHtml .= '<tr><td style="text-align:center;border:1px solid #000;padding:4px;width:8%;">' . $n . '</td>'
                . '<td style="border:1px solid #000;padding:4px;font-size:9pt;">' . $desc . '</td>'
                . '<td style="text-align:center;border:1px solid #000;padding:4px;width:14%;">' . ($cant !== '' ? (string) $cant : '……') . '</td></tr>';
        }

        // Izquierda (director): se deja en blanco para firma/sello en físico. Derecha: "Persona que firma" de la entrega.
        $nombreDirectorPdf = '';
        $personaQueFirma = htmlspecialchars(trim((string) ($c['firma_responsable'] ?? '')), ENT_QUOTES, 'UTF-8');

        $logoBlock = $logoSrc !== ''
            ? '<div style="text-align:center;margin-bottom:8px;"><img src="' . $logoSrc . '" style="max-height:56px;" alt="Logo" /></div>'
            : '';

        return <<<HTML
<style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 10pt; color: #000; }
    .caja { border: 2px solid #000; padding: 10px; margin-bottom: 12px; }
    .titulo-caja { text-align: center; font-weight: bold; font-size: 11pt; margin: 8px 0; }
    .grid-2 { width: 100%; }
    .grid-2 td { vertical-align: top; padding: 2px 4px; font-size: 9pt; }
    .narrativa { text-align: justify; font-size: 9pt; line-height: 1.35; margin: 12px 0; text-transform: uppercase; }
    table.items { width: 100%; border-collapse: collapse; margin-top: 8px; }
    .firmas { width: 100%; margin-top: 24px; font-size: 9pt; }
    .firmas td { vertical-align: top; width: 50%; padding: 8px; }
    .sello { border: 1px dashed #333; min-height: 64px; margin-top: 6px; }
</style>
<div style="max-width: 100%;">
    {$logoBlock}
    <div style="text-align:center;font-size:9pt;font-weight:bold;margin-bottom:6px;">MINISTERIO DE EDUCACIÓN, CIENCIA Y TECNOLOGÍA</div>

    <div class="caja">
        <table class="grid-2" width="100%">
            <tr>
                <td><strong>CÓDIGO No:</strong> {$codigo}</td>
                <td><strong>MUNICIPIO:</strong> {$municipio}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>DEPARTAMENTO:</strong> {$depto}</td>
            </tr>
        </table>
        <div class="titulo-caja">ACTA DE RECEPCIÓN DE BIENES</div>
        <table class="grid-2" width="100%">
            <tr>
                <td colspan="2"><strong>FECHA:</strong> {$fechaTxt}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>NOMBRE DEL CENTRO EDUCATIVO:</strong> {$nombreCe}</td>
            </tr>
        </table>
    </div>

    <p class="narrativa">
        EL (LA) SUSCRITO (A) HACE CONSTAR QUE HA RECIBIDO LOS BIENES QUE SE DETALLAN A CONTINUACIÓN DE LA DIRECCIÓN DE INNOVACIÓN Y TECNOLOGÍA EDUCATIVA DEL MINISTERIO DE EDUCACIÓN, CIENCIA Y TECNOLOGÍA
        {$textoAdquisicion}, QUEDANDO BAJO NUESTRA RESPONSABILIDAD COMO INSTITUCIÓN EDUCATIVA EN EL USO Y RESGUARDO DEL MISMO.
    </p>

    <table class="items" cellspacing="0">
        <thead>
            <tr>
                <th style="border:1px solid #000;background:#eee;padding:6px;width:8%;">ITEM</th>
                <th style="border:1px solid #000;background:#eee;padding:6px;">DESCRIPCIÓN DE LOS BIENES</th>
                <th style="border:1px solid #000;background:#eee;padding:6px;width:16%;">CANTIDAD RECIBIDA</th>
            </tr>
        </thead>
        <tbody>
            {$filasHtml}
        </tbody>
    </table>

    <table class="firmas" cellspacing="0">
        <tr>
            <td>
                <strong>Firma de Director o Sub-director del Centro Escolar</strong><br/><br/>
                <strong>Nombre:</strong> {$nombreDirectorPdf}<br/><br/>
                <strong>Sello:</strong>
                <div class="sello"></div>
            </td>
            <td>
                <strong>Firma de Técnico que entrega</strong><br/>
                <span style="font-size:8pt;">Departamento de Tecnologías Emergentes Aplicadas a la Educación</span><br/><br/>
                <strong>Nombre:</strong> {$personaQueFirma}<br/><br/>
                <strong>Sello:</strong>
                <div class="sello"></div>
            </td>
        </tr>
    </table>
</div>
HTML;
    }

    public static function enviarPdf(int $idEntrega): void
    {
        if (!class_exists(\Mpdf\Mpdf::class)) {
            http_response_code(500);
            echo 'mPDF no está instalado. Ejecute: composer install';
            exit;
        }

        $datos = self::obtenerDatos($idEntrega);
        if ($datos === null) {
            http_response_code(404);
            echo 'Entrega no encontrada.';
            exit;
        }

        $html = self::construirHtml($datos);

        $mpdf = new \Mpdf\Mpdf([
            'format' => 'Letter',
            'margin_left' => 12,
            'margin_right' => 12,
            'margin_top' => 12,
            'margin_bottom' => 12,
            'default_font' => 'dejavusans',
        ]);
        $mpdf->SetTitle('Acta de recepción de bienes');
        $mpdf->WriteHTML($html);

        $nombreArchivo = 'Acta_recepcion_entrega_' . $idEntrega . '.pdf';
        $dest = class_exists(\Mpdf\Output\Destination::class)
            ? \Mpdf\Output\Destination::INLINE
            : 'I';
        $mpdf->Output($nombreArchivo, $dest);
    }
}
