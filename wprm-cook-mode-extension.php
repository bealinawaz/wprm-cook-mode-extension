<?php
/**
 * Plugin Name: WP Recipe Maker Cook Mode Extension
 * Plugin URI: https://bealinawaz.com/
 * Description: Adds a Cook Mode toggle to WP Recipe Maker recipe cards that prevents the screen from turning off during cooking.
 * Version: 1.0.0
 * Author: Ali Nawaz
 * Author URI: https://bealinawaz.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wprm-cook-mode
 * Domain Path: /languages/
 * Requires at least: 5.0
 * Tested up to: 6.3
 * Requires PHP: 7.4
 */

// Prevent direct access to the file.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Define constants for plugin path and URL.
 * This is a best practice to improve performance and consistency.
 */
define( 'WPRM_COOK_MODE_VERSION', '1.0.0' );
define( 'WPRM_COOK_MODE_FILE', __FILE__ );
define( 'WPRM_COOK_MODE_PATH', plugin_dir_path( WPRM_COOK_MODE_FILE ) );
define( 'WPRM_COOK_MODE_URL', plugin_dir_url( WPRM_COOK_MODE_FILE ) );

/**
 * Main plugin class.
 *
 * This class uses the Singleton design pattern to ensure only one instance is ever loaded.
 */
class WPRM_Cook_Mode_Extension {

    /**
     * The single instance of the class.
     *
     * @var WPRM_Cook_Mode_Extension|null
     */
    private static $instance = null;

    /**
     * Gets the single instance of the class.
     *
     * @return WPRM_Cook_Mode_Extension
     */
    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * WPRM_Cook_Mode_Extension constructor.
     *
     * Private constructor to enforce the singleton pattern.
     */
    private function __construct() {
        add_action( 'init', array( $this, 'init' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
    }

    /**
     * Initialize plugin hooks and functionality.
     *
     * This method runs after WordPress is loaded and determines if the main plugin is active.
     */
    public function init() {
        if ( ! $this->is_wp_recipe_maker_active() ) {
            add_action( 'admin_notices', array( $this, 'display_wprm_not_active_notice' ) );
            return;
        }

        // Hook into WP Recipe Maker's output filters to inject the Cook Mode toggle.
        add_filter( 'wprm_recipe_shortcode_output', array( $this, 'add_cook_mode_to_shortcode' ), 10, 4 );
        add_filter( 'wprm_get_template', array( $this, 'add_cook_mode_to_recipe' ), 10, 4 );

        // Enqueue frontend scripts and styles.
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        // Add admin settings page and settings.
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'init_settings' ) );
        add_filter( 'plugin_action_links_' . plugin_basename( WPRM_COOK_MODE_FILE ), array( $this, 'add_settings_link' ) );
    }

    /**
     * Displays an admin notice if WP Recipe Maker is not active.
     */
    public function display_wprm_not_active_notice() {
        $class   = 'notice notice-error';
        $message = __( 'WP Recipe Maker Cook Mode Extension requires WP Recipe Maker plugin to be installed and activated.', 'wprm-cook-mode' );
        printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
    }

    /**
     * Checks if the WP Recipe Maker plugin is active.
     *
     * @return bool
     */
    private function is_wp_recipe_maker_active() {
        return class_exists( 'WP_Recipe_Maker' );
    }

    /**
     * Adds the Cook Mode toggle to the recipe template output.
     *
     * @param string $output The HTML output of the recipe template.
     * @param object $recipe The recipe object.
     * @param string $type The template type.
     * @param string $slug The template slug.
     * @return string Modified HTML output.
     */
    public function add_cook_mode_to_recipe( $output, $recipe, $type, $slug ) {
        if ( $type !== 'recipe' || empty( $recipe ) ) {
            return $output;
        }

        return $this->maybe_add_cook_mode_html( $output );
    }

    /**
     * Adds the Cook Mode toggle to the recipe shortcode output.
     *
     * @param string $output The HTML output of the recipe shortcode.
     * @param object $recipe The recipe object.
     * @param array  $args The shortcode attributes.
     * @param string $template The template name.
     * @return string Modified HTML output.
     */
    public function add_cook_mode_to_shortcode( $output, $recipe, $args, $template ) {
        return $this->maybe_add_cook_mode_html( $output );
    }

