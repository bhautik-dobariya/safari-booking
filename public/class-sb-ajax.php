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
		
		$safari_booking_basic_settings = get_option( 'safari_booking_basic_settings' );

		if( $_POST['type'] == 'gir-jungle-trail' ){
			$time_settings = $safari_booking_basic_settings['gir_jungle_trail_timing'];
		}else{
			$time_settings = $safari_booking_basic_settings['devalia_park_timing'];
		}

		if( !in_array( $_POST['time'], $time_settings ) ){
			wp_send_json_error(array(
				'message' => __( 'Sorry, the timing is not available. please try a different time.', 'safari-booking' )
			));
		}

		//echo date('Y-m-d',strtotime($_POST['date'])); die;

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

		$time = explode('to',$_POST['time']);

		//echo date('Y-m-d H:i a', current_time( 'timestamp', 0 )).' >= '.date('Y-m-d H:i a', strtotime( '-1 hour', strtotime( date( 'Y-m-d', strtotime( $_POST['date'] ) ).' '.$time[0] ) ) );

		if ( current_time( 'timestamp', 0 ) >= strtotime( '-1 hour', strtotime( date( 'Y-m-d', strtotime( $_POST['date'] ) ).' '.$time[0] ) ) ) {
		 	wp_send_json_error(array(
				'message' => __( 'Sorry, you can only book 1 hour before the time you select.', 'safari-booking' )
			));  
		}
		
		$nationality = array();

		if( !empty( $_POST['adults'] ) ){
			foreach ( $_POST['adults'] as $key => $adult ) {
				$nationality[] = $adult['nationality'];
			}
		}

		if( !empty( $_POST['childs'] ) ){
			foreach ( $_POST['childs'] as $key => $child ) {
				$nationality[] = $child['nationality'];
			}
		}

		$price = 0;

		if( in_array( 'Foreigner', $nationality ) ){
			$price = calculate_price( $_POST['adult'], $_POST['child'], 'foreigner' );
		}else{
			$price = calculate_price( $_POST['adult'], $_POST['child'], 'indian' );
		}

		wp_send_json_success(array(
			'total_amount' => $price
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
				'message' => __( 'Sorry, Unauthorize aceess.', 'safari-booking' ),
				'LINE' => __LINE__
			));
		}
		
		try {

			$safari_booking_basic_settings = get_option( 'safari_booking_basic_settings' );

			$keyId = $safari_booking_basic_settings['razor_pay_key_id'];
			$keySecret = $safari_booking_basic_settings['razor_pay_key_secret'];

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
					'status' => 'success',
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

			$_POST['booking_code'] = $booking_code;

			$this->send_mail_to_customer( $_POST );
			$this->send_mail_to_admin( $_POST );

			wp_send_json_success( array(
				'redirect' => $_POST['thankyou_url'].'?booking_code='.$booking_code,
				'LINE' => __LINE__
			) );
			
		}catch(Exception $e) {
			wp_send_json_error( array(
				'message' => $e->getMessage(),
				'LINE' => __LINE__
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

		global $wpdb;

		$upload_dir = wp_upload_dir();

		add_filter( 'wp_mail_content_type', array( $this, 'girlionsafaribooking_set_html_mail_content_type' ) );

		$to = $data['email'];
		$subject = 'Thank you for Booking on girlionsafaribooking - '.$data['booking_code'];
		$headers[] = 'From: girlionsafaribooking <girlionsafaribooking.com/>';

		ob_start(); 

		$safari_booking_table = $wpdb->prefix . 'safari_booking';
			
		$booking = $wpdb->get_row( "
			SELECT * FROM 
				$safari_booking_table 
			WHERE 
				booking_code = '".$data['booking_code']."' 
		",ARRAY_A );
		
		if( !empty( $booking ) ){ ?>
			<table cellspacing="0" cellpadding="0" width="100%" style="margin: 0px auto; font-family: sans-serif; font-size: 15px; max-width: 580px;">
				<tbody>
					<!-- <tr>
						<td cellspacing="0" align="center"><a href="#" ><img src="logo.png" style="margin-bottom: -3px;" ></a></td>
					</tr> -->
					<tr>
						<td>
							<table align="center" cellspacing="0" width="100%" style="background: #E09900; padding: 15px; border-radius: 15px 15px 0px 0px;" >
								<tr>
									<td align="center" style="color:#ffffff; font-size: 18px; " >Booking Details</td>
								</tr>
							</table>
							
						</td>
					</tr>
					<tr>
						<td>
							<table width="100%" style="background: #ffffff; padding: 15px; border: 1px solid #efefef; font-size: 13px;" >
								<tbody>
									<tr>
										<td height=25><label style="font-weight: bold; padding-right: 15px;">Booking Code</label></td>
										<td height=25><label style="font-weight: bold; padding-right: 15px;"><?php echo $booking['booking_code']; ?></label></td>
									</tr>
									<tr>
										<td colspan="2"><hr style="border: 1px solid #efefef!important; border-width: 1px 0 0 0!important;padding-top: 15px; margin-top: 15px;"></td>
									</tr>
									<tr>
										<td height="25"><label style="font-weight: bold; padding-right: 15px;">Booking Date:</label> <value><?php echo date( 'd-m-Y', strtotime( $booking['booking_date'] ) ); ?></value></td>
										<td height="25"><label style="font-weight: bold; padding-right: 15px;">Booking Timing:</label> <value><?php echo $booking['booking_time']; ?></value></td>
									</tr>
									<tr>
										<td height="25"><label style="font-weight: bold; padding-right: 15px;">No. of Adult:</label> <value><?php echo $booking['no_of_adult']; ?></value></td>
										<td height="25"><label style="font-weight: bold; padding-right: 15px;">No. of Child:</label> <value><?php echo $booking['no_of_child']; ?></value></td>
									</tr>
									<tr>
										<td height="25"><label style="font-weight: bold; padding-right: 15px;">Name:</label> <value><?php echo $booking['name']; ?></value></td>
									</tr>
									<tr>
										<td height="25"><label style="font-weight: bold; padding-right: 15px;">Email:</label> <value><?php echo $booking['email']; ?></value></td>
									</tr>
									<tr>
										<td height="25"><label style="font-weight: bold; padding-right: 15px;">Mobile Number:</label> <value><?php echo $booking['mobile']; ?></value></td>
									</tr>
									<tr>
										<td height="25"><label style="font-weight: bold; padding-right: 15px;">Full Address:</label> <value><?php echo $booking['address']; ?></value></td>
									</tr>
									<tr>
										<td colspan="2"><hr style="border: 1px solid #efefef!important; border-width: 1px 0 0 0!important;padding-top: 15px; margin-top: 15px;"></td>
									</tr>
									
									<?php

									$safari_booking_customers_table = $wpdb->prefix . 'safari_booking_customers';
		
									$booking_customers = $wpdb->get_results( "
										SELECT * FROM 
											$safari_booking_customers_table 
										WHERE 
											booking_id = '".$booking['id']."'
										AND 
											person_type = 'adult' 
									",ARRAY_A );

									if( !empty( $booking_customers ) ){

										$i = 1;

										foreach ( $booking_customers as $key => $booking_customers_adult ) { 
											$proof_file = $upload_dir['baseurl'].'/safari_booking/'.$booking['id'].'/'.$booking_customers_adult['id'].'/'.$booking_customers_adult['proof_file'];
											?>

										<tr>
											<td colspan="2" style="padding-bottom: 20px;" ><strong>Adult Details</strong></td>
										</tr>
										<tr>
											<td height="25"><span style="font-weight: bold; padding-right: 15px;"><?php echo $i; ?></span><span>Adult</span></td>
											<td height="25"><label style="font-weight: bold padding-right: 15px;"><?php echo $booking_customers_adult['name']; ?></label><span></span></td>
										</tr>
										<tr>
											<td height=25><label style="font-weight: bold; padding-right: 15px;">Age:</label><span><?php echo $booking_customers_adult['age']; ?></span></td>
											<td height=25><label style="font-weight: bold; padding-right: 15px;">Gender:</label><span><?php echo $booking_customers_adult['gender']; ?></span></td>
										</tr>
										<tr>
											<td height=25><label style="font-weight: bold; padding-right: 15px;">Nationality:</label><span><?php echo $booking_customers_adult['nationality']; ?></span></td>
											<?php if( $booking_customers_adult['nationality'] == 'Indian' ){ ?>
												<td height=25><label style="font-weight: bold; padding-right: 15px;">State:</label><span><?php echo $booking_customers_adult['state']; ?></span></td>
											<?php }else{ ?>
												<td height=25><label style="font-weight: bold; padding-right: 15px;">Country:</label><span><?php echo $booking_customers_adult['country']; ?></span></td>
											<?php } ?>
										</tr>
										<tr>
											<td height=25><label style="font-weight: bold; padding-right: 15px;">ID Proof:</label><span><?php echo $booking_customers_adult['id_proof']; ?></span></td>
											<td height=25><label style="font-weight: bold; padding-right: 15px;">ID Proof Number:</label><span><?php echo $booking_customers_adult['id_proof_number']; ?></span></td>
										</tr>
										<tr>
											<td colspan="2"><label style="font-weight: bold; padding-right: 15px;">ID Proof Photo:</label><img src="<?php echo $proof_file; ?>" style="width: 100px;display: block;"></td>
										</tr>

									<?php $i++; } } ?>

									<tr>
										<td colspan="2"><hr style="border: 1px solid #efefef!important; border-width: 1px 0 0 0!important;padding-top: 15px; margin-top: 15px;"></td>
									</tr>

									<?php

									$safari_booking_customers_table = $wpdb->prefix . 'safari_booking_customers';
		
									$booking_customers = $wpdb->get_results( "
										SELECT * FROM 
											$safari_booking_customers_table 
										WHERE 
											booking_id = '".$booking['id']."'
										AND 
											person_type = 'child' 
									",ARRAY_A );

									if( !empty( $booking_customers ) ){

										$i = 1;

										foreach ( $booking_customers as $key => $booking_customers_child ) { ?>

										<tr>
											<td colspan="2" style="padding-bottom: 20px;" ><strong>Child Details</strong></td>
										</tr>
										<tr>
											<td height="25"><span style="font-weight: bold; padding-right: 15px;"><?php echo $i; ?></span><span>Child</span></td>
											<td height="25"><label style="font-weight: bold padding-right: 15px;"><?php echo $booking_customers_child['name']; ?></label><span></span></td>
										</tr>
										<tr>
											<td height=25><label style="font-weight: bold; padding-right: 15px;">Age:</label><span><?php echo $booking_customers_child['age']; ?></span></td>
											<td height=25><label style="font-weight: bold; padding-right: 15px;">Gender:</label><span><?php echo $booking_customers_child['gender']; ?></span></td>
										</tr>
										<tr>
											<td height=25><label style="font-weight: bold; padding-right: 15px;">Nationality:</label><span><?php echo $booking_customers_child['nationality']; ?></span></td>
											<?php if( $booking_customers_child['nationality'] == 'Indian' ){ ?>
												<td height=25><label style="font-weight: bold; padding-right: 15px;">State:</label><span><?php echo $booking_customers_child['state']; ?></span></td>
											<?php }else{ ?>
												<td height=25><label style="font-weight: bold; padding-right: 15px;">Country:</label><span><?php echo $booking_customers_child['country']; ?></span></td>
											<?php } ?>
										</tr>
										<tr>
											<td height=25><label style="font-weight: bold; padding-right: 15px;">ID Proof:</label><span><?php echo $booking_customers_child['id_proof']; ?></span></td>
											<td height=25><label style="font-weight: bold; padding-right: 15px;">ID Proof Number:</label><span><?php echo $booking_customers_child['id_proof_number']; ?></span></td>
										</tr>

									<?php $i++; } } ?>

									<tr>
										<td colspan="2"><hr style="border: 1px solid #efefef!important; border-width: 1px 0 0 0!important;padding-top: 15px; margin-top: 15px;"></td>
									</tr>

									<tr>
										<td height=25><label style="font-weight: bold; padding-right: 15px;">Total:</label></td>
										<td height=25><label style="font-weight: bold; padding-right: 15px;">₹<?php echo $booking['amount']; ?></label></td>
									</tr>

								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
			<?php
		}

		$message = ob_get_clean();

		wp_mail( $to, $subject, $message, $headers );

		remove_filter( 'wp_mail_content_type', array( $this, 'girlionsafaribooking_set_html_mail_content_type' ) );

	}

	public function send_mail_to_admin( $data ){

		global $wpdb;

		$upload_dir = wp_upload_dir();

		add_filter( 'wp_mail_content_type', array( $this, 'girlionsafaribooking_set_html_mail_content_type' ) );

		$safari_booking_basic_settings = get_option( 'safari_booking_basic_settings' );

		$to = $safari_booking_basic_settings['admin_email'];
		$subject = 'New Booking received on girlionsafaribooking - '.$data['booking_code'];
		$headers[] = 'From: girlionsafaribooking <girlionsafaribooking.com/>';

		ob_start(); 

		$safari_booking_table = $wpdb->prefix . 'safari_booking';
			
		$booking = $wpdb->get_row( "
			SELECT * FROM 
				$safari_booking_table 
			WHERE 
				booking_code = '".$data['booking_code']."'
		",ARRAY_A );
		
		if( !empty( $booking ) ){ ?>
			<table cellspacing="0" cellpadding="0" width="100%" style="margin: 0px auto; font-family: sans-serif; font-size: 15px; max-width: 580px;">
				<tbody>
					<!-- <tr>
						<td cellspacing="0" align="center"><a href="#" ><img src="logo.png" style="margin-bottom: -3px;" ></a></td>
					</tr> -->
					<tr>
						<td>
							<table align="center" cellspacing="0" width="100%" style="background: #E09900; padding: 15px; border-radius: 15px 15px 0px 0px;" >
								<tr>
									<td align="center" style="color:#ffffff; font-size: 18px; " >Booking Details</td>
								</tr>
							</table>
							
						</td>
					</tr>
					<tr>
						<td>
							<table width="100%" style="background: #ffffff; padding: 15px; border: 1px solid #efefef; font-size: 13px;" >
								<tbody>
									<tr>
										<td height=25><label style="font-weight: bold; padding-right: 15px;">Booking Code</label></td>
										<td height=25><label style="font-weight: bold; padding-right: 15px;"><?php echo $booking['booking_code']; ?></label></td>
									</tr>
									<tr>
										<td colspan="2"><hr style="border: 1px solid #efefef!important; border-width: 1px 0 0 0!important;padding-top: 15px; margin-top: 15px;"></td>
									</tr>
									<tr>
										<td height="25"><label style="font-weight: bold; padding-right: 15px;">Booking Date:</label> <value><?php echo date( 'd-m-Y', strtotime( $booking['booking_date'] ) ); ?></value></td>
										<td height="25"><label style="font-weight: bold; padding-right: 15px;">Booking Timing:</label> <value><?php echo $booking['booking_time']; ?></value></td>
									</tr>
									<tr>
										<td height="25"><label style="font-weight: bold; padding-right: 15px;">No. of Adult:</label> <value><?php echo $booking['no_of_adult']; ?></value></td>
										<td height="25"><label style="font-weight: bold; padding-right: 15px;">No. of Child:</label> <value><?php echo $booking['no_of_child']; ?></value></td>
									</tr>
									<tr>
										<td height="25"><label style="font-weight: bold; padding-right: 15px;">Name:</label> <value><?php echo $booking['name']; ?></value></td>
									</tr>
									<tr>
										<td height="25"><label style="font-weight: bold; padding-right: 15px;">Email:</label> <value><?php echo $booking['email']; ?></value></td>
									</tr>
									<tr>
										<td height="25"><label style="font-weight: bold; padding-right: 15px;">Mobile Number:</label> <value><?php echo $booking['mobile']; ?></value></td>
									</tr>
									<tr>
										<td height="25"><label style="font-weight: bold; padding-right: 15px;">Full Address:</label> <value><?php echo $booking['address']; ?></value></td>
									</tr>
									<tr>
										<td colspan="2"><hr style="border: 1px solid #efefef!important; border-width: 1px 0 0 0!important;padding-top: 15px; margin-top: 15px;"></td>
									</tr>
									
									<?php

									$safari_booking_customers_table = $wpdb->prefix . 'safari_booking_customers';
		
									$booking_customers = $wpdb->get_results( "
										SELECT * FROM 
											$safari_booking_customers_table 
										WHERE 
											booking_id = '".$booking['id']."'
										AND 
											person_type = 'adult' 
									",ARRAY_A );

									if( !empty( $booking_customers ) ){

										$i = 1;

										foreach ( $booking_customers as $key => $booking_customers_adult ) { 
											$proof_file = $upload_dir['baseurl'].'/safari_booking/'.$booking['id'].'/'.$booking_customers_adult['id'].'/'.$booking_customers_adult['proof_file'];
											?>

										<tr>
											<td colspan="2" style="padding-bottom: 20px;" ><strong>Adult Details</strong></td>
										</tr>
										<tr>
											<td height="25"><span style="font-weight: bold; padding-right: 15px;"><?php echo $i; ?></span><span>Adult</span></td>
											<td height="25"><label style="font-weight: bold padding-right: 15px;"><?php echo $booking_customers_adult['name']; ?></label><span></span></td>
										</tr>
										<tr>
											<td height=25><label style="font-weight: bold; padding-right: 15px;">Age:</label><span><?php echo $booking_customers_adult['age']; ?></span></td>
											<td height=25><label style="font-weight: bold; padding-right: 15px;">Gender:</label><span><?php echo $booking_customers_adult['gender']; ?></span></td>
										</tr>
										<tr>
											<td height=25><label style="font-weight: bold; padding-right: 15px;">Nationality:</label><span><?php echo $booking_customers_adult['nationality']; ?></span></td>
											<?php if( $booking_customers_adult['nationality'] == 'Indian' ){ ?>
												<td height=25><label style="font-weight: bold; padding-right: 15px;">State:</label><span><?php echo $booking_customers_adult['state']; ?></span></td>
											<?php }else{ ?>
												<td height=25><label style="font-weight: bold; padding-right: 15px;">Country:</label><span><?php echo $booking_customers_adult['country']; ?></span></td>
											<?php } ?>
										</tr>
										<tr>
											<td height=25><label style="font-weight: bold; padding-right: 15px;">ID Proof:</label><span><?php echo $booking_customers_adult['id_proof']; ?></span></td>
											<td height=25><label style="font-weight: bold; padding-right: 15px;">ID Proof Number:</label><span><?php echo $booking_customers_adult['id_proof_number']; ?></span></td>
										</tr>
										<tr>
											<td colspan="2"><label style="font-weight: bold; padding-right: 15px;">ID Proof Photo:</label><img src="<?php echo $proof_file; ?>" style="width: 100px;display: block;"></td>
										</tr>

									<?php $i++; } } ?>

									<tr>
										<td colspan="2"><hr style="border: 1px solid #efefef!important; border-width: 1px 0 0 0!important;padding-top: 15px; margin-top: 15px;"></td>
									</tr>

									<?php

									$safari_booking_customers_table = $wpdb->prefix . 'safari_booking_customers';
		
									$booking_customers = $wpdb->get_results( "
										SELECT * FROM 
											$safari_booking_customers_table 
										WHERE 
											booking_id = '".$booking['id']."'
										AND 
											person_type = 'child' 
									",ARRAY_A );

									if( !empty( $booking_customers ) ){

										$i = 1;

										foreach ( $booking_customers as $key => $booking_customers_child ) { ?>

										<tr>
											<td colspan="2" style="padding-bottom: 20px;" ><strong>Child Details</strong></td>
										</tr>
										<tr>
											<td height="25"><span style="font-weight: bold; padding-right: 15px;"><?php echo $i; ?></span><span>Child</span></td>
											<td height="25"><label style="font-weight: bold padding-right: 15px;"><?php echo $booking_customers_child['name']; ?></label><span></span></td>
										</tr>
										<tr>
											<td height=25><label style="font-weight: bold; padding-right: 15px;">Age:</label><span><?php echo $booking_customers_child['age']; ?></span></td>
											<td height=25><label style="font-weight: bold; padding-right: 15px;">Gender:</label><span><?php echo $booking_customers_child['gender']; ?></span></td>
										</tr>
										<tr>
											<td height=25><label style="font-weight: bold; padding-right: 15px;">Nationality:</label><span><?php echo $booking_customers_child['nationality']; ?></span></td>
											<?php if( $booking_customers_child['nationality'] == 'Indian' ){ ?>
												<td height=25><label style="font-weight: bold; padding-right: 15px;">State:</label><span><?php echo $booking_customers_child['state']; ?></span></td>
											<?php }else{ ?>
												<td height=25><label style="font-weight: bold; padding-right: 15px;">Country:</label><span><?php echo $booking_customers_child['country']; ?></span></td>
											<?php } ?>
										</tr>
										<tr>
											<td height=25><label style="font-weight: bold; padding-right: 15px;">ID Proof:</label><span><?php echo $booking_customers_child['id_proof']; ?></span></td>
											<td height=25><label style="font-weight: bold; padding-right: 15px;">ID Proof Number:</label><span><?php echo $booking_customers_child['id_proof_number']; ?></span></td>
										</tr>

									<?php $i++; } } ?>

									<tr>
										<td colspan="2"><hr style="border: 1px solid #efefef!important; border-width: 1px 0 0 0!important;padding-top: 15px; margin-top: 15px;"></td>
									</tr>

									<tr>
										<td height=25><label style="font-weight: bold; padding-right: 15px;">Total:</label></td>
										<td height=25><label style="font-weight: bold; padding-right: 15px;">₹<?php echo $booking['amount']; ?></label></td>
									</tr>

								</tbody>
							</table>
						</td>
					</tr>
				</tbody>
			</table>
			<?php
		}

		$message = ob_get_clean();

		wp_mail( $to, $subject, $message, $headers );

		remove_filter( 'wp_mail_content_type', array( $this, 'girlionsafaribooking_set_html_mail_content_type' ) );

	}

	public function girlionsafaribooking_set_html_mail_content_type() {
	    return 'text/html';
	}

}

new SB_Ajax();

?>