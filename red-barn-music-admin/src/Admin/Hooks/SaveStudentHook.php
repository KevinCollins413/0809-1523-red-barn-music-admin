<?php
namespace RedBarnMusic\Admin\Hooks;

defined( 'ABSPATH' ) || exit;

class SaveStudentHook {

    public static function register(): void {
        add_action( 'acf/save_post', [ __CLASS__, 'fill_student_title' ], 20 );
    }

    public static function fill_student_title( $post_id ) {
        if ( get_post_type( $post_id ) !== 'student' ) {
            return;
        }

        $first = get_field( 'first_name', $post_id ) ?: '';
        $last  = get_field( 'last_name',  $post_id ) ?: '';
        $new_title = trim( "$first $last" );

        if ( empty( $new_title ) ) {
            return;
        }

        if ( get_the_title( $post_id ) === $new_title ) {
            return;
        }

        // Prevent infinite loop
        remove_action( 'acf/save_post', [ __CLASS__, 'fill_student_title' ], 20 );

        wp_update_post( [
            'ID'         => $post_id,
            'post_title' => $new_title,
            'post_name'  => sanitize_title( $new_title ),
        ] );

        // Re-hook
        add_action( 'acf/save_post', [ __CLASS__, 'fill_student_title' ], 20 );
    }
}
