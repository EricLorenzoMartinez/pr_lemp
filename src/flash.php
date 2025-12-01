<?php
/**
 * Gestió de missatges flash
 *
 * Els missatges flash es guarden a la sessió i es mostren una sola vegada
 */

/**
 * Afegir un missatge flash
 */
function add_flash_message($message, $type = 'info') {
    if (!isset($_SESSION['flash_messages'])) {
        $_SESSION['flash_messages'] = [];
    }
    $_SESSION['flash_messages'][] = [
        'message' => $message,
        'type' => $type
    ];
}

/**
 * Obtenir i esborrar tots els missatges flash
 */
function get_flash_messages() {
    $messages = [];
    if (isset($_SESSION['flash_messages']) && is_array($_SESSION['flash_messages'])) {
        $messages = $_SESSION['flash_messages'];
        unset($_SESSION['flash_messages']);
    }
    return $messages;
}

/**
 * Mostrar tots els missatges flash
 */
function display_flash_messages() {
    $messages = get_flash_messages();
    if (empty($messages)) {
        return;
    }

    foreach ($messages as $flash) {
        $color = 'blue'; // per defecte
        switch ($flash['type']) {
            case 'success':
                $color = 'green';
                break;
            case 'error':
                $color = 'red';
                break;
            case 'warning':
                $color = 'orange';
                break;
            case 'info':
            default:
                $color = 'blue';
                break;
        }

        echo "<div style='color: {$color}; padding: 10px; margin: 10px 0; border: 1px solid {$color}; border-radius: 4px;'>{$flash['message']}</div>";
    }
}

/**
 * Comprovar si hi ha missatges flash
 */
function has_flash_messages() {
    return !empty($_SESSION['flash_messages']);
}
?>
