<?php
include('db.php');
require_once('tcpdf/tcpdf.php');

// TCPDF nesnesi oluştur
$pdf = new TCPDF();
$pdf->AddPage();

// Başlık
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Düşük Stok Raporu (5 Adet ve Altı)', 0, 1, 'C');

// Sütun Başlıkları
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(60, 10, 'Ürün Adı', 1);
$pdf->Cell(30, 10, 'Miktar', 1);
$pdf->Cell(60, 10, 'Kategori', 1);
$pdf->Ln();

// Veritabanı sorgusu: düşük stoklu ürünler
$sql = "SELECT urun_ad, urun_miktar, urun_kategori FROM ürünler WHERE urun_miktar <= 5";
$result = $conn->query($sql);

// Ürünleri yazdır
$pdf->SetFont('helvetica', '', 12);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $pdf->Cell(60, 10, $row['urun_ad'], 1);
        $pdf->Cell(30, 10, $row['urun_miktar'], 1);
        $pdf->Cell(60, 10, $row['urun_kategori'], 1);
        $pdf->Ln();
    }
} else {
    $pdf->Cell(0, 10, 'Düşük stokta ürün bulunmamaktadır.', 1, 1, 'C');
}

// PDF olarak çıktıyı ver
$pdf->Output('dusuk_stok_raporu.pdf', 'I');
?>
