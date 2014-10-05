jQuery(document).ready(function($) {
		
	$('#wpdonethis-form').submit(function() {
		$('#wpdonethis-loading').show();
		$('#wpdonethis-submit').attr('disabled', true);

		event.preventDefault();

		var $form = $( this ),
			term = $form.find( "input[name='wpdonethis_done']" ).val();

      data = {
      	action: 'wpdonethis_get_results',
      	wpdonethis_nonce: wpdonethis_vars.wpdonethis_nonce,
      	wpdonethis_done: term,
      };

     	$.post(ajaxurl, data, function (response) {
			$('#wpdonethis-results').html(response);
			$('#wpdonethis-loading').hide();
			$('#wpdonethis-submit').attr('disabled', false);

		});


		
		return false;
	});
});