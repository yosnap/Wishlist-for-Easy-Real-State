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
        <svg class="wishlist-icon" height="24" version="1.1" width="24" xmlns="http://www.w3.org/2000/svg" xmlns:cc="http://creativecommons.org/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#">
            <g transform="translate(0 -1028.4)">
                <path d="m7 1031.4c-1.5355 0-3.0784 0.5-4.25 1.7-2.3431 2.4-2.2788 6.1 0 8.5l9.25 9.8 9.25-9.8c2.279-2.4 2.343-6.1 0-8.5-2.343-2.3-6.157-2.3-8.5 0l-0.75 0.8-0.75-0.8c-1.172-1.2-2.7145-1.7-4.25-1.7z" class="wishlist-heart" fill="none" stroke="#c0392b" stroke-width="2" />
            </g>
        </svg>
        <span class="tooltip-text">Agregar a Wishlist</span>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const wishlistBtn = document.getElementById('wishlist-btn-<?php echo esc_attr($property_id); ?>');
            const propertyId = wishlistBtn.getAttribute('data-id');
            const tooltip = wishlistBtn.querySelector('.tooltip-text');

            // Crear un portal para el tooltip y agregarlo al body
            const tooltipPortal = document.createElement('div');
            tooltipPortal.classList.add('tooltip-text-portal');
            document.body.appendChild(tooltipPortal);

            // Función para actualizar el texto del tooltip
            function updateTooltipText(isInWishlist) {
                if (isInWishlist) {
                    tooltipPortal.textContent = 'Eliminar del Wishlist';
                } else {
                    tooltipPortal.textContent = 'Agregar a Wishlist';
                }
            }

            // Posicionar el tooltip portal al hacer hover sobre el botón
            wishlistBtn.addEventListener('mouseenter', function() {
                const rect = wishlistBtn.getBoundingClientRect();
                tooltipPortal.style.visibility = 'visible';
                tooltipPortal.style.opacity = '1';
                tooltipPortal.style.top = `${rect.top - tooltipPortal.offsetHeight}px`; // Posicionarlo encima del botón
                tooltipPortal.style.left = `${rect.left + (wishlistBtn.offsetWidth / 2) - (tooltipPortal.offsetWidth / 2)}px`; // Centrarlo horizontalmente
            });

            wishlistBtn.addEventListener('mouseleave', function() {
                tooltipPortal.style.visibility = 'hidden';
                tooltipPortal.style.opacity = '0';
            });

            // Evitar que el botón de favoritos propague el clic al enlace de la card
            wishlistBtn.addEventListener('click', function(event) {
                event.stopPropagation(); // Detiene la propagación del clic al enlace principal
            });

            // Obtener la lista de favoritos desde LocalStorage
            let wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];

            // Función para sincronizar los favoritos con el servidor mediante AJAX
            function updateWishlistOnServer(wishlist) {
                fetch('/wp-admin/admin-ajax.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: 'action=set_wishlist_ids&wishlist_ids=' + JSON.stringify(wishlist)
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Favoritos sincronizados con el servidor:', data);

                        // Recargar la página solo si estamos en la página de favoritos
                        if (window.location.pathname === '/favoritos/') { // Asegúrate de usar la URL correcta
                            location.reload(); // Recargar para actualizar el listado de favoritos
                        }
                    })
                    .catch((error) => {
                        console.error('Error al sincronizar los favoritos:', error);
                    });
            }

            // Si el ítem ya está en favoritos, actualizar la clase CSS y el texto del tooltip
            if (wishlist.includes(propertyId)) {
                wishlistBtn.classList.add('in-wishlist'); // Añadir clase si está en favoritos
                updateTooltipText(true); // Cambiar el texto a "Eliminar del Wishlist"
            } else {
                updateTooltipText(false); // Cambiar el texto a "Agregar a Wishlist"
            }

            // Escuchar el evento de clic para agregar o quitar de favoritos
            wishlistBtn.addEventListener('click', function() {
                let wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];
                const isInWishlist = wishlist.includes(propertyId);

                if (isInWishlist) {
                    wishlist = wishlist.filter(id => id !== propertyId); // Quitar de favoritos
                    wishlistBtn.classList.remove('in-wishlist'); // Eliminar clase CSS si se quita de favoritos
                } else {
                    wishlist.push(propertyId); // Agregar a favoritos
                    wishlistBtn.classList.add('in-wishlist'); // Añadir clase CSS para indicar favorito
                }

                // Actualizar el texto del tooltip según el estado del ítem
                updateTooltipText(!isInWishlist);

                // Actualizar LocalStorage
                localStorage.setItem('wishlist', JSON.stringify(wishlist));

                // Sincronizar con el servidor
                updateWishlistOnServer(wishlist);
            });
        });
    </script>


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

// Iniciar sesión si no está iniciada
if (!session_id()) {
    session_start();
}

// Función AJAX para establecer los IDs de favoritos en la sesión
function set_wishlist_ids_in_session()
{
    if (isset($_POST['wishlist_ids'])) {
        // Decodificar los IDs de favoritos enviados por AJAX
        $wishlist_ids = json_decode(stripslashes($_POST['wishlist_ids']), true);

        if (!empty($wishlist_ids)) {
            // Almacenar los IDs de favoritos en una sesión
            $_SESSION['wishlist_ids'] = $wishlist_ids;
            wp_send_json_success($wishlist_ids);
        } else {
            wp_send_json_error('No se enviaron IDs válidos.');
        }
    } else {
        wp_send_json_error('No se recibieron IDs.');
    }
}
add_action('wp_ajax_set_wishlist_ids', 'set_wishlist_ids_in_session');
add_action('wp_ajax_nopriv_set_wishlist_ids', 'set_wishlist_ids_in_session');
