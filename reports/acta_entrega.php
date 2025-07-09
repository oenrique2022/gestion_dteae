<?php
include_once '../includes/config.php';
require_once '../vendor/autoload.php'; // Incluye TCPDF desde Composer
require_once '../includes/Entrega.php'; 
$idEntrega=addslashes($_REQUEST['idEntrega']);
class EntregaPDF extends TCPDF{
    // Encabezado personalizado
    public function Header()
    {
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 10, 'ACTA DE RECEPCIÓN DE BIENES', 0, 1, 'C');
    }

    // Pie de página personalizado
    public function Footer()
    {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Página ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, 0, 'C');
    }
}

$entrega=new Entrega;
$datosEntrega=$entrega->obtenerCompletoPorId($idEntrega);
$items=$entrega->listarDetalles($idEntrega);
//print_r($items);
// Crear una nueva instancia de PDF
$pdf = new EntregaPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Configuración del documento
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Sistema de Gestión Administrativa');
$pdf->SetTitle('Acta de Recepción de Bienes');
$pdf->SetMargins(10, 20, 10);
$pdf->SetAutoPageBreak(true, 20);

// Agregar una página
$pdf->AddPage();

// Configuración de la fuente
$pdf->SetFont('helvetica', '', 10);

// Contenido del PDF
$codigo = $datosEntrega['codigo_infraestructura'];
$municipio = "APASTEPEQUE";
$departamento = "SAN VICENTE";
$institucion = $datosEntrega['nombre_ce'];
$contrato = $datosEntrega['numero_contrato'] . " " . $datosEntrega['nombre_contrato'];
/*$items = [
    ['1', 'Kit Makeblock Ultimate 2.0', '1'],
    ['2', 'Kit Makeblock Ranger', '1'],
    ['3', 'Kit KNEX (12605)', '1'],
    ['4', 'Kit KNEX (23012)', '1'],
    ['5', 'Kit Bluebot...', '1'],
    ['6', 'Kit Botley 2.0...', '1'],
    ['7', 'Caja color negro...', '1'],
    ['8', 'Cajas clasificadoras...', '6'],
    ['9', 'USB 64 GB Memory', '2'],
    ['10', 'Baterías recargables...', '2'],
];
*/
$mensaje="EL (LA) SUSCRITO (A) HACE CONSTAR QUE HA RECIBIDO LOS BIENES QUE SE DETALLAN A CONTINUACIÓN DE LA DIRECCIÓN DE TECNOLOGÍA EDUCATIVA 
DEL MINISTERIO DE EDUCACIÓN, CIENCIA Y TECNOLOGÍA ADQUIRIDOS SEGÚN CONTRATO MINEDUCYT No. $contrato CON FINANCIAMIENTOS GOES INVERSIÓN 7239, 
QUEDANDO BAJO NUESTRA RESPONSABILIDAD COMO INSTITUCIÓN EDUCATIVA EN EL USO Y RESGUARDO DEL MISMO.";
// Cabecera de la tabla
$html = <<<EOD
<table border="1">
<tr>
    <td colspan="2"><b>CÓDIGO:</b> $codigo</td>
</tr>    
<tr>    
    <td><b>MUNICIPIO:</b> $municipio</td>
    <td><b>DEPARTAMENTO:</b> $departamento</td>
</tr>
<tr>
    <td colspan="2" align="center"> <br> ACTA DE RECEPCIÓN DE BIENES <br></td>
</tr> 
<tr>
    <td colspan="2"><b>NOMBRE DEL CENTRO EDUCATIVO:</b> $institucion</td>
</tr>
<tr>
    <td colspan="2"><b>CONTRATO:</b> $contrato</td>
</tr>
<tr>
    <td colspan="2"> <p>$mensaje </p> <br> </td>
</tr>
</table>
<br><br>
<table border="1" cellpadding="5">
    <thead>
        <tr>
            <th width="10%">ITEM</th>
            <th width="70%">DESCRIPCIÓN DE LOS BIENES</th>
            <th width="20%">CANTIDAD RECIBIDA</th>
        </tr>
    </thead>
    <tbody>
EOD;

// Agregar los elementos
$n=1;
foreach ($items as $item) {
    $html .= '<tr>';
    $html .= '<td width="10%" align="center">' . $n . '</td>';
    $html .= '<td width="70%">' . $item['nombre_equipo'] . '</td>';
    $html .= '<td width="20%" align="center">' . $item['cantidad'] . '</td>';
    $html .= '</tr>';
    $n++;
}

$html .= <<<EOD
    </tbody>
</table>
<br><br>
<table border="0">
<tr>
    <td width="55%" >______________________________________<br>Firma de Director o Sub-director</td>
    <td width="50%" align="center">______________________________________<br>Firma del Técnico que entrega<br> Departamento de Tecnologías Emergentes Aplicadas a la Educación</td>
</tr>
<tr>
    <td width="60%"><p> </p><br>Nombre: ________________________<br>Sello:</td>
    <td width="50%"><p> </p><br>Nombre: ________________________<br>Sello:</td>
</tr>
</table>
EOD;

// Escribir el HTML en el PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Salida del archivo
$pdf->Output('acta_recepcion.pdf', 'I');
