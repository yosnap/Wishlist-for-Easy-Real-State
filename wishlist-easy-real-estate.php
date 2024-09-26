<?php

/**
 * Plugin Name: Wishlist para Propiedades
 * Plugin URI: https://example.com
 * Description: Un plugin que permite a los usuarios añadir propiedades a una lista de favoritos usando LocalStorage y un ícono SVG personalizado.
 * Version: 1.8
 * Author: YoSn4p
 * Author URI: https://example.com
 */

if (!defined('ABSPATH')) {
    exit;
}

// Registrar el shortcode para mostrar el botón de wishlist con SVG
function wishlist_button_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'property_id' => '',
    ), $atts);

    $property_id = !empty($atts['property_id']) ? $atts['property_id'] : get_the_ID();

    if (empty($property_id)) {
        return '';
    }

    ob_start();
?>
    <div id="wishlist-btn-<?php echo esc_attr($property_id); ?>" class="wishlist-toggle" data-id="<?php echo esc_attr($property_id); ?>" aria-label="Agregar a favoritos">
        <!-- SVG corazón vacío -->
        <svg class="wishlist-icon" height="24" version="1.1" width="24" xmlns="http://www.w3.org/2000/svg">
            <g transform="translate(0 -1028.4)">
                <path d="m7 1031.4c-1.5355 0-3.0784 0.5-4.25 1.7-2.3431 2.4-2.2788 6.1 0 8.5l9.25 9.8 9.25-9.8c2.279-2.4 2.343-6.1 0-8.5-2.343-2.3-6.157-2.3-8.5 0l-0.75 0.8-0.75-0.8c-1.172-1.2-2.7145-1.7-4.25-1.7z" class="wishlist-heart" fill="none" stroke="#c0392b" stroke-width="2" />
            </g>
        </svg>
        <span class="tooltip-text">Agregar a Wishlist</span>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('wishlist_button', 'wishlist_button_shortcode');

// Cargar los estilos CSS personalizados
function wishlist_enqueue_styles()
{
    wp_enqueue_style('wishlist-styles', plugin_dir_url(__FILE__) . 'wishlist.css');
}
add_action('wp_enqueue_scripts', 'wishlist_enqueue_styles');

// Cargar los scripts JS personalizados
function wishlist_enqueue_scripts()
{
    wp_enqueue_script('wishlist-js', plugin_dir_url(__FILE__) . 'wishlist.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'wishlist_enqueue_scripts');

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Inicia la sesión si no está ya iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Función para procesar los IDs de favoritos y almacenarlos en la sesión
function set_wishlist_ids_in_session()
{
    if (isset($_POST['wishlist_ids'])) {
        $wishlist_ids = json_decode(stripslashes($_POST['wishlist_ids']), true);

        if (!empty($wishlist_ids)) {
            $_SESSION['wishlist'] = $wishlist_ids; // Almacenar en la sesión
            wp_send_json_success($wishlist_ids);
        } else {
            wp_send_json_error('No se enviaron IDs válidos.');
        }
    } else {
        wp_send_json_error('No se recibieron IDs.');
    }
}



// Registrar las funciones AJAX
add_action('wp_ajax_set_wishlist_ids', 'set_wishlist_ids_in_session');
add_action('wp_ajax_nopriv_set_wishlist_ids', 'set_wishlist_ids_in_session');
