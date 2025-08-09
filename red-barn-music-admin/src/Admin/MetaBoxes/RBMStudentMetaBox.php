<?php
namespace RedBarnMusic\Admin\MetaBoxes;

defined('ABSPATH') || exit;

class RBMStudentMetaBox {
    public function __construct() {
        // Hide the native title input on Student screens (we compute it)
        add_action('admin_head-post.php', [$this, 'maybe_hide_title']);
        add_action('admin_head-post-new.php', [$this, 'maybe_hide_title']);

        // After save, sync the computed title + ensure record date exists
        add_action('save_post_rbm_student', [$this, 'after_save_student'], 20, 2);
    }

    /**
     * Hide the default title field for rbm_student to avoid manual edits.
     */
    public function maybe_hide_title() {
        $screen = get_current_screen();
        if (!$screen || $screen->post_type !== 'rbm_student') {
            return;
        }

        // Hide the title input and add a small note
        ?>
        <style>
            #titlediv { display: none !important; }
            .rbm-title-note {
                margin: 12px 0 0;
                padding: 10px 12px;
                border-left: 4px solid #0073aa;
                background: #f0f6fc;
            }
        </style>
        <div class="rbm-title-note">
            <strong>Student Name:</strong> The post title is generated automatically from
            <em>Student Last Name</em> and <em>Student First Name</em>.
        </div>
        <?php
    }

    /**
     * On save, compute the title from ACF/FF meta and ensure _rbm_record_date is set.
     */
    public function after_save_student($post_id, $post) {
        // Safety checks
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (wp_is_post_revision($post_id)) return;
        if ($post->post_type !== 'rbm_student') return;

        // Pull values by meta name (works with or without ACF active)
        $first = trim((string) get_post_meta($post_id, '_rbm_student_first_name', true));
        $last  = trim((string) get_post_meta($post_id, '_rbm_student_last_name', true));
        $email = trim((string) get_post_meta($post_id, '_rbm_student_email', true));

        // Compute title: "Last, First" or fallback to email or "Student"
        $new_title = $this->build_title($first, $last, $email);

        // Update post title if different
        if ($new_title && $new_title !== $post->post_title) {
            remove_action('save_post_rbm_student', [$this, 'after_save_student'], 20); // prevent loop
            wp_update_post([
                'ID'         => $post_id,
                'post_title' => $new_title,
            ]);
            add_action('save_post_rbm_student', [$this, 'after_save_student'], 20, 2);
        }

        // Ensure record date exists (Y-m-d)
        $record_date = get_post_meta($post_id, '_rbm_record_date', true);
        if (empty($record_date)) {
            $today = current_time('Y-m-d');
            if (function_exists('update_field')) {
                update_field('_rbm_record_date', $today, $post_id);
            } else {
                update_post_meta($post_id, '_rbm_record_date', $today);
            }
        }
    }

    private function build_title($first, $last, $email) {
        $first = trim((string)$first);
        $last  = trim((string)$last);
        $email = trim((string)$email);

        if ($last || $first) {
            return trim($last . ', ' . $first, ', ');
        }
        if ($email) {
            return $email;
        }
        return 'Student';
    }
}
