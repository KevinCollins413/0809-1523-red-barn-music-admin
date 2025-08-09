<?php
namespace RedBarnMusic\Admin\Pages;

use RedBarnMusic\Admin\Interfaces\ComponentInterface;

defined('ABSPATH') || exit;

class AdminController implements ComponentInterface {

    public function register(): void {
        add_action('admin_menu', [$this, 'register_admin_pages']);
    }

    public function register_admin_pages(): void {
        // Register all plugin admin pages here
        (new AdminPageStudentAddEdit())->register();
        (new RoomsByDayPage())->register();
        (new ChangeLogPage())->register();
        (new TeacherFormEntriesPage())->register();
        (new AdminPageTeacherAvailability())->register();
        (new AllDaysPage())->register();
    }
}

// ✅ REQUIRED to render ACF fields on custom admin pages using acf_form()
add_action('admin_head', function () {
    if (isset($_GET['page']) && $_GET['page'] === 'rbm-student-add-edit') {
        acf_form_head();
    }
});
