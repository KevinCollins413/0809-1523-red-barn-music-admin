<?php
namespace RedBarnMusic\Admin\Pages;

use RedBarnMusic\Admin\Interfaces\ComponentInterface;

defined('ABSPATH') || exit;

class AdminPageTeacherAvailability implements ComponentInterface {
    public function register(): void {
        add_submenu_page(
            'edit.php?post_type=teacher',
            'Teacher Availability',
            'Availability',
            'manage_options',
            'rbm-teacher-availability',
            [ $this, 'render' ]
        );
    }

    public function render(): void {
        echo '<div class="wrap"><h1>Teacher Availability</h1><p>Here you can set teacher availabilities.</p></div>';
    }
}
