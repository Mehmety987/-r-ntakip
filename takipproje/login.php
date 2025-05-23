<?php
session_start(); // ❗ Giriş işlemi için gerekli — eksik olabilir
include('db.php');

// Kayıt işlemi
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["register"])) {
  $ad = $_POST["username"];
  $email = $_POST["email"];
  $password = $_POST["password"];
  $confirm = $_POST["confirmPassword"];

  if ($password !== $confirm) {
    echo "<script>alert('Şifreler eşleşmiyor');</script>";
  } else {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO kullanicilar (ad, email, password) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $ad, $email, $hashedPassword);

    if ($stmt->execute()) {
      echo "<script>alert('Kayıt başarılı! Giriş yapabilirsiniz.');</script>";
    } else {
      echo "<script>alert('Hata: " . $stmt->error . "');</script>";
    }

    $stmt->close();
  }
}

// Giriş işlemi
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["login"])) {
  $email = $_POST["email"];
  $password = $_POST["password"];

  $stmt = $conn->prepare("SELECT kullanici_id, ad, password FROM kullanicilar WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();
  $stmt->close();

  if ($user && password_verify($password, $user["password"])) {
    // ✅ Kullanıcı doğrulandı, oturum başlat
    $_SESSION["user_id"] = $user["kullanici_id"]; // ❗ buradaki "id" yerine "kullanici_id"
    $_SESSION["user_ad"] = $user["ad"];
    header("Location: index.php");
    exit();
  } else {
    echo "<script>alert('Geçersiz e-posta veya şifre');</script>";
  }
}
?>



<!DOCTYPE html>
<html lang="tr">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login & Register</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-blue-900 to-white">

  <div class="min-h-screen flex items-center justify-center ">
    <div class="w-full max-w-md bg-white p-8 rounded-lg shadow-lg">
      <!-- Giriş / Kayıt Sekmeleri -->
      <div class="flex justify-center space-x-4 mb-6">
        <button id="loginTab" class="w-full py-2 bg-[#1e3a8a] text-white rounded-lg focus:outline-none">Giriş
          Yap</button>
        <button id="registerTab" class="w-full py-2 bg-gray-300 text-gray-700 rounded-lg focus:outline-none">Kayıt
          Ol</button>
      </div>

      <!-- Giriş Formu -->
      <form method="POST" action="" id="loginForm" class="space-y-6">
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">E-posta</label>
          <input type="email" id="email" name="email"
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]"
            placeholder="E-posta adresinizi girin" required>
        </div>
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Şifre</label>
          <input type="password" id="password" name="password"
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]"
            placeholder="Şifrenizi girin" required>
        </div>
        <button type="submit" name="login"
          class="w-full py-2 bg-[#1e3a8a] text-white rounded-lg hover:bg-[#162f6a] focus:outline-none">Giriş
          Yap</button>
      </form>


      <!-- Kayıt Formu -->
      <!-- Kayıt Formu -->
      <form method="POST" action="" id="registerForm" class="space-y-6 hidden">
        <div>
          <label for="username" class="block text-sm font-medium text-gray-700">Kullanıcı Adı</label>
          <input type="text" id="username" name="username" required
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]"
            placeholder="Kullanıcı adınızı girin" />
        </div>
        <div>
          <label for="email" class="block text-sm font-medium text-gray-700">E-posta</label>
          <input type="email" name="email" required
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]"
            placeholder="E-posta adresinizi girin" />
        </div>
        <div>
          <label for="password" class="block text-sm font-medium text-gray-700">Şifre</label>
          <input type="password" name="password" required
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]"
            placeholder="Şifrenizi girin" />
        </div>
        <div>
          <label for="confirmPassword" class="block text-sm font-medium text-gray-700">Şifreyi Onayla</label>
          <input type="password" name="confirmPassword" required
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#1e3a8a]"
            placeholder="Şifrenizi tekrar girin" />
        </div>
        <button type="submit" name="register"
          class="w-full py-2 bg-[#1e3a8a] text-white rounded-lg hover:bg-[#162f6a] focus:outline-none">
          Kayıt Ol
        </button>
      </form>

    </div>

    <script>
      const loginTab = document.getElementById('loginTab');
      const registerTab = document.getElementById('registerTab');
      const loginForm = document.getElementById('loginForm');
      const registerForm = document.getElementById('registerForm');

      loginTab.addEventListener('click', () => {
        loginForm.classList.remove('hidden');
        registerForm.classList.add('hidden');
        loginTab.classList.add('bg-[#1e3a8a]', 'text-white');
        loginTab.classList.remove('bg-gray-300', 'text-gray-700');
        registerTab.classList.add('bg-gray-300', 'text-gray-700');
        registerTab.classList.remove('bg-[#1e3a8a]', 'text-white');
      });

      registerTab.addEventListener('click', () => {
        loginForm.classList.add('hidden');
        registerForm.classList.remove('hidden');
        registerTab.classList.add('bg-[#1e3a8a]', 'text-white');
        registerTab.classList.remove('bg-gray-300', 'text-gray-700');
        loginTab.classList.add('bg-gray-300', 'text-gray-700');
        loginTab.classList.remove('bg-[#1e3a8a]', 'text-white');
      });
    </script>
</body>

</html>