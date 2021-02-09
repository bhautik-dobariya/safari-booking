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

		$upload_dir = wp_upload_dir();

		$booking_id = $_POST['booking_id'];

		ob_start();
		
		?>
		<table cellspacing="0" cellpadding="0" width="100%" style="margin: 0px auto; font-family: sans-serif; font-size: 15px; max-width: 580px;">
			<tbody>
				<tr>
					<td>
						<table width="100%" style="background: #ffffff; padding: 15px; border: 1px solid #efefef; font-size: 13px;" >
							<tbody>

								<?php

								$safari_booking_customers_table = $wpdb->prefix . 'safari_booking_customers';
	
								$booking_customers = $wpdb->get_results( "
									SELECT * FROM 
										$safari_booking_customers_table 
									WHERE 
										booking_id = '".$booking_id."'
									AND 
										person_type = 'adult' 
								",ARRAY_A );

								if( !empty( $booking_customers ) ){

									$i = 1;

									foreach ( $booking_customers as $key => $booking_customers_adult ) { 
										$proof_file = $upload_dir['baseurl'].'/safari_booking/'.$booking_id.'/'.$booking_customers_adult['id'].'/'.$booking_customers_adult['proof_file'];
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
										booking_id = '".$booking_id."'
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

							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
		
		<?php
		
		$html = ob_get_clean();

		wp_send_json_success(array(
			'html' => $html
		));		

	}

}

new SB_Admin_Ajax();

?>