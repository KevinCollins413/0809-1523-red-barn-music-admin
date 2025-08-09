<?php
/**
 * FluentFormHandler
 *
 * Maps Fluent Forms (ID 51) submissions to the `rbm_student` CPT & ACF fields,
 * and enforces consistent defaults across FF, ACF, and manual WP entries.
 */

namespace RedBarnMusic\Admin\Integrations;

defined('ABSPATH') || exit;

class FluentFormHandler
{
    /** @var int|null */
    private $form_id;

    /** Unified defaults (ACF, FF, and handler agree on these) */
    private array $defaults = [
        '_rbm_lesson_day_of_week'       => 'TBD',
        '_rbm_lesson_time'              => 'TBD',
        '_rbm_lesson_room'              => 'TBD',
        '_rbm_teacher'                  => 'TBD',
        '_rbm_student_status'           => 'pending',
        '_rbm_student_active'           => 1,
        '_rbm_newsletter_opt_in'        => 0,
        '_rbm_submission_type'          => 'inquiry', // safe fallback
        // _rbm_record_date handled separately as DateTime
    ];

    public function __construct(?int $form_id = null)
    {
        $this->form_id = $form_id;
    }

    public function register(): void
    {
        // Use the "before insert" hook so we can normalize/patch values consistently
        add_action('fluentform_before_insert_submission', [$this, 'handle_submission'], 10, 3);
    }

    /**
     * Handle a Fluent Forms submission before it's written to FF's DB.
     *
     * @param array $insertData (by reference in FF internals, but we treat as value here)
     * @param array $formData   submitted values
     * @param array $form       form meta
     */
    public function handle_submission($insertData, $formData, $form): void
    {
        // Limit to our form if specified
        if ($this->form_id && (int) ($form['id'] ?? 0) !== (int) $this->form_id) {
            return;
        }

        // --- Normalize Record DateTime ---
        $record_dt_raw = (string) ($formData['_rbm_record_date'] ?? '');
        $record_dt     = $this->normalize_ff_datetime($record_dt_raw);
        if (!$record_dt) {
            // WP timezone-aware "now"
            $record_dt = current_time('mysql'); // Y-m-d H:i:s
        }

        // --- Build map with incoming data (leave as-is; defaults applied after) ---
        $mapped = [
            '_rbm_student_first_name'         => $formData['_rbm_student_first_name'] ?? '',
            '_rbm_student_last_name'          => $formData['_rbm_student_last_name'] ?? '',
            '_rbm_student_email'              => $formData['_rbm_student_email'] ?? '',
            '_rbm_student_phone'              => $formData['_rbm_student_phone'] ?? '',
            '_rbm_student_dob'                => $formData['_rbm_student_dob'] ?? '',
            '_rbm_student_age'                => $formData['_rbm_student_age'] ?? '',
            '_rbm_parent_first_name'          => $formData['_rbm_parent_first_name'] ?? '',
            '_rbm_parent_last_name'           => $formData['_rbm_parent_last_name'] ?? '',
            '_rbm_parent_email'               => $formData['_rbm_parent_email'] ?? '',
            '_rbm_parent_phone'               => $formData['_rbm_parent_phone'] ?? '',
            '_rbm_second_guardian_first_name' => $formData['_rbm_second_guardian_first_name'] ?? ($formData['_rbm_second_guardian_name'] ?? ''), // tolerate legacy
            '_rbm_second_guardian_last_name'  => $formData['_rbm_second_guardian_last_name'] ?? '',
            '_rbm_second_guardian_phone'      => $formData['_rbm_second_guardian_phone'] ?? '',
            '_rbm_preferred_instruments'      => $this->normalize_csv($formData['_rbm_preferred_instruments'] ?? ''),
            '_rbm_preferred_days_of_week'     => $this->normalize_csv($formData['_rbm_preferred_days_of_week'] ?? ''),
            '_rbm_preferred_contact_method'   => $formData['_rbm_preferred_contact_method'] ?? '',
            '_rbm_media_release'              => $this->to_bool($formData['_rbm_media_release'] ?? ''),
            '_rbm_student_notes'              => $formData['_rbm_student_notes'] ?? '',
            '_rbm_comments'                   => $formData['_rbm_comments'] ?? '',
            '_rbm_student_status'             => $formData['_rbm_student_status'] ?? '',
            '_rbm_student_active'             => $this->to_bool($formData['_rbm_student_active'] ?? ''),
            '_rbm_student_instrument'         => $formData['_rbm_student_instrument'] ?? '',
            '_rbm_lesson_day_of_week'         => $formData['_rbm_lesson_day_of_week'] ?? '',
            '_rbm_lesson_time'                => $formData['_rbm_lesson_time'] ?? '',
            '_rbm_lesson_room'                => $formData['_rbm_lesson_room'] ?? '',
            '_rbm_teacher'                    => $formData['_rbm_teacher'] ?? '',
            '_rbm_newsletter_opt_in'          => $this->to_bool($formData['_rbm_newsletter_opt_in'] ?? ($formData['_rbm_newsletter_optin'] ?? '')),
            '_rbm_submission_type'            => $formData['_rbm_submission_type'] ?? '',
            '_rbm_record_date'                => $record_dt,
        ];

        // --- Apply unified defaults where empty/falsey ---
        foreach ($this->defaults as $key => $default) {
            if (!isset($mapped[$key]) || $this->is_empty($mapped[$key])) {
                $mapped[$key] = $default;
            }
        }

        // --- Create the Student CPT now (always new per today’s requirement) ---
        $title = $this->build_title(
            $mapped['_rbm_student_first_name'] ?? '',
            $mapped['_rbm_student_last_name'] ?? '',
            $mapped['_rbm_student_email'] ?? ''
        );

        $post_id = wp_insert_post([
            'post_type'   => 'rbm_student',
            'post_status' => 'publish',
            'post_title'  => $title ?: 'Student',
        ], true);

        if (is_wp_error($post_id) || !$post_id) {
            error_log('❌ FluentFormHandler: Failed to create rbm_student: ' . (is_wp_error($post_id) ? $post_id->get_error_message() : 'unknown'));
            return;
        }

        // --- Persist to ACF/meta ---
        foreach ($mapped as $key => $value) {
            if (function_exists('update_field')) {
                update_field($key, $value, $post_id);
            } else {
                update_post_meta($post_id, $key, $value);
            }
        }

        // Recompute title in case ACF filters altered names
        $final_title = $this->build_title(
            get_post_meta($post_id, '_rbm_student_first_name', true),
            get_post_meta($post_id, '_rbm_student_last_name', true),
            get_post_meta($post_id, '_rbm_student_email', true)
        );
        if ($final_title && $final_title !== get_post_field('post_title', $post_id)) {
            wp_update_post(['ID' => $post_id, 'post_title' => $final_title]);
        }

        // Optional debug
        // error_log("✅ FF→ACF Student #{$post_id} created (submission type: {$mapped['_rbm_submission_type']})");
    }

