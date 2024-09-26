# Code for Bricks custom query loop:
## Código para custom query de bricks
```php
// Verificar si la clave 'wishlist' existe en la sesión
$wishlist_ids = isset($_SESSION['wishlist']) ? $_SESSION['wishlist'] : [];

if (empty($wishlist_ids)) {
    echo 'No tienes propiedades en tu wishlist.';
    return []; // Retornar una consulta vacía si no hay favoritos
}

// Si hay favoritos, configurar la consulta para las propiedades favoritas
return [
    'post_type'      => 'property',
    'post_status'    => 'publish',
    'post__in'       => $wishlist_ids, // Filtrar solo las propiedades favoritas
    'posts_per_page' => -1,            // Mostrar todas las propiedades favoritas
    'paged'          => get_query_var('paged', 1) // Soporte para paginación
];
```

### Notas
La ruta de favoritos tiene que ser /favoritos/
se tiene que editar en la línea:
```javascript
if (window.location.pathname === "/favoritos/") {
```
