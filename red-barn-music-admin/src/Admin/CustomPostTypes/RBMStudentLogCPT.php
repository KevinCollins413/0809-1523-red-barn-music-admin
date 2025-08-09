<?php
namespace RedBarnMusic\Admin\CustomPostTypes;

defined('ABSPATH') || exit;

class RBMStudentLogCPT {
    public function __construct() {
        add_action('init', [$this, 'register'], 0);
    }

    public function register(): void {
        $labels = [
            'name'              => 'Student Logs',
            'singular_name'     => 'Student Log',
            'menu_name'         => 'Student Logs',
            'name_admin_bar'    => 'Student Log',
            'add_new'           => 'Add New',
            'add_new_item'      => 'Add New Log',
            'new_item'          => 'New Log',
            'edit_item'         => 'Edit Log',
            'view_item'         => 'View Log',
            'all_items'         => 'All Logs',
            'search_items'      => 'Search Logs',
            'not_found'         => 'No logs found',
        ];

        $args = [
            'labels'            => $labels,
            'public'            => false,
            'show_ui'           => true,
            'show_in_menu'      => 'red-barn-music-admin',   // ✅ unified slug
            'show_in_admin_bar' => false,
            'supports'          => ['title', 'editor'],
            'capability_type'   => 'post',
            'map_meta_cap'      => true,
            'has_archive'       => false,
            'rewrite'           => false,
            'menu_icon'         => 'dashicons-clipboard',
        ];

        register_post_type('rbm_student_log', $args);
    }
}
