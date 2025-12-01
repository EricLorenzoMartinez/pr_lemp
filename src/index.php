<?php
require_once 'db.php';
require_once 'flash.php';
session_start();

// Configuración inicial
$filter = $_GET['filter'] ?? 'all';

try {
    $db = App\DB\Database::getInstance();
    $conn = $db->getConnection();

    // Construir la consulta según el filtro
    $sql = "SELECT * FROM attractions";
    $params = [];

    if ($filter === 'available') {
        $sql .= " WHERE maintenance = 0";
    } elseif ($filter === 'maintenance') {
        $sql .= " WHERE maintenance = 1";
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $attractions = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $attraction_count = count($attractions);

} catch (PDOException $e) {
    error_log("Error en la consulta: " . $e->getMessage());
    $attractions = [];
    $attraction_count = 0;
    add_flash_message("Error cargando las atracciones", "error");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Taquilla — Home</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-50 text-gray-800 font-sans">

  <!-- Mensajes flash -->
  <div id="flash-message" aria-live="polite" class="container mx-auto px-4 py-2">
    <?php display_flash_messages(); ?>
  </div>

  <header class="bg-blue-600 text-white shadow-md">
    <div class="container mx-auto px-4 py-6">
      <h1 class="text-3xl font-bold">Parque Temático de Coches Deportivos</h1>
      <nav class="mt-4">
        <a href="login.php" class="bg-white text-blue-600 px-4 py-2 rounded-lg font-medium hover:bg-blue-100 transition duration-200">
          Iniciar compra
        </a>
      </nav>
    </div>
  </header>

  <!-- Imagen temática -->
  <figure class="container mx-auto px-4 py-8">
    <img id="theme-image" src="./assets/img/theme_image.jpg" alt="Imagen temática del parque de coches deportivos"
         class="w-full h-64 object-cover rounded-lg shadow-md" />
    <figcaption class="text-center text-gray-600 mt-2 italic">
      Bienvenido al mundo de la velocidad
    </figcaption>
  </figure>

  <!-- Filtro tipo desplegable -->
  <section aria-labelledby="filtro-title" class="container mx-auto px-4 py-6">
    <h2 id="filtro-title" class="text-2xl font-semibold mb-4">Filtrar atracciones</h2>

    <div class="flex items-center gap-4 bg-white p-4 rounded-lg shadow-sm border">
      <form method="GET" class="flex items-center gap-3">
        <label for="filter-maintenance" class="font-medium">Estado:</label>
        <select id="filter-maintenance" name="filter" onchange="this.form.submit()"
                class="border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>Todas</option>
            <option value="maintenance" <?= $filter === 'maintenance' ? 'selected' : '' ?>>En mantenimiento</option>
            <option value="available" <?= $filter === 'available' ? 'selected' : '' ?>>Disponibles</option>
        </select>
      </form>

      <span class="text-lg">
        Mostrando: <strong id="attraction-count" class="text-blue-600"><?= $attraction_count ?></strong> atracciones
      </span>
    </div>
  </section>

  <!-- Lista de atracciones -->
  <section aria-labelledby="lista-title" class="container mx-auto px-4 py-6">
    <h2 id="lista-title" class="text-2xl font-semibold mb-6">Atracciones</h2>
    <div id="attraction-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php if (empty($attractions)): ?>
            <p class="text-gray-500 text-center col-span-full py-8">No se encontraron atracciones.</p>
        <?php else: ?>
            <?php foreach ($attractions as $attraction): ?>
                <article class="attraction bg-white rounded-lg shadow-md p-6 border border-gray-200 hover:shadow-lg transition duration-200">
                    <h3 class="text-xl font-semibold text-blue-700 mb-2"><?= htmlspecialchars($attraction['name']) ?></h3>
                    <p class="text-gray-600 mb-4"><?= htmlspecialchars($attraction['description']) ?></p>

                    <span class="badge <?= $attraction['maintenance'] ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' ?> px-3 py-1 rounded-full text-sm font-medium">
                        <?= $attraction['maintenance'] ? 'En mantenimiento' : 'Disponible' ?>
                    </span>

                    <div class="mt-4 space-y-1 text-sm text-gray-500">
                        <?php if ($attraction['duration_minutes']): ?>
                            <p>Duración: <span class="font-medium"><?= $attraction['duration_minutes'] ?> minutos</span></p>
                        <?php endif; ?>
                        <?php if ($attraction['min_height_cm']): ?>
                            <p>Altura mínima: <span class="font-medium"><?= $attraction['min_height_cm'] ?> cm</span></p>
                        <?php endif; ?>
                        <?php if ($attraction['category']): ?>
                            <p>Categoría: <span class="font-medium"><?= htmlspecialchars($attraction['category']) ?></span></p>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
  </section>

</body>
</html>
