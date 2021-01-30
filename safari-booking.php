<?php
/**
 * Plugin Name:       Safari Booking
 * Plugin URI:        https://girlionsafaribooking.com/
 * Description:       Custom safari booking form plugin with Razorpay payment gateway.
 * Version:           1.0.0
 * Author:            girlionsafaribooking
 * Author URI:        https://girlionsafaribooking.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       safari-booking
 * Domain Path:       /languages
 */


/**
 * Main Class Safari_Booking
 */
class Safari_Booking{
	
	/**
	 * Safari Booking Constructor.
	 */
	function __construct(){
		
		$this->define_constants();
		$this->define_tables();
		$this->includes();
		$this->init_hooks();

	}

	/**
	 * Define WC Constants.
	 */
	private function define_constants() {
		define( 'SB_VERSION', '1.0.0' );
		define( 'SB_PLUGIN_DIR', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'SB_PLUGIN_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
	}

	/**
	 * Register custom tables within $wpdb object.
	 */
	private function define_tables() {

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$charset_collate = $wpdb->get_charset_collate();

		$safari_booking_table = $wpdb->prefix . 'safari_booking';

		$sql = "CREATE TABLE IF NOT EXISTS $safari_booking_table (
			id int(11) NOT NULL AUTO_INCREMENT,
			booking_code varchar(10),
			booking_date varchar(10),
			booking_time varchar(50),
			booking_type varchar(50),
			no_of_adult int(10),
			no_of_child int(10),
			name varchar(200),
			mobile varchar(200),
			email varchar(200),
			address text,
			amount int(10),
			payment_id varchar(200),
			staus varchar(20),
			PRIMARY KEY  (id)
		) $charset_collate;";

		dbDelta( $sql );

		$safari_booking_customers_table = $wpdb->prefix . 'safari_booking_customers';

		$sql = "CREATE TABLE IF NOT EXISTS $safari_booking_customers_table (
			id int(11) NOT NULL AUTO_INCREMENT,
			booking_id int(11) NOT NULL,
			name varchar(200),
			age int(4),
			gender varchar(10),
			nationality varchar(50),
			state varchar(50),
			country varchar(50),
			id_proof varchar(50),
			id_proof_number varchar(50),
			proof_file text,
			person_type varchar(10),
			PRIMARY KEY  (id)
		) $charset_collate;";

		dbDelta( $sql );
	}

	/**
	 * Include required core files used in admin and on the frontend.
	 */
	public function includes() {

		//public(frontend) files
		include_once ('public/class-sb-shortcodes.php');
		include_once ('public/class-sb-ajax.php');

		// include_once ('admin/class-sb-admin.php');
		// include_once ('admin/class-sb-booking-list.php');
		// include_once ('admin/class-sb-settings.php');

	}

	/**
	 * Hook into actions and filters.
	 *
	 * @since 1.0
	 */
	private function init_hooks() {
		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'wp_enqueue_scripts', array( $this, 'safari_booking_enqueue_styles' ) );
	}

	public function init(){

	}

	public function safari_booking_enqueue_styles() { 
	    wp_enqueue_script("jquery-ui-tabs");

	    // Load the datepicker script (pre-registered in WordPress).
	    wp_enqueue_script( 'jquery-ui-datepicker' );

	    // You need styling for the datepicker. For simplicity I've linked to the jQuery UI CSS on a CDN.
	    wp_register_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css' );
	    wp_enqueue_style( 'jquery-ui' );  

		//wp_enqueue_style( 'bootstrap-css', SB_PLUGIN_URL.'/assets/css/bootstrap.min.css', false, time() );
		wp_enqueue_style( 'safari-booking-css', SB_PLUGIN_URL.'/assets/css/safari-booking.css', false, time() );
		wp_enqueue_style( 'waitMe-min-css', SB_PLUGIN_URL.'/assets/css/waitMe.min.css', false, time() );

	    wp_enqueue_script( 'waitMe-min-js', SB_PLUGIN_URL.'/assets/js/waitMe.min.js', array( 'jquery' ), time() );
	    wp_enqueue_script( 'jquery-validate-min-js', SB_PLUGIN_URL.'/assets/js/jquery.validate.min.js', array( 'jquery' ), time() );
	    wp_enqueue_script( 'razorpay-checkout-js', 'https://checkout.razorpay.com/v1/checkout.js', array( 'jquery' ), time() );
	    
	    wp_register_script( 'safari-booking-js', SB_PLUGIN_URL.'/assets/js/safari-booking.js', array( 'jquery' ), time() );
		 
		// Localize the script with new data
		$safari_booking = array(
		    'ajaxurl' => admin_url('admin-ajax.php'),
		);
		wp_localize_script( 'safari-booking-js', 'safari_booking', $safari_booking );
		 
		// Enqueued script with localized data.
		wp_enqueue_script( 'safari-booking-js' );

	}

}
new Safari_Booking();