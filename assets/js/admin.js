jQuery(document).ready(function($) {
	var $body = $('body');

	$body.on('click', '.showTitle', function() {

		$('#post-body-content').toggle();
		console.log('Worked');
	});

	$( '.date-picker' ).datepicker({
		dateFormat: 'yy-mm-dd',
		numberOfMonths: 1

	});

});