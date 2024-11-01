jQuery(function ($) {

	function settings_handler() {

		var handler = {

			init: function () {

				$('select').change(this.display_option_associated_field_on_change);

			},
			display_option_associated_field_on_change: function () {

				var $select = $(this);
				var selected_option = $select.val();
				var options_fields_data = $select.data('options_fields');

				if (!options_fields_data) {
					return;
				}

				var selected_option_associated_setting = options_fields_data[selected_option] ? options_fields_data[selected_option] : '';
				var options = $select.find('option').toArray().map(option => $(option).val());

				for (var i = 0; i < options.length; i++) {

					var option = options[i];
					var option_associated_setting = options_fields_data[option] ? options_fields_data[option] : '';

					if (option_associated_setting === selected_option_associated_setting/*option === selected_option*/) {

						$('#' + selected_option_associated_setting).closest('tr').removeClass('vpr-hidden');

					} else {

						$('#' + option_associated_setting).closest('tr').addClass('vpr-hidden');

					}

				}

			}

		}

		handler.init();

		return handler;

	}

	settings_handler();

});

