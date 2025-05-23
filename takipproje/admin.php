<?php
include('db.php');

// Ürün Ekleme
if (isset($_POST['urunEkle'])) {
  $ad = $_POST['ad'];
  $kategori = $_POST['kategori'];
  $stok = $_POST['stok'];

  $kontrol = $conn->prepare("SELECT COUNT(*) FROM ürünler WHERE urun_ad = ?");
  $kontrol->bind_param("s", $ad);
  $kontrol->execute();
  $kontrol->bind_result($sayac);
  $kontrol->fetch();
  $kontrol->close();

  if ($sayac > 0) {
    echo "<script>alert('⚠️ Bu isimde bir ürün zaten var!'); window.location.href='admin.php';</script>";
    exit;
  }

  $resim = $_FILES['resim'];
  $resimData = file_get_contents($resim['tmp_name']);
  $stmt = $conn->prepare("INSERT INTO ürünler (urun_ad, urun_miktar, urun_kategori, urun_resim) VALUES (?, ?, ?, ?)");
  $stmt->bind_param("siss", $ad, $stok, $kategori, $resimData);

  if ($stmt->execute()) {
    echo "<script>alert('✅ Ürün başarıyla eklendi.'); window.location.href='admin.php';</script>";
  } else {
    echo "<script>alert('Hata: " . $stmt->error . "'); window.location.href='admin.php';</script>";
  }
  $stmt->close();
}

// Ürün Listeleme
if (isset($_GET['listele'])) {
  $sonuc = $conn->query("SELECT urun_id, urun_ad, urun_kategori, urun_miktar, urun_resim FROM ürünler ORDER BY urun_id DESC");

  while ($row = $sonuc->fetch_assoc()) {
    $resimBase64 = base64_encode($row['urun_resim']);
    echo '
      <div class="bg-white p-4 rounded-2xl border-2 border-gray-200 shadow-lg flex gap-4 items-center card">
        <img src="data:image/jpeg;base64,' . $resimBase64 . '" alt="' . htmlspecialchars($row['urun_ad']) . '" class="w-20 h-20 object-cover rounded-xl border" />
        <div class="flex-1">
          <div class="font-bold text-xl text-blue-800">' . htmlspecialchars($row['urun_ad']) . '</div>
          <div class="text-sm text-gray-500">Kategori: ' . htmlspecialchars($row['urun_kategori']) . '</div>
          <div class="text-sm text-gray-500">Stok: ' . htmlspecialchars($row['urun_miktar']) . '</div>
        </div>
        <div class="flex gap-2">
          
<form method="POST" action="admin.php" enctype="multipart/form-data">
  <input type="hidden" name="urun_id" value="' . $row['urun_id'] . '">
  <input type="text" name="ad" placeholder="Yeni Ad" required class="border rounded px-2 py-1" value="' . htmlspecialchars($row['urun_ad']) . '">
  <input type="text" name="kategori" placeholder="Kategori" required class="border rounded px-2 py-1" value="' . htmlspecialchars($row['urun_kategori']) . '">
  <input type="number" name="stok" placeholder="Stok" required class="border rounded px-2 py-1" value="' . $row['urun_miktar'] . '">
  <input type="file" name="resim" class="rounded py-1">

  <button type="submit" name="islem" value="guncelle" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">Güncelle</button>

  <button type="submit" name="islem" value="sil" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600">Sil</button>
</form>
        </div>
      </div>';
  }
  exit;
}

