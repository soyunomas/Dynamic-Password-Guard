<?php
/**
 * Plugin Name:       Dynamic Password Guard
 * Plugin URI:        https://github.com/soyunomas/Dynamic-Password-Guard
 * Description:       Aumenta la seguridad del inicio de sesión requiriendo una contraseña base combinada con datos dinámicos basados en el tiempo.
 * Version:           1.1.1
 * Requires at least: 5.2
 * Requires PHP:      7.4
 * Author:            (Tu Nombre o Nombre de Empresa)
 * Author URI:        (Tu URL)
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       dynamic-password-guard
 * Domain Path:       /languages
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) or die( '¡Acceso no autorizado!' );

// --- Define plugin constants ---
// Sugerencia: Actualizar versión si haces release con estos cambios
define( 'DPG_VERSION', '1.1.1' );
define( 'DPG_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'DPG_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'DPG_PLUGIN_FILE', __FILE__ );
define( 'DPG_OPTIONS_GROUP', 'dpg_settings_group' );
define( 'DPG_SETTINGS_PAGE_SLUG', 'dynamic-password-guard-settings' );
define( 'DPG_GLOBAL_ENABLED_OPTION', 'dpg_global_enabled' );
define( 'DPG_ALLOW_USER_CONFIG_OPTION', 'dpg_allow_user_config' );
define( 'DPG_USER_ENABLED_META', 'dpg_user_enabled' );
define( 'DPG_USER_PRE_KEY_META', 'dpg_user_pre_key' );
define( 'DPG_USER_POST_KEY_META', 'dpg_user_post_key' );


// --- Activation Hook ---
/**
 * Sets default global options on plugin activation.
 */
function dpg_activate() {
    $default_options = [
        DPG_GLOBAL_ENABLED_OPTION      => 0,
        DPG_ALLOW_USER_CONFIG_OPTION => 0
    ];
    foreach ( $default_options as $option_name => $default_value ) {
        add_option( $option_name, $default_value );
    }
}
register_activation_hook( DPG_PLUGIN_FILE, 'dpg_activate' );


// --- Deactivation Hook ---
/**
 * Placeholder for deactivation tasks.
 */
function dpg_deactivate() {
    // Nothing needed for deactivation in this version.
}
register_deactivation_hook( DPG_PLUGIN_FILE, 'dpg_deactivate' );


// --- Carga del Text Domain ---
/**
 * Loads the plugin text domain for translation.
 */
function dpg_load_textdomain() {
    load_plugin_textdomain( 'dynamic-password-guard', false, basename( DPG_PLUGIN_DIR ) . '/languages' );
}
add_action( 'plugins_loaded', 'dpg_load_textdomain' );


// --- Admin Settings Page ---
/**
 * Adds the options page to the admin menu.
 */
function dpg_add_admin_menu() {
    add_options_page(
        __( 'Dynamic Password Guard', 'dynamic-password-guard' ),
        __( 'Dynamic Password Guard', 'dynamic-password-guard' ),
        'manage_options',
        DPG_SETTINGS_PAGE_SLUG,
        'dpg_render_settings_page'
    );
}
add_action( 'admin_menu', 'dpg_add_admin_menu' );

/**
 * Registers settings, sections, and fields.
 */
function dpg_settings_init() {
    register_setting(DPG_OPTIONS_GROUP, DPG_GLOBAL_ENABLED_OPTION, 'dpg_sanitize_checkbox');
    register_setting(DPG_OPTIONS_GROUP, DPG_ALLOW_USER_CONFIG_OPTION, 'dpg_sanitize_checkbox');

    add_settings_section(
        'dpg_general_settings_section',
        __( 'Configuración General', 'dynamic-password-guard' ),
        'dpg_general_settings_section_callback',
        DPG_SETTINGS_PAGE_SLUG
    );
    add_settings_field(DPG_GLOBAL_ENABLED_OPTION, __('Habilitar Globalmente', 'dynamic-password-guard'), 'dpg_render_global_enabled_field', DPG_SETTINGS_PAGE_SLUG, 'dpg_general_settings_section');
    add_settings_field(DPG_ALLOW_USER_CONFIG_OPTION, __('Permitir Configuración por Usuario', 'dynamic-password-guard'), 'dpg_render_allow_user_config_field', DPG_SETTINGS_PAGE_SLUG, 'dpg_general_settings_section');
}
add_action( 'admin_init', 'dpg_settings_init' );

