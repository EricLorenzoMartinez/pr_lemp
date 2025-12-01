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

$order = null;
$order_items = [];

// Recuperar el último pedido PENDING del usuario
try {
    $db = App\DB\Database::getInstance();
    $conn = $db->getConnection();

    // Obtener el último pedido PENDING del usuario
    $stmt = $conn->prepare("
        SELECT o.*
        FROM orders o
        WHERE o.buyer_email = ? AND o.status = 'PENDING'
        ORDER BY o.created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$_SESSION['username']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($order) {
        // Obtener los items del pedido
        $stmt = $conn->prepare("
            SELECT oi.*, tt.label, tt.code
            FROM order_items oi
            JOIN ticket_types tt ON oi.ticket_type_id = tt.id
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order['id']]);
        $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        add_flash_message("No se encontró ningún pedido pendiente", "error");
    }

} catch (PDOException $e) {
    add_flash_message("Error cargando el pedido: " . $e->getMessage(), "error");
}

// Procesar las acciones (confirmar o cancelar)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $order) {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'confirm') {
            // Cambiar estado a COMPLETED
            $stmt = $conn->prepare("UPDATE orders SET status = 'COMPLETED' WHERE id = ?");
            $stmt->execute([$order['id']]);

            add_flash_message("Pedido confirmado correctamente. Número de pedido: #" . $order['id'], "success");
            $_SESSION['order_number'] = $order['id'];
            header('Location: confirm.php');
            exit;

        } elseif ($action === 'cancel') {
            // Cambiar estado a CANCELLED
            $stmt = $conn->prepare("UPDATE orders SET status = 'CANCELLED' WHERE id = ?");
            $stmt->execute([$order['id']]);

            add_flash_message("Pedido cancelado correctamente", "warning");
            header('Location: confirm.php');
            exit;
        }
    } catch (PDOException $e) {
        add_flash_message("Error procesando la acción: " . $e->getMessage(), "error");
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Vista previa</title>
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
      <h1 class="text-3xl font-bold mb-4">Vista previa del pedido</h1>
      <nav class="flex flex-wrap gap-4 mb-2">
        <a href="index.php" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-blue-100 transition duration-200">
          Home
        </a>
        <a href="buy.php" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium hover:bg-gray-300 transition duration-200">
          Editar compra
        </a>
      </nav>
      <p class="text-blue-100">Usuario: <?= htmlspecialchars($_SESSION['username']) ?></p>
    </div>
  </header>

  <main class="container mx-auto px-4 py-8">
    <?php if ($order && !empty($order_items)): ?>
      <!-- Contenido del carrito/pedido -->
      <section aria-labelledby="cart-title" class="max-w-4xl mx-auto">
        <h2 id="cart-title" class="text-2xl font-semibold text-blue-700 mb-6">Resumen del pedido #<?= $order['id'] ?></h2>
        <div id="cart-preview" class="bg-white rounded-lg shadow-md p-6 border border-gray-200">
          <div class="space-y-4">
            <?php foreach ($order_items as $item): ?>
              <?php if ($item['quantity'] > 0): ?>
                <div class="cart-item flex justify-between items-center py-3 border-b border-gray-100 last:border-b-0">
                  <div>
                    <span class="font-medium text-gray-800"><?= htmlspecialchars($item['label']) ?> x <?= $item['quantity'] ?></span>
                    <p class="text-sm text-gray-500"><?= number_format($item['unit_price'], 2) ?> € cada uno</p>
                  </div>
                  <span class="font-semibold text-lg text-green-600">
                    <?= number_format($item['quantity'] * $item['unit_price'], 2) ?> €
                  </span>
                </div>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
          <div class="cart-total flex justify-between items-center pt-4 mt-4 border-t border-gray-300">
            <strong class="text-xl text-gray-800">Total:</strong>
            <strong class="text-2xl text-blue-600"><?= number_format($order['total'], 2) ?> €</strong>
          </div>
        </div>
      </section>

      <!-- Acciones: confirmar o cancelar -->
      <div class="max-w-4xl mx-auto mt-8 flex flex-col sm:flex-row gap-4 justify-center">
        <form method="post" class="flex-1 sm:flex-none">
          <button id="finalize-button" type="submit" name="action" value="confirm"
                  class="w-full bg-green-500 text-white py-3 px-6 rounded-lg font-semibold text-lg hover:bg-green-600 transition duration-200">
            Confirmar compra
          </button>
        </form>

        <form method="post" class="flex-1 sm:flex-none">
          <button id="cancel-button" type="submit" name="action" value="cancel"
                  class="w-full bg-red-500 text-white py-3 px-6 rounded-lg font-semibold text-lg hover:bg-red-600 transition duration-200">
            Cancelar pedido
          </button>
        </form>
      </div>

    <?php elseif (!$order): ?>
      <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-8 text-center">
        <p class="text-gray-600 mb-4">No hay pedidos pendientes.</p>
        <a href="buy.php" class="inline-block bg-blue-500 text-white px-6 py-3 rounded-lg font-medium hover:bg-blue-600 transition duration-200">
          Realizar una compra
        </a>
      </div>
    <?php endif; ?>
  </main>

</body>
</html>
