<?php
session_start();
require_once 'flash.php';

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    // Validación del email en el servidor
    if (empty($email)) {
        add_flash_message("El email es obligatorio", "error");
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        add_flash_message("El formato del email no es válido", "error");
    } else {
        // Todo correcto - guardar en sesión y redirigir
        $_SESSION['username'] = $email;
        add_flash_message("¡Bienvenido! Sesión iniciada correctamente", "success");
        header('Location: buy.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans min-h-screen flex items-center justify-center">

  <!-- Mensajes flash -->
  <div id="flash-message" aria-live="polite" class="fixed top-0 left-0 right-0 z-50">
    <?php display_flash_messages(); ?>
  </div>

  <div class="w-full max-w-md mx-4">
    <header class="bg-blue-600 text-white rounded-t-lg shadow-md">
      <div class="px-6 py-4">
        <h1 class="text-2xl font-bold text-center">Identificación</h1>
        <nav class="mt-4 text-center">
          <a href="index.php" class="inline-block bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-blue-100 transition duration-200">
            Volver a Home
          </a>
        </nav>
      </div>
    </header>

    <!-- Formulario de login con email -->
    <form id="login-form" method="post" novalidate class="bg-white rounded-b-lg shadow-md p-8 border border-gray-200">
      <div class="mb-6">
        <label for="email-input" class="block text-sm font-medium text-gray-700 mb-2">
          Email:
        </label>
        <input
          id="email-input"
          name="email"
          type="email"
          required
          placeholder="nombre@dominio.com"
          value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
          class="w-full border border-gray-300 rounded-md px-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
        />
      </div>

      <button
        type="submit"
        class="w-full bg-green-500 text-white py-3 px-4 rounded-md font-semibold text-lg hover:bg-green-600 transition duration-200 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
      >
        Continuar a compra
      </button>
    </form>
  </div>

</body>
</html>