/**
 * Renders the description for the general settings section.
 */
function dpg_general_settings_section_callback() {
    echo '<p>' . esc_html__( 'Ajusta el comportamiento general de Dynamic Password Guard.', 'dynamic-password-guard' ) . '</p>';
}

/**
 * Renders the checkbox field for the 'dpg_global_enabled' option.
 */
function dpg_render_global_enabled_field() {
    $option_value = get_option( DPG_GLOBAL_ENABLED_OPTION, 0 );
    ?>
    <label><input type="checkbox" name="<?php echo esc_attr( DPG_GLOBAL_ENABLED_OPTION ); ?>" value="1" <?php checked( 1, $option_value ); ?> /> <?php esc_html_e( 'Activar la funcionalidad de contraseña dinámica para todo el sitio.', 'dynamic-password-guard' ); ?></label>
    <p class="description"><?php esc_html_e( 'Si está marcado, el plugin intentará aplicar las reglas de contraseña dinámica durante el inicio de sesión.', 'dynamic-password-guard' ); ?></p>
    <?php
}

/**
 * Renders the checkbox field for the 'dpg_allow_user_config' option.
 */
function dpg_render_allow_user_config_field() {
    $option_value = get_option( DPG_ALLOW_USER_CONFIG_OPTION, 0 );
    ?>
    <label><input type="checkbox" name="<?php echo esc_attr( DPG_ALLOW_USER_CONFIG_OPTION ); ?>" value="1" <?php checked( 1, $option_value ); ?> /> <?php esc_html_e( 'Permitir que los usuarios configuren sus propias reglas en su perfil.', 'dynamic-password-guard' ); ?></label>
    <p class="description"><?php esc_html_e( 'Si está marcado, aparecerá una sección en la página de perfil de cada usuario.', 'dynamic-password-guard' ); ?> <?php esc_html_e( 'Nota: La funcionalidad por usuario solo aplica si la opción global "Habilitar Globalmente" también está activada.', 'dynamic-password-guard' ); ?></p>
    <?php
}

/**
 * Sanitizes checkbox input. Ensures the value is either 0 or 1.
 * @param mixed $input The input value.
 * @return int 0 or 1.
 */
function dpg_sanitize_checkbox( $input ) {
    return ( isset( $input ) && $input == 1 ? 1 : 0 );
}

/**
 * Renders the HTML content of the settings page.
 */
function dpg_render_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('No tienes permisos suficientes para acceder a esta página.', 'dynamic-password-guard'));
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php settings_fields( DPG_OPTIONS_GROUP ); ?>
            <?php do_settings_sections( DPG_SETTINGS_PAGE_SLUG ); ?>
            <?php submit_button( __( 'Guardar Cambios', 'dynamic-password-guard' ) ); ?>
        </form>
    </div>
    <?php
}


// --- User Profile Fields ---
/**
 * Returns the available time variable options for dropdowns.
 * @return array Associative array of [value => label].
 */
function dpg_get_time_variable_options() {
    // El contenido de esta función no cambia
     return [
        'none'          => __( 'Ninguna', 'dynamic-password-guard' ),
        'hour_hh'       => __( 'Hora (00-23)', 'dynamic-password-guard' ),
        'minute_mm'     => __( 'Minuto (00-59)', 'dynamic-password-guard' ),
        'day_dd'        => __( 'Día del Mes (01-31)', 'dynamic-password-guard' ),
        'month_mm'      => __( 'Mes (01-12)', 'dynamic-password-guard' ),
        'year_yy'       => __( 'Año (últimos 2 dígitos, ej. 24)', 'dynamic-password-guard' ),
        'dayofweek_num' => __( 'Día de la Semana (1=Lunes, 7=Domingo)', 'dynamic-password-guard' ),
    ];
}

