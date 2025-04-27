<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package   DynamicPasswordGuard
 * @author    soyunomas
 * @license   GPL-2.0+
 * @link      https://github.com/soyunomas/Dynamic-Password-Guard
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// --- Eliminar Opciones Globales ---
$global_options = [
    'dpg_global_enabled',
    'dpg_allow_user_config',
    // Añadir aquí cualquier otra opción global que creemos en el futuro
];

foreach ( $global_options as $option_name ) {
    delete_option( $option_name );
}


// --- Eliminar Metadatos de Usuario ---
// Definimos las claves de metadatos que usamos
$user_meta_keys = [
    'dpg_user_enabled',
    'dpg_user_pre_key',
    'dpg_user_post_key',
    // Añadir aquí cualquier otro metadato de usuario que creemos
];

// Obtenemos todos los IDs de usuario (puede ser intensivo en sitios muy grandes)
// Para sitios extremadamente grandes, se necesitaría un enfoque por lotes.
$all_user_ids = get_users( array( 'fields' => 'ID' ) );

foreach ( $all_user_ids as $user_id ) {
    foreach ( $user_meta_keys as $meta_key ) {
        delete_user_meta( $user_id, $meta_key );
    }
}

// --- Otras Tareas de Limpieza (si fueran necesarias) ---
// Por ejemplo: eliminar tablas personalizadas (si las creáramos)
// global $wpdb;
// $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}mi_tabla_personalizada" );
