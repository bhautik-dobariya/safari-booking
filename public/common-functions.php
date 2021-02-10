<?php

function calculate_price( $adult = 0, $child = 0, $nationality = 'indian' ){

	$safari_booking_basic_settings = get_option( 'safari_booking_basic_settings' );
	$price = 0;

	if( $nationality == 'indian' ){
		$adult_price = $safari_booking_basic_settings['adult_price_indian'];
		$price = (int) $adult_price + ( (int) $safari_booking_basic_settings['child_price'] * (int) $child );
	}else{
		$price = $safari_booking_basic_settings['adult_price_foreigner'];
	}

	return $price;

}

?>