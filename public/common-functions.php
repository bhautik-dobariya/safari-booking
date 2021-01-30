<?php

function calculate_price( $adult = 0, $child = 0 ){

	$safari_booking_basic_settings = get_option( 'safari_booking_basic_settings' );
	$price = 0;
	$price = (int) $safari_booking_basic_settings['adult_price'] + ( (int) $safari_booking_basic_settings['child_price'] * (int) $child );
	return $price;

}

?>