<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('LoginCanvas_Customizer')) {
    class LoginCanvas_Customizer {
        private static $instance = null;

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
            // Add section
            $wp_customize->add_section('logincanvas_settings', array(
                'title' => __('LoginCanvas Settings', 'logincanvas'),
                'priority' => 150,
            ));

            // Background Type Setting
            $wp_customize->add_setting('logincanvas_background_type', array(
                'default' => 'image',
                'sanitize_callback' => 'sanitize_text_field',
            ));

            $wp_customize->add_control('logincanvas_background_type', array(
                'label' => __('Background Type', 'logincanvas'),
                'section' => 'logincanvas_settings',
                'type' => 'radio',
                'choices' => array(
                    'image' => __('Images', 'logincanvas'),
                    'video' => __('Video', 'logincanvas'),
                )
            ));

            // Multiple Images Control
            $wp_customize->add_setting('logincanvas_background_images', array(
                'default' => '',
                'sanitize_callback' => array($this, 'sanitize_background_images'),
            ));

            $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'logincanvas_background_images', array(
                'label' => __('Background Images', 'logincanvas'),
                'description' => __('Select multiple images. Hold Ctrl/Cmd to select multiple.', 'logincanvas'),
                'section' => 'logincanvas_settings',
                'mime_type' => 'image',
                'button_labels' => array(
                    'select' => __('Select Images', 'logincanvas'),
                    'change' => __('Change Images', 'logincanvas'),
                ),
                'active_callback' => function() {
                    return get_theme_mod('logincanvas_background_type', 'image') === 'image';
                }
            )));

            // Video Background
            $wp_customize->add_setting('logincanvas_background_video', array(
                'default' => '',
                'sanitize_callback' => 'absint',
            ));

            $wp_customize->add_control(new WP_Customize_Media_Control($wp_customize, 'logincanvas_background_video', array(
                'label' => __('Background Video', 'logincanvas'),
                'description' => __('Select a video file (MP4 recommended).', 'logincanvas'),
                'section' => 'logincanvas_settings',
                'mime_type' => 'video',
                'active_callback' => function() {
                    return get_theme_mod('logincanvas_background_type', 'image') === 'video';
                }
            )));

            // Header Footer Settings
            $wp_customize->add_setting('logincanvas_enable_header_footer', array(
                'default' => false,
                'sanitize_callback' => 'rest_sanitize_boolean',
            ));

            $wp_customize->add_control('logincanvas_enable_header_footer', array(
                'label' => __('Enable Header and Footer', 'logincanvas'),
                'section' => 'logincanvas_settings',
                'type' => 'checkbox',
            ));
        }

        public function sanitize_background_images($value) {
            if (empty($value)) {
                return '';
            }
            return sanitize_text_field($value);
        }
    }
}