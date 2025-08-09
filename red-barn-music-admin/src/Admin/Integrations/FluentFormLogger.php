<?php
namespace RedBarnMusic\Admin\Integrations;

defined('ABSPATH') || exit;

class FluentFormLogger {
    public function register(): void {
        add_action('fluentform_submission_inserted', [$this, 'log_submission'], 10, 3);
    }

    public function log_submission($entryId, $formId, $formData): void {
        $title = 'Submission from Form #' . $formId;
        $content = print_r($formData, true);

        wp_insert_post([
            'post_title'   => $title,
            'post_content' => $content,
            'post_type'    => 'rbm_student_log',
            'post_status'  => 'publish',
        ]);
    }
}
