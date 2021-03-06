<?php
/**
 * Theme feedback class.
 * Prompt users to check out Charmed Pro after a day.
 *
 * Heavily based on code by Rhys Wynne
 * https://winwar.co.uk/2014/10/ask-wordpress-plugin-reviews-week/
 * 
 * @package Charmed
 */
 


if ( ! class_exists( 'ThemeBeans_Request_Feedback' ) ) :
class ThemeBeans_Request_Feedback {



    /**
     * Private variables.
     *
     * These should be customised for each project.
     */
    private $slug;        // The plugin slug
    private $name;        // The plugin name
    private $time_limit;  // The time limit at which notice is shown



    /**
     * Variables.
     */
    public $nobug_option;



    /**
     * Fire the constructor up :)
     */
    public function __construct( $args ) {

        $this->slug        = $args['slug'];
        $this->name        = $args['name'];
        if ( isset( $args['time_limit'] ) ) {
            $this->time_limit  = $args['time_limit'];
        } else {
            $this->time_limit = WEEK_IN_SECONDS;
        }

        $this->nobug_option = $this->slug . '-no-bug';

        // Loading main functionality
        add_action( 'admin_init', array( $this, 'check_installation_date' ) );
        add_action( 'admin_init', array( $this, 'set_no_bug' ), 5 );
    }



    /**
     * Seconds to words.
     */
    public function seconds_to_words( $seconds ) {

        // Get the years
        $years = ( intval( $seconds ) / YEAR_IN_SECONDS ) % 100;
        if ( $years > 1 ) {
            return sprintf( __( '%s years', $this->slug ), $years );
        } elseif ( $years > 0) {
            return __( 'a year', $this->slug );
        }

        // Get the weeks
        $weeks = ( intval( $seconds ) / WEEK_IN_SECONDS ) % 52;
        if ( $weeks > 1 ) {
            return sprintf( __( '%s weeks', $this->slug ), $weeks );
        } elseif ( $weeks > 0) {
            return __( 'a week', $this->slug );
        }

        // Get the days
        $days = ( intval( $seconds ) / DAY_IN_SECONDS ) % 7;
        if ( $days > 1 ) {
            return sprintf( __( '%s days', $this->slug ), $days );
        } elseif ( $days > 0) {
            return __( 'a day', $this->slug );
        }

        // Get the hours
        $hours = ( intval( $seconds ) / HOUR_IN_SECONDS ) % 24;
        if ( $hours > 1 ) {
            return sprintf( __( '%s hours', $this->slug ), $hours );
        } elseif ( $hours > 0) {
            return __( 'an hour', $this->slug );
        }

        // Get the minutes
        $minutes = ( intval( $seconds ) / MINUTE_IN_SECONDS ) % 60;
        if ( $minutes > 1 ) {
            return sprintf( __( '%s minutes', $this->slug ), $minutes );
        } elseif ( $minutes > 0) {
            return __( 'a minute', $this->slug );
        }

        // Get the seconds
        $seconds = intval( $seconds ) % 60;
        if ( $seconds > 1 ) {
            return sprintf( __( '%s seconds', $this->slug ), $seconds );
        } elseif ( $seconds > 0) {
            return __( 'a second', $this->slug );
        }

        return;
    }



    /**
     * Check date on admin initiation and add to admin notice if it was more than the time limit.
     */
    public function check_installation_date() {

        if ( true != get_site_option( $this->nobug_option ) ) {

            // If not installation date set, then add it
            $install_date = get_site_option( $this->slug . '-activation-date' );
            if ( '' == $install_date ) {
                add_site_option( $this->slug . '-activation-date', time() );
            }

            // If difference between install date and now is greater than time limit, then display notice
            if ( ( time() - $install_date ) >  $this->time_limit  ) {
                add_action( 'admin_notices', array( $this, 'display_admin_notice' ) );
            }

        }

    }



    /**
     * Display Admin Notice, asking for a review.
     */
    public function display_admin_notice() {

        global $pagenow;
    
        if (( $pagenow == 'index.php' )) {

            $current_user = wp_get_current_user();
            $url = get_home_url();

            $no_bug_url = wp_nonce_url( admin_url( '?' . $this->nobug_option . '=true' ), 'review-nonce' );
            $time = $this->seconds_to_words( time() - get_site_option( $this->slug . '-activation-date' ) );

            echo '<div class="updated feedback-notice">
                    <p><b>'. __( 'Instantly save 10% on Charmed Pro!</b> Just add Charmed Pro to your cart and we\'ll automagically apply your discount.', 'charmed' ) . '</p>
                    <p>
                    <a class="button button-secondary" href="' . esc_url( 'https://themebeans.com/themes/charmed/#upsell' ) . '" target="_blank">' . __( 'Learn More', 'charmed' ) . '</a>
                    <a class="button button-primary" href="' . esc_url( PRO_UPGRADE_URL.'&discount=CHARMED_SAVE10' ) . '" target="_blank">' . __( 'Add to Cart - $59', 'charmed' ) . '</a>
                    </p>
                </div>';
        }

    }



    /**
     * Set the theme to no longer bug users if user asks not to be.
     */
    public function set_no_bug() {

        // Bail out if not on correct page
        if (
            ! isset( $_GET['_wpnonce'] )
            ||
            (
                ! wp_verify_nonce( $_GET['_wpnonce'], 'review-nonce' )
                ||
                ! is_admin()
                ||
                ! isset( $_GET[$this->nobug_option] )
                ||
                ! current_user_can( 'manage_options' )
            )
        ) {
            return;
        }

        add_site_option( $this->nobug_option, true );
    }
}
endif;



/**
 * Set the variables for the feedback class
 */
new ThemeBeans_Request_Feedback( array(
    'slug'        => 'charmed',
    'name'        => 'Charmed',
    'time_limit'  => DAY_IN_SECONDS,
) );