    /**
     * Helper function to add the Cook Mode HTML based on settings.
     *
     * @param string $output The original HTML output.
     * @return string Modified HTML output.
     */
    private function maybe_add_cook_mode_html( $output ) {
        $settings  = get_option( 'wprm_cook_mode_settings', $this->get_default_settings() );
        $enabled   = isset( $settings['enabled'] ) ? (bool) $settings['enabled'] : true;
        $position  = isset( $settings['position'] ) ? $settings['position'] : 'top';
        $label     = isset( $settings['label'] ) ? $settings['label'] : __( 'Cook Mode', 'wprm-cook-mode' );
        $description = isset( $settings['description'] ) ? $settings['description'] : __( 'Prevent screen from turning off', 'wprm-cook-mode' );
        $toggle_color = isset( $settings['toggle_color'] ) ? $settings['toggle_color'] : $this->get_default_settings()['toggle_color'];

        if ( ! $enabled ) {
            return $output;
        }

        $cook_mode_html = $this->get_cook_mode_html( $label, $description, $toggle_color );

        if ( 'top' === $position ) {
            $output = $cook_mode_html . $output;
        } else {
            $output = $output . $cook_mode_html;
        }

        return $output;
    }

    /**
     * Generates the HTML for the Cook Mode toggle.
     *
     * @param string $label The label text.
     * @param string $description The description text.
     * @return string The generated HTML.
     */
    private function get_cook_mode_html( $label, $description, $toggle_color ) {
        ob_start();
        ?>
        <div class="wprm-cook-mode-container wprm-hide-on-print" style="margin: 15px 0; padding: 10px; background: #f9f9f9; border-radius: 5px; border-left: 4px solid <?php echo esc_attr( $toggle_color ); ?>;">
            <div class="wprm-cook-mode-toggle">
                <label class="wprm-cook-mode-switch">
                    <input type="checkbox" id="wprm-cook-mode-checkbox" aria-describedby="wprm-cook-mode-description">
                    <span class="wprm-cook-mode-slider"></span>
                </label>
                <span class="wprm-cook-mode-label" style="margin: 0 10px; font-size: 16px; font-weight: 500;"><?php echo esc_html($label); ?></span>
                <span id="wprm-cook-mode-description" class="wprm-cook-mode-description" style="color: #666; font-size: 14px;"><?php echo esc_html($description); ?></span>
            </div>
            <div id="wprm-cook-mode-status" class="wprm-cook-mode-status" style="margin-top: 8px; font-size: 12px; color: <?php echo esc_attr( $toggle_color ); ?>; display: none;"></div>
        </div>
        <style>
            .wprm-cook-mode-switch input:checked + .wprm-cook-mode-slider {
                background-color: <?php echo esc_attr( $toggle_color ); ?>;
            }

            .wprm-cook-mode-switch input:focus + .wprm-cook-mode-slider {
                box-shadow: 0 0 1px <?php echo esc_attr( $toggle_color ); ?>;
            }
        </style>
        <?php
        return ob_get_clean();
    }

    /**
     * Enqueues frontend scripts and styles.
     */
    public function enqueue_scripts() {
        // Enqueue only on single posts/pages as a performance optimization.
        if ( ! is_single() && ! is_page() ) {
            return;
        }

        wp_enqueue_style(
            'wprm-cook-mode-style',
            WPRM_COOK_MODE_URL . 'assets/cook-mode.css',
            array(),
            WPRM_COOK_MODE_VERSION
        );

        wp_enqueue_script(
            'wprm-cook-mode-script',
            WPRM_COOK_MODE_URL . 'assets/cook-mode.js',
            array( 'jquery' ),
            WPRM_COOK_MODE_VERSION,
            true
        );

        wp_localize_script(
            'wprm-cook-mode-script',
            'wprm_cook_mode',
            array(
                'active_text'   => __( 'Cook Mode Active', 'wprm-cook-mode' ),
                'inactive_text' => __( 'Cook Mode Inactive', 'wprm-cook-mode' ),
                'error_text'    => __( 'Cook Mode not supported on this device', 'wprm-cook-mode' ),
                'nonce'         => wp_create_nonce( 'wprm_cook_mode_nonce' ),
            )
        );
    }