if (isset($_POST['islem'])) {
  $id = $_POST['urun_id'];
  $islem = $_POST['islem'];

  if ($islem == "sil") {
    $stmt = $conn->prepare("DELETE FROM ürünler WHERE urun_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    echo "<script>alert('🗑️ Ürün silindi'); window.location.href='admin.php';</script>";
    exit;
  } elseif ($islem == "guncelle") {
    $ad = $_POST['ad'];
    $kategori = $_POST['kategori'];
    $stok = $_POST['stok'];

    if ($_FILES['resim']['size'] > 0) {
      $resimData = file_get_contents($_FILES['resim']['tmp_name']);
      $stmt = $conn->prepare("UPDATE ürünler SET urun_ad=?, urun_kategori=?, urun_miktar=?, urun_resim=? WHERE urun_id=?");
      $stmt->bind_param("ssisi", $ad, $kategori, $stok, $resimData, $id);
    } else {
      $stmt = $conn->prepare("UPDATE ürünler SET urun_ad=?, urun_kategori=?, urun_miktar=? WHERE urun_id=?");
      $stmt->bind_param("ssii", $ad, $kategori, $stok, $id);
    }

    if ($stmt->execute()) {
      echo "<script>alert('✅ Ürün güncellendi'); window.location.href='admin.php';</script>";
    } else {
      echo "<script>alert('Güncelleme hatası'); window.location.href='admin.php';</script>";
    }
    $stmt->close();
    exit;
  }

}


?>



<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Ürün Takip Admin Paneli</title>
  <script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .tab:not(.hidden) {
      animation: fade 0.3s ease-in-out;
    }

    @keyframes fade {
      from {
        opacity: 0;
        transform: translateY(10px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .card:hover {
      transform: scale(1.02);
      transition: transform 0.2s ease;
    }

    /* Arka plan efekti - animasyonlu grid */
    body::before {
      content: "";
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: radial-gradient(circle, rgba(255, 255, 255, 0.05) 1px, transparent 1px),
        linear-gradient(to bottom right, #1e3a8a, #ffffff);
      /* Lacivert -> Beyaz geçiş */
      background-size: 40px 40px, cover;
      animation: float 25s linear infinite;
      z-index: 0;
      opacity: 0.3;
      pointer-events: none;
      /* 👈 Tıklanmayı engellemesini önler */
    }

    @keyframes float {
      0% {
        transform: translate(0, 0);
      }

      50% {
        transform: translate(-30px, -20px);
      }

      100% {
        transform: translate(0, 0);
      }
    }
  </style>


</head>


<body class="relative min-h-screen bg-gradient-to-br from-blue-900 to-white text-gray-800 overflow-x-hidden">
  <div class="max-w-6xl mx-auto py-10 px-6">
    <h1 class="text-4xl font-extrabold text-center mb-10 text-blue-900 drop-shadow-md">Ürün Takip Admin Paneli</h1>

    <div class="grid sm:grid-cols-2 md:grid-cols-4 gap-4 mb-10">
      <button data-tab="ekle"
        class="tab-btn px-4 py-3 bg-white shadow-lg rounded-xl hover:bg-blue-200 transition font-semibold">➕ Ürün
        Ekle</button>
      <button data-tab="listele"
        class="tab-btn px-4 py-3 bg-white shadow-lg rounded-xl hover:bg-blue-200 transition font-semibold">📋 Ürünleri
        Listele</button>
      <button data-tab="rapor"
        class="tab-btn px-4 py-3 bg-white shadow-lg rounded-xl hover:bg-blue-200 transition font-semibold">📄 Stok
        Raporu</button>
      <button data-tab="dusuk"
        class="tab-btn px-4 py-3 bg-white shadow-lg rounded-xl hover:bg-blue-200 transition font-semibold">⚠️ Düşük
        Stok</button>
    </div>

    <div id="ekle" class="tab">
      <form id="urunForm" action="admin.php" method="POST" enctype="multipart/form-data"
        class="bg-white p-6 rounded-xl shadow-xl space-y-4">
        <input name="ad" type="text" placeholder="Ürün adı" required
          class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
        <input name="kategori" type="text" placeholder="Kategori" required
          class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />
        <input name="stok" type="number" placeholder="Miktar (stok)" required
          class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />

        <!-- Ürün Resmi Seçim -->
        <input name="resim" type="file" required
          class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400" />

        <button type="submit" name="urunEkle"
          class="bg-blue-600 text-white px-6 py-3 rounded-lg font-bold hover:bg-blue-700 transition">✔️ Ürün
          Ekle</button>
        <div class="mt-10 text-center">
          <a href="index.php"
            class="inline-block bg-gray-700 text-white px-6 py-3 rounded-lg font-bold hover:bg-gray-800 transition">🔙
            Anasayfaya Dön</a>
        </div>
      </form>
    </div>



    <div id="listele" class="tab hidden">
      <div id="urunListesi" class="grid grid-cols-1 md:grid-cols-2 gap-4"></div>
    </div>

    <div id="rapor" class="tab hidden">
      <div class="bg-white p-6 rounded-xl shadow-xl text-center">
        <a href="stok_raporu.php" target="_blank"
          class="w-full max-w-xs px-4 py-2 bg-[#1e3a8a] text-white rounded-lg hover:bg-[#162f6a] transition text-center text-lg">
          PDF Olarak İndir
        </a>
      </div>
    </div>

    <div id="dusuk" class="tab hidden">
      <div class="bg-white p-6 rounded-xl shadow-xl text-center mb-4">
        <a href="dusuk_stok_raporu.php" target="_blank"
          class="w-full max-w-xs px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-center text-lg">
          Düşük Stok Raporu
        </a>
        <div id="dusukListesi" class="space-y-2"></div>
      </div>
    </div>

    <script>
      const urunler = [];

      document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
          const target = btn.dataset.tab;
          document.querySelectorAll('.tab').forEach(tab => tab.classList.add('hidden'));
          document.getElementById(target).classList.remove('hidden');
          if (target === 'listele') renderList();
          if (target === 'dusuk') renderLowStock();
        });
      });

      function renderList() {
        const list = document.getElementById('urunListesi');
        list.innerHTML = 'Yükleniyor...';

        fetch('admin.php?listele=1')
          .then(response => response.text())
          .then(html => {
            list.innerHTML = html;
          })
          .catch(error => {
            list.innerHTML = `<div class="text-red-600 font-bold">Hata oluştu: ${error.message}</div>`;
          });
      }


      function renderLowStock() {
        const list = document.getElementById('dusukListesi');
        list.innerHTML = '';
        urunler.filter(u => u.stok <= 5).forEach(u => {
          list.innerHTML += `<div class="bg-yellow-100 border border-yellow-300 p-3 rounded-xl shadow-sm">⚠️ ${u.ad} (${u.kategori}) - Stok: ${u.stok}</div>`;
        });
      }

      function exportPDF(dusuk = false) {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();
        const data = dusuk ? urunler.filter(u => u.stok <= 5) : urunler;
        doc.text("Ürün Raporu", 10, 10);
        data.forEach((u, i) => {
          doc.text(`${u.ad} (${u.kategori}) - Stok: ${u.stok}`, 10, 20 + i * 10);
        });
        doc.save(dusuk ? "dusuk_stok_raporu.pdf" : "stok_raporu.pdf");
      }

      document.getElementById('raporBtn').addEventListener('click', () => exportPDF(false));
      document.getElementById('dusukBtn').addEventListener('click', () => exportPDF(true));

      document.addEventListener('DOMContentLoaded', () => {
        document.querySelector('[data-tab="ekle"]').click();
      });
    </script>
</body>

</html>