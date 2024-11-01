jQuery(document).ready(function () {
	var $ = jQuery;

	var $default_or_group = $('.vegacorp-or-groups .or_group_table').clone();

	function taxonomy_fields_handler() {

		var handler = {

			init: function () {

				$(document).ajaxComplete(this.reset_fields_on_ajax_complete);

			},
			reset_fields_on_ajax_complete: function (event, xhr, options) {

				if (!options.data || options.data.indexOf('action=add-tag') === -1) {
					return;
				}

				$('.vpr-field').each((index, field) => {

					var $field = $(field);

					$field.find('input').each((index, input) => {

						var $input = $(input);

						if ($input.attr('type') !== 'checkbox') {
							$input.val('');
						}

						if ($input.attr('type') === 'checkbox') {
							$input.attr('checked', false);
						}

					});

					$field.find('select option').each((index, option) => {

						$(option).prop('selected', false);

					});

				});

				$('.vegacorp-or-groups').empty().append($default_or_group);

			}

		};

		handler.init();

		return handler;

	}

	taxonomy_fields_handler();

	var $popupTrigger = jQuery('.cpr-open-conditions-popup');
	if ($popupTrigger.length) {
		var $conditions = jQuery('.cpr-conditions-wrapper');

		$popupTrigger.click(function (e) {
			e.preventDefault();

			$conditions.toggleClass('cpr-opened-popup');
		});
		$conditions.find('.cpr-close-popup').click(function (e) {
			e.preventDefault();

			$conditions.toggleClass('cpr-opened-popup');
		});
	}

	var $enabled = jQuery('#_vpr_post_conditions_enabled');
	console.log('$enabled: ', $enabled);
	$enabled.change(function (e) {
		if (jQuery(this).is(':checked')) {
			jQuery('.cpr-settings-row').show();
		} else {
			jQuery('.cpr-settings-row').hide();
		}
	});
	$enabled.trigger('change');
});