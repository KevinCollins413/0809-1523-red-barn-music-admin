<?php
namespace RedBarnMusic\Admin\Pages;

use RedBarnMusic\Admin\Interfaces\ComponentInterface;

defined( 'ABSPATH' ) || exit;

class AllDaysPage implements ComponentInterface {

    public function register(): void {
        add_submenu_page(
            'edit.php?post_type=student',
            'All Days',
            'All Days',
            'manage_options',
            'rbm-all-days',
            [ $this, 'render' ]
        );
    }

    public function render(): void {
        echo '<div class="wrap"><h1>All Days by Room</h1>';
        echo '<style>
            .rbm-grid td.blocked, .rbm-grid td.booked { background-color: #d0d0d0; }
            .rbm-grid td a { text-decoration: none; }
        </style>';

        $days = [ 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday' ];

        foreach ( $days as $day ) {
            echo '<h2>' . esc_html( $day ) . '</h2>';
            // Reuse the same render_grid() logic for each day
            ( new RoomsByDayPage() )->render_grid( $day );
        }

        echo '</div>';
    }
}
