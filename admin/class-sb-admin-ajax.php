<?php
/**
 * SB_Ajax
 */
class SB_Admin_Ajax{
	
	function __construct(){
		add_action( 'wp_ajax_get_booking_information', array( $this, 'get_booking_information' ) );
		add_action( 'wp_ajax_nopriv_get_booking_information', array( $this, 'get_booking_information' ) );
	}

	public function get_booking_information(){

		global $wpdb;

		$safari_booking_table = $wpdb->prefix . 'safari_booking_customers';
			
		$booking = $wpdb->get_row( "
			SELECT * FROM 
				$safari_booking_table 
			WHERE 
				booking_id = '".$_POST['booking_id']."' 
		" );

		ob_start(); ?>
		
		<h3><?php echo "<pre>"; print_r( $booking ); echo "</pre>"; ?></h3>
		
		<?php $html = ob_get_clean();

		if( !empty( $booking ) ){
			wp_send_json_success(array(
				'html' => $html
			));
		}

	}

}

new SB_Admin_Ajax();

?>