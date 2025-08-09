<?php
namespace RedBarnMusic\Admin\Interfaces;

defined('ABSPATH') || exit;

/**
 * PageInterface - interface for admin page controllers.
 */
interface PageInterface {
    public function register(): void;
}
