<?php
/**
 * LoginCanvas Customizer Class
 */

if (!defined('ABSPATH')) {
    exit;
}

class LoginCanvas_Customizer {
    private static $instance;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('customize_register', array($this, 'register_customizer_settings'));
    }

    public function register_customizer_settings($wp_customize) {
        // Add section for LoginCanvas settings
        $wp_customize->add_section('logincanvas_settings', array(
            'title' => __('LoginCanvas Settings', 'logincanvas'),
            'priority' => 150,
        ));

        // Background Images Control
        $wp_customize->add_setting('logincanvas_background_images', array(
            'default' => '',
            'sanitize_callback' => array($this, 'sanitize_background_images'),
        ));

        $wp_customize->add_control(new WP_Customize_Upload_Control($wp_customize, 'logincanvas_background_images', array(
            'label' => __('Background Images', 'logincanvas'),
            'description' => __('Select multiple images for random background display', 'logincanvas'),
            'section' => 'logincanvas_settings',
            'settings' => 'logincanvas_background_images',
            'button_labels' => array(
                'select' => __('Select Images', 'logincanvas'),
                'change' => __('Change Images', 'logincanvas'),
                'remove' => __('Remove', 'logincanvas'),
                'default' => __('Default', 'logincanvas'),
                'frame_title' => __('Select Background Images', 'logincanvas'),
                'frame_button' => __('Choose Images', 'logincanvas'),
            ),
        )));

        // Header Footer Toggle
        $wp_customize->add_setting('logincanvas_enable_header_footer', array(
            'default' => false,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ));

        $wp_customize->add_control('logincanvas_enable_header_footer', array(
            'label' => __('Enable Header and Footer', 'logincanvas'),
            'description' => __('Display site header and footer on login page', 'logincanvas'),
            'section' => 'logincanvas_settings',
            'type' => 'checkbox',
        ));
    }

    public function sanitize_background_images($value) {
        if (empty($value)) {
            return '';
        }

        $images = explode(',', $value);
        $sanitized_images = array();

        foreach ($images as $image) {
            if (wp_http_validate_url($image)) {
                $sanitized_images[] = esc_url_raw($image);
            }
        }

        return implode(',', $sanitized_images);
    }
}

// Initialize Customizer
LoginCanvas_Customizer::get_instance();