<?php
use Razorpay\Api\Api;

/**
 * SB_Ajax
 */
class SB_Ajax{
	
	function __construct(){
		add_action( 'wp_ajax_check_availability_and_verify', array( $this, 'check_availability_and_verify' ) );
		add_action( 'wp_ajax_nopriv_check_availability_and_verify', array( $this, 'check_availability_and_verify' ) );

		add_action( 'wp_ajax_add_safari_booking', array( $this, 'add_safari_booking' ) );
		add_action( 'wp_ajax_nopriv_add_safari_booking', array( $this, 'add_safari_booking' ) );
	}

	public function check_availability_and_verify(){

		global $wpdb;

		$safari_booking_table = $wpdb->prefix . 'safari_booking';
		
		$booking = $wpdb->get_row( "
			SELECT * FROM 
				$safari_booking_table 
			WHERE 
				booking_date = '".date('Y-m-d',strtotime($_POST['date']))."' 
			AND 
				booking_time = '".$_POST['time']."' 
			AND 	
				booking_type = '".$_POST['type']."' 
		" );

		if( !empty( $booking ) ){
			wp_send_json_error(array(
				'message' => __( 'Sorry, the booking date or timing is not available. please try a different date or time.', 'safari-booking' )
			));
		}
		
		wp_send_json_success(array(
			'total_amount' => $this->calculate_price( $_POST['adult'], $_POST['child'] )
		));

	}

	public function add_safari_booking(){

		global $wpdb;

		$upload_dir = wp_upload_dir();

		foreach ( $_POST['adults']  as $key => $adult ) {
			if( isset( $_FILES['adults'] ) && !empty( $_FILES['adults'] ) ){
				$_POST['adults'][$key]['idprooffile']['name'] = $_FILES['adults']['name'][$key];
				$_POST['adults'][$key]['idprooffile']['type'] = $_FILES['adults']['type'][$key];
				$_POST['adults'][$key]['idprooffile']['tmp_name'] = $_FILES['adults']['tmp_name'][$key];
				$_POST['adults'][$key]['idprooffile']['error'] = $_FILES['adults']['error'][$key];
				$_POST['adults'][$key]['idprooffile']['size'] = $_FILES['adults']['size'][$key];
			}
		}

		if ( ! isset( $_POST['_wpnonce'] )  || ! wp_verify_nonce( $_POST['_wpnonce'] )  ) {
			wp_send_json_error(array(
				'message' => __( 'Sorry, Unauthorize aceess.', 'safari-booking' )
			));
		}
		
		try {

			$keyId = 'rzp_test_N5A13aKGNaNYg8';
			$keySecret = '5BtEceoqt3zU8FhwxDFlHVrN';
			include(SB_PLUGIN_DIR.'/razorpay-php/Razorpay.php');
			$api = new Api($keyId, $keySecret);
			
		    $razorpay_payment_id = $_POST['razorpay_payment_id'];
		    $total_amount = $_POST['total_amount'] * 100;
			
			$payment = $api->payment->fetch($razorpay_payment_id);

		  	$payment->capture(array('amount' => $total_amount, 'currency' => 'INR'));

		    if($payment->error == NULL){
			
				$table = $wpdb->prefix.'safari_booking';
				
				$booking_code = $this->generate_booking_code();

				$data = array(
					'booking_code' => $booking_code,
					'booking_date' => date( 'Y-m-d', strtotime( $_POST['date'] ) ),
					'booking_time' => $_POST['time'],
					'booking_type' => $_POST['type'],
					'no_of_adult' => $_POST['adult'],
					'no_of_child' => $_POST['child'],
					'name' => $_POST['customer_name'],
					'mobile' => $_POST['mobile'],
					'email' => $_POST['email'],
					'address' => $_POST['address'],
					'amount' => $_POST['total_amount'],
					'payment_id' => $_POST['razorpay_payment_id'],
					'staus' => 'success',
				);

				$wpdb->insert( $table,$data );
				$safari_booking_id = $wpdb->insert_id;

				$table = $wpdb->prefix.'safari_booking_customers';

				if( !empty( $_POST['adults'] ) ){

					foreach ( $_POST['adults'] as $key => $adult ) {

						$data = array(
							'booking_id' => $safari_booking_id,
							'name' => $adult['name'],
							'age' => $adult['age'],
							'gender' => $adult['gender'],
							'nationality' => $adult['nationality'],
							'state' => $adult['state'],
							'country' => $adult['country'],
							'id_proof' => $adult['id_proof'],
							'id_proof_number' => $adult['idnumber'],
							'proof_file' => '',
							'person_type' => $adult['person_type'],
						);

						$wpdb->insert( $table, $data );
						$safari_booking_customers_id = $wpdb->insert_id;

						if ( !file_exists( $upload_dir['basedir'].'/safari_booking/' ) ) {
						    mkdir( $upload_dir['basedir'].'/safari_booking/', 0777 );
						}

						if ( !file_exists( $upload_dir['basedir'].'/safari_booking/'.$safari_booking_id.'/' ) ) {
						    mkdir( $upload_dir['basedir'].'/safari_booking/'.$safari_booking_id.'/', 0777 );
						}

						if ( !file_exists( $upload_dir['basedir'].'/safari_booking/'.$safari_booking_id.'/'.$safari_booking_customers_id.'/' ) ) {
						    mkdir( $upload_dir['basedir'].'/safari_booking/'.$safari_booking_id.'/'.$safari_booking_customers_id.'/', 0777 );
						}

						$basedir = $upload_dir['basedir'].'/safari_booking/'.$safari_booking_id.'/'.$safari_booking_customers_id.'/';
						$baseurl = $upload_dir['baseurl'].'/safari_booking/'.$safari_booking_id.'/'.$safari_booking_customers_id.'/';

						$filename  = pathinfo($adult['idprooffile']['name'],PATHINFO_FILENAME);
						$extension = pathinfo($adult['idprooffile']['name'],PATHINFO_EXTENSION);
					  	$filename  = sanitize_title( $adult['id_proof'] ) .'.'. $extension;
					  	
						// Upload file
						if( move_uploaded_file( $adult['idprooffile']['tmp_name'], $basedir.$filename ) ){
							
							$data = array( 'proof_file' => $filename ); 
							
							$where = array( 'id' => $safari_booking_customers_id );
							
							$wpdb->update( $table, $data, $where );

						}

					}

				}

				if( !empty( $_POST['childs'] ) ){

					foreach ( $_POST['childs'] as $key => $child ) {

						$data = array(
							'booking_id' => $safari_booking_id,
							'name' => $child['name'],
							'age' => $child['age'],
							'gender' => $child['gender'],
							'nationality' => $child['nationality'],
							'state' => $child['state'],
							'country' => $child['country'],
							'id_proof' => $child['id_proof'],
							'id_proof_number' => $child['idnumber'],
							'proof_file' => '',
							'person_type' => $child['person_type'],
						);

						$wpdb->insert( $table,$data );
						$safari_booking_customers_id = $wpdb->insert_id;
						
					}

				}

			}

			$this->send_mail_to_customer( $_POST );
			$this->send_mail_to_admin( $_POST );

			wp_send_json_success( array(
				'redirect' => home_url('/'.$_POST['thankyou_url'].'?booking_code='.$booking_code)
			) );
		}
		//catch exception
		catch(Exception $e) {
			wp_send_json_error( array(
				'message' => $e->getMessage()
			) );
		}

	}

	public function generate_booking_code(){

		global $wpdb;

		$safari_booking_table = $wpdb->prefix . 'safari_booking';

		$booking_code = strtoupper(wp_generate_password( 10, false ));

		$booking = $wpdb->get_row( "SELECT booking_code FROM $safari_booking_table WHERE booking_code = '".$booking_code."' " );

		if( !empty( $booking ) ){
			$this->generate_booking_code();
		}

		return $booking_code;
	}

	public function send_mail_to_customer( $data ){

	}

	public function send_mail_to_admin( $data ){

	}


	public function calculate_price( $adult, $child ){
		$price = 0;
		$price = 3600 + ( 100 * $child );
		return $price;
	}
}

new SB_Ajax();

?>