<?php
include('db.php');
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
$kullanici_adi = $_SESSION["user_ad"];

// Veritabanından ürünleri çekmek için sorgu
$sql = "SELECT urun_id, urun_ad, urun_miktar, urun_kategori, urun_resim FROM ürünler";
$result = $conn->query($sql);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['urun_id']) && isset($_POST['miktar']) && isset($_POST['action'])) {
    $urun_id = intval($_POST['urun_id']);
    $degisecek_miktar = intval($_POST['miktar']);
    $action = $_POST['action']; // "increase" veya "decrease"

    if ($urun_id > 0 && $degisecek_miktar > 0) {
        $sql = "SELECT urun_miktar FROM ürünler WHERE urun_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $urun_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $mevcut_miktar = $row['urun_miktar'];

        // Miktarı arttır veya azalt
        $action = $_POST['action']; // "increase" veya "decrease"

        if ($action === "increase") {
            $yeni_miktar = $mevcut_miktar + $degisecek_miktar;
        } elseif ($action === "decrease") {
            $yeni_miktar = max(0, $mevcut_miktar - $degisecek_miktar); // 0'ın altına inmesin
        } else {
            echo "invalid_action";
            exit();
        }

        // Güncelle
        $update_sql = "UPDATE ürünler SET urun_miktar = ? WHERE urun_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ii", $yeni_miktar, $urun_id);
        if ($update_stmt->execute()) {
            echo "success";
        } else {
            echo "error";
        }
    } else {
        echo "invalid";
    }
    exit();

}

?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Takip Proje</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="style.css">
</head>

<body class="bg-gray-100">

    <!-- Navbar -->
    <nav class="bg-[#1e3a8a] p-4">
        <div class="flex justify-center items-center space-x-6">
            <span class="text-white text-3xl font-semibold">LAS Ürün Stok</span>
            <a href="index.php" class="text-white text-xl font-semibold">Anasayfa</a>
            <div class="flex items-center space-x-2">
                <img src="img/user.png" alt="Hesap" class="w-8 h-8 rounded-full">
                <span class="text-white font-medium"><?= htmlspecialchars($kullanici_adi) ?></span>
                <a href="logout.php" class="text-white bg-red-600 px-3 py-1 rounded hover:bg-red-700 transition">Çıkış</a>
            </div>
        </div>
    </nav>


    <!-- Arama Alanı ve Butonlar -->
    <div class="flex flex-col items-center justify-center mt-6 space-y-4 px-4">
        <div class="w-full max-w-md flex space-x-2">
            <input id="searchInput" type="text" placeholder="Ürün ara..."
                class="flex-1 px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]" />
            <button onclick="searchProduct()"
                class="px-4 py-2 bg-[#1e3a8a] text-white rounded-lg hover:bg-[#162f6a] transition">
                Ara
            </button>
        </div>

        <a href="stok_raporu.php" target="_blank"
            class="w-full max-w-xs px-4 py-2 bg-[#1e3a8a] text-white rounded-lg hover:bg-[#162f6a] transition text-center text-lg">
            Stok Raporu
        </a>

        <a href="dusuk_stok_raporu.php" target="_blank"
            class="w-full max-w-xs px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition text-center text-lg">
            Düşük Stok Raporu
        </a>

    </div>

    <!-- İçerik Bölümü -->
    <div class="content p-8">
        <div class="card-container">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <?php
                // Ürünleri döngü ile ekrana yazdırıyoruz
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $urun_id = $row['urun_id'];
                        $urun_ad = $row['urun_ad'];
                        $urun_miktar = $row['urun_miktar'];
                        $urun_kategori = $row['urun_kategori'];
                        $urun_resim = base64_encode($row['urun_resim']); // Resmi Base64 formatında kodluyoruz
                        ?>
                        <!-- Kart -->
                        <div class="bg-white p-4 rounded-lg shadow-lg border border-[#1e3a8a]">
                            <img src="data:image/jpeg;base64,<?= $urun_resim ?>" alt="Ürün Resmi"
                                class="w-full h-96 object-contain rounded-lg">
                            <p class="mt-4 text-center text-base font-medium text-gray-700">Kategori: <?= $urun_kategori ?></p>
                            <h3 class="mt-4 text-center text-lg font-semibold"><?= $urun_ad ?></h3>
                            <div class="flex justify-center items-center mt-4 space-x-4">
                                <button id="decrease<?= $urun_id ?>" class="counter-button">Azalt</button>
                                <input type="number" id="manual-input<?= $urun_id ?>" min="1"
                                    class="manual-input w-20 h-[42px] px-2 py-1 border border-gray-300 rounded text-center text-lg"
                                    placeholder="Adet">
                                <button id="increase<?= $urun_id ?>" class="counter-button">Arttır</button>
                            </div>
                            <!-- Elle Ekleme butonu -->
                            <div class="flex justify-center items-center mt-2">
                                <button class="add-button counter-button">Ekle</button>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo "Ürün bulunamadı.";
                }
                ?>
            </div>
        </div>
    </div>

    <!-- Arama JS -->
    <script>
        function searchProduct() {
            const query = document.getElementById('searchInput').value.toLowerCase();
            const cards = document.querySelectorAll('.card-container .bg-white');

            cards.forEach(card => {
                const title = card.querySelector('h3').textContent.toLowerCase();
                card.style.display = title.includes(query) ? "block" : "none";
            });
        }
    </script>

    <script>
        // Arttır
        document.querySelectorAll('[id^=increase]').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.id.replace('increase', '');
                const input = document.getElementById('manual-input' + id);
                let value = parseInt(input.value) || 0;
                input.value = value + 1;
            });
        });

        // Azalt + Veritabanında stok azaltma
        document.querySelectorAll('[id^=decrease]').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.id.replace('decrease', '');
                const input = document.getElementById('manual-input' + id);
                let value = parseInt(input.value) || 0;

                // Sıfırın altına da düşebilsin
                input.value = value - 1;

                // Değer negatifse veritabanından azaltma yapılmaz
                if (value > 0) {
                    fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `urun_id=${id}&miktar=1&action=decrease`
                    })
                        .then(response => response.text())
                        .then(data => {
                            if (data === 'success') {
                                console.log("Stok azaltıldı.");
                            } else {
                                alert("Veritabanı güncellenemedi: " + data);
                            }
                        })
                        .catch(error => {
                            alert("İstek başarısız oldu.");
                            console.error(error);
                        });
                }
            });
        });

        document.querySelectorAll('.add-button').forEach(button => {
            button.addEventListener('click', () => {
                const card = button.closest('.bg-white');
                const input = card.querySelector('.manual-input');
                const urunId = card.querySelector('button[id^=increase]').id.replace('increase', '');
                const miktar = parseInt(input.value);

                if (!isNaN(miktar) && miktar !== 0) {
                    const action = miktar > 0 ? "increase" : "decrease";

                    fetch(window.location.href, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `urun_id=${urunId}&miktar=${Math.abs(miktar)}&action=${action}`
                    })
                        .then(response => response.text())
                        .then(data => {
                            if (data === 'success') {
                                alert(`Stok başarıyla ${action === 'increase' ? 'artırıldı' : 'azaltıldı'}.`);
                                input.value = '';
                            } else {
                                alert("Hata oluştu: " + data);
                            }
                        })
                        .catch(error => {
                            alert("İstek başarısız oldu.");
                            console.error(error);
                        });
                } else {
                    alert("Lütfen sıfırdan farklı bir sayı girin.");
                }
            });
        });

    </script>



</body>

</html>

<?php
// Veritabanı bağlantısını kapat
$conn->close();
?>