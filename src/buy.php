<?php
session_start();
require_once 'db.php';
require_once 'flash.php';

// Verificar si el usuario está logado
if (!isset($_SESSION['username'])) {
    add_flash_message("Debes iniciar sesión primero", "error");
    header('Location: login.php');
    exit;
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantities = $_POST['quantity'] ?? [];
    $ticket_ids = $_POST['ticket_ids'] ?? [];

    // Validaciones servidor
    $hasAtLeastOne = false;
    $allValid = true;

    foreach ($ticket_ids as $ticket_id) {
        $quantity = $quantities[$ticket_id] ?? 0;

        // Verificar que la cantidad está entre 0-100
        if (!is_numeric($quantity) || $quantity < 0 || $quantity > 100 || floor($quantity) != $quantity) {
            $allValid = false;
            add_flash_message("Las cantidades deben ser números enteros entre 0 y 100", "error");
            break;
        }

        if ($quantity > 0) {
            $hasAtLeastOne = true;
        }
    }

    if (!$hasAtLeastOne) {
        add_flash_message("Debes seleccionar al menos una entrada", "error");
    }

    // Si todo es válido, crear el pedido
    if ($allValid && $hasAtLeastOne && !has_flash_messages()) {
        try {
            $db = App\DB\Database::getInstance();
            $conn = $db->getConnection();

            // Verificar que los ticket_ids existen y obtener los precios
            $placeholders = str_repeat('?,', count($ticket_ids) - 1) . '?';
            $stmt = $conn->prepare("SELECT id, price FROM ticket_types WHERE id IN ($placeholders)");
            $stmt->execute($ticket_ids);
            $valid_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($valid_tickets) !== count($ticket_ids)) {
                add_flash_message("Error: algunos tipos de ticket no existen", "error");
            } else {
                // Calcular total
                $total = 0;
                foreach ($valid_tickets as $ticket) {
                    $quantity = $quantities[$ticket['id']];
                    if ($quantity > 0) {
                        $total += $quantity * $ticket['price'];
                    }
                }

                // Crear pedido PENDING
                $stmt = $conn->prepare("INSERT INTO orders (buyer_email, total, status) VALUES (?, ?, 'PENDING')");
                $stmt->execute([$_SESSION['username'], $total]);
                $order_id = $conn->lastInsertId();

                // Añadir items del pedido
                foreach ($valid_tickets as $ticket) {
                    $quantity = $quantities[$ticket['id']];
                    if ($quantity > 0) {
                        $stmt = $conn->prepare("INSERT INTO order_items (order_id, ticket_type_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
                        $stmt->execute([$order_id, $ticket['id'], $quantity, $ticket['price']]);
                    }
                }

                add_flash_message("Pedido creado correctamente. Redirigiendo a vista previa...", "success");
                header('Location: preview.php');
                exit;
            }

        } catch (PDOException $e) {
            add_flash_message("Error en la base de datos: " . $e->getMessage(), "error");
        }
    }
}

// Cargar tipos de ticket desde BBDD
try {
    $db = App\DB\Database::getInstance();
    $conn = $db->getConnection();
    $stmt = $conn->query("SELECT id, code, label, price, description FROM ticket_types");
    $ticket_types = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $ticket_types = [];
    add_flash_message("Error cargando tipos de ticket", "error");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Compra</title>
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
      <h1 class="text-3xl font-bold mb-4">Compra de entradas</h1>
      <nav class="flex flex-wrap gap-4 mb-2">
        <a href="index.php" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-blue-100 transition duration-200">
          Home
        </a>
        <a href="login.php" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-300 transition duration-200">
          Cambiar de usuario
        </a>
      </nav>
      <p class="text-blue-100">Usuario: <?= htmlspecialchars($_SESSION['username']) ?></p>
    </div>
  </header>

  <!-- Formulario de compra -->
  <main class="container mx-auto px-4 py-8">
    <form id="buy-form" method="post" novalidate class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-8 border border-gray-200">
      <p class="text-lg text-gray-700 mb-6 text-center">Selecciona cantidades (0–100). El precio se muestra junto al tipo:</p>

      <fieldset class="border border-gray-300 rounded-lg p-6">
        <legend class="text-xl font-semibold text-blue-700 px-2">Tipos de entrada</legend>

        <div class="space-y-6 mt-4">
          <?php foreach ($ticket_types as $ticket): ?>
          <div class="ticket-row flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
            <label for="quantity-<?= $ticket['id'] ?>" id="ticket-type-<?= $ticket['id'] ?>" class="flex-1">
              <span class="font-semibold text-lg text-blue-700"><?= htmlspecialchars($ticket['label']) ?></span>
              <span class="ticket-price text-green-600 font-bold text-lg ml-2"><?= number_format($ticket['price'], 2) ?> €</span>
              <?php if (!empty($ticket['description'])): ?>
                <br><small class="text-gray-600 text-sm"><?= htmlspecialchars($ticket['description']) ?></small>
              <?php endif; ?>
            </label>
            <div class="flex items-center gap-4">
              <input
                id="quantity-<?= $ticket['id'] ?>"
                name="quantity[<?= $ticket['id'] ?>]"
                type="number"
                min="0"
                max="100"
                step="1"
                value="0"
                inputmode="numeric"
                class="w-24 border border-gray-300 rounded-md px-3 py-2 text-center focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
              <input type="hidden" name="ticket_ids[]" value="<?= $ticket['id'] ?>" />
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </fieldset>

      <div class="mt-8 text-center">
        <button type="submit" class="bg-green-500 text-white px-8 py-3 rounded-lg font-semibold text-lg hover:bg-green-600 transition duration-200">
          Ir a vista previa
        </button>
      </div>
    </form>
  </main>

</body>
</html>
