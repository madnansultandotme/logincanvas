<?php
if (!defined('ABSPATH')) {
    exit;
}

// Include WordPress customizer classes
require_once ABSPATH . 'wp-includes/class-wp-customize-control.php';

// Custom Multiple Images Control Class
class LoginCanvas_Multiple_Images_Control extends WP_Customize_Control {
    public $type = 'multiple-images';

    public function render_content() {
        ?>
        <label>
            <?php if (!empty($this->label)): ?>
                <span class="customize-control-title"><?php echo esc_html($this->label); ?></span>
            <?php endif; ?>
            
            <?php if (!empty($this->description)): ?>
                <span class="description customize-control-description"><?php echo esc_html($this->description); ?></span>
            <?php endif; ?>
        </label>

        <div class="multiple-images-control">
            <input type="hidden" <?php $this->link(); ?> value="<?php echo esc_attr($this->value()); ?>" class="multiple-images-value" />
            <div class="images-container">
                <?php 
                $images = !empty($this->value()) ? explode(',', $this->value()) : array();
                foreach ($images as $image_id) {
                    if (!empty($image_id)) {
                        $image = wp_get_attachment_image_src($image_id, 'thumbnail');
                        if ($image) {
                            echo '<div class="image-preview" data-id="' . esc_attr($image_id) . '">';
                            echo '<img src="' . esc_url($image[0]) . '" />';
                            echo '<button type="button" class="remove-image">×</button>';
                            echo '</div>';
                        }
                    }
                }
                ?>
            </div>
            <button type="button" class="button upload-button"><?php echo esc_html__('Select Images', 'logincanvas'); ?></button>
        </div>

        <style>
        .multiple-images-control .images-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 10px;
        }
        .multiple-images-control .image-preview {
            position: relative;
            width: 80px;
            height: 80px;
        }
        .multiple-images-control .image-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .multiple-images-control .remove-image {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #dc3232;
            color: #fff;
            border: none;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            var frame;
            
            $('.multiple-images-control .upload-button').on('click', function(e) {
                e.preventDefault();
                var button = $(this);
                var container = button.closest('.multiple-images-control');
                
                if (frame) {
                    frame.open();
                    return;
                }
                
                frame = wp.media({
                    title: '<?php echo esc_js(__('Select Background Images', 'logincanvas')); ?>',
                    button: {
                        text: '<?php echo esc_js(__('Add Selected Images', 'logincanvas')); ?>'
                    },
                    multiple: true,
                    library: {
                        type: 'image'
                    }
                });
                
                frame.on('select', function() {
                    var selection = frame.state().get('selection');
                    var imageIds = [];
                    
                    var existingIds = container.find('.multiple-images-value').val();
                    if (existingIds) {
                        imageIds = existingIds.split(',');
                    }
                    
                    selection.forEach(function(attachment) {
                        attachment = attachment.toJSON();
                        if ($.inArray(attachment.id.toString(), imageIds) === -1) {
                            imageIds.push(attachment.id);
                            
                            var preview = $('<div class="image-preview" data-id="' + attachment.id + '">' +
                                '<img src="' + (attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url) + '" />' +
                                '<button type="button" class="remove-image">×</button>' +
                                '</div>');
                            container.find('.images-container').append(preview);
                        }
                    });
                    
                    container.find('.multiple-images-value').val(imageIds.join(',')).trigger('change');
                });
                
                frame.open();
            });
            
            $('.multiple-images-control').on('click', '.remove-image', function() {
                var container = $(this).closest('.multiple-images-control');
                var preview = $(this).closest('.image-preview');
                var removeId = preview.data('id').toString();
                
                var imageIds = container.find('.multiple-images-value').val().split(',');
                imageIds = imageIds.filter(function(id) {
                    return id !== removeId;
                });
                container.find('.multiple-images-value').val(imageIds.join(',')).trigger('change');
                
                preview.remove();
            });
        });
        </script>
        <?php
    }
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
                'transport' => 'refresh'
            ));

            $wp_customize->add_control(new LoginCanvas_Multiple_Images_Control($wp_customize, 'logincanvas_background_images', array(
                'label' => __('Background Images', 'logincanvas'),
                'description' => __('Select multiple images for the background gallery.', 'logincanvas'),
                'section' => 'logincanvas_settings',
                'active_callback' => function() {
                    return get_theme_mod('logincanvas_background_type') === 'image';
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
                    return get_theme_mod('logincanvas_background_type') === 'video';
                }
            )));

            // Add JavaScript for dynamic visibility
            add_action('customize_controls_print_footer_scripts', function() {
                ?>
                <script>
                (function($) {
                    wp.customize('logincanvas_background_type', function(setting) {
                        setting.bind(function(value) {
                            var videoControl = $('#customize-control-logincanvas_background_video');
                            var imageControl = $('#customize-control-logincanvas_background_images');
                            
                            if (value === 'video') {
                                videoControl.show();
                                imageControl.hide();
                            } else {
                                videoControl.hide();
                                imageControl.show();
                            }
                        });
                    });

                    // Trigger initial state
                    $(document).ready(function() {
                        var initialValue = wp.customize('logincanvas_background_type').get();
                        if (initialValue === 'video') {
                            $('#customize-control-logincanvas_background_video').show();
                            $('#customize-control-logincanvas_background_images').hide();
                        } else {
                            $('#customize-control-logincanvas_background_video').hide();
                            $('#customize-control-logincanvas_background_images').show();
                        }
                    });
                })(jQuery);
                </script>
                <?php
            });

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
            
            $imageIds = explode(',', $value);
            $sanitizedIds = array();
            
            foreach ($imageIds as $id) {
                if (wp_attachment_is_image($id)) {
                    $sanitizedIds[] = absint($id);
                }
            }
            
            return implode(',', array_filter($sanitizedIds));
        }
    }
}