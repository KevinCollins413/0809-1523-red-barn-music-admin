<?php
namespace RedBarnMusic\Admin\Pages;
defined('ABSPATH') || exit;

use RedBarnMusic\Admin\Interfaces\ComponentInterface;

class ChangeLogPage implements ComponentInterface {
    public function register(): void {
        add_submenu_page(
            'edit.php?post_type=student',
            'Change Log',
            'Change Log',
            'manage_options',
            'rbm-change-log',
            [ $this, 'render' ]
        );
    }

    public function render(): void {
        echo '<div class="wrap"><h1>Change Log</h1>';
        $logs = new \WP_Query([ 'post_type' => 'student_log', 'posts_per_page' => 20 ]);
        echo '<table class="wp-list-table widefat fixed striped"><thead><tr><th>Date</th><th>Entry</th></tr></thead><tbody>';
        foreach ($logs->posts as $post) {
            echo '<tr><td>' . esc_html(get_the_date('', $post)) . '</td>';
            echo '<td><pre>' . esc_html($post->post_content) . '</pre></td></tr>';
        }
        echo '</tbody></table></div>';
    }
}
