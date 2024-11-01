<?php
if (!class_exists('VPR_Helpers')) {

	class VPR_Helpers {

		/**
		 * 
		 * Renders a html tag attributes
		 * 
		 * @param array $attributes Array with the html attributes to be used in a html tag
		 * 
		 */
		public function display_tag_attributes($attributes = array()) {

			if (!empty($attributes)) {
				foreach ($attributes as $attribute_key => $attribute) {

					//If attribute key is not string we assume is a single attribute 
					if (is_string($attribute_key)) {
						echo implode('', array(' ', esc_html($attribute_key), '="', esc_attr($attribute), '" '));
					} else {
						echo implode('', array(' ', esc_attr($attribute), ' '));
					}
				}
			}
		}

		/**
		 * 
		 * Renders a html tag
		 * 
		 * @param array $tag_args $tag_args['tag'] The name of the tag: default value is empty string,
		 *                        $tag_args['attributes'] The html attributes of the tag: default value is array(),
		 *                        $tag_args['closed'] If the tag must be closed: default value is false,
		 *                        $tag_args['content'] The content that is inside the tag: default value is empty string  
		 * 
		 */
		public function display_tag($tag_args = array()) {

			$default_args = array(
				'tag' => '',
				'attributes' => array(),
				'closed' => true,
				'content' => ''
			);

			$args = apply_filters(
					'vc_tag_arguments', array_merge($default_args, $tag_args)
			);

			if (empty($args['tag'])) {
				return;
			}

			do_action('vc_before_display_tag', $args);
			?><<?php
			esc_attr_e($args['tag']);
			$this->display_tag_attributes($args['attributes']);
			?>><?php
			if ($args['closed']) {
				echo $args['content'];
				echo '</' . esc_attr($args['tag']) . '>';
			}

			do_action('vc_after_display_tag', $args);
		}

		/**
		 * 
		 * @see $self::display_tag()
		 * 
		 * @return string Returns a html tag as string 
		 *  
		 */
		public function generate_tag($tag_args = array()) {

			ob_start();

			$this->display_tag($tag_args);

			return ob_get_clean();
		}

		public function get_dir_files($dir) {
			$dir = wp_normalize_path($dir);
			$files_cache = wp_cache_get($dir);

			if (!empty($files_cache)) {
				return $files_cache;
			}

			$files = $this->get_files($dir);

			wp_cache_set($dir, $files);

			return $files;
		}

		public function get_files($dir) {

			$dir_objects = scandir($dir);
			$files = array();
			$dirs = array();

			foreach ($dir_objects as $dir_object) {


				$slash = '/';

				$dir_object_path = str_replace('//', '/', $dir . $slash . $dir_object);

				if (in_array($dir_object, array('.', '..'))) {
					continue;
				}

				if (is_dir($dir_object_path)) {
					$dirs[] = $dir_object_path;
					continue;
				}

				if (is_file($dir_object_path)) {

					$file_pieces = explode('.', $dir_object);

					if (end($file_pieces) === 'php') {
						$files[] = $dir_object_path;
					}
				}
			}

			foreach ($dirs as $dir) {

				$files = array_merge($files, $this->get_dir_files($dir));
			}

			return $files;
		}

		public function get_post_value($key, $empty_value = '') {

			$value = empty($_POST[$key]) ? $empty_value : $_POST[$key];

			$meta_keys = (object) vpr_helpers()->get_meta_keys();
			$post_types_keys = (object) vpr_helpers()->get_post_types_keys();

			if ($key === $meta_keys->post_conditions_enabled) {
				$value = boolval($value);
			} elseif ($key === $post_types_keys->posts_restrictions) {
				$value = vpr_helpers()->conditions_handler()->sanitize_groups($value);
			} elseif (is_string($value)) {
				$value = sanitize_text_field($value);
			} elseif (is_array($value)) {
				$value = array_map('sanitize_text_field', $value);
			}

			return $value;
		}

		/**
		 * Gets the post types keys used in this plugin
		 * 
		 * @return array The array of post types keys
		 * 
		 */
		public function get_post_types_keys() {

			return apply_filters(
					'vpr_post_types_keys', array(
				'posts_restrictions' => 'vpr_conditions'
					)
			);
		}

		/**
		 * Gets the post types register args for this plugin
		 * 
		 * @return array Associate array of post types register args with the post types keys as indexes of the array
		 * 
		 */
		public function get_post_types_args() {

			$post_types_keys = (object) $this->get_post_types_keys();

			return apply_filters(
					'vpr_post_types_args', array(
				$post_types_keys->posts_restrictions => array(
					'labels' => array(
						'name' => __('Posts restrictions', 'wp-conditional-post-restrictions'),
						'add_new_item' => __('Add new restrictions', 'wp-conditional-post-restrictions'),
						'edit_item' => __('Edit restrictions', 'wp-conditional-post-restrictions')
					),
					'capability_type' => 'post',
					'has_archive' => false,
					'show_in_menu' => false,
					'public' => false,
					'show_ui' => true
				)
					)
			);
		}

		/**
		 * Gets the post types keys that will have support for the restrictions fields, so they can be restricted
		 * 
		 * @return array Array of post types keys that will support the restrictions fields
		 * 
		 */
		public function get_supported_post_types() {

			$get_post_types_args = array('public' => true);

			$post_types_raw = get_post_types($get_post_types_args, 'objects');
			$post_types_keys = array_diff(array_keys($post_types_raw), array('attachment'));
			$valid_post_types = array();

			foreach ($post_types_keys as $post_type_key) {
				$valid_post_types[$post_type_key] = $post_types_raw[$post_type_key];
			}

			return apply_filters('vpr_supported_post_types', $valid_post_types);
		}

		/**
		 * Gets the taxonomies keys that will have support for the restrictions fields, so they can be restricted
		 * 
		 * @return array Array of taxonomies keys that will support the restrictions fields
		 * 
		 */
		public function get_supported_taxonomies($output = 'names') {

			$supported_post_types = $this->get_supported_post_types();

			$taxonomies = array();

			$taxonomies = get_taxonomies(array(
				'show_ui' => true,
				'public' => true
					), $output);

			return apply_filters('vpr_supported_taxonomies', $taxonomies, $supported_post_types);
		}

		/**
		 * Gets the conditions handler constructor args
		 * 
		 * @return array The args needed to instance the conditions handler 
		 * 
		 */
		public function get_conditions_handler_args() {

			$post_types_keys = (object) $this->get_post_types_keys();

			$conditions_args = array(
				'prefix' => 'vpr_',
				'conditions_folder' => VPR_PATH . 'inc/conditions',
				'conditions_post_key' => $post_types_keys->posts_restrictions,
				'conditions_groups' => array(
					'posts' => array(
						'label' => __('Posts', 'wp-conditional-post-restrictions')
					),
					'user' => array(
						'label' => __('User', 'wp-conditional-post-restrictions')
					),
					'woocommerce' => array(
						'label' => __('WooCommerce', 'wp-conditional-post-restrictions')
					),
					'buddypress' => array(
						'label' => __('BuddyPress', 'wp-conditional-post-restrictions')
					),
					'learndash' => array(
						'label' => __('LearnDash', 'wp-conditional-post-restrictions')
					),
					'learnpress' => array(
						'label' => __('LearnPress', 'wp-conditional-post-restrictions')
					),
					'tutorlms' => array(
						'label' => __('TutorLMS', 'wp-conditional-post-restrictions')
					),
					'edd' => array(
						'label' => __('Easy Digital Downloads', 'wp-conditional-post-restrictions')
					),
					'givewp' => array(
						'label' => __('GiveWP', 'wp-conditional-post-restrictions')
					),
					'date' => array(
						'label' => __('Date', 'wp-conditional-post-restrictions')
					),
					'wpultimo' => array(
						'label' => __('WP Ultimo', 'wp-conditional-post-restrictions')
					)
				)
			);

			return apply_filters('vpr_conditions_handler_args', $conditions_args);
		}

		public function get_user_roles() {

			if (!function_exists('get_editable_roles')) {
				require_once ABSPATH . 'wp-admin/includes/user.php';
			}
			$user_roles = get_editable_roles();

			$user_roles_array = array();

			$user_roles_array["guest"] = __("Guest customer", 'wp-conditional-post-restrictions');
			foreach ($user_roles as $key => $user_role) {

				$user_roles_array[$key] = $user_role["name"];
			}
			$user_roles_array["customer"] = __("Registered customer", 'wp-conditional-post-restrictions');

			return $user_roles_array;
		}

		/**
		 * Retrieves the conditions handler for this plugin, if the handler not exists it will be instantiated
		 * 
		 * @return Vegacorp_Conditions_Handler The conditions handler
		 * 
		 */
		public function conditions_handler() {

			$existent_handler = !empty($GLOBALS['vpr_conditions_handler']) ? $GLOBALS['vpr_conditions_handler'] : null;

			if (!empty($existent_handler)) {
				return $existent_handler;
			}

			$handler = vegacorp_create_conditions_handler($this->get_conditions_handler_args());

			$GLOBALS['vpr_conditions_handler'] = $handler;

			return $handler;
		}

		/**
		 * Gets the meta keys for the fields used in this plugin
		 * 
		 * @param boolean $options If set to true, The underscore at the beginning of the meta keys will be removed
		 * 
		 * @return array The meta keys used in this plugin 
		 *  
		 */
		public function get_meta_keys($options = false) {

			$meta_keys = apply_filters(
					'vpr_meta_keys', array(
				'what_happens_when_post_is_restricted' => '_vpr_what_happens_when_post_is_restricted',
				'whitelisted_roles' => '_vpr_whitelisted_roles',
				'selected_post_types' => '_vpr_selected_post_types',
				'post_url_redirection' => '_vpr_post_url_redirection',
				'restricted_post_message' => '_vpr_restricted_post_message',
				'what_happens_when_category_is_restricted' => '_vpr_what_happens_when_category_is_restricted',
				'what_happens_when_the_conditions_are_met' => '_vpr_what_happens_when_the_conditions_are_met',
				'error_message' => '_vpr_error_message',
				'category_url_redirection' => '_vpr_category_url_redirection',
				'show_the_content_of_another_page' => '_vpr_page',
				'category_objects_to_apply' => '_vpr_category_objects_to_apply',
				'inherited_from_term' => '_vpr_inherited_from_term',
				'post_conditions_enabled' => '_vpr_post_conditions_enabled'
					)
			);

			return !$options ? $meta_keys : array_map(array($this, 'remove_first_underscore'), $meta_keys);
		}

		function is_user_whitelisted() {
			$is_whitelisted = false;
			$whitelisted_user_roles = get_option('vpr_whitelisted_roles');
			if (empty($whitelisted_user_roles) || !is_array($whitelisted_user_roles)) {
				$whitelisted_user_roles = array('edit_others_posts');
			}
			if (!is_user_logged_in() && in_array('guest', $whitelisted_user_roles, true)) {
				$is_whitelisted = true;
				return $is_whitelisted;
			}

			// Exit if user role is whitelisted to let them view all products regardless of country restriction
			if (is_user_logged_in()) {

				foreach ($whitelisted_user_roles as $whitelisted_user_role) {

					if (current_user_can($whitelisted_user_role)) {
						$is_whitelisted = true;
						break;
					}
				}
			}
			return $is_whitelisted;
		}

		function get_user_agent() {
			return isset($_SERVER['HTTP_USER_AGENT']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT'])) : ''; // @codingStandardsIgnoreLine
		}

		/**
		 * Gets the options for selection fields 
		 *   
		 * @return array The list of fields meta keys with their respective options
		 * 
		 */
		public function fields_options() {

			//Getting the cached data
			$cache = wp_cache_get('vpr_fields_options');

			//If the cache exists we return the cached data
			if (!empty($cache)) {
				return $cache;
			}

			//Getting the meta keys used in this plugin
			$meta_keys = (object) $this->get_meta_keys();
			//Getting the supported post types that can be restricted options
			$post_types = $this->get_supported_post_types();
			//Getting the pages options
			$pages_raw = new WP_Query(array('post_type' => 'page', 'posts_per_page' => -1));
			//Formatting the pages array, so it can be used as options
			$pages = wp_list_pluck($pages_raw->posts, 'post_title', 'ID');

			//Defining the options
			$fields_options = apply_filters(
					'vpr_fields_options', array(
				$meta_keys->what_happens_when_post_is_restricted => array(
					'redirect_to_url' => __('Redirect to url', 'wp-conditional-post-restrictions'),
					'remove_content_and_show_message' => __('Remove the content and show message', 'wp-conditional-post-restrictions'),
					'show_fragment_of_the_content_and_show_message_after_fragment' => __('Show fragment of the content and show message after fragment', 'wp-conditional-post-restrictions')
				),
				$meta_keys->selected_post_types => wp_list_pluck($post_types, 'label'),
				$meta_keys->what_happens_when_category_is_restricted => array(
					'show_error_404' => __('Show error 404', 'wp-conditional-post-restrictions'),
					'show_the_normal_page_without_posts' => __('Show the normal page without posts', 'wp-conditional-post-restrictions'),
					'show_an_error_message' => __('Show an error message', 'wp-conditional-post-restrictions'),
					'redirect_to_another_url' => __('Redirect to another url', 'wp-conditional-post-restrictions'),
					'show_the_content_of_another_page' => __('Show the content of another page', 'wp-conditional-post-restrictions')
				),
				$meta_keys->what_happens_when_the_conditions_are_met => array(
					'allow_access' => __('Allow access', 'wp-conditional-post-restrictions'),
					'restrict_access' => __('Restrict access', 'wp-conditional-post-restrictions')
				),
				$meta_keys->show_the_content_of_another_page => $pages,
				$meta_keys->category_objects_to_apply => array(
					'' => __('None', 'wp-conditional-post-restrictions'),
					'category' => __('Category', 'wp-conditional-post-restrictions'),
					'category+posts' => __('Category and posts', 'wp-conditional-post-restrictions'),
					'posts' => __('Posts', 'wp-conditional-post-restrictions')
				)
					)
			);

			//Setting the cache
			wp_cache_set('vpr_fields_options', apply_filters('vpr_fields_options_array', $fields_options));

			return $fields_options;
		}

		/**
		 * Gets the field options for the given field meta key
		 * 
		 * @param string $field_key The meta key for which we want the options
		 * 
		 * @return Array The options for the given field, if the field has no options an empty array will be returned
		 * 
		 */
		public function get_field_options($field_key) {

			$fields_options = $this->fields_options();

			return !empty($fields_options[$field_key]) ? $fields_options[$field_key] : array();
		}

		/**
		 * Generates a label html tag
		 * 
		 * @param array $args $ars['label]: The label text, $args['id']: The field id to use in the "for" attribute,
		 *                    $args['attributes']: The attributes that our label will have
		 * 
		 * @return string The generated label
		 * 
		 */
		public function get_field_label($args) {

			//Initializing default args
			$label_args = array(
				'tag' => 'label',
				'content' => !empty($args['label']) ? $args['label'] : '',
				'attributes' => array(
				)
			);

			//Initializing rhe label "for" attribute
			if (!empty($args['id'])) {
				$label_args['attributes']['for'] = $args['id'];
			}

			//Generating the label
			$label = !empty($label_args['content']) ? $this->generate_tag($label_args) : '';

			//If the label if not empty we concatenate a space 
			if (!empty($label)) {
				$label .= ' ';
			}

			return $label;
		}

		/**
		 * Displays a html select tag
		 * 
		 * @param $field_args $field_args['label']: The label text for the select label, $field_args['id]: The select id, $field_args['name']: The select name,
		 *                    $field_args['options']: array of keys => values, $field_args['value']: List of selected values or value, $args['class']: string of css classes,
		 *                    $field_args['wrapped']: If the select must be wrapped, $field_args['wrapper_attributes']: The html attributes of the select wrapper
		 *                   
		 */
		public function select($field_args) {

			//Initializing the select default args
			$default_args = array(
				'label' => '',
				'options' => array(),
				'value' => '',
				'wrapped' => true,
				'wrapper_attributes' => array()
			);

			//Defining the args that we dont use in the select tag to remove them later
			$non_field_attributes = array('label', 'options', 'wrapped', 'value', 'wrapper_attributes');

			//Merging all args
			$args = array_merge($default_args, $field_args);

			//Generating the select label
			$label = $this->get_field_label($args);

			//Getting the select options
			$options = !empty($args['options']) ? $this->generate_options($args['options'], $args['value']) : '';

			//Removing the args that are not part of the select html attributes
			$select_args = $this->remove_non_field_attributes($args, $non_field_attributes);

			//Generating the select
			$select = $this->generate_tag(array(
				'tag' => 'select',
				'attributes' => $select_args,
				'content' => $options
			));

			//Concatening the label and the select
			$select = $label . $select;

			//Wrapping the select
			if ($args['wrapped']) {
				$select = $this->generate_tag(array(
					'tag' => 'div',
					'attributes' => $args['wrapper_attributes'],
					'content' => $select
				));
			}

			//Displaying the select
			echo $select;
		}

		/**
		 * Displays an input select tag
		 * 
		 * @param $field_args $field_args['label']: The label text for the input label, $field_args['id]: The input id, $field_args['name']: The input name,
		 *                    $field_args['type']: array of keys => values, $field_args['value']: Value, $args['class']: string of css classes,
		 *                    $field_args['wrapped']: If the input must be wrapped, $field_args['wrapper_attributes']: The html attributes of the input wrapper
		 *                   
		 */
		public function input($field_args) {

			//Initializing the input default args
			$default_args = array(
				'label' => '',
				'value' => '',
				'wrapped' => true,
				'type' => 'text',
				'wrapper_attributes' => array()
			);

			//Defining the args that we dont use in the input tag to remove them later
			$non_field_attributes = array('label', 'wrapped', 'wrapper_attributes');

			//Merging all args
			$args = array_merge($default_args, $field_args);

			//Generating the input label
			$label = $this->get_field_label($args);

			//Removing the args that are not part of the input html attributes
			$input_args = $this->remove_non_field_attributes($field_args, $non_field_attributes);

			//Generating the input
			$input = $this->generate_tag(array(
				'tag' => 'input',
				'attributes' => $input_args,
				'closed' => false
			));

			//Concatening the label and the input
			$input = $label . $input;

			//Wrapping the input
			if ($args['wrapped']) {
				$input = $this->generate_tag(array(
					'tag' => 'div',
					'attributes' => $args['wrapper_attributes'],
					'content' => $input
				));
			}

			//Displayin the input
			echo $input;
		}

		/**
		 * Function to Remove elements from a html attributes array 
		 * 
		 * @param array $field_args     The html attributes array for which we want to remove elements
		 * @param array $keys_to_remove The keys of the elements we want to remove from the array
		 * 
		 * @return array The html attributes array with the unwanted elements removed
		 * 
		 */
		public function remove_non_field_attributes($field_args, $keys_to_remove) {

			$attributes = array();

			foreach ($field_args as $key => $value) {

				if (in_array($key, $keys_to_remove, true)) {

					continue;
				}

				$attributes[$key] = $value;
			}

			return $attributes;
		}

		/**
		 * Generates html options tags string
		 * 
		 * @param $options The associative array to generate the options
		 * @param $selected_options The values to know which options will be selected
		 * 
		 * @return string The generated options string
		 * 
		 */
		public function generate_options($options, $selected_options = array()) {

			//Initializing the selected options
			$selected_options = is_scalar($selected_options) ? array($selected_options) : $selected_options;

			//Initialzing the options string
			$generated_options = '';
			if (!isset($options[''])) {
				$generated_options .= '<option value="">--</option>';
			}

			foreach ($options as $option_value => $option_label) {

				//Initializing the option attributes
				$option_attributes = array('value' => $option_value);

				//Checking if the option will have the "selected" attribute
				if (in_array($option_value, $selected_options)) {
					$option_attributes[] = 'selected';
				}

				//Generating the option
				$generated_options .= $this->generate_tag(array(
					'tag' => 'option',
					'attributes' => $option_attributes,
					'content' => $option_label
				));
			}

			return $generated_options;
		}

		/**
		 * Applies the sanitize_text_field to and array
		 * 
		 * @param array The array we want to sanitize
		 * 
		 * @return array The sanitized array 
		 * 
		 */
		public function sanitize_array($array) {
			return array_map('sanitize_text_field', $array);
		}

		/**
		 * Removes the first underscore at the beggining of a string
		 * 
		 * @param string $string The string from we want to remove a underscore
		 * 
		 * @return string The string with the first underscore at the beginning removed 
		 * 
		 */
		public function remove_first_underscore($string) {

			return ltrim($string, '_');
		}

		/**
		 * Gets all conditions posts meta data
		 * 
		 * @return array Array of meta data of the conditions posts
		 * 
		 */
		public function get_global_conditions_data() {

			//Checking if the data is already cached
			$cache = wp_cache_get('vpr_global_conditions_data_cache');

			//If the data is already cached we return the cached data
			if (!empty($cache)) {
				return apply_filters('vpr_global_conditions_data', $cache);
			}

			//Getting the post types keys used in this plugin
			$post_types = (object) $this->get_post_types_keys();
			//Getting the mata keys used in this plugin
			$meta_keys = (object) $this->get_meta_keys();

			//Preparing the wp query args
			$query_args = array(
				'post_type' => $post_types->posts_restrictions,
				'fields' => 'ids',
				'posts_per_page' => -1,
				'post_status' => 'publish'
			);

			//Performing the wp query to get the conditions posts
			$query = new WP_Query($query_args);

			//Getting the or groups conditions posts 
			$conditions_posts_ids = $query->get_posts();

			//Initializing the conditions posts meta data array
			$conditions_array = array();

			//Getting the conditions posts meta data
			foreach ($conditions_posts_ids as $conditions_post_id) {

				//Getting and validating the conditions or gruops
				$or_groups_raw = get_post_meta($conditions_post_id, $post_types->posts_restrictions, true);

				$or_groups = !empty($or_groups_raw) ? $or_groups_raw : array();

				//Getting and validating the select post types
				$selected_post_types_raw = get_post_meta($conditions_post_id, $meta_keys->selected_post_types, true);

				$selected_post_types = !empty($selected_post_types_raw) ? array_map('sanitize_text_field', $selected_post_types_raw) : array();

				//Getting and validating the action when post types are restricted
				$what_happens_when_the_conditions_are_met = sanitize_text_field(get_post_meta($conditions_post_id, $meta_keys->what_happens_when_the_conditions_are_met, true));

				//Adding configuration to the final array
				$conditions_array[$conditions_post_id] = compact('or_groups', 'selected_post_types', 'what_happens_when_the_conditions_are_met');
			}

			//Setting the cache
			wp_cache_Set('vpr_global_conditions_data_cache', $conditions_array);

			return apply_filters('vpr_global_conditions_data', $conditions_array);
		}

		/**
		 * Gets the restrictions configuration of a post or category
		 * 
		 * @param int $object_id The id of the object
		 * @param string $type   The type of the object, can be 'post' or 'category'. Default is 'post'
		 * 
		 * @return array Array with the restrictions fields
		 * 
		 */
		public function get_object_restrictions_configuration($object_id, $type = 'post') {

			//Defining the callback that we will use for obtain the object meta data
			if ($type === 'post') {
				$meta_callback = 'get_post_meta';
			}

			if ($type === 'category') {
				$meta_callback = 'get_term_meta';
			}

			if (!is_callable($meta_callback)) {
				return array();
			}

			//Getting the cached data
			$cache_key = implode('_', array('vpr', $type, 'object', $object_id));
			$cache = wp_cache_get($cache_key);

			//If there is cached data we return that data
			if (!empty($cache)) {
				return $cache;
			}

			//Getting the meta keys used in this plugin
			$meta_keys = (object) $this->get_meta_keys();

			//Getting object configuration
			$what_happens_when_the_conditions_are_met = sanitize_text_field(call_user_func_array($meta_callback, array($object_id, $meta_keys->what_happens_when_the_conditions_are_met, true)));

			$or_groups = call_user_func_array($meta_callback, array($object_id, 'vpr_conditions', true));

			$conditions_enabled = boolval(call_user_func_array($meta_callback, array($object_id, $meta_keys->post_conditions_enabled, true)));

			$configuration = array(
				'what_happens_when_the_conditions_are_met' => $what_happens_when_the_conditions_are_met,
				'conditions_enabled' => $conditions_enabled,
				'or_groups' => !empty($or_groups) ? $or_groups : array(),
			);

			//Adding category exclusive fields
			if ($type === 'category') {
				$configuration['apply_to'] = sanitize_text_field(call_user_func_array($meta_callback, array($object_id, $meta_keys->category_objects_to_apply, true)));
			}

			//Applying filters
			$filtered_configuration = apply_filters(
					'vpr_object_configuration', $configuration, $object_id, $type
			);

			//Setting the cache
			wp_cache_set($cache_key, $filtered_configuration);

			return $filtered_configuration;
		}

		/**
		 * Determines if an object is restricted, according to its configuration or global configuration
		 * 
		 * @param WP_Post|WP_Term $object The object we want to check, can be a WP_Post or WP_term
		 * 
		 * @return boolean 
		 * 
		 */
		public function is_restricted($object) {

			//Initialing the type of the object
			$type = '';

			//Checking if the object is a post
			if (is_a($object, 'WP_Post')) {
				$object_id = $object->ID;
				$type = 'post';
			}

			//Checking if the object is a term
			if (is_a($object, 'WP_Term')) {
				$object_id = $object->term_id;
				$type = 'category';
			}

			//If the type is empty that means the object is invalid so it is not restricted
			if (empty($type)) {
				return false;
			}

			//Checking if the data is already cached
			$cache_found = false;

			$cache_key = implode('_', array('vpr', $type, $object_id, 'is_restricted'));

			$cache = wp_cache_get($cache_key, '', false, $cache_found);

			//If the data is already cached, the cached data is returned
			if ($cache_found) {
				return $cache;
			}

			//Initializing the "is restricted" flag
			$is_restricted = false;

			//Getting the object custom configuration
			$single_configuration = $this->get_object_restrictions_configuration($object_id, $type);

			//Getting global configuration
			$global_configuration = !$single_configuration['conditions_enabled'] ? $this->get_global_conditions_data() : array();

			//Initializing the conditions that will be tested
			$conditions_to_test = array();

			//Initializing the "configuration used" flag
			$configuration_used = '';

			//Defining which configuration wll be used, if the single object configuration or the global configuration
			if ($single_configuration['conditions_enabled']) {
				$conditions_to_test = array($object_id => $single_configuration['or_groups']);
				$configuration_used = 'single';
			}

			if (!empty($global_configuration)) {
				$conditions_to_test = wp_list_pluck($global_configuration, 'or_groups');
				$configuration_used = 'global';
			}
			//Checking if the object is restricted using the single configuration
			if ($configuration_used === 'single') {

				$is_restricted = $this->is_single_restricted($object, $single_configuration, $conditions_to_test, $type);
			}

			//Checking if the object is restricted using the global configuration
			if ($configuration_used === 'global') {

				$is_restricted = $this->is_global_restricted($object, $global_configuration, $conditions_to_test, $type);
			}

			//Applying filters
			$is_restricted_filtered = apply_filters('vpr_is_restricted', $is_restricted, compact('object', 'configuration_used', 'single_configuration', 'global_configuration'));

			//Setting the cache
			wp_cache_set($cache_key, $is_restricted_filtered);

			return $is_restricted_filtered;
		}

		/**
		 * 
		 * Determines if the object is restricted using the object custom configuration
		 * 
		 * @param WP_Post|WP_Term $object
		 * @param array $single_configuration
		 * @param array $conditions
		 * @param array $type
		 * 
		 * @return returns true if the object is restricted, false if not
		 * 
		 */
		public function is_single_restricted($object, $single_configuration, $conditions, $type) {

			//Initializing the filter vars
			$filter_vars = compact('object', 'single_configuration', 'conditions', 'type');

			//Checking if the configuration is applied to the category if the object is a category
			if ($type === 'category' && !in_array($single_configuration['apply_to'], array('category', 'category+posts'))) {
				return apply_filters('vpr_is_single_restricted', false, $filter_vars);
			}

			//Evaluating the conditions
			$this->conditions_handler()->evaluate_conditions($conditions);

			//Checking if the conditions met
			$conditions_met = count($this->conditions_handler()->get('successful_conditions_groups_keys')) > 0;

			//Initializing restriction flag
			$is_restricted = false;

			//Checking if the object is restricted
			if ($conditions_met && $single_configuration['what_happens_when_the_conditions_are_met'] === 'restrict_access') {
				$is_restricted = true;
			}

			if (!$conditions_met && $single_configuration['what_happens_when_the_conditions_are_met'] === 'allow_access') {
				$is_restricted = true;
			}

			$filter_vars['conditions_met'] = $conditions_met;

			return apply_filters('vpr_is_single_restricted', $is_restricted, $filter_vars);
		}

		function get_user_ip() {
			$ip = null;
			// Support for Cloudflare when the country header is disabled
			if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
				$ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
			} elseif (isset($_SERVER['HTTP_X_SUCURI_CLIENTIP'])) {
				$ip = $_SERVER['HTTP_X_SUCURI_CLIENTIP'];
			} else {
				$ip = $_SERVER['REMOTE_ADDR'];
			}

			if (defined('WPCPR_USER_IP')) {
				$ip = WPCPR_USER_IP;
			}
			return $ip;
		}

		/**
		 * 
		 * Determines if the object is restricted using the global configuration
		 * 
		 * @param WP_Post|WP_Term $object 
		 * @param array $global_configuration
		 * @param array $conditions
		 * @param array $type
		 * 
		 * @return returns true if the object is restricted, false if not
		 * 
		 */
		public function is_global_restricted($object, $global_configuration, $conditions, $type) {

			//Initializing flags            
			$is_restricted = false;

			//Initializing the list of restricted post types
			$restricted_post_types = array();

			//Evaluating the conditions
			$this->conditions_handler()->evaluate_conditions($conditions);

			//Getting the succesful conditions indexes or keys
			$succesful_conditions_groups_ids = $this->conditions_handler()->get('successful_conditions_groups_keys');
			//Checking each succesful conditions configuration to determine which post types are restricted 
			foreach ($global_configuration as $conditions_groups_post_id => $configuration) {

				$what_happens_when_the_conditions_are_met = $configuration['what_happens_when_the_conditions_are_met'];
				$conditions_met = in_array($conditions_groups_post_id, $succesful_conditions_groups_ids);

				if ($conditions_met && $what_happens_when_the_conditions_are_met === 'restrict_access') {

					$restricted_post_types = array_merge($restricted_post_types, $configuration['selected_post_types']);
				}

				if (!$conditions_met && $what_happens_when_the_conditions_are_met === 'allow_access') {

					$restricted_post_types = array_merge($restricted_post_types, $configuration['selected_post_types']);
				}
			}

			//Checking if the post post type is among the restricted post types
			if ($type === 'post') {
				$is_restricted = in_array($object->post_type, $restricted_post_types);
			}

			//Cheking if the category associated post types are among the restricted post types
			if ($type === 'category') {

				$taxonomy_associated_post_types = $this->get_taxonomy_associated_post_types($object->taxonomy);
				$is_restricted = !empty(array_intersect($taxonomy_associated_post_types, $restricted_post_types));
			}

			return apply_filters('vpr_is_global_restricted', $is_restricted, compact('object', 'global_configuration', 'conditions', 'type', 'restricted_post_types'));
		}

		/**
		 * Gets the taxonomy associated post types
		 * 
		 * @param string $taxonomy the taxonomy key
		 * 
		 * @retun array The associated post types
		 * 
		 */
		public function get_taxonomy_associated_post_types($taxonomy) {

			$taxonomy_obj = get_taxonomy($taxonomy);

			if (!empty($taxonomy_obj)) {
				return $taxonomy_obj->object_type;
			}

			return array();
		}

	}

}

if (!function_exists('vpr_helpers')) {

	function vpr_helpers() {

		if (!empty($GLOBALS['vpr_helpers'])) {
			return $GLOBALS['vpr_helpers'];
		}

		$GLOBALS['vpr_helpers'] = new VPR_Helpers();

		return $GLOBALS['vpr_helpers'];
	}

}