/**
 * Displays the DPG configuration fields on the user profile page.
 * ***** MODIFICADA para añadir el campo nonce *****
 * @param WP_User $user The user object whose profile is being displayed.
 */
function dpg_render_user_profile_fields( $user ) {
    if ( ! get_option( DPG_ALLOW_USER_CONFIG_OPTION, 0 ) ) return;
    // Verificación explícita si el usuario actual puede editar al usuario mostrado
    if ( ! current_user_can( 'edit_user', $user->ID ) ) return;

    $user_enabled = get_user_meta( $user->ID, DPG_USER_ENABLED_META, true );
    $pre_key      = get_user_meta( $user->ID, DPG_USER_PRE_KEY_META, true );
    $post_key     = get_user_meta( $user->ID, DPG_USER_POST_KEY_META, true );
    $user_enabled = ( $user_enabled === '' ) ? 0 : (int) $user_enabled;
    $pre_key      = empty( $pre_key ) ? 'none' : $pre_key;
    $post_key     = empty( $post_key ) ? 'none' : $post_key;
    $time_options = dpg_get_time_variable_options();
    ?>
    <h2><?php esc_html_e( 'Dynamic Password Guard', 'dynamic-password-guard' ); ?></h2>
    <p class="description">
        <?php esc_html_e('Configura una capa adicional para tu contraseña. Si la activas, necesitarás añadir prefijo/sufijo basado en tiempo a tu contraseña habitual.', 'dynamic-password-guard'); ?><br>
        <?php esc_html_e('Ej: Contraseña="MiPass123", Pre="Hora(HH)", Post="Día(DD)". Si son 14:30 del día 08, introduce "14MiPass12308".', 'dynamic-password-guard'); ?>
    </p>
    <table class="form-table" role="presentation">
        <tr>
            <th><label for="<?php echo esc_attr( DPG_USER_ENABLED_META ); ?>"><?php esc_html_e( 'Activar Contraseña Dinámica', 'dynamic-password-guard' ); ?></label></th>
            <td>
                <fieldset><legend class="screen-reader-text"><span><?php esc_html_e( 'Activar Contraseña Dinámica', 'dynamic-password-guard' ); ?></span></legend>
                <label><input type="checkbox" name="<?php echo esc_attr( DPG_USER_ENABLED_META ); ?>" id="<?php echo esc_attr( DPG_USER_ENABLED_META ); ?>" value="1" <?php checked( 1, $user_enabled ); ?> /> <?php esc_html_e( 'Usar contraseña dinámica para mi cuenta.', 'dynamic-password-guard' ); ?></label></fieldset>
                <p class="description"><?php esc_html_e( 'Si está marcado, deberás usar el formato dinámico al iniciar sesión.', 'dynamic-password-guard' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="<?php echo esc_attr( DPG_USER_PRE_KEY_META ); ?>"><?php esc_html_e( 'Variable Pre-Clave (Prefijo)', 'dynamic-password-guard' ); ?></label></th>
            <td>
                <select name="<?php echo esc_attr( DPG_USER_PRE_KEY_META ); ?>" id="<?php echo esc_attr( DPG_USER_PRE_KEY_META ); ?>">
                    <?php foreach ( $time_options as $value => $label ) : ?>
                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $pre_key, $value ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="description"><?php esc_html_e( 'Se añadirá ANTES de tu contraseña base.', 'dynamic-password-guard' ); ?></p>
            </td>
        </tr>
        <tr>
            <th><label for="<?php echo esc_attr( DPG_USER_POST_KEY_META ); ?>"><?php esc_html_e( 'Variable Post-Clave (Sufijo)', 'dynamic-password-guard' ); ?></label></th>
            <td>
                <select name="<?php echo esc_attr( DPG_USER_POST_KEY_META ); ?>" id="<?php echo esc_attr( DPG_USER_POST_KEY_META ); ?>">
                    <?php foreach ( $time_options as $value => $label ) : ?>
                        <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $post_key, $value ); ?>><?php echo esc_html( $label ); ?></option>
                    <?php endforeach; ?>
                </select>
                 <p class="description"><?php esc_html_e( 'Se añadirá DESPUÉS de tu contraseña base.', 'dynamic-password-guard' ); ?></p>
            </td>
        </tr>
        <?php
        // *** NUEVO: Añadir campo Nonce para seguridad ***
        // Se coloca dentro de la tabla para asegurar que se envíe con el formulario
        ?>
        <tr>
            <td colspan="2"><?php wp_nonce_field( 'dpg_save_user_profile_' . $user->ID, 'dpg_profile_nonce' ); ?></td>
        </tr>
    </table>
    <?php
}
add_action( 'show_user_profile', 'dpg_render_user_profile_fields', 10, 1 );
add_action( 'edit_user_profile', 'dpg_render_user_profile_fields', 10, 1 );

