jQuery(function ($) {

	function metabox_handler() {

		var handler = {

			init: function () {

				$('.vpr-select-2').select2();

			}

		}

		handler.init();

		return handler;

	}

	metabox_handler();

	var $enabled = jQuery('#_vpr_post_conditions_enabled');
	console.log('$enabled: ', $enabled);
	$enabled.change(function (e) {
		if (jQuery(this).is(':checked')) {
			jQuery('.cpr-settings-row, #vpr_conditions').show();
		} else {
			jQuery('.cpr-settings-row, #vpr_conditions').hide();
		}
	});
	$enabled.trigger('change');
});
