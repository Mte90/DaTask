(function ($) {
  "use strict";

  jQuery(document).ready(function () {
	jQuery('.dt-buttons .complete').on('click', function () {
	  if (!jQuery(this).hasClass('disabled')) {
		jQuery(this).addClass('disabled').find('.fa-refresh').css('display', 'inherit').addClass('animate');
		jQuery.ajax({
		  type: 'GET',
		  data: {
			action: 'dt_complete_task',
			_wpnonce: jQuery('.dt-buttons #dt-task-nonce').val(),
			ID: jQuery('#complete-task').attr('data-complete')
		  },
		  url: dt_js_vars.ajaxurl,
		  success: function (value) {
			jQuery('html, body').animate({scrollTop: jQuery('#respond').offset().top}, 'slow', function () {
			  jQuery('#respond').animate({'margin-left': '-=30px', 'margin-right': '+=30px'}, 200, function () {
				jQuery('#respond').animate({'margin-left': '+=60px', 'margin-right': '-=30px'}, 200, function () {
				  jQuery('#respond').animate({'margin-left': '-=30px', 'margin-right': '+=30px'}, 200);
				});
			  });
			});
			jQuery('.dt-buttons .complete').find('.fa-exclamation-circle').hide();
			jQuery('.dt-buttons .complete').find('.fa-refresh').removeClass('animate').hide();
			jQuery('.dt-buttons .complete').find('.fa-check').show();
			jQuery('.dt-buttons .save-later').removeClass('disabled');
			jQuery('.dt-buttons .remove').removeClass('disabled');
		  },
		  error: function (value) {
			jQuery('.dt-buttons .complete').removeClass('disabled').find('.fa-refresh').removeClass('animate');
		  }
		});
	  }
	});

	jQuery('.dt-buttons .save-later:not(.disabled)').on('click', function () {
	  if (!jQuery(this).hasClass('disabled')) {
		jQuery(this).addClass('disabled').find('.fa-refresh').css('display', 'inherit').addClass('animate');
		jQuery.ajax({
		  type: 'GET',
		  data: {
			action: 'dt_task_later',
			_wpnonce: jQuery('.dt-buttons #dt-task-nonce').val(),
			ID: jQuery('#save-for-later').attr('data-save-later')
		  },
		  url: dt_js_vars.ajaxurl,
		  success: function (value) {
			jQuery('.dt-buttons .save-later').find('.fa-refresh').removeClass('animate').hide();
			jQuery('.dt-buttons .complete').removeClass('disabled').find('.fa-check').hide();
		  },
		  error: function (value) {
			jQuery(".dt-buttons .complete").removeClass('disabled').find('.fa-refresh').removeClass('animate');
		  }
		});
	  }
	});

	jQuery('.dt-buttons .remove').on('click', function () {
	  jQuery(this).addClass('disabled').find('.fa-refresh').css('display', 'inherit').addClass('animate');
	  jQuery.ajax({
		type: 'GET',
		data: {
		  action: 'dt_remove_task',
		  _wpnonce: jQuery('.dt-buttons #dt-task-nonce').val(),
		  ID: jQuery('#remove-task').attr('data-remove')
		},
		url: dt_js_vars.ajaxurl,
		success: function (value) {
		  jQuery('.dt-buttons .remove').find('.fa-refresh').removeClass('animate').removeClass('animate').removeClass('fa-refresh').addClass('fa-check');
		  jQuery('.dt-buttons .complete').removeClass('disabled').find('.fa-check').hide();
		},
		error: function (value) {
		  jQuery('.dt-buttons .remove').removeClass('disabled').find('.fa-refresh').removeClass('animate');
		}
	  });
	});

	jQuery('#user-contact-form button').on('click', function () {
	  jQuery(this).addClass('disabled');
	  jQuery.ajax({
		type: 'POST',
		data: {
		  action: 'dt_contact_user',
		  _wpnonce: jQuery('#user-contact-form #dt_user_nonce').val(),
		  content: jQuery('textarea[name="datask-email-subject"]').val(),
		  user_login: jQuery('#user-contact-form button').data('user')
		},
		url: dt_js_vars.ajaxurl,
		success: function (value) {
		  jQuery('textarea[name="datask-email-subject"]').addClass('disabled');
		},
		error: function (value) {
		  jQuery('#user-contact-form button').removeClass('disabled');
		}
	  });
	});
  });

}(jQuery));