/**
 * Saves the DPG configuration fields from the user profile page.
 * ***** MODIFICADA para verificar el nonce *****
 * @param int $user_id The ID of the user being updated.
 */
function dpg_save_user_profile_fields( $user_id ) {
    // 1. Comprobaciones iniciales (Mantener)
    if ( ! get_option( DPG_ALLOW_USER_CONFIG_OPTION, 0 ) ) return;
    // Verificación explícita de permisos
    if ( ! current_user_can( 'edit_user', $user_id ) ) {
         return; // Salir si no tiene permisos
    }

    // 2. *** NUEVO: Verificar el Nonce ***
    // Comprueba si el campo nonce fue enviado y si es válido para la acción esperada.
    if ( ! isset( $_POST['dpg_profile_nonce'] ) || ! wp_verify_nonce( sanitize_key($_POST['dpg_profile_nonce']), 'dpg_save_user_profile_' . $user_id ) ) {
       // Si el nonce no está o no es válido, muestra un error y detiene la ejecución.
       wp_die( __( 'Error de seguridad al guardar la configuración de Dynamic Password Guard. Por favor, inténtalo de nuevo.', 'dynamic-password-guard' ), __( 'Error de Seguridad', 'dynamic-password-guard' ), array( 'response' => 403 ) );
    }

    // 3. El resto del código para guardar los datos (Mantener)
    // Save Enabled State
    $user_enabled = isset( $_POST[ DPG_USER_ENABLED_META ] ) && $_POST[ DPG_USER_ENABLED_META ] == '1' ? 1 : 0;
    update_user_meta( $user_id, DPG_USER_ENABLED_META, $user_enabled );

    $valid_time_options = array_keys( dpg_get_time_variable_options() );

    // Save Pre-Key
    $selected_pre_key = 'none';
    if ( isset( $_POST[ DPG_USER_PRE_KEY_META ] ) ) {
        $potential_key = sanitize_text_field( wp_unslash( $_POST[ DPG_USER_PRE_KEY_META ] ) );
        if ( in_array( $potential_key, $valid_time_options, true ) ) {
            $selected_pre_key = $potential_key;
        }
    }
    update_user_meta( $user_id, DPG_USER_PRE_KEY_META, $selected_pre_key );

    // Save Post-Key
    $selected_post_key = 'none';
     if ( isset( $_POST[ DPG_USER_POST_KEY_META ] ) ) {
        $potential_key = sanitize_text_field( wp_unslash( $_POST[ DPG_USER_POST_KEY_META ] ) );
        if ( in_array( $potential_key, $valid_time_options, true ) ) {
            $selected_post_key = $potential_key;
        }
    }
    update_user_meta( $user_id, DPG_USER_POST_KEY_META, $selected_post_key );
}
add_action( 'personal_options_update', 'dpg_save_user_profile_fields', 10, 1 );
add_action( 'edit_user_profile_update', 'dpg_save_user_profile_fields', 10, 1 );


