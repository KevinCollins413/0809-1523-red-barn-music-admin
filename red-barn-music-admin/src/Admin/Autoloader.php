<?php
namespace RedBarnMusic\Admin;

defined('ABSPATH') || exit;

class Autoloader {
    public static function register(): void {
        spl_autoload_register([__CLASS__, 'autoload']);
    }

    public static function autoload(string $class): void {
        if (strpos($class, 'RedBarnMusic\\Admin\\') !== 0) {
            return;
        }

        $base_dir = __DIR__ . '/';
        $relative_class = substr($class, strlen('RedBarnMusic\\Admin\\'));
        $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    }
}
