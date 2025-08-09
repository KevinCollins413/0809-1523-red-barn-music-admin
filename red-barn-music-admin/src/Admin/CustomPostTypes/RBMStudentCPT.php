<?php
namespace RedBarnMusic\Admin\CustomPostTypes;

defined('ABSPATH') || exit;

class RBMStudentCPT {
    public function __construct() {
        add_action('init', [$this, 'register'], 0);
    }

    public function register(): void {
        $labels = [
            'name'              => 'Students',
            'singular_name'     => 'Student',
            'menu_name'         => 'Students',
            'name_admin_bar'    => 'Student',
            'add_new'           => 'Add New',
            'add_new_item'      => 'Add New Student',
            'new_item'          => 'New Student',
            'edit_item'         => 'Edit Student',
            'view_item'         => 'View Student',
            'all_items'         => 'All Students',
            'search_items'      => 'Search Students',
            'not_found'         => 'No students found',
        ];

        $args = [
            'labels'            => $labels,
            'public'            => false,
            'show_ui'           => true,
            'show_in_menu'      => 'red-barn-music-admin',   // ✅ unified slug
            'show_in_admin_bar' => true,
            'show_in_rest'      => true,
            'supports'          => ['title'],
            'capability_type'   => 'post',
            'map_meta_cap'      => true,
            'has_archive'       => false,
            'rewrite'           => false,
            'menu_icon'         => 'dashicons-id',
        ];

        register_post_type('rbm_student', $args);
    }
}
