<?php
include('db.php');
require_once('tcpdf/tcpdf.php');

$pdf = new TCPDF();
$pdf->AddPage();

$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Stok Raporu', 0, 1, 'C');

$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(60, 10, 'Ürün Adı', 1);
$pdf->Cell(30, 10, 'Miktar', 1);
$pdf->Cell(60, 10, 'Kategori', 1);
$pdf->Ln();

$sql = "SELECT urun_ad, urun_miktar, urun_kategori FROM ürünler";
$result = $conn->query($sql);

$pdf->SetFont('helvetica', '', 12);
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(60, 10, $row['urun_ad'], 1);
    $pdf->Cell(30, 10, $row['urun_miktar'], 1);
    $pdf->Cell(60, 10, $row['urun_kategori'], 1);
    $pdf->Ln();
}

$pdf->Output('stok_raporu.pdf', 'I');
?>
