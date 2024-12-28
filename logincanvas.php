<?php
/**
 * Plugin Name: LoginCanvas
 * Plugin URI: https://github.com/madnansultandotme/logincanvas
 * Description: Enhance your WordPress login page with random background images and header/footer support
 * Version: 1.0.1
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * Author: Team Zeppelin
 * Author URI: https://www.linkedin.com/in/dev-madnansultan/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: logincanvas
 * Domain Path: /languages
 */

// Prevent direct file access
if (!defined('ABSPATH')) {
    exit('Direct file access is prohibited.');
}

// Define plugin constants only if not already defined
if (!defined('LOGINCANVAS_VERSION')) {
    define('LOGINCANVAS_VERSION', '1.0.1');
}
if (!defined('LOGINCANVAS_PLUGIN_DIR')) {
    define('LOGINCANVAS_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('LOGINCANVAS_PLUGIN_URL')) {
    define('LOGINCANVAS_PLUGIN_URL', plugin_dir_url(__FILE__));
}

// Error handling function
function logincanvas_handle_error($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }

    $error_message = sprintf(
        'LoginCanvas Error: %s in %s on line %d',
        $errstr,
        basename($errfile),
        $errline
    );

    // Log error securely
    if (function_exists('error_log')) {
        error_log($error_message);
    }

    // Display admin notice if user can manage options
    if (is_admin() && current_user_can('manage_options')) {
        add_action('admin_notices', function() use ($error_message) {
            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                esc_html($error_message)
            );
        });
    }

    return true; // Don't execute PHP internal error handler
}

// Set custom error handler
set_error_handler('logincanvas_handle_error', E_ALL);

// Class autoloader
function logincanvas_autoloader($class_name) {
    // Only load our plugin classes
    if (strpos($class_name, 'LoginCanvas_') !== 0) {
        return;
    }

    // Convert class name to filename
    $class_file = strtolower(
        str_replace('_', '-', 
            str_replace('LoginCanvas_', '', $class_name)
        )
    );
    $class_path = LOGINCANVAS_PLUGIN_DIR . 'includes/class-logincanvas-' . $class_file . '.php';

    // Check if file exists before requiring
    if (file_exists($class_path)) {
        require_once $class_path;
    }
}

// Register autoloader
spl_autoload_register('logincanvas_autoloader');

// Plugin main class
class LoginCanvas {
    private static $instance = null;
    private $customizer;
    private $background;
    private $header_footer;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Add activation checks
        register_activation_hook(__FILE__, array($this, 'activate'));
        
