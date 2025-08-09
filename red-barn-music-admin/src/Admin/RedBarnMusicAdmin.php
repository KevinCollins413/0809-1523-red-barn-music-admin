<?php
namespace RedBarnMusic\Admin;

defined('ABSPATH') || exit;

class RedBarnMusicAdmin {
    public function __construct() {
        add_action('admin_menu', [$this, 'register_menu']);
    }

    public function register_menu(): void {
        add_menu_page(
            'Red Barn Music',
            'Red Barn Music',
            'manage_options',
            'red-barn-music-admin',   // ✅ unified slug
            [$this, 'render_dashboard'],
            'dashicons-album',
            58
        );
    }

    public function render_dashboard(): void {
        echo '<div class="wrap"><h1>Red Barn Music Admin Dashboard</h1></div>';
    }
}
