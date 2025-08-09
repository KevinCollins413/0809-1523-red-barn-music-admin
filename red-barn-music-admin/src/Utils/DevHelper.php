<?php
namespace RedBarnMusic\Utils;

defined('ABSPATH') || exit;

class DevHelper {
    public static function is_debug_mode(): bool {
        return defined('WP_DEBUG') && WP_DEBUG && defined('WP_DEBUG_LOG') && WP_DEBUG_LOG;
    }

    public static function log($message, $context = ''): void {
        if (!self::is_debug_mode()) return;
        $label = '[RBM]';
        $output = is_array($message) || is_object($message)
            ? print_r($message, true)
            : (string) $message;

        if ($context) {
            $output = "$label $context: $output";
        } else {
            $output = "$label $output";
        }

        error_log($output);
    }

    public static function log_post($post_id): void {
        if (!self::is_debug_mode()) return;
        $meta = get_post_meta($post_id);
        self::log($meta, "Post #$post_id meta");
    }
}
