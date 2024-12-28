<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('LoginCanvas_Header_Footer')) {
    class LoginCanvas_Header_Footer {
        private static $instance = null;

        public static function get_instance() {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private function __construct() {
            if (get_theme_mod('logincanvas_enable_header_footer', false)) {
                add_action('login_init', array($this, 'initialize_header_footer'));
                add_action('login_enqueue_scripts', array($this, 'enqueue_theme_styles'));
            }
        }

        public function initialize_header_footer() {
            add_action('login_header', array($this, 'render_header'), 5);
            add_action('login_footer', array($this, 'render_footer'), 15);
        }

        public function enqueue_theme_styles() {
            // Enqueue theme's style.css
            wp_enqueue_style('theme-style', get_stylesheet_uri());
            
            // Add custom CSS to contain styles to header/footer
            $custom_css = "
                .login-header, .login-footer {
                margin: 0;
                    max-width: 100%;
                }
                .login-header {
                    position: relative;
                    z-index: 1;
                }
                .login-footer {
                    position: relative;
                    z-index: 1;
                    margin-top: 40px;
                }
                #login {
                  max-width: 450px;
                   width: 400px;
                }
                .login-header *, .login-footer * {
                    box-sizing: border-box;
                }
                /* Restrict theme styles to header/footer only */
                #login form,
                #login h1,
                #login p,
                #login label {
                    color: #333 !important;
                }
            ";
            wp_add_inline_style('theme-style', $custom_css);
        }

        public function render_header() {
            ob_start();
            ?>
            <div class="login-header">
                <?php get_header(); ?>
            </div>
            <?php
            echo ob_get_clean();
        }

        public function render_footer() {
            // Check if footer is already rendered
            if (did_action('get_footer')) {
            return;
            }

            ob_start();
            ?>
            <div class="login-footer">
            <?php get_footer(); ?>
            </div>
            <?php
            echo ob_get_clean();
        }
    }
}