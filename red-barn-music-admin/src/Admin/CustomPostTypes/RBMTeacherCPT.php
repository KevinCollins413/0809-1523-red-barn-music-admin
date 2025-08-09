<?php
namespace RedBarnMusic\Admin\CustomPostTypes;

defined('ABSPATH') || exit;

class RBMTeacherCPT {
    public function __construct() {
        add_action('init', [$this, 'register'], 0);
    }

    public function register(): void {
        $labels = [
            'name'              => 'Teachers',
            'singular_name'     => 'Teacher',
            'menu_name'         => 'Teachers',
            'name_admin_bar'    => 'Teacher',
            'add_new'           => 'Add New',
            'add_new_item'      => 'Add New Teacher',
            'new_item'          => 'New Teacher',
            'edit_item'         => 'Edit Teacher',
            'view_item'         => 'View Teacher',
            'all_items'         => 'All Teachers',
            'search_items'      => 'Search Teachers',
            'not_found'         => 'No teachers found',
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
            'menu_icon'         => 'dashicons-welcome-learn-more',
        ];

        register_post_type('rbm_teacher', $args);
    }
}
