<?php
namespace RedBarnMusic\Admin\Pages;

use RedBarnMusic\Admin\Interfaces\ComponentInterface;

defined( 'ABSPATH' ) || exit;

class RoomsByDayPage implements ComponentInterface {

    public function register(): void {
        add_submenu_page(
            'edit.php?post_type=student',
            'Rooms by Day',
            'Rooms by Day',
            'manage_options',
            'rbm-rooms-by-day',
            [ $this, 'render' ]
        );
    }

    public function render(): void {
        echo '<div class="wrap"><h1>Rooms by Day</h1>';

        $days = [ 'TBD','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday' ];
        $day  = isset( $_GET['day'] ) ? sanitize_text_field( $_GET['day'] ) : 'TBD';

        echo '<form method="get" style="margin-bottom:1em;">';
        echo '<input type="hidden" name="page" value="rbm-rooms-by-day">';
        echo '<label for="rbm_day">Day: </label>';
        echo '<select id="rbm_day" name="day">';
        foreach ( $days as $d ) {
            $sel = ( $d === $day ) ? ' selected' : '';
            echo '<option value="' . esc_attr( $d ) . '"' . $sel . '>' . esc_html( $d ) . '</option>';
        }
        echo '</select> ';
        echo '<button type="submit" class="button">View</button>';
        echo '</form>';

        echo '<style>
            .rbm-grid td.blocked, .rbm-grid td.booked { background-color: #d0d0d0; }
            .rbm-grid td a { text-decoration: none; }
        </style>';

        $this->render_grid( $day );
        echo '</div>';
    }

    public function render_grid( string $day ): void {
        $times = [
            '1300'=>'1:00 PM','1315'=>'1:15 PM',
            '1330'=>'1:30 PM','1345'=>'1:45 PM',
            '1400'=>'2:00 PM','1415'=>'2:15 PM',
            '1430'=>'2:30 PM','1445'=>'2:45 PM',
        ];
        $rooms = [ 'R1','R2','R3','R4','R5','R6' ];

        $all = get_posts([
            'post_type'   => 'student',
            'numberposts' => -1,
            'meta_query'  => [
                [ 'key' => 'day_time_room', 'value' => "{$day}_", 'compare' => 'LIKE' ],
            ],
        ]);

        $slot_student = [];
        $occupied     = array_fill_keys( $rooms, [] );
        $time_keys    = array_keys( $times );

        foreach ( $all as $stu ) {
            $meta = get_post_meta( $stu->ID, 'day_time_room', true );
            list( $stu_day, $stu_time, $stu_room ) = explode( '_', $meta );

            if ( $stu_day !== $day ) {
                continue;
            }

            $slot_student[ $meta ] = $stu;

            $dur    = intval( get_post_meta( $stu->ID, 'lesson_duration', true ) ) ?: 30;
            $slices = max( 1, (int) ceil( $dur / 15 ) );
            $idx    = array_search( $stu_time, $time_keys, true );

            for ( $i = 0; $i < $slices; $i++ ) {
                if ( isset( $time_keys[ $idx + $i ] ) ) {
                    $occupied[ $stu_room ][] = $time_keys[ $idx + $i ];
                }
            }
        }

        echo '<table class="rbm-grid wp-list-table widefat fixed striped">';
        echo   '<thead><tr><th>Time</th>';
        foreach ( $rooms as $room ) {
            echo '<th>Room ' . esc_html( substr( $room, 1 ) ) . '</th>';
        }
        echo   '</tr></thead><tbody>';

        foreach ( $times as $time_key => $time_label ) {
            echo '<tr><td>' . esc_html( $time_label ) . '</td>';
            foreach ( $rooms as $room ) {
                $slot_id = "{$day}_{$time_key}_{$room}";

                if ( isset( $slot_student[ $slot_id ] ) ) {
                    $stu = $slot_student[ $slot_id ];
                    $url = admin_url(
                        "post.php?post={$stu->ID}&action=edit&slot_id=" . urlencode( $slot_id )
                    );
                    echo '<td class="booked"><a href="' . esc_url( $url ) . '">'
                         . esc_html( $stu->post_title ) . '</a></td>';
                } elseif ( in_array( $time_key, $occupied[ $room ], true ) ) {
                    echo '<td class="blocked">&nbsp;</td>';
                } else {
                    $url = admin_url(
                        'post-new.php?post_type=student&slot_id=' . urlencode( $slot_id )
                    );
                    echo '<td><a href="' . esc_url( $url ) . '">+</a></td>';
                }
            }
            echo '</tr>';
        }

        echo '</tbody></table>';
    }
}
