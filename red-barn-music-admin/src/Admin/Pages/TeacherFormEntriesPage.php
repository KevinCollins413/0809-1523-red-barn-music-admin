<?php
declare(strict_types=1); // Added declare(strict_types=1); for consistency, assuming your other files use it.
namespace RedBarnMusic\Admin\Pages;

use RedBarnMusic\Admin\Interfaces\ComponentInterface;
use FluentForm\App\Models\Submission;

defined( 'ABSPATH' ) || exit;

class TeacherFormEntriesPage implements ComponentInterface {

    /** ID of your Teacher sign-up FluentForm */
    protected int $formId = 39;

    public function register(): void {
        add_submenu_page(
            'edit.php?post_type=teacher',     // parent: Teachers CPT
            'Teacher Form Entries',           // page title
            'Form Entries',                   // menu title
            'manage_options',                 // capability
            'rbm-teacher-form-entries',       // menu slug
            [ $this, 'render' ]               // callback
        );
    }

    public function render(): void {
        echo '<div class="wrap"><h1>Teacher Sign-Up Entries</h1>';

        // fetch latest 100 submissions
        $entries = Submission::where('form_id', $this->formId)
            ->orderBy('created_at', 'desc')
            ->take(100)
            ->get();

        // ─── DEBUG: dump raw response_data of first entry ─────────────
        // Corrected: Use ! $entries->isEmpty() or $entries->count() > 0
        if ( !$entries->isEmpty() ) { // Corrected line for the Fatal Error
            $firstEntry = $entries[0];
            echo '<h2>Raw response_data for entry #'. intval( $firstEntry->id ) .'</h2>';
            echo '<pre style="background:#f9f9f9;padding:10px;border:1px solid #ccc;">'
                 . esc_html( print_r( $firstEntry->response_data, true ) )
                 . '</pre>';
        }
        // ────────────────────────────────────────────────────────────────

        // table header
        echo '<table class="wp-list-table widefat fixed striped"><thead>';
        echo '<tr><th>ID</th><th>Date</th><th>Name</th><th>Email</th><th>Instruments</th><th>Notes</th></tr>';
        echo '</thead><tbody>';

        if ( $entries->isEmpty() ) {
            echo '<tr><td colspan="6">No entries found.</td></tr>';
        } else {
            foreach ( $entries as $entry ) {
                $data = $entry->response_data;

                // These keys are currently 'first_name' and 'last_name'
                // You should verify these keys against the "Raw response_data" dump
                // on your admin page to ensure they match your Fluent Form field names.
                $first = isset( $data['first_name'] ) ? $data['first_name'] : '';
                $last  = isset( $data['last_name'] )  ? $data['last_name']  : '';
                $name  = esc_html( trim( "$first $last" ) );

                $email = isset( $data['email'] ) ? esc_html( $data['email'] ) : '';

                if ( ! empty( $data['instruments'] ) ) {
                    $instr = is_array( $data['instruments'] )
                        ? implode( ', ', $data['instruments'] )
                        : $data['instruments'];
                    $instruments = esc_html( $instr );
                } else {
                    $instruments = '';
                }

                $notes = isset( $data['availability_notes'] )
                    ? esc_html( $data['availability_notes'] )
                    : '';

                echo '<tr>';
                echo '<td>' . intval( $entry->id ) . '</td>';
                echo '<td>' . esc_html( $entry->created_at ) . '</td>';
                echo '<td>' . $name . '</td>';
                echo '<td>' . $email . '</td>';
                echo '<td>' . $instruments . '</td>';
                echo '<td>' . $notes . '</td>';
                echo '</tr>';
            }
        }

        echo '</tbody></table>';
        echo '</div>';
    }

} // end class