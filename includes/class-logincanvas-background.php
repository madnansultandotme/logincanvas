<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('LoginCanvas_Background')) {
    class LoginCanvas_Background {
        private static $instance = null;

        public static function get_instance() {
            if (null === self::$instance) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private function __construct() {
            add_action('login_enqueue_scripts', array($this, 'enqueue_styles'));
            add_action('login_header', array($this, 'output_background'));
        }

        public function enqueue_styles() {
            wp_enqueue_style(
                'logincanvas-style',
                LOGINCANVAS_PLUGIN_URL . 'assets/css/login-style.css',
                array(),
                LOGINCANVAS_VERSION
            );

            // Add inline CSS for background overlay
            $custom_css = "
                .login-background-overlay {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.4);
                    z-index: -1;
                }
            ";
            wp_add_inline_style('logincanvas-style', $custom_css);
        }

        public function output_background() {
            $background_type = get_theme_mod('logincanvas_background_type', 'image');

            if ($background_type === 'video') {
                $this->render_video_background();
            } else {
                $this->render_image_background();
            }
        }

        private function render_image_background() {
            $background_images = get_theme_mod('logincanvas_background_images');
            if (empty($background_images)) {
                return;
            }

            $images = array_filter(explode(',', $background_images));
            if (empty($images)) {
                return;
            }

            $random_image = $images[array_rand($images)];
            $image_url = wp_get_attachment_url($random_image);

            if ($image_url) {
                ?>
                <style type="text/css">
                    body.login {
                        background-image: url(<?php echo esc_url($image_url); ?>);
                        background-size: cover;
                        background-position: center;
                        background-repeat: no-repeat;
                        position: relative;
                    }
                </style>
                <div class="login-background-overlay"></div>
                <?php
            }
        }

        private function render_video_background() {
            $video_id = get_theme_mod('logincanvas_background_video');
            if (!$video_id) {
                return;
            }

            $video_url = wp_get_attachment_url($video_id);
            if (!$video_url) {
                return;
            }

            ?>
            <style type="text/css">
                body.login {
                    position: relative;
                    background: transparent;
                }
                .login-video-background {
                    position: fixed;
                    right: 0;
                    bottom: 0;
                    min-width: 100%;
                    min-height: 100%;
                    width: auto;
                    height: auto;
                    z-index: -2;
                    object-fit: cover;
                }

                @media (min-aspect-ratio: 16/9) {
                    .login-video-background {
                        width: 100%;
                        height: auto;
                    }
                }

                @media (max-aspect-ratio: 16/9) {
                    .login-video-background {
                        width: auto;
                        height: 100%;
                    }
                }

                @media (max-width: 767px) {
                    .login-video-background {
                        display: none;
                    }
                    body.login {
                        background: #f0f0f1;
                    }
                }
            </style>
            <video autoplay muted loop playsinline class="login-video-background">
                <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
            </video>
            <div class="login-background-overlay"></div>
            <?php
        }
    }
}