<?php
/**
 * DocGen Implementation
 *
 * @package     DocGen_WPClass
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Plugin Name: DocGen Implementation
 * Plugin URI: http://example.com/docgen-implementation
 * Description: Implementation of WP DocGen for generating various documents
 * Version: 1.0.0
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * Author: arisciwek
 * Author URI: http://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: docgen-implementation
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

// Plugin version
define('DOCGEN_WPCLASS_VERSION', '1.0.0');

// Plugin paths
define('DOCGEN_WPCLASS_FILE', __FILE__);
define('DOCGEN_WPCLASS_DIR', plugin_dir_path(__FILE__));
define('DOCGEN_WPCLASS_URL', plugin_dir_url(__FILE__));
define('DOCGEN_WPCLASS_BASENAME', plugin_basename(__FILE__));

/**
 * Autoloader untuk class-class plugin
 */
spl_autoload_register(function($class) {
    // Base namespace untuk plugin
    $namespace = 'DocGen_WPClass_';
    
    // Check if class uses our namespace
    if (strpos($class, $namespace) !== 0) {
        return;
    }

    // Remove namespace from class name
    $class_name = str_replace($namespace, '', $class);
    
    // Convert class name to filename
    $filename = 'class-' . strtolower(
        str_replace('_', '-', $class_name)
    ) . '.php';

    // Different paths for different types of classes
    $possible_paths = array(
        DOCGEN_WPCLASS_DIR . 'admin/' . $filename,
        DOCGEN_WPCLASS_DIR . 'includes/' . $filename,
        DOCGEN_WPCLASS_DIR . 'modules/' . $filename
    );

    // Try to load the file
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            break;
        }
    }
});

/**
 * Check WP DocGen dependency
 */
function docgen_wpclass_check_dependencies() {
    if (!class_exists('WP_DocGen')) {
        add_action('admin_notices', function() {
            $message = sprintf(
                /* translators: %s: Plugin name */
                __('%s requires WP DocGen plugin to be installed and activated.', 'docgen-implementation'),
                '<strong>DocGen Implementation</strong>'
            );
            echo '<div class="notice notice-error"><p>' . wp_kses_post($message) . '</p></div>';
        });
        return false;
    }
    return true;
}

/**
 * Initialize plugin
 */
function docgen_wpclass_init() {
    // Check dependencies
    if (!docgen_wpclass_check_dependencies()) {
        return;
    }

    // Load required files
    require_once DOCGEN_WPCLASS_DIR . 'includes/class-dwpc-module-loader.php';

    // Initialize module loader
    $module_loader = new DocGen_WPClass_Module_Loader();
    $module_loader->discover_modules();

    // Initialize admin menu - will handle loading of other admin classes
    DocGen_WPClass_Admin_Menu::get_instance();

    // Load text domain
    load_plugin_textdomain(
        'docgen-implementation',
        false,
        dirname(DOCGEN_WPCLASS_BASENAME) . '/languages'
    );

    do_action('docgen_wpclass_loaded');
}
add_action('plugins_loaded', 'docgen_wpclass_init');

/**
 * Plugin activation
 *
function docgen_wpclass_activate() {
    // Create required directories
    $upload_dir = wp_upload_dir();
    $temp_dir = $upload_dir['basedir'] . '/docgen-temp';
    
    require_once DOCGEN_WPCLASS_DIR . 'admin/class-dwpc-directory-handler.php';
    $directory_handler = new DocGen_WPClass_Directory_Handler();
    
    // Create secure temp directory
    $result = $directory_handler->create_secure_directory(
        'docgen-temp',
        $upload_dir['basedir']
    );
    
    if (is_wp_error($result)) {
        wp_die($result->get_error_message());
    }

    // Set default settings
    $default_settings = array(
        'temp_dir' => $temp_dir,
        'template_dir' => trailingslashit($upload_base) . 'docgen-templates',
        'output_format' => 'docx',
        'debug_mode' => false
    );
    
    if (!get_option('docgen_wpclass_settings')) {
        add_option('docgen_wpclass_settings', $default_settings);
    }

    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'docgen_wpclass_activate');
*/

/**
 * Plugin activation
 */
function docgen_wpclass_activate() {
    // Get upload directory
    $upload_dir = wp_upload_dir();
    $upload_base = $upload_dir['basedir'];
    
    require_once DOCGEN_WPCLASS_DIR . 'admin/class-dwpc-directory-handler.php';
    $directory_handler = new DocGen_WPClass_Directory_Handler();
    
    // Set default settings
    $default_settings = array(
        'temp_dir' => trailingslashit($upload_base) . 'docgen-temp',
        'template_dir' => trailingslashit($upload_base) . 'docgen-templates',
        'output_format' => 'docx',
        'debug_mode' => false
    );
    
    // Buat direktori jika belum ada menggunakan method yang sudah ada
    foreach (array('docgen-temp', 'docgen-templates') as $dir) {
        $full_path = trailingslashit($upload_base) . $dir;
        if (!file_exists($full_path)) {
            wp_mkdir_p($full_path);
            // Set basic security
            @chmod($full_path, 0755);
            @file_put_contents($full_path . '/index.php', '<?php // Silence is golden.');
        }
    }
    
    // Update/create settings
    if (!get_option('docgen_wpclass_settings')) {
        add_option('docgen_wpclass_settings', $default_settings);
    } else {
        update_option('docgen_wpclass_settings', $default_settings);
    }

    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'docgen_wpclass_activate');

/**
 * Plugin deactivation
 */
function docgen_wpclass_deactivate() {
    // Get settings
    $settings = get_option('docgen_wpclass_settings');
    
    // Clean up temp directory if exists
    if (!empty($settings['temp_dir']) && file_exists($settings['temp_dir'])) {
        require_once DOCGEN_WPCLASS_DIR . 'admin/class-dwpc-directory-handler.php';
        $directory_handler = new DocGen_WPClass_Directory_Handler();
        
        // Gunakan method clean_directory untuk membersihkan file
        $cleanup_result = $directory_handler->clean_directory($settings['temp_dir'], array(
            'older_than' => 0, // hapus semua file
            'keep_latest' => 0, // tidak perlu menyimpan file
            'recursive' => true // hapus semua subfolder
        ));
        
        if (is_wp_error($cleanup_result)) {
            error_log('Error cleaning temp directory: ' . $cleanup_result->get_error_message());
        } else {
            error_log('Temp directory cleaned: ' . print_r($cleanup_result, true));
        }
    }

    // Clear scheduled hooks
    wp_clear_scheduled_hook('docgen_wpclass_cleanup_temp');

    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'docgen_wpclass_deactivate');