    private function is_empty($val): bool
    {
        if (is_array($val)) return count(array_filter($val, fn($v) => trim((string)$v) !== '')) === 0;
        return trim((string)$val) === '';
    }

    private function normalize_csv($val): string
    {
        if (is_array($val)) {
            $val = array_filter(array_map('trim', $val), fn($v) => $v !== '');
            return implode(', ', $val);
        }
        return trim((string)$val);
    }

    private function to_bool($val): int
    {
        if (is_array($val)) $val = reset($val);
        $val = strtolower(trim((string)$val));
        return in_array($val, ['1', 'true', 'yes', 'on', 'checked'], true) ? 1 : 0;
    }

    private function build_title($first, $last, $email): string
    {
        $first = trim((string)$first);
        $last  = trim((string)$last);
        $email = trim((string)$email);
        if ($last || $first) {
            $t = $last;
            if ($first !== '') $t .= ($t ? ', ' : '') . $first;
            return $t ?: 'Student';
        }
        return $email ?: 'Student';
    }

    /**
     * Convert FF datetime (d.m.Y H:i) → Y-m-d H:i:s for ACF DateTime
     */
    private function normalize_ff_datetime(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') return '';
        $tz  = wp_timezone();
        $dt  = \DateTime::createFromFormat('d.m.Y H:i', $raw, $tz);
        if ($dt instanceof \DateTime) {
            return $dt->format('Y-m-d H:i:s');
        }
        $ts = strtotime($raw);
        return $ts ? date('Y-m-d H:i:s', $ts) : '';
    }
}
