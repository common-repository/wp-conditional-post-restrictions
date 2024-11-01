<?php
if (!class_exists('WPCPR_Sheet_Editor')) {

	class WPCPR_Sheet_Editor {

		static private $instance = false;

		private function __construct() {
			
		}

		function init() {
			add_action('vg_sheet_editor/initialized', array($this, 'init_wpse_features'));
		}

		function init_wpse_features() {
			add_action('vg_sheet_editor/editor/register_columns', array($this, 'register_columns'));
			add_action('vg_sheet_editor/editor_page/after_content', array($this, 'render_tooltip_first_time'));
			add_filter('vg_sheet_editor/woocommerce/teasers/allowed_columns', array($this, 'allow_columns'));
			add_filter('vg_sheet_editor/woocommerce/teasers/allowed_variation_columns', array($this, 'allow_columns'));
			add_filter('vg_sheet_editor/custom_columns/teaser/allow_to_lock_column', array($this, 'allow_column'), 10, 2);
			add_filter('vg_sheet_editor/factory/is_column_allowed', array($this, 'allow_column'), 10, 2);
			add_filter('vg_sheet_editor/infinite_serialized_column/column_settings', array($this, 'filter_column_settings'), 5, 3);
		}

		function filter_column_settings($column_settings, $serialized_field, $post_type) {

			if (post_type_exists($post_type)) {
				if (preg_match('/vpr_conditions_\d+=conditions=\d+=type/', $column_settings['key'])) {
					$condition_options = array('' => '');
					foreach (vpr_helpers()->conditions_handler()->get('conditions') as $condition) {
						$condition_options[$condition->getCondition_key()] = $condition->getLabel();
					}
					asort($condition_options);
					$column_settings['formatted'] = array(
						'editor' => 'select',
						'selectOptions' => $condition_options
					);
				}
				if (preg_match('/vpr_conditions_\d+=conditions=\d+=operator/', $column_settings['key'])) {
					$condition_options = array('' => '');
					foreach (vpr_helpers()->conditions_handler()->get('conditions') as $condition) {
						$condition_options[$condition->getCondition_key()] = $condition->getLabel();
					}
					$column_settings['formatted'] = array(
						'editor' => 'select',
						'selectOptions' => array(
							"equal_to" => __("=", VEGACORP_CONDITIONS_TEXT_DOMAIN),
							"not_equal_to" => __("!= (Not equal)", VEGACORP_CONDITIONS_TEXT_DOMAIN),
							"equal_to_field" => __("Equal to field", VEGACORP_CONDITIONS_TEXT_DOMAIN),
							"not_equal_to_field" => __("Not equal to field", VEGACORP_CONDITIONS_TEXT_DOMAIN),
							"less_than" => __("<", VEGACORP_CONDITIONS_TEXT_DOMAIN),
							"less_or_equal_than" => __("<=", VEGACORP_CONDITIONS_TEXT_DOMAIN),
							"higher_than" => __(">", VEGACORP_CONDITIONS_TEXT_DOMAIN),
							"higher_or_equal_than" => __(">=", VEGACORP_CONDITIONS_TEXT_DOMAIN),
							"contains" => __("Contains", VEGACORP_CONDITIONS_TEXT_DOMAIN),
							"not_contains" => __("Not contains", VEGACORP_CONDITIONS_TEXT_DOMAIN),
							"appears_in_this_list" => __("Appears in this list", VEGACORP_CONDITIONS_TEXT_DOMAIN),
							"contains_keywords" => __("Contains any of these keywords", VEGACORP_CONDITIONS_TEXT_DOMAIN),
						)
					);
				}
				if (preg_match('/vpr_conditions/', $column_settings['key'])) {
					$column_settings['title'] = str_replace(array('Vpr Conditions', ': Data'), array('Restrictions', ': Value'), $column_settings['title']);
					$title_parts = array_map('trim', explode(':', $column_settings['title']));
					foreach ($title_parts as $title_part_index => $title_part) {
						if (is_numeric($title_part)) {
							$title_parts[$title_part_index] = (int) $title_part + 1;
						}
					}
					$column_settings['title'] = implode(' : ', $title_parts);
					$column_settings['title'] = str_replace(array('Restrictions : ', 'Conditions : '), array('Restrictions : Group ', 'Condition '), $column_settings['title']);
				}
			}
			return $column_settings;
		}

		function allow_column($allowed_to_lock, $column_key) {
			if (in_array($column_key, array('_vpr_what_happens_when_the_conditions_are_met'), true)) {
				$allowed_to_lock = false;
			}

			return $allowed_to_lock;
		}

		function allow_columns($columns) {
			$columns[] = '_vpr_what_happens_when_the_conditions_are_met';
			return $columns;
		}

		function render_tooltip_first_time($post_type) {
			if (!post_type_exists($post_type)) {
				return;
			}
			$flag_key = 'wpcpr_hide_sheet_tip';
			if (get_option($flag_key)) {
				return;
			}
			update_option($flag_key, 1);
			?>
			<script>
				jQuery(document).ready(function () {
					jQuery('body').on('vgSheetEditor:afterRowsInsert', function () {
						hot.selectColumns('vcwccr_selected_countries', 'vcwccr_availability_operator');
						vgseCustomTooltip(jQuery('#vgse-wrapper .handsontable .ht__active_highlight').first(), <?php echo json_encode(__('You can edit the content restrictions here. You can edit hundreds of items at once, auto complete cells, and copy paste.', 'wp-conditional-post-restrictions')); ?>, 'top', false);
					});
				});
			</script>
			<?php
		}

		/**
		 * Register spreadsheet columns
		 */
		function register_columns($editor) {
			foreach ($editor->args['enabled_post_types'] as $post_type) {
				if (!post_type_exists($post_type)) {
					continue;
				}

				$editor->args['columns']->register_item('_vpr_what_happens_when_the_conditions_are_met', $post_type, array(
					'title' => __('Restrictions', 'wp-conditional-post-restrictions'),
					'formatted' => array(
						'editor' => 'select',
						'selectOptions' => array(
							'' => __('Disabled', 'wp-conditional-post-restrictions'),
							'allow_access' => __('Allow access', 'wp-conditional-post-restrictions'),
							'restrict_access' => __('Restrict access', 'wp-conditional-post-restrictions')
						)),
					'data_type' => 'meta_data',
					'supports_formulas' => true,
					'supports_sql_formulas' => true,
				));
				$editor->args['columns']->remove_item('_vpr_post_conditions_enabled', $post_type);
			}
		}

		/**
		 * Creates or returns an instance of this class.
		 */
		static function get_instance() {
			if (null == WPCPR_Sheet_Editor::$instance) {
				WPCPR_Sheet_Editor::$instance = new WPCPR_Sheet_Editor();
				WPCPR_Sheet_Editor::$instance->init();
			}
			return WPCPR_Sheet_Editor::$instance;
		}

		function __set($name, $value) {
			$this->$name = $value;
		}

		function __get($name) {
			return $this->$name;
		}

	}

}

if (!function_exists('WPCPR_Sheet_Editor_Obj')) {

	function WPCPR_Sheet_Editor_Obj() {
		return WPCPR_Sheet_Editor::get_instance();
	}

}
add_action('init', 'WPCPR_Sheet_Editor_Obj', 5);
