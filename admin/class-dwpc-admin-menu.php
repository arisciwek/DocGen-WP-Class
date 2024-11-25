<?php
/**
 * Admin Menu Handler
 *
 * @package     DocGen_WPClass
 * @subpackage  Admin
 * @version     1.0.0
 * @author      arisciwek
 * 
 * Path: admin/class-dwpc-admin-menu.php
 * 
 * Description: Handles admin menu registration and page routing
 * 
 * Changelog:
 * 1.0.0 - Initial implementation
 * - Menu registration
 * - Page routing to new class structure
 */

if (!defined('ABSPATH')) {
    die('Direct access not permitted.');
}

class DocGen_WPClass_Admin_Menu {
    /**
     * Menu instance
     * @var self
     */
    private static $instance = null;

    /**
     * Parent menu slug
     * @var string
     */
    private $parent_slug = 'docgen-implementation';

    /**
     * Dashboard page instance
     * @var DocGen_WPClass_Dashboard_Page
     */
    private $dashboard_page;

    /**
     * Settings page instance
     * @var DocGen_WPClass_Settings_Page
     */
    private $settings_page;

    /**
     * Constructor
     */
    private function __construct() {
        // Initialize page handlers
        require_once DOCGEN_WPCLASS_DIR . 'admin/class-dwpc-admin-page.php';
        require_once DOCGEN_WPCLASS_DIR . 'admin/class-dwpc-dashboard-page.php';
        require_once DOCGEN_WPCLASS_DIR . 'admin/class-dwpc-settings-page.php';
        require_once DOCGEN_WPCLASS_DIR . 'admin/class-dwpc-directory-handler.php';

        $this->dashboard_page = new DocGen_WPClass_Dashboard_Page();
        $this->settings_page = new DocGen_WPClass_Settings_Page();

        add_action('admin_menu', array($this, 'register_menus'));
    }

    /**
     * Get menu instance
     * @return self
     */
    public static function get_instance() {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register admin menus
     */
    public function register_menus() {
        // Add main menu
        add_menu_page(
            __('DocGen Implementation', 'docgen-implementation'),
            __('DocGen Impl', 'docgen-implementation'),
            'manage_options',
            $this->parent_slug,
            array($this->dashboard_page, 'render'),
            'dashicons-media-document',
            30
        );

        // Add dashboard submenu
        add_submenu_page(
            $this->parent_slug,
            __('Dashboard', 'docgen-implementation'),
            __('Dashboard', 'docgen-implementation'),
            'manage_options',
            $this->parent_slug,
            array($this->dashboard_page, 'render')
        );

        // Add settings submenu
        add_submenu_page(
            $this->parent_slug,
            __('Settings', 'docgen-implementation'),
            __('Settings', 'docgen-implementation'),
            'manage_options',
            $this->parent_slug . '-settings',
            array($this->settings_page, 'render')
        );

        do_action('docgen_wpclass_register_admin_menu');
    }

    /**
     * Get parent menu slug
     * @return string
     */
    public function get_parent_slug() {
        return $this->parent_slug;
    }
}
