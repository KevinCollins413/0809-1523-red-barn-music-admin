<?php
namespace RedBarnMusic\Admin\Pages;

use RedBarnMusic\Admin\Interfaces\PageInterface;

defined('ABSPATH') || exit;

class AdminPageStudentAddEdit implements PageInterface {
    public function register(): void {
        add_submenu_page(
            'edit.php?post_type=student',
            'Add/Edit Student',
            'Add/Edit Student',
            'manage_options',
            'rbm_add_edit_student',
            [$this, 'render']
        );
    }

    public function render(): void {
        echo '<div class="wrap">';
        echo '<h1>Add/Edit Student</h1>';

        if (!function_exists('acf_form')) {
            echo '<p><strong>ACF is required for this page.</strong></p>';
            echo '</div>';
            return;
        }

        // --- MAIN TABBED FORM ---
        acf_form([
            'post_id' => 'new_post',
            'post_title' => true,
            'post_content' => false,
            'new_post' => [
                'post_type' => 'student',
                'post_status' => 'publish',
            ],
            'field_groups' => ['group_student_details'], // <-- ACF group with tabs
            'submit_value' => 'Save Student',
        ]);

        // --- OPTIONAL OVERVIEW ---
        echo '<hr><h2>All Fields (Overview)</h2>';

        $field_objects = acf_get_fields('group_student_details');

        if ($field_objects) {
            foreach ($field_objects as $field) {
                acf_render_field_wrap($field);
            }
        } else {
            echo '<p><em>No additional ACF fields found.</em></p>';
        }

        echo '</div>'; // .wrap
    }
}