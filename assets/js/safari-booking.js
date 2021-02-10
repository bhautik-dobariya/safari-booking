(function($){

	$(document).ready(function(){

		$( ".Jungle-tab" ).tabs().show();
		
		$("#gir-datepicker, #dev-datepicker").datepicker({
			dateFormat: 'dd-mm-yy',
			minDate: 0 
		});

		var payment_form 		  = $("#payment_form").validate();
		var gir_jungle_trail_form = $("#gir-jungle-trail-form").validate();
		var devalia_park_form 	  = $("#devalia-park-form").validate();

		$(document).on('click', '#gir-jungle-trail-submit', function (e) {

			$('#gir-jungle-trail-tab').find('.avialbility-error').text('').hide();

			e.preventDefault();

			var $this = $(this);

			var adult = $this.closest('form').find('#giradult').val();
			var child = $this.closest('form').find('#girchild').val();
			
			var total = parseInt( adult ) + parseInt( child );
			
			if( total > safari_booking.person_capacity ){
				$('#gir-jungle-trail-tab').find('.avialbility-error').text('Sorry, you can not select more than '+safari_booking.person_capacity+' passengers.').show();
				return false;
			}

			if($( "#gir-jungle-trail-form" ).valid()){
				$( "#gir-jungle-trail-form" ).submit();
			}else{
			 	gir_jungle_trail_form.focusInvalid();
			}

		});

		$(document).on('click', '#devalia-park-submit', function (e) {

			$('#devalia-park-tab').find('.avialbility-error').text('').hide();

			e.preventDefault();

			var $this = $(this);

			var adult = $this.closest('form').find('#devadult').val();
			var child = $this.closest('form').find('#devchild').val();
			
			var total = parseInt( adult ) + parseInt( child );
			
			if( total > safari_booking.person_capacity ){
				$('#devalia-park-tab').find('.avialbility-error').text('Sorry, you can not select more than '+safari_booking.person_capacity+' passengers.').show();
				return false;
			}

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

			var $this = $(this);

			if( $this.val() == '' ){
				return false;
			}

			$('#gir-jungle-trail-tab').find('.avialbility-error').text('').hide();

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
	            		$('#gir-jungle-trail-tab').find('.avialbility-error').text(response.data.message).show();
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

			var $this = $(this);

			if( $this.val() == '' ){
				return false;
			}

			$('#devalia-park-tab').find('.avialbility-error').text('').hide();

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
	            		$('#devalia-park-tab').find('.avialbility-error').text(response.data.message).show();
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

		$( document ).on('change','.nationality_select',function(){

			if( $(this).val() == 'Foreigner' ){
				$(this).closest('.form-group').find('.state_select').hide();
				$(this).closest('.form-group').find('.country_select').show();
				$(this).closest('.form-group').next().find('.proof_select').val("Passport").attr("selected","selected");
				$(this).closest('.form-group').next().find('.proof_select').find('option:not(:selected)').attr("disabled","disabled");
			}else{
				$(this).closest('.form-group').find('.state_select').show();
				$(this).closest('.form-group').find('.country_select').hide();
				$(this).closest('.form-group').next().find('.proof_select').val("").attr("selected","selected");
				$(this).closest('.form-group').next().find('.proof_select').find('option').removeAttr("disabled");
			}

			var adult = $('input[name="adult"]').val();
			var child = $('input[name="child"]').val();
			var price = 0;
			
			var nationality = [];

			$('.nationality_select').each(function(){
				if( $(this).val() != '' ){
					nationality.push( $(this).val() );
				}
			});

			if( nationality.includes('Foreigner') ){
				price = calculate_price( adult, child, 'foreigner' );
			}else{
				price = calculate_price( adult, child, 'indian' );
			}

			$('#lblTotal').text('₹'+price);
			$('#total_amount').val(price);

		});

		function calculate_price( adult = 0, child = 0, nationality = 'indian' ){

			var safari_booking_basic_settings = safari_booking.safari_booking_basic_settings;
			var price = 0;

			if( nationality == 'indian' ){
				var adult_price = safari_booking_basic_settings.adult_price_indian;
				price = parseInt( adult_price ) + ( parseInt( safari_booking_basic_settings.child_price ) * parseInt( child ) );
			}else{
				price = safari_booking_basic_settings.adult_price_foreigner;
			}

			return price;

		}

		$(document).on('click', '#pay-now', function (e) {

			e.preventDefault();

			var $this = $(this);

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
						        key: safari_booking.safari_booking_basic_settings.razor_pay_key_id,
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
						        	
						        	var form = $('#payment_form')[0];
							    	var formData = new FormData(form);
							    		formData.append('action','add_safari_booking');
							    		formData.append('razorpay_payment_id',transaction.razorpay_payment_id);
							    		formData.append('total_amount',response.data.total_amount);

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


							            $.ajax({
							                url:safari_booking.ajaxurl,
							                type: 'post',
							                data : formData,
							                dataType: 'json',
							                processData: false,
		          							contentType: false,
							                success: function (response) {
							                    if(response.success){
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
		            	}else{
		            		alert(response.data.message);
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