// --- Core Authentication Logic ---

/**
 * Calculates the dynamic key value based on the key type and current time.
 * Uses the WordPress configured timezone via current_time().
 *
 * @param string $key_type The type of key ('none', 'hour_hh', 'minute_mm', etc.).
 * @param int    $timestamp The current timestamp adjusted to WP's timezone.
 * @return string The calculated key value or an empty string.
 */
function dpg_calculate_dynamic_key_value( $key_type, $timestamp ) {
    if ( 'none' === $key_type || empty( $key_type ) ) return '';
    // Format according to the selected key type
    switch ( $key_type ) {
        case 'hour_hh': return date( 'H', $timestamp ); // 24-hour format (00-23)
        case 'minute_mm': return date( 'i', $timestamp ); // Minutes with leading zeros (00-59)
        case 'day_dd': return date( 'd', $timestamp ); // Day of the month, 2 digits with leading zeros (01-31)
        case 'month_mm': return date( 'm', $timestamp ); // Numeric representation of a month, with leading zeros (01-12)
        case 'year_yy': return date( 'y', $timestamp ); // A two digit representation of a year (e.g., 24)
        case 'dayofweek_num': return date( 'N', $timestamp ); // ISO 8601 numeric representation of the day of the week (1 for Monday through 7 for Sunday)
        default: return ''; // Return empty for unknown types
    }
}

/**
 * Main authentication filter for Dynamic Password Guard.
 * Hooks into 'authenticate' to check for dynamic password rules before standard WP checks.
 *
 * @param WP_User|WP_Error|null $user     WP_User object if already authenticated, WP_Error on error, null otherwise.
 * @param string                $username Submitted username.
 * @param string                $password Submitted password (potentially the combined dynamic password).
 * @return WP_User|WP_Error|null WP_User on success, WP_Error on DPG format failure, null on DPG base password failure (for stealth).
 */
