<?php
/**
 * Plugin Name: Red Barn Music Admin
 * Description: Admin plugin for managing music school students, teachers, and lessons.
 * Version: 1.0.0
 * Author: Kevin Collins
 */

defined('ABSPATH') || exit;

// PSR-4 Autoloader
require_once __DIR__ . '/src/Admin/Autoloader.php';
\RedBarnMusic\Admin\Autoloader::register();

// Imports
use RedBarnMusic\Admin\CustomPostTypes\RBMStudentCPT;
use RedBarnMusic\Admin\CustomPostTypes\RBMTeacherCPT;
use RedBarnMusic\Admin\CustomPostTypes\RBMStudentLogCPT;
use RedBarnMusic\Admin\Integrations\FluentFormLogger;
use RedBarnMusic\Admin\Integrations\FluentFormHandler;
use RedBarnMusic\Admin\RedBarnMusicAdmin;         // ✅ matches src/Admin/RedBarnMusicAdmin.php
use RedBarnMusic\Admin\MetaBoxes\RBMStudentMetaBox;

add_action('plugins_loaded', function () {
    try {
        // --- Admin Menu (top-level) ---
        new RedBarnMusicAdmin();

        // --- Custom Post Types (constructor-based hooking) ---
        new RBMStudentCPT();
        new RBMTeacherCPT();
        new RBMStudentLogCPT();

        // --- Meta Boxes ---
        new RBMStudentMetaBox();

        // --- Integrations (Fluent Forms) ---
        if (class_exists(FluentFormLogger::class)) {
            (new FluentFormLogger())->register();
        }
        if (class_exists(FluentFormHandler::class)) {
            // Listen only to form ID 51
            (new FluentFormHandler(51))->register();
        }

        error_log('✅ Red Barn Music plugin initialized successfully.');
    } catch (Throwable $e) {
        error_log('❌ Red Barn Music plugin failed to initialize: ' . $e->getMessage());
    }
});