        // Initialize plugin if all requirements are met
        if ($this->check_requirements()) {
            $this->init();
        }
    }

    public function check_requirements() {
        $requirements_met = true;

        // Check PHP version
        if (version_compare(PHP_VERSION, '7.2', '<')) {
            add_action('admin_notices', function() {
                printf(
                    '<div class="notice notice-error"><p>%s</p></div>',
                    esc_html__('LoginCanvas requires PHP version 7.2 or higher.', 'logincanvas')
                );
            });
            $requirements_met = false;
        }

        // Check WordPress version
        if (version_compare($GLOBALS['wp_version'], '5.0', '<')) {
            add_action('admin_notices', function() {
                printf(
                    '<div class="notice notice-error"><p>%s</p></div>',
                    esc_html__('LoginCanvas requires WordPress version 5.0 or higher.', 'logincanvas')
                );
            });
            $requirements_met = false;
        }

        return $requirements_met;
    }

    public function init() {
        // Load translations
        add_action('plugins_loaded', array($this, 'load_textdomain'));

        try {
            // Initialize components only if files exist
            if ($this->check_required_files()) {
                $this->customizer = LoginCanvas_Customizer::get_instance();
                $this->background = LoginCanvas_Background::get_instance();
                $this->header_footer = LoginCanvas_Header_Footer::get_instance();
                
                // Add admin menu
                add_action('admin_menu', array($this, 'add_admin_menu'));
                
                // Add settings link
                add_filter('plugin_action_links_' . plugin_basename(__FILE__), array($this, 'add_settings_link'));
            }
        } catch (Exception $e) {
            error_log('LoginCanvas initialization error: ' . $e->getMessage());
            add_action('admin_notices', function() use ($e) {
                printf(
                    '<div class="notice notice-error"><p>%s</p></div>',
                    esc_html('LoginCanvas initialization error: ' . $e->getMessage())
                );
            });
        }
    }

    private function check_required_files() {
        $required_files = array(
            'customizer' => LOGINCANVAS_PLUGIN_DIR . 'includes/class-logincanvas-customizer.php',
            'background' => LOGINCANVAS_PLUGIN_DIR . 'includes/class-logincanvas-background.php',
            'header-footer' => LOGINCANVAS_PLUGIN_DIR . 'includes/class-logincanvas-header-footer.php'
        );

        foreach ($required_files as $name => $path) {
            if (!file_exists($path)) {
                add_action('admin_notices', function() use ($name) {
                    printf(
                        '<div class="notice notice-error"><p>%s</p></div>',
                        esc_html(sprintf(
                            __('LoginCanvas: Required file missing - %s. Please reinstall the plugin.', 'logincanvas'),
                            $name
                        ))
                    );
                });
                return false;
            }
        }
        return true;
    }

    public function add_admin_menu() {
        add_options_page(
            __('LoginCanvas Settings', 'logincanvas'),
            __('LoginCanvas', 'logincanvas'),
            'manage_options',
            'logincanvas-settings',
            array($this, 'render_settings_page')
        );
    }

    public function render_settings_page() {
        // Verify user capabilities
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'logincanvas'));
        }

        // Add nonce for security
        $nonce = wp_create_nonce('logincanvas_settings');
        
        ?>
        <div class="wrap">
            <h1><?php echo esc_html__('LoginCanvas Settings', 'logincanvas'); ?></h1>
            <div class="notice notice-info">
                <p><?php echo esc_html__('Customize your login page appearance in the WordPress Customizer.', 'logincanvas'); ?></p>
                <p>
                    <a href="<?php echo esc_url(admin_url('customize.php?autofocus[section]=logincanvas_settings')); ?>" class="button button-primary">
                        <?php echo esc_html__('Open Customizer', 'logincanvas'); ?>
                    </a>
                </p>
            </div>
        </div>
        <?php
    }

    public function add_settings_link($links) {
        $settings_link = sprintf(
            '<a href="%s">%s</a>',
            esc_url(admin_url('options-general.php?page=logincanvas-settings')),
            esc_html__('Settings', 'logincanvas')
        );
        array_unshift($links, $settings_link);
        return $links;
    }

    public function load_textdomain() {
        load_plugin_textdomain(
            'logincanvas',
            false,
            dirname(plugin_basename(__FILE__)) . '/languages/'
        );
    }

    public function activate() {
        // Clear any existing errors
        error_clear_last();
        
        // Verify requirements on activation
        if (!$this->check_requirements()) {
            deactivate_plugins(plugin_basename(__FILE__));
            wp_die(
                esc_html__('LoginCanvas cannot be activated. Please check the error messages above.', 'logincanvas'),
                'Plugin Activation Error',
                array('back_link' => true)
            );
        }

        // Create necessary options with default values
        $default_options = array(
            'version' => LOGINCANVAS_VERSION,
            'background_images' => '',
            'enable_header_footer' => false
        );

        foreach ($default_options as $option => $value) {
            if (get_option('logincanvas_' . $option) === false) {
                add_option('logincanvas_' . $option, $value);
            }
        }

        // Flush rewrite rules
        flush_rewrite_rules();
    }
}

// Initialize the plugin safely
function logincanvas_init() {
    try {
        return LoginCanvas::get_instance();
    } catch (Exception $e) {
        error_log('LoginCanvas initialization error: ' . $e->getMessage());
        add_action('admin_notices', function() use ($e) {
            printf(
                '<div class="notice notice-error"><p>%s</p></div>',
                esc_html('LoginCanvas initialization error: ' . $e->getMessage())
            );
        });
    }
}

// Start the plugin
add_action('plugins_loaded', 'logincanvas_init');

// Restore error handler
restore_error_handler();