function dpg_authenticate_user( $user, $username, $password ) {

    // 1. Initial Checks: If another filter already decided, or basic data is missing.
    if ( $user instanceof WP_User || is_wp_error( $user ) ) {
        return $user; // Pass through if already resolved.
    }
    if ( empty( $username ) || empty( $password ) ) {
        return $user; // Let WP handle empty fields. $user is null here.
    }

    // 2. Check if the plugin is globally enabled.
    $is_globally_enabled = get_option( DPG_GLOBAL_ENABLED_OPTION, 0 );
    if ( ! $is_globally_enabled ) {
        return $user; // DPG is off, use standard authentication. $user is null here.
    }

    // 3. Get the WP_User object for the submitted username.
    $user_object = get_user_by( 'login', $username );

    // If user doesn't exist, return null to let WP handle the standard "Unknown username" error.
    if ( ! $user_object instanceof WP_User ) {
        return null;
    }

    // 4. Check if DPG is enabled for this specific user.
    $is_user_config_allowed = get_option( DPG_ALLOW_USER_CONFIG_OPTION, 0 );
    $is_dpg_enabled_for_user = get_user_meta( $user_object->ID, DPG_USER_ENABLED_META, true );
    $is_user_config_allowed_bool = (bool) $is_user_config_allowed;
    $is_dpg_enabled_for_user_bool = (bool) $is_dpg_enabled_for_user;

    // If user config isn't allowed OR this user hasn't enabled it, DPG logic doesn't apply.
    if ( ! $is_user_config_allowed_bool || ! $is_dpg_enabled_for_user_bool ) {
        return $user; // Use standard authentication. $user is null here.
    }

    // --- DPG Logic Applies ---

    // 5. Get the user's Pre/Post key configuration.
    $pre_key_type = get_user_meta( $user_object->ID, DPG_USER_PRE_KEY_META, true );
    $post_key_type = get_user_meta( $user_object->ID, DPG_USER_POST_KEY_META, true );
    $pre_key_type = empty( $pre_key_type ) ? 'none' : $pre_key_type;
    $post_key_type = empty( $post_key_type ) ? 'none' : $post_key_type;

    // 6. If both keys are 'none', DPG is enabled but inactive for this user. Use standard auth.
    if ( 'none' === $pre_key_type && 'none' === $post_key_type ) {
        // Aunque esté 'activo', si no hay claves, funciona como desactivado para este login.
        return $user; // $user is null here.
    }

    // 7. Calculate the expected dynamic key values based on WordPress time.
    $current_timestamp = current_time( 'timestamp' );
    $expected_pre_value = dpg_calculate_dynamic_key_value( $pre_key_type, $current_timestamp );
    $expected_post_value = dpg_calculate_dynamic_key_value( $post_key_type, $current_timestamp );

    $pre_len = strlen( $expected_pre_value );
    $post_len = strlen( $expected_post_value );
    $submitted_len = strlen( $password );

    // 8. Verify if the submitted password matches the expected dynamic format.
    $format_ok = true; // Assume OK initially

    // Check minimum length
    if ( $submitted_len < ( $pre_len + $post_len ) ) {
        $format_ok = false;
    }

    // Check prefix (if applicable and format still potentially OK)
    if ( $format_ok && $pre_len > 0 ) {
        // Use strict comparison
        if ( substr( $password, 0, $pre_len ) !== $expected_pre_value ) {
            $format_ok = false;
        }
    }

    // Check suffix (if applicable and format still potentially OK)
    if ( $format_ok && $post_len > 0 ) {
        // Check length before substr comparison
        if ($submitted_len < $post_len || substr( $password, -$post_len ) !== $expected_post_value ) {
             $format_ok = false;
         }
    }

    // --- Final Decision ---
    if ( $format_ok ) {
        // Format matches! Now, extract and check the base password.
        $base_password_len = $submitted_len - $pre_len - $post_len;
        // Ensure length isn't negative (shouldn't happen with checks above, but safety first)
        $base_password_len = max(0, $base_password_len);
        $potential_base_password = substr( $password, $pre_len, $base_password_len );

        // Verify the extracted base password against the stored hash.
        if ( wp_check_password( $potential_base_password, $user_object->user_pass, $user_object->ID ) ) {
            // SUCCESS! Format and base password are correct.
            return $user_object; // Grant access.
        } else {
            // Base password incorrect.
            // STEALTH MODE: Return null. This allows standard WP auth filters (priority 20+)
            // to run, which will fail because the full submitted password doesn't match hash,
            // resulting in the generic "Incorrect password" error without revealing DPG.
            return null;
        }
    } else {
        // Format does NOT match the expected dynamic format (e.g., user submitted only base password).
        // FORCE FAILURE NOW: We must prevent standard WP auth filters (priority 20+)
        // from potentially validating the submitted password if it happens to be the base password.
        // We achieve this by removing those standard filters *before* returning an error.
        // This ensures only the DPG format works when DPG is active.

        remove_filter( 'authenticate', 'wp_authenticate_username_password',    20 );
        remove_filter( 'authenticate', 'wp_authenticate_email_password',       20 );
        // Optional: remove application password check too for completeness
        // remove_filter( 'authenticate', 'wp_authenticate_application_password', 20 );

        // Now return a WP_Error. This halts the 'authenticate' filter chain for this attempt
        // and signals WordPress to show an error. We use the custom error message defined earlier.
        return new WP_Error('incorrect_password', __('<strong>ERROR</strong>: La contraseña introducida no tiene el formato dinámico correcto o la contraseña base es incorrecta.', 'dynamic-password-guard'));
    }

} // End of dpg_authenticate_user function

// Hook our function into 'authenticate' with PRIORITY 10.
add_filter( 'authenticate', 'dpg_authenticate_user', 10, 3 );