    /**
     * Enqueues admin scripts and styles for the settings page.
     *
     * @param string $hook The current admin page hook.
     */
    public function enqueue_admin_scripts( $hook ) {
        // Check if we're on the settings page - fixed the hook check
        if ( 'settings_page_wprm-cook-mode-settings' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_script( 'wp-color-picker' );
        
        // Inline script to initialize the color picker.
        wp_add_inline_script(
            'wp-color-picker',
            "jQuery(document).ready(function($) {
                $('.wp-color-picker').wpColorPicker();
            });"
        );
    }

    /**
     * Adds a "Settings" link to the plugin on the plugins page.
     *
     * @param array $links Array of action links.
     * @return array Modified array of links.
     */
    public function add_settings_link( $links ) {
        $settings_link = sprintf( '<a href="%s">%s</a>',
            esc_url( admin_url( 'options-general.php?page=wprm-cook-mode-settings' ) ),
            __( 'Settings', 'wprm-cook-mode' )
        );
        array_unshift( $links, $settings_link );
        return $links;
    }

    /**
     * Adds the admin menu page for plugin settings.
     */
    public function add_admin_menu() {
        add_options_page(
            __( 'WP Recipe Maker Cook Mode Settings', 'wprm-cook-mode' ),
            __( 'WPRM Cook Mode', 'wprm-cook-mode' ),
            'manage_options',
            'wprm-cook-mode-settings',
            array( $this, 'admin_page' )
        );
    }

    /**
     * Initializes plugin settings using the Settings API.
     */
    public function init_settings() {
        register_setting(
            'wprm_cook_mode_settings',
            'wprm_cook_mode_settings',
            array(
                'sanitize_callback' => array( $this, 'sanitize_settings' ),
                'default'           => $this->get_default_settings(),
            )
        );
        
        add_settings_section(
            'wprm_cook_mode_main',
            __( 'Cook Mode Settings', 'wprm-cook-mode' ),
            array( $this, 'settings_section_callback' ),
            'wprm-cook-mode-settings'
        );
        
        add_settings_field(
            'enabled',
            __( 'Enable Cook Mode', 'wprm-cook-mode' ),
            array( $this, 'enabled_field_callback' ),
            'wprm-cook-mode-settings',
            'wprm_cook_mode_main'
        );
        
        add_settings_field(
            'position',
            __( 'Position', 'wprm-cook-mode' ),
            array( $this, 'position_field_callback' ),
            'wprm-cook-mode-settings',
            'wprm_cook_mode_main'
        );
        
        add_settings_field(
            'label',
            __( 'Label Text', 'wprm-cook-mode' ),
            array( $this, 'label_field_callback' ),
            'wprm-cook-mode-settings',
            'wprm_cook_mode_main'
        );
        
        add_settings_field(
            'description',
            __( 'Description Text', 'wprm-cook-mode' ),
            array( $this, 'description_field_callback' ),
            'wprm-cook-mode-settings',
            'wprm_cook_mode_main'
        );

        add_settings_field(
            'toggle_color',
            __( 'Toggle Color', 'wprm-cook-mode' ),
            array( $this, 'toggle_color_field_callback' ),
            'wprm-cook-mode-settings',
            'wprm_cook_mode_main'
        );
    }
    
    /**
     * Sanitizes the settings array.
     *
     * @param array $input The settings input from the form.
     * @return array The sanitized settings array.
     */
    public function sanitize_settings( $input ) {
        $output = $this->get_default_settings();

        $output['enabled']     = isset( $input['enabled'] ) ? (bool) $input['enabled'] : false;
        $output['position']    = isset( $input['position'] ) ? sanitize_text_field( $input['position'] ) : $output['position'];
        $output['label']       = isset( $input['label'] ) ? sanitize_text_field( $input['label'] ) : $output['label'];
        $output['description'] = isset( $input['description'] ) ? sanitize_text_field( $input['description'] ) : $output['description'];
        $output['toggle_color'] = isset( $input['toggle_color'] ) ? sanitize_hex_color( $input['toggle_color'] ) : $output['toggle_color'];

        return $output;
    }

    /**
     * Renders the settings section description.
     */
    public function settings_section_callback() {
        echo '<p>' . esc_html__( 'Configure the Cook Mode feature for your recipe cards.', 'wprm-cook-mode' ) . '</p>';
    }
    
    /**
     * Renders the "Enabled" field.
     */
    public function enabled_field_callback() {
        $settings = get_option( 'wprm_cook_mode_settings', $this->get_default_settings() );
        $enabled  = isset( $settings['enabled'] ) ? $settings['enabled'] : true;
        ?>
        <input type="checkbox" name="wprm_cook_mode_settings[enabled]" value="1" <?php checked( 1, $enabled, true ); ?> />
        <p class="description"><?php echo esc_html__( 'Enable Cook Mode toggle on recipe cards', 'wprm-cook-mode' ); ?></p>
        <?php
    }
    
    /**
     * Renders the "Position" field.
     */
    public function position_field_callback() {
        $settings = get_option( 'wprm_cook_mode_settings', $this->get_default_settings() );
        $position = isset( $settings['position'] ) ? $settings['position'] : 'top';
        ?>
        <select name="wprm_cook_mode_settings[position]">
            <option value="top" <?php selected( $position, 'top' ); ?>><?php echo esc_html__( 'Top of Recipe', 'wprm-cook-mode' ); ?></option>
            <option value="bottom" <?php selected( $position, 'bottom' ); ?>><?php echo esc_html__( 'Bottom of Recipe', 'wprm-cook-mode' ); ?></option>
        </select>
        <p class="description"><?php echo esc_html__( 'Choose where to display the Cook Mode toggle', 'wprm-cook-mode' ); ?></p>
        <?php
    }
    
    /**
     * Renders the "Label" field.
     */
    public function label_field_callback() {
        $settings = get_option( 'wprm_cook_mode_settings', $this->get_default_settings() );
        $label    = isset( $settings['label'] ) ? $settings['label'] : __( 'Cook Mode', 'wprm-cook-mode' );
        ?>
        <input type="text" name="wprm_cook_mode_settings[label]" value="<?php echo esc_attr( $label ); ?>" class="regular-text" />
        <p class="description"><?php echo esc_html__( 'Text label for the Cook Mode toggle', 'wprm-cook-mode' ); ?></p>
        <?php
    }
    
    /**
     * Renders the "Description" field.
     */
    public function description_field_callback() {
        $settings    = get_option( 'wprm_cook_mode_settings', $this->get_default_settings() );
        $description = isset( $settings['description'] ) ? $settings['description'] : __( 'Prevent screen from turning off', 'wprm-cook-mode' );
        ?>
        <input type="text" name="wprm_cook_mode_settings[description]" value="<?php echo esc_attr( $description ); ?>" class="regular-text" />
        <p class="description"><?php echo esc_html__( 'Description text that appears below the toggle', 'wprm-cook-mode' ); ?></p>
        <?php
    }

    /**
     * Renders the "Toggle Color" color picker field.
     */
    public function toggle_color_field_callback() {
        $settings = get_option( 'wprm_cook_mode_settings', $this->get_default_settings() );
        $color    = isset( $settings['toggle_color'] ) ? $settings['toggle_color'] : $this->get_default_settings()['toggle_color'];
        ?>
        <input type="text" name="wprm_cook_mode_settings[toggle_color]" value="<?php echo esc_attr( $color ); ?>" class="wp-color-picker" data-default-color="<?php echo esc_attr( $this->get_default_settings()['toggle_color'] ); ?>" />
        <p class="description"><?php echo esc_html__( 'Choose the color for the "ON" state of the toggle.', 'wprm-cook-mode' ); ?></p>
        <?php
    }
    
    
    /**
     * Returns the default settings for the plugin.
     *
     * @return array
     */
    private function get_default_settings() {
        return array(
            'enabled'      => true,
            'position'     => 'top',
            'label'        => __( 'Cook Mode', 'wprm-cook-mode' ),
            'description'  => __( 'Prevent screen from turning off', 'wprm-cook-mode' ),
            'toggle_color' => '#2271b1', // Default WordPress admin blue color
        );
    }
    
    /**
     * Renders the full admin settings page.
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            
            <div class="notice notice-info">
                <p><strong><?php echo esc_html__( 'WP Recipe Maker Cook Mode Extension', 'wprm-cook-mode' ); ?></strong> - <?php echo esc_html__( 'Version 1.0.0', 'wprm-cook-mode' ); ?></p>
                <p><?php echo esc_html__( 'This extension adds a Cook Mode toggle to your WP Recipe Maker recipe cards.', 'wprm-cook-mode' ); ?></p>
            </div>
            
            <form action="options.php" method="post">
                <?php
                settings_fields( 'wprm_cook_mode_settings' );
                do_settings_sections( 'wprm-cook-mode-settings' );
                submit_button();
                ?>
            </form>
             <div style="display: flex; flex-wrap: wrap; gap: 20px; margin-top: 20px;">
                <div class="card" style="flex: 1 1 48%;">
                    <h2><?php _e('About Cook Mode', 'wprm-cook-mode'); ?></h2>
                    <p><?php _e('Cook Mode uses the Screen Wake Lock API to prevent your device screen from turning off while following recipes. This feature works on most modern browsers and devices.', 'wprm-cook-mode'); ?></p>
                    <p><strong><?php _e('Browser Support:', 'wprm-cook-mode'); ?></strong></p>
                    <ul style="list-style-type: disc; margin-left: 20px;">
                        <li><?php _e('Chrome 84+', 'wprm-cook-mode'); ?></li>
                        <li><?php _e('Edge 84+', 'wprm-cook-mode'); ?></li>
                        <li><?php _e('Safari 16.4+', 'wprm-cook-mode'); ?></li>
                        <li><?php _e('Firefox (limited support)', 'wprm-cook-mode'); ?></li>
                    </ul>
                    
                    <h3><?php _e('How to Use', 'wprm-cook-mode'); ?></h3>
                    <ol style="margin-left: 20px;">
                        <li><?php _e('Ensure the Cook Mode is enabled above', 'wprm-cook-mode'); ?></li>
                        <li><?php _e('Visit any page with a WP Recipe Maker recipe', 'wprm-cook-mode'); ?></li>
                        <li><?php _e('Look for the Cook Mode toggle on the recipe card', 'wprm-cook-mode'); ?></li>
                        <li><?php _e('Toggle it ON to prevent your screen from going to sleep', 'wprm-cook-mode'); ?></li>
                        <li><?php _e('Toggle it OFF when you\'re done cooking', 'wprm-cook-mode'); ?></li>
                    </ol>
                </div>
                
                <div class="card" style="flex: 1 1 48%;">
                    <h2><?php _e('Need Help?', 'wprm-cook-mode'); ?></h2>
                    <p><?php _e('If you encounter any issues or have questions:', 'wprm-cook-mode'); ?></p>
                    <ul style="list-style-type: disc; margin-left: 20px;">
                        <li><?php _e('Make sure WP Recipe Maker plugin is installed and active', 'wprm-cook-mode'); ?></li>
                        <li><?php _e('Test on a modern browser (Chrome, Edge, Safari)', 'wprm-cook-mode'); ?></li>
                        <li><?php _e('Check that your recipes are created with WP Recipe Maker', 'wprm-cook-mode'); ?></li>
                    </ul>
                    <p><?php _e('Still need help? Reach out to me at ', 'wprm-cook-mode'); ?><a href="mailto:bealinawaz@gmail.com">bealinawaz@gmail.com</a></p>
                </div>
            </div>
        </div>
        <?php
    }
}

// Initialize the plugin
WPRM_Cook_Mode_Extension::get_instance();