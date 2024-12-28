<?php
/**
 * LoginCanvas Header Footer Handler Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class LoginCanvas_Header_Footer {
    private static $instance;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('login_init', array($this, 'initialize_header_footer'));
    }

    public function initialize_header_footer() {
        if (!get_theme_mod('logincanvas_enable_header_footer', false)) {
            return;
        }

        add_action('login_header', array($this, 'render_header'), 5);
        add_action('login_footer', array($this, 'render_footer'), 15);
    }

    public function render_header() {
        if (!get_theme_mod('logincanvas_enable_header_footer', false)) {
            return;
        }

        // Store output buffering
        ob_start();
        
        // Get header template
        get_header();
        
        // Get buffered content
        $header = ob_get_clean();
        
        // Output modified header
        echo wp_kses_post($this->sanitize_output($header));
    }

    public function render_footer() {
        if (!get_theme_mod('logincanvas_enable_header_footer', false)) {
            return;
        }

        // Store output buffering
        ob_start();
        
        // Get footer template
        get_footer();
        
        // Get buffered content
        $footer = ob_get_clean();
        
        // Output modified footer
        echo wp_kses_post($this->sanitize_output($footer));
    }

    private function sanitize_output($content) {
        // Remove scripts and other potentially problematic elements
        $content = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content);
        
        // Additional sanitization if needed
        return $content;
    }
}

// Initialize Header Footer Handler
LoginCanvas_Header_Footer::get_instance();