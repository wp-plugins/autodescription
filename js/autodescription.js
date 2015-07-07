jQuery(function ($) {
	$('#autodescription_title, #autodescription_description').on('keyup', function(event){
		$('#' + event.target.id + '_chars').html($(event.target).val().length.toString());
	});
});