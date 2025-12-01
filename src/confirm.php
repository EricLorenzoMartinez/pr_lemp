<?php
session_start();
require_once 'flash.php';

// Verificar si el usuario está logado
if (!isset($_SESSION['username'])) {
    add_flash_message("Debes iniciar sesión primero", "error");
    header('Location: login.php');
    exit;
}

// Obtener número de pedido de la sesión
$order_number = $_SESSION['order_number'] ?? '';
$username = $_SESSION['username'];

// Limpiar la variable de sesión después de usarla
unset($_SESSION['order_number']);

// Determinar el tipo de operación basado en los mensajes flash existentes
$has_success = false;
$has_warning = false;

$messages = get_flash_messages();
foreach ($messages as $message) {
    if ($message['type'] === 'success') {
        $has_success = true;
    } elseif ($message['type'] === 'warning') {
        $has_warning = true;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Confirmación</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen">

  <!-- Mensajes flash -->
  <div id="flash-message" aria-live="polite" class="container mx-auto px-4 py-2">
    <?php display_flash_messages(); ?>
  </div>

  <header class="bg-blue-600 text-white shadow-md">
    <div class="container mx-auto px-4 py-6">
      <h1 class="text-3xl font-bold mb-4">Resultado de la operación</h1>
      <nav class="flex flex-wrap gap-4 mb-2">
        <a href="index.php" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-blue-100 transition duration-200">
          Volver a Home
        </a>
        <a href="buy.php" class="bg-green-500 text-white px-4 py-2 rounded-lg font-medium hover:bg-green-600 transition duration-200">
          Nueva compra
        </a>
      </nav>
      <p class="text-blue-100">Usuario: <?= htmlspecialchars($username) ?></p>
    </div>
  </header>

  <main class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-8 border border-gray-200">
      <!-- Mostrar el número de pedido cuando esté COMPLETED -->
      <?php if (!empty($order_number)): ?>
        <div class="text-center mb-6">
          <div class="bg-green-50 border border-green-200 rounded-lg p-6 mb-4">
            <p class="text-lg text-gray-700 mb-2">Tu número de pedido es:</p>
            <p class="text-3xl font-bold text-green-600 mb-4" id="order-number">#<?= htmlspecialchars($order_number) ?></p>
            <p class="text-green-700">¡Gracias por tu compra! En breve recibirás un email de confirmación.</p>
          </div>
        </div>
      <?php else: ?>
        <p class="text-center text-gray-500 mb-6">Tu número de pedido es: <strong id="order-number" class="text-gray-700">—</strong></p>
      <?php endif; ?>

      <!-- Mensaje adicional para pedidos cancelados -->
      <?php if ($has_warning): ?>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
          <p class="text-yellow-700 text-lg mb-2">Tu pedido ha sido cancelado.</p>
          <p class="text-yellow-600">Puedes realizar una nueva compra cuando lo desees.</p>
        </div>
      <?php endif; ?>

      <?php if (empty($order_number) && !$has_warning): ?>
        <div class="text-center text-gray-500 py-8">
          <p>No hay información de pedido disponible.</p>
        </div>
      <?php endif; ?>
    </div>
  </main>

</body>
</html>
