<?php
if (!class_exists('VPR_Restrictions_Post_Type')) {

	class VPR_Restrictions_Post_Type {

		/**
		 * @var array $post_types_keys The post types keys used in this plugins
		 */
		private $post_types_keys;

		/**
		 * @var array $meta_keys The meta keys used in this plugin
		 */
		private $meta_keys;

		public function __construct() {

			$this->post_types_keys = (object) vpr_helpers()->get_post_types_keys();

			$this->meta_keys = (object) vpr_helpers()->get_meta_keys();

			$this->init();
		}

		/**
		 * Registers the post types for this plugin 
		 */
		public function register() {


			$post_types_args = vpr_helpers()->get_post_types_args();

			register_post_type(
					$this->post_types_keys->posts_restrictions, $post_types_args[$this->post_types_keys->posts_restrictions]
			);
		}

		/**
		 * Adds the conditions rules metabox and the conditions metabox to the supported post types
		 */
		public function add_meta_boxes() {

			$post_types_keys = array_keys(vpr_helpers()->get_supported_post_types());

			$post_types_keys[] = $this->post_types_keys->posts_restrictions;

			add_meta_box(
					$this->post_types_keys->posts_restrictions . '_rules', __('Restrictions rules', 'wp-conditional-post-restrictions'), array($this, 'render_meta_box_rules_fields'), $post_types_keys
			);

			add_meta_box(
					$this->post_types_keys->posts_restrictions, __('Restrictions', 'wp-conditional-post-restrictions'), array($this, 'render_meta_box_conditions_fields'), $post_types_keys
			);
		}

		/**
		 * Renders the conditions metabox content
		 */
		public function render_meta_box_conditions_fields() {

			global $post;

			//Getting the conditions saved for this post
			$or_groups_raw = get_post_meta($post->ID, $this->post_types_keys->posts_restrictions, true);

			//Sanitizing the conditions
			$or_groups = !empty($or_groups_raw) ? vpr_helpers()->conditions_handler()->sanitize_groups($or_groups_raw) : array();

			//Checking if the post has inherited data from a term
			$inherited_from_term = intval(get_post_meta($post->ID, $this->meta_keys->inherited_from_term, true));

			//Getting the settings link to generate the button that open the settings page, This is only for the "vpr_conditions" posts edit page
			$settings_link = $post->post_type === $this->post_types_keys->posts_restrictions ? admin_url('options-general.php?page=vpr_settings') : '';

			//Getting the conditions html
			ob_start();
			vpr_helpers()->conditions_handler()->render_groups($or_groups, array('settings_link' => $settings_link));
			$or_groups_rendered = ob_get_clean();

			//If the post has inherited its configuration from a term, then the conditions can't be edited
			if (!empty($inherited_from_term)) {
				$or_groups_rendered = str_replace('<input', '<input disabled ', $or_groups_rendered);
				$or_groups_rendered = str_replace('<select', '<select disabled ', $or_groups_rendered);
				$or_groups_rendered = str_replace('<button', '<button disabled ', $or_groups_rendered);
			}

			//If the post has inherited its configuration from a term we show a notice to the user
			$this->render_configuration_inherited_from_term_notice();

			//Displaying the conditions
			echo $or_groups_rendered;
		}

		/**
		 * Renders the fields to configure the conditions rules 
		 */
		public function render_meta_box_rules_fields() {

			//The post that is being edited or created
			global $post;

			//Checking if the configuration is inherited. The "vpr_conditions" posts doesnt inherit configurations 
			$inherited_from_term = $post->post_type !== $this->post_types_keys->posts_restrictions ? intval(get_post_meta($post->ID, $this->meta_keys->inherited_from_term, true)) : 0;

			//If the configuration is inherited from a term, we show a message to the user
			$this->render_configuration_inherited_from_term_notice();

			//Rendering the field to enable conditions, this field is not present in the "vpr_conditions" post type
			if ($post->post_type !== $this->post_types_keys->posts_restrictions) {

				$conditions_enabled = boolval(get_post_meta($post->ID, $this->meta_keys->post_conditions_enabled, true));

				vpr_helpers()->input(array(
					'label' => __('Enable content restrictions?'),
					'id' => $this->meta_keys->post_conditions_enabled,
					'name' => $this->meta_keys->post_conditions_enabled,
					'type' => 'checkbox',
					'wrapper_attributes' => array(
						'class' => 'vpr-field'
					),
					'value' => 1,
					checked(true, $conditions_enabled, false),
					!empty($inherited_from_term) ? 'disabled' : ''
				));
			}

			//Displaying the field to select post types, This is only for "vpr_conditions" posts
			if ($post->post_type === $this->post_types_keys->posts_restrictions) {

				$selected_post_types_raw = get_post_meta($post->ID, $this->meta_keys->selected_post_types, true);
				$selected_post_types = !empty($selected_post_types_raw) && is_array($selected_post_types_raw) ? $selected_post_types_raw : array();

				vpr_helpers()->select(array(
					'label' => __('Select post types', 'wp-conditional-post-restrictions'),
					'id' => $this->meta_keys->selected_post_types . '[]',
					'name' => $this->meta_keys->selected_post_types . '[]',
					'options' => $this->get_post_types_options($selected_post_types),
					'class' => 'vpr-select-2 vpr-post-type-selector',
					'wrapper_attributes' => array(
						'class' => 'vpr-field'
					),
					'value' => array_map('sanitize_text_field', $selected_post_types),
					'multiple' => 'multiple'
				));
				$used_labels = wp_list_pluck(array_intersect_key(get_post_types(array('show_ui' => true), 'objects'), array_flip($GLOBALS['already_selected_post_types'])), 'label');
				?>

				<script>
					jQuery(document).ready(function () {
						var $select = jQuery('.vpr-post-type-selector');
						var usedOptions = <?php echo json_encode(array_values($used_labels)); ?>;
						var options = '';
						usedOptions.forEach(function (label) {
							options += '<option disabled value="' + label + '">' + label + '</option>';

						});
						if (options) {
							$select.prepend('<optgroup label="Already used in another conditions page">' + options + '</optgroup>');
						}
					});
				</script>
				<?php
				do_action('vpr/post_types/after_select_rendered');
			}

			//Rendering the field to configure what happens when the conditions are met
			$what_happens_when_the_conditions_are_met = get_post_meta($post->ID, $this->meta_keys->what_happens_when_the_conditions_are_met, true);

			$what_happens_when_the_conditions_are_met_field_args = array(
				'label' => __('What happens when the conditions are met?', 'wp-conditional-post-restrictions'),
				'id' => $this->meta_keys->what_happens_when_the_conditions_are_met,
				'name' => $this->meta_keys->what_happens_when_the_conditions_are_met,
				'options' => vpr_helpers()->get_field_options($this->meta_keys->what_happens_when_the_conditions_are_met),
				'wrapper_attributes' => array(
					'class' => 'vpr-field cpr-settings-row'
				),
				'value' => sanitize_text_field($what_happens_when_the_conditions_are_met)
			);

			//If the configutation is inherited, then the field can't be edited
			if (!empty($inherited_from_term)) {
				$what_happens_when_the_conditions_are_met_field_args[] = 'disabled';
			}

			//Displaying the field
			vpr_helpers()->select($what_happens_when_the_conditions_are_met_field_args);

			// Allow to configure custom redirect URL per post
			if (get_option('vpr_what_happens_when_post_is_restricted') === 'redirect_to_url' && $post->post_type !== 'vpr_conditions') {
				vpr_helpers()->input(array(
					'label' => __('Redirect to this URL when restricted'),
					'id' => $this->meta_keys->post_url_redirection,
					'name' => $this->meta_keys->post_url_redirection,
					'type' => 'text',
					'wrapper_attributes' => array(
						'class' => 'vpr-field cpr-settings-row'
					),
					'value' => get_post_meta($post->ID, $this->meta_keys->post_url_redirection, true),
				));
			}
		}

		/**
		 * Renders the notice which indicates the user that the post has its restrictions configuration inherited from a term
		 */
		public function render_configuration_inherited_from_term_notice() {

			//The post that is being edited or created
			global $post;

			//Getting the data that was inherited if it exists
			$term_data = get_post_meta($post->ID, $this->meta_keys->inherited_from_term, true);

			//If there is no inherited data, then the notice is not displayed
			if (empty($term_data)) {
				return;
			}

			//Getting the term
			$term = get_term_by('term_id', $term_data['term_id'], $term_data['taxonomy']);

			//If the term not exists, we do nothing
			if (!is_a($term, 'WP_Term')) {
				return;
			}

			//Getting the edit term page link
			$term_edition_link = admin_url('term.php?taxonomy=' . $term->taxonomy . '&tag_ID=' . $term->term_id);
			//Generating the term edit page link tag
			$term_edition_link_tag = '<a href="' . $term_edition_link . '">' . $term->name . '</a>';
			//The message that the user will see            
			$message = __('This setting is inherited from the category', 'wp-conditional-post-restrictions') . ': ' . $term_edition_link_tag;

			//Displaying the notice
			?><p class="vpr_inherited_from_term_notice"><?php echo wp_kses_post($message); ?></p><?php
		}

		/**
		 * Enqueues the necessary assets for the restrictions fields 
		 */
		public function enqueues($hook) {

			if (!is_admin()) {
				return;
			}

			if (!in_array($hook, array('post.php', 'post-new.php'))) {
				return;
			}

			global $post;

			$valid_post_types = array_keys(vpr_helpers()->get_supported_post_types());
			$valid_post_types[] = $this->post_types_keys->posts_restrictions;

			//If the post type is not supported, the assets dont will be enqueued
			if (!in_array($post->post_type, $valid_post_types)) {
				return;
			}

			//Enqueueing the conditions repeatable fields assets 
			vpr_helpers()->conditions_handler()->enqueue_or_groups_assets();
			//Enqueueing the restrictions rules assets
			$this->conditions_edit_enqueue_assets();
		}

		/**
		 * Enqueues the restrictions rules assets
		 */
		public function conditions_edit_enqueue_assets() {

			wp_enqueue_style('vpr_meta_box_style', VPR_URL . 'assets/css/conditions-post-type-meta-boxes.css');

			wp_enqueue_script('vpr_meta_box', VPR_URL . 'assets/js/meta-boxes.js');
		}

		/**
		 * Saves the restrictions fields of a supported post 
		 * 
		 * @param $post_id The post id to save the restrictions fields 
		 * 
		 */
		public function save_posts_restrictions_fields($post_id) {

			if (!isset($_REQUEST['vpr_conditions'])) {
				return;
			}
			$supported_post_types_keys = array_keys(vpr_helpers()->get_supported_post_types());
			$supported_post_types_keys[] = 'vpr_conditions';
			$post_type = get_post_type($post_id);

			//If the post type of the post is not supported we do nothing
			if (!in_array($post_type, $supported_post_types_keys)) {
				return;
			}

			//Checking if the post has inherited data from categories, this is needed because when a post is saved its categories can change, so that any already inherited data might not be valid
			$inherited_data = $this->get_restrictions_data_inherited_from_categories($post_id);

			do_action('vpr_before_save_post_restrictions_fields', $post_id, $post_type, $inherited_data, $supported_post_types_keys);

			//If the post has inherited its configuration from a category, then there is nothing to save
			if ($inherited_data === true) {
				return;
			}

			//If inherited data exists we will get the or groups from that data, otherwise we will get them from $_POST
			$or_groups_raw = empty($inherited_data) ? vpr_helpers()->get_post_value($this->post_types_keys->posts_restrictions, array()) : $inherited_data['or_groups'];
			$or_groups = vpr_helpers()->conditions_handler()->sanitize_groups($or_groups_raw);

			update_post_meta($post_id, $this->post_types_keys->posts_restrictions, $or_groups);

			if (isset($_POST[$this->meta_keys->post_url_redirection])) {
				update_post_meta($post_id, $this->meta_keys->post_url_redirection, sanitize_text_field($_POST[$this->meta_keys->post_url_redirection]));
			}

			//Saving selected post types only if the post is of the type vpr_conditions 
			if ($post_type === $this->post_types_keys->posts_restrictions) {

				$selected_post_types_raw = vpr_helpers()->get_post_value($this->meta_keys->selected_post_types, array());
				$selected_post_types = array_map('sanitize_text_field', $selected_post_types_raw);
				update_post_meta($post_id, $this->meta_keys->selected_post_types, $selected_post_types);
			}

			//Saving field that enables the post individual conditions
			if ($post_type !== $this->post_types_keys->posts_restrictions) {

				$conditions_enabled = vpr_helpers()->get_post_value($this->meta_keys->post_conditions_enabled, 0);

				$conditions_enabled = !empty($inherited_data) ? $inherited_data['conditions_enabled'] : $conditions_enabled;

				update_post_meta($post_id, $this->meta_keys->post_conditions_enabled, $conditions_enabled);
			}

			//If inherited data exists we will get the action when the conditions are met, otherwise we will get it from $_POST super global
			$what_happens_when_conditions_are_met = empty($inherited_data) ? vpr_helpers()->get_post_value($this->meta_keys->what_happens_when_the_conditions_are_met, 'allow_access') : $inherited_data['what_happens_when_the_conditions_are_met'];
			update_post_meta($post_id, $this->meta_keys->what_happens_when_the_conditions_are_met, sanitize_text_field($what_happens_when_conditions_are_met));

			if (!empty($inherited_data)) {
				//If the post has inherited data, then we save that data 
				update_post_meta($post_id, $this->meta_keys->inherited_from_term, $inherited_data['term_data']);
			} else {
				//If the post has not inherited data, then we delete the flag that indicates the post has inherited data
				delete_post_meta($post_id, $this->meta_keys->inherited_from_term);
			}

			do_action('vpr_after_save_post_restrictions_fields', $post_id, $post_type, $inherited_data, $supported_post_types_keys);
		}

		/**
		 * Search among the post categories for inherited configuration
		 * 
		 * @param int $post_id The post id for we want the inherited configuration
		 * 
		 * @return mixed True if the post has already inherited data, or array of the inherited configuration from a category
		 * 
		 */
		public function get_restrictions_data_inherited_from_categories($post_id) {

			//Checking if the post has already inherited data
			$inherited_from_term_data = get_post_meta($post_id, $this->meta_keys->inherited_from_term, true);

			//If the flag to know if the post has inherited data from a category is empty, then we initialize it
			if (empty($inherited_from_term_data)) {
				$inherited_from_term_data = array();
				$inherited_from_term_data['term_id'] = 0;
				$inherited_from_term_data['taxonomy'] = '';
			}

			//Initializing the inherited data
			$inherited_data = array();

			//Getting the list of taxonomies with their terms ids that are assigned to the post
			$post_taxonomies_terms = $this->get_post_assigned_terms($post_id);

			//Getting the inherited category taxonomy terms ids to check if the inherited data term is still asigned to the post
			$taxonomy_terms_to_check = !empty($post_taxonomies_terms[$inherited_from_term_data['taxonomy']]) ? $post_taxonomies_terms[$inherited_from_term_data['taxonomy']] : array();

			/*
			 * If there is inherited data from a category and that category is still associated to the post, 
			 * then that means the restrictions configuration of the post is being handled from that category
			 */
			if (!empty($taxonomy_terms_to_check) && in_array(intval($inherited_from_term_data['term_id']), $taxonomy_terms_to_check)) {

				return true;
			}

			//Searching among the terms if any of them has data to be inherited 
			foreach ($post_taxonomies_terms as $taxonomy => $terms_ids) {

				foreach ($terms_ids as $term_id) {

					//Getting how the category applies the restrictions configuration
					$apply_to = get_term_meta($term_id, $this->meta_keys->category_objects_to_apply, true);

					//If the term is not configured to inherit data, then we do nothing
					if (in_array($apply_to, array('', 'category'))) {
						continue;
					}

					//Getting the inherited configuration
					$conditions_enabled = get_term_meta($term_id, $this->meta_keys->post_conditions_enabled, true);
					$what_happens_when_the_conditions_are_met = get_term_meta($term_id, $this->meta_keys->what_happens_when_the_conditions_are_met, true);
					$or_groups = get_term_meta($term_id, $this->post_types_keys->posts_restrictions, true);
					$inherited_data = compact('apply_to', 'what_happens_when_the_conditions_are_met', 'or_groups', 'conditions_enabled');
					$inherited_data['term_data'] = compact('term_id', 'taxonomy');

					//We use the data of the first term that has data to inherit
					break;
				}

				//Returning the inherited data found
				if (!empty($inherited_data)) {
					return $inherited_data;
				}
			}

			return $inherited_data;
		}

		/**
		 * Gets all the taxonomies with their terms associated to a post
		 *  
		 * @param int|string $post_id The post id which we want the taxonomies and terms
		 * 
		 * @return array Array of arrays of terms ids with the taxonomy from that they belong to as a key
		 * 
		 */
		public function get_post_assigned_terms($post_id) {

			$post_type = get_post_type($post_id);

			$supported_taxonomies = vpr_helpers()->get_supported_taxonomies();

			$post_type_taxonomies = get_object_taxonomies($post_type);

			$taxonomies_terms = array();

			foreach ($supported_taxonomies as $supported_taxonomy) {

				if (!in_array($supported_taxonomy, $post_type_taxonomies)) {
					continue;
				}

				$current_taxonomy_terms_ids = wp_get_post_terms($post_id, $supported_taxonomy, array('fields' => 'ids'));

				$taxonomies_terms[$supported_taxonomy] = $current_taxonomy_terms_ids;
			}

			return $taxonomies_terms;
		}

		/**
		 * Gets the selectable post types options, with the already selected post types in other conditions posts removed
		 * 
		 * @param array $post_types_to_preserve Optional. Array of post types keys that we want to remain selectable as options
		 * 
		 * @return array List of post types keys options that can be selected
		 * 
		 */
		public function get_post_types_options($post_types_to_preserve = array()) {

			global $wpdb;

			//Getting all post types selections
			$post_types_options = vpr_helpers()->get_field_options($this->meta_keys->selected_post_types);

			//Defining the meta keyes used in this plugin
			$meta_keys = $this->meta_keys;

			//Defining the query to get the post types already selected in other conditions posts
			$query = "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '{$meta_keys->selected_post_types}'";

			//Performing the query
			$already_selected_post_types_raw = $wpdb->get_col($query);

			//Unserializing results
			$already_selected_post_types = array_map('unserialize', $already_selected_post_types_raw);

			//Removing invalid results
			$already_selected_post_types = array_filter($already_selected_post_types, 'is_array');

			//Merging all selected post types arrays 
			$already_selected_post_types = !empty($already_selected_post_types) ? array_merge(...$already_selected_post_types) : array();
			$GLOBALS['already_selected_post_types'] = $already_selected_post_types;

			//Removing post types selected in other conditions posts
			foreach ($already_selected_post_types as $already_selected_post_type) {

				//If the post type must be preserved it wont be removed
				if (in_array($already_selected_post_type, $post_types_to_preserve)) {
					continue;
				}

				unset($post_types_options[$already_selected_post_type]);
			}

			return apply_filters('vpr/post_types/options_for_rendering', $post_types_options, $post_types_to_preserve);
		}

		/**
		 * Removes the editor from the edit page of the "vpr_conditions" posts
		 */
		public function remove_support() {
			remove_post_type_support($this->post_types_keys->posts_restrictions, 'editor');
		}

		/**
		 * Adds the ajax used by the conditions
		 */
		public function add_conditions_ajax_actions() {

			foreach (vpr_helpers()->conditions_handler()->get('conditions') as $condition_key => $condition) {

				if (method_exists($condition, 'add_ajax')) {
					call_user_func_array(array($condition, 'add_ajax'), array());
				}
			}
		}

		/**
		 * Initializes the conditions actions and filters
		 */
		public function init() {

			$this->add_conditions_ajax_actions();
			$this->register();
			$this->remove_support();
			add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
			add_action('admin_enqueue_scripts', array($this, 'enqueues'));
			add_action('save_post', array($this, 'save_posts_restrictions_fields'));
		}

	}

}

