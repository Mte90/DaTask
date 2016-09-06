(function($){
	$('.misc-pub-section.curtime.misc-pub-section-last').removeClass('misc-pub-section-last');

	var clearExpirationValues,
	stamp = $('#expire-datestamp').html();

	$('.edit-expire-datestamp').click( function(e) {
		e.preventDefault();
		if ($('#expire-datestamp-value').is(':hidden')) {
			$('#expire-datestamp-value').slideDown('normal');
			$('.edit-expire-datestamp').hide();
		}
	} );

	$('.cancel-expire-datestamp').click( function(e) {
		e.preventDefault();

		$('#expire-datestamp-value').slideUp('normal');
		$('.expire-datestamp-wrap').find('input').each( function(i, e) {
			if( typeof $(this).data('original-value') !== 'undefined' ){
				$(this).val( $(this).data('original-value') );
			}
		} );
		$('#expire-datestamp').html(stamp);
		$('.edit-expire-datestamp').show();
		$('.expire-datestamp-wrap').data('set', 'false');
	} );

	$('.remove-expire-datestamp').click( function(e) {
		e.preventDefault();

		clearExpirationValues();
		$('#expire-datestamp-value').slideUp('normal');
		$('#expire-datestamp').html('No Expiration Set');
		$('.edit-expire-datestamp').show();
		$('.expire-datestamp-wrap').data('set', 'true');
	} );

	$('#publish, #save-post').one('click', function(e){
		e.preventDefault();

		if( !$('.expire-datestamp-wrap').data('set') )
			clearExpirationValues();

		$(this).click();
	});

	$('.save-expire-datestamp').click( function(e) {
		e.preventDefault();

		var year = $('#expire_year').val(),
		month = $('#expire_month').val(),
		day = $('#expire_day').val(),
		hour = $('#expire_hour').val(),
		minute = $('#expire_minute').val(),
		attemptedDate = new Date( year, month - 1, day, hour, minute ),
		originalDate = new Date(
			$('#expire_year').data('original_value'),
			$('#expire_month').data('original_value'),
			$('#expire_day').data('original_value'),
			$('#expire_hour').data('original_value'),
			$('#expire_minute').data('original_value')
		);

		if ( attemptedDate.getFullYear() != year || (1 + attemptedDate.getMonth()) != month || attemptedDate.getDate() != day || attemptedDate.getMinutes() != minute ) {
			$('.expire-datestamp-wrap', '#expire-datestamp-value').addClass('form-invalid');
			return false;
		} else {
			$('.expire-datestamp-wrap', '#expire-datestamp-value').removeClass('form-invalid');
		}

		$('#expire-datestamp-value').slideUp('normal');
		$('.edit-expiredatestamp').show();
		if ( originalDate.toUTCString() == attemptedDate.toUTCString() ) { //hack
			$('#expire-datestamp').html(stamp);
		} else {
			$('#expire-datestamp').html(
				'Expires on: <b>' +
				$('option[value=' + $('#expire_month').val() + ']', '#expire_month').text() + ' ' +
				day + ', ' +
				year + ' @ ' +
				hour + ':' +
				minute + '</b> '
			);
		}
		$('#expire-datestamp-value').slideUp('normal');
		$('#expire-datestamp-value').siblings('a.edit-expire-datestamp').show();
		$('.expire-datestamp-wrap').data('set', 'true');
	});

	clearExpirationValues = function(){
		$('#expire_day').val('');
		$('#expire_year').val('');
		$('#expire_hour').val('');
		$('#expire_minute').val('');
		$('#expire_second').val('');
		$('#expire_month').val('');
	}
})(jQuery);