(function($){

	$(document).ready(function(){

		$( ".Jungle-tab" ).tabs();
		
		$("#gir-datepicker, #dev-datepicker").datepicker({
			dateFormat: 'dd-mm-yy',
			minDate: 0 
		});

		var payment_form 		  = $("#payment_form").validate();
		var gir_jungle_trail_form = $("#gir-jungle-trail-form").validate();
		var devalia_park_form 	  = $("#devalia-park-form").validate();

		$(document).on('click', '#gir-jungle-trail-submit', function (e) {

			e.preventDefault();

			$this = $(this);

			if($( "#gir-jungle-trail-form" ).valid()){
				$( "#gir-jungle-trail-form" ).submit();
			}else{
			 	gir_jungle_trail_form.focusInvalid();
			}

		});

		$(document).on('click', '#devalia-park-submit', function (e) {

			e.preventDefault();

			$this = $(this);

			if($( "#devalia-park-form" ).valid()){
				$( "#devalia-park-form" ).submit();
			}else{
			 	devalia_park_form.focusInvalid();
			}

		});

		$( document ).on('change','#gir-datepicker',function(){
			$('#girtime').prop('selectedIndex',0);
		});

		$( document ).on('change','#dev-datepicker',function(){
			$('#devtime').prop('selectedIndex',0);
		});

		$( document ).on('change','#girtime',function(){

			$this = $(this);

			if( $this.val() == '' ){
				return false;
			}

			$('#gir-jungle-trail-tab').find('.error').text('').hide();

			$('#gir-jungle-trail-submit').prop('disabled', true);

			$this.closest('#gir-jungle-trail-form').waitMe({
				effect : 'bounce',
				text : '',
				bg : 'rgba(255,255,255,0.7)',
				color : '#000000',
				maxSize : '',
				waitTime : -1,
				textPos : 'vertical',
				fontSize : '',
				source : '',
				onClose : function() {}
			});

			var form = $('#gir-jungle-trail-form')[0];
	    	var formData = new FormData(form);
	    		formData.append('action','check_availability_and_verify');

			$.ajax({
	          	type : "POST",
	          	dataType : "json",
	          	url : safari_booking.ajaxurl,
	          	data : formData,
	          	processData: false,
	          	contentType: false,
	          	success: function(response) {
	            	if( response.success ){
	            		$('#gir-jungle-trail-submit').prop('disabled', false);
	            	}else{
	            		$('#gir-jungle-trail-tab').find('.error').text(response.data.message).show();
	            		$('#gir-jungle-trail-submit').prop('disabled', true);
	            	}
	            	$this.closest('#gir-jungle-trail-form').waitMe('hide');
	          	},error: function (jqXHR, exception) {
	          		var msg = '';
			        if (jqXHR.status === 0) {
			            msg = 'Not connect.\n Verify Network.';
			        } else if (jqXHR.status == 404) {
			            msg = 'Requested page not found. [404]';
			        } else if (jqXHR.status == 500) {
			            msg = 'Internal Server Error [500].';
			        } else if (exception === 'parsererror') {
			            msg = 'Requested JSON parse failed.';
			        } else if (exception === 'timeout') {
			            msg = 'Time out error.';
			        } else if (exception === 'abort') {
			            msg = 'Ajax request aborted.';
			        } else {
			            msg = 'Uncaught Error.\n' + jqXHR.responseText;
			        }
			        console.log(msg);
			        $this.closest('#gir-jungle-trail-form').waitMe('hide');
	          	}
	      	});

		});

		$( document ).on('change','#devtime',function(){

			$this = $(this);

			if( $this.val() == '' ){
				return false;
			}

			$('#devalia-park-tab').find('.error').text('').hide();

			$('#devalia-park-submit').prop('disabled', true);

			$this.closest('#devalia-park-form').waitMe({
				effect : 'bounce',
				text : '',
				bg : 'rgba(255,255,255,0.7)',
				color : '#000000',
				maxSize : '',
				waitTime : -1,
				textPos : 'vertical',
				fontSize : '',
				source : '',
				onClose : function() {}
			});

			var form = $('#devalia-park-form')[0];
	    	var formData = new FormData(form);
	    		formData.append('action','check_availability_and_verify');

			$.ajax({
	          	type : "POST",
	          	dataType : "json",
	          	url : safari_booking.ajaxurl,
	          	data : formData,
	          	processData: false,
	          	contentType: false,
	          	success: function(response) {
	            	if( response.success ){
	            		$('#devalia-park-submit').prop('disabled', false);
	            	}else{
	            		$('#devalia-park-tab').find('.error').text(response.data.message).show();
	            		$('#devalia-park-submit').prop('disabled', true);
	            	}
	            	$this.closest('#devalia-park-form').waitMe('hide');
	          	},error: function (jqXHR, exception) {
	          		var msg = '';
			        if (jqXHR.status === 0) {
			            msg = 'Not connect.\n Verify Network.';
			        } else if (jqXHR.status == 404) {
			            msg = 'Requested page not found. [404]';
			        } else if (jqXHR.status == 500) {
			            msg = 'Internal Server Error [500].';
			        } else if (exception === 'parsererror') {
			            msg = 'Requested JSON parse failed.';
			        } else if (exception === 'timeout') {
			            msg = 'Time out error.';
			        } else if (exception === 'abort') {
			            msg = 'Ajax request aborted.';
			        } else {
			            msg = 'Uncaught Error.\n' + jqXHR.responseText;
			        }
			        console.log(msg);
			        $this.closest('#devalia-park-form').waitMe('hide');
	          	}
	      	});

		});

		$(document).on('click', '#pay-now', function (e) {

			e.preventDefault();

			$this = $(this);

			if($( "#payment_form" ).valid()){

				$this.closest('#payment_form').waitMe({
					effect : 'bounce',
					text : '',
					bg : 'rgba(255,255,255,0.7)',
					color : '#000000',
					maxSize : '',
					waitTime : -1,
					textPos : 'vertical',
					fontSize : '',
					source : '',
					onClose : function() {}
				});

			  	var form = $('#payment_form')[0];
		    	var formData = new FormData(form);
		    		formData.append('action','check_availability_and_verify');

			  	$.ajax({
		          	type : "POST",
		          	dataType : "json",
		          	url : safari_booking.ajaxurl,
		          	data : formData,
		          	processData: false,
		          	contentType: false,
		          	success: function(response) {
		          		
		            	if( response.success ){

		              		var razorpay_options = {
						        key: 'rzp_test_N5A13aKGNaNYg8',
						        amount: response.data.total_amount * 100,
						        name: 'Gir Lion Safari Booking',
						        description: 'Gir Lion Safari Booking',
						        image: 'http://girlionsafaribooking.com/wp-content/uploads/2021/01/logo.png',
						        netbanking: true,
						        currency: 'INR',
						        prefill: {
						            name: $('input[name="customer_name"]').val(),
						            email: $('input[name="email"]').val(),
						            contact: $('input[name="mobile"]').val()
						        },
						        handler: function (transaction) {
						        	console.log('handler called');
						        	var form = $('#payment_form')[0];
							    	var formData = new FormData(form);
							    		formData.append('action','add_safari_booking');
							    		formData.append('razorpay_payment_id',transaction.razorpay_payment_id);
							    		formData.append('total_amount',response.data.total_amount);

							            $.ajax({
							                url:safari_booking.ajaxurl,
							                type: 'post',
							                data : formData,
							                dataType: 'json',
							                processData: false,
		          							contentType: false,
							                success: function (response) {
							                    if(response){
							                        window.location = response.data.redirect;
							                    }else{
							                    	alert( response.data.message );
							                    }
							                    $this.closest('#payment_form').waitMe('hide');
							                },error: function (jqXHR, exception) {
								          		var msg = '';
										        if (jqXHR.status === 0) {
										            msg = 'Not connect.\n Verify Network.';
										        } else if (jqXHR.status == 404) {
										            msg = 'Requested page not found. [404]';
										        } else if (jqXHR.status == 500) {
										            msg = 'Internal Server Error [500].';
										        } else if (exception === 'parsererror') {
										            msg = 'Requested JSON parse failed.';
										        } else if (exception === 'timeout') {
										            msg = 'Time out error.';
										        } else if (exception === 'abort') {
										            msg = 'Ajax request aborted.';
										        } else {
										            msg = 'Uncaught Error.\n' + jqXHR.responseText;
										        }
										        console.log(msg);
										        $this.closest('#payment_form').waitMe('hide');
								          	}
							            });
						        },
						        "modal": {
						            "ondismiss": function () {
						                console.log('ondismiss called');
						            }
						        },
						        "theme": {
									"color": "#f58220"
								}
						    };
						    // obj        
						    var objrzpv1 = new Razorpay(razorpay_options);
						    objrzpv1.open();
		            	}

		            	$this.closest('#payment_form').waitMe('hide');

		          	},error: function (jqXHR, exception) {
		          		var msg = '';
				        if (jqXHR.status === 0) {
				            msg = 'Not connect.\n Verify Network.';
				        } else if (jqXHR.status == 404) {
				            msg = 'Requested page not found. [404]';
				        } else if (jqXHR.status == 500) {
				            msg = 'Internal Server Error [500].';
				        } else if (exception === 'parsererror') {
				            msg = 'Requested JSON parse failed.';
				        } else if (exception === 'timeout') {
				            msg = 'Time out error.';
				        } else if (exception === 'abort') {
				            msg = 'Ajax request aborted.';
				        } else {
				            msg = 'Uncaught Error.\n' + jqXHR.responseText;
				        }
				        console.log(msg);
				        $this.closest('#payment_form').waitMe('hide');
		          	}
		      	});

		    }else{
			 	payment_form.focusInvalid();
			}
		            
		});

	});

})(jQuery);