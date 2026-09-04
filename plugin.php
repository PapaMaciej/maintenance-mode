<?php
/**
 * Plugin Name: Simple Maintenance Mode
 * Description: Puts your site into maintenance mode with a custom message, while logged-in admins can still browse normally.
 * Version: 1.0
 * Author: Maciej
 */

// Prevent direct access to this file
if (!defined('ABSPATH')) {
    exit;
}

class Simple_Maintenance_Mode {

    private $option_enabled = 'smm_enabled';
    private $option_message = 'smm_message';

    public function __construct() {
        add_action('admin_menu', array($this, 'add_settings_page'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('template_redirect', array($this, 'maybe_show_maintenance_page'));
    }

    // Add a settings page under Settings > Maintenance Mode
    public function add_settings_page() {
        add_options_page(
            'Maintenance Mode',
            'Maintenance Mode',
            'manage_options',
            'simple-maintenance-mode',
            array($this, 'render_settings_page')
        );
    }

    // Register the settings so WordPress knows to save them
    public function register_settings() {
        register_setting('smm_settings_group', $this->option_enabled);
        register_setting('smm_settings_group', $this->option_message);
    }

    // Render the settings page HTML
    public function render_settings_page() {
        ?>
        <div class="wrap">
            <h1>Maintenance Mode Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('smm_settings_group'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable Maintenance Mode</th>
                        <td>
                            <input type="checkbox" name="<?php echo esc_attr($this->option_enabled); ?>" value="1"
                                <?php checked(1, get_option($this->option_enabled), true); ?> />
                            <p class="description">When checked, visitors will see your maintenance message instead of the site. Logged-in administrators can still browse normally.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Maintenance Message</th>
                        <td>
                            <textarea name="<?php echo esc_attr($this->option_message); ?>" rows="4" cols="50"><?php
                                echo esc_textarea(get_option($this->option_message, "We'll be back soon. Thanks for your patience!"));
                            ?></textarea>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Save Settings'); ?>
            </form>
        </div>
        <?php
    }

    // Show the maintenance page to regular visitors if enabled
    public function maybe_show_maintenance_page() {
        // Admins can still browse the site normally
        if (current_user_can('manage_options')) {
            return;
        }

        $enabled = get_option($this->option_enabled);

        if ($enabled) {
            $message = get_option($this->option_message, "We'll be back soon. Thanks for your patience!");
            wp_die(
                '<div style="text-align:center; font-family: sans-serif; padding-top: 100px;">'
                . '<h1>Under Maintenance</h1>'
                . '<p>' . esc_html($message) . '</p>'
                . '</div>',
                'Maintenance Mode',
                array('response' => 503)
            );
        }
    }
}

new Simple_Maintenance_Mode();