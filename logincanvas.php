<?php
/**
 * Plugin Name: LoginCanvas
 * Plugin URI: https://github.com/madnansultandotme/logincanvas
 * Description: Enhance your WordPress login page with random background images and header/footer support
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * Author: Muhammad Adnan Sultan
 * Author URI: https://www.linkedin.com/in/dev-madnansultan/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: logincanvas
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('LOGINCANVAS_VERSION', '1.0.0');
define('LOGINCANVAS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('LOGINCANVAS_PLUGIN_URL', plugin_dir_url(__FILE__));
define( 'WP_DEBUG', true ); 

// Plugin main class
class LoginCanvas {
    /**
     * Instance of the plugin
     */
    private static $instance;

    /**
     * Get plugin instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->init();
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Initialize plugin
     */
    private function init() {
        // Load text domain for translations
        add_action('plugins_loaded', array($this, 'load_textdomain'));
    }

    /**
     * Include required files
     */
    private function includes() {
        require_once LOGINCANVAS_PLUGIN_DIR . 'includes/class-logincanvas-customizer.php';
        require_once LOGINCANVAS_PLUGIN_DIR . 'includes/class-logincanvas-background.php';
        require_once LOGINCANVAS_PLUGIN_DIR . 'includes/class-logincanvas-header-footer.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));
        
        // Deactivation hook
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));

        // Version upgrade hook
        add_action('plugins_loaded', array($this, 'update_check'));
    }

    /**
     * Load plugin text domain
     */
    public function load_textdomain() {
        load_plugin_textdomain(
            'logincanvas',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }

    /**
     * Plugin activation
     */
    public function activate() {
        // Verify WordPress version compatibility
        if (version_compare($GLOBALS['wp_version'], '5.0', '<')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                esc_html__('LoginCanvas requires WordPress version 5.0 or higher.', 'logincanvas'),
                'Plugin Activation Error',
                array('back_link' => true)
            );
        }

        // Verify PHP version compatibility
        if (version_compare(PHP_VERSION, '7.2', '<')) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                esc_html__('LoginCanvas requires PHP version 7.2 or higher.', 'logincanvas'),
                'Plugin Activation Error',
                array('back_link' => true)
            );
        }

        // Create necessary database tables or options
        update_option('logincanvas_version', LOGINCANVAS_VERSION);
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clean up if necessary
    }

    /**
     * Check and update plugin version
     */
    public function update_check() {
        $current_version = get_option('logincanvas_version', '0');
        if (version_compare($current_version, LOGINCANVAS_VERSION, '<')) {
            // Perform upgrade tasks if necessary
            update_option('logincanvas_version', LOGINCANVAS_VERSION);
        }
    }
}

// Initialize the plugin
function logincanvas() {
    return LoginCanvas::get_instance();
}

// Start the plugin
logincanvas();