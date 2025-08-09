<?php
declare(strict_types=1);

namespace RedBarnMusic\ACF;

use RedBarnMusic\Utils\DevHelper;

defined('ABSPATH') || exit;

class ACFSlotPrefill {
    public function register_hooks(): void {
        add_filter('acf/load_value', [$this, 'prefill_slot_fields_from_url'], 10, 3);
    }

    public function prefill_slot_fields_from_url($value, $post_id, $field) {
        if (!is_admin() || get_current_screen()->post_type !== 'student') {
            return $value;
        }

        if (!isset($_GET['slot_id'])) {
            return $value;
        }

        $slot_id = sanitize_text_field($_GET['slot_id']);
        DevHelper::log($slot_id, 'ACF prefill: slot_id from URL');

        $parts = explode('_', $slot_id);
        if (count($parts) !== 3) {
            DevHelper::log('Invalid slot_id format', 'ACF prefill');
            return $value;
        }

        [$day, $time, $room] = $parts;

        DevHelper::log([
            'field_name' => $field['name'],
            'day'        => $day,
            'time'       => $time,
            'room'       => $room,
        ], 'ACF prefill field match');

        switch ($field['name']) {
            case 'day_of_week':
                return $day;
            case 'time_slot':
                return $time;
            case 'room':
                return $room;
            case 'day_time_room':
                return $slot_id;
            default:
                return $value;
        }
    }
}
