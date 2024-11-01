<?php
/*
  Setting views class
 */

if (!trait_exists("Vegacorp_Conditions_Settings_Views")) {

	trait Vegacorp_Conditions_Settings_Views {
		
		public function enable_conditions_option_view($passed_label = '') {
			//Getting option
			$option = get_option($this->get('conditions_post_key') . '_enabled');
			$checked = "";
                        
                        $default_label = __('The conditions are deactivated. Check this option and save to activate them', VEGACORP_CONDITIONS_TEXT_DOMAIN);;
                        
                        $label = !empty($passed_label) ? $passed_label : $default_label;
			//Checking option value
			if ($option === "on") {
				$checked = "checked";
			}

			//Option view
			?>		
			<input 
				id = "<?php esc_attr_e($this->get('conditions_post_key') . '_enabled'); ?>" 
				name = "<?php esc_attr_e($this->get('conditions_post_key') . '_enabled'); ?>" 
				<?php echo esc_attr($checked); ?> 
				type = "checkbox"
			>
			<?php if (empty($checked)) { ?>
                             <span class="error" style="color: red;"><?php esc_html_e($label); ?></span>
			<?php
			}
		}

		public function get_payment_gateway_conditions_posts() {

			//Getting vg payment gateways conditions posts
			$args = array(
				"post_type"      => $this->conditions_post_key,
				"posts_per_page" => -1
			);

			//Executing query 
			$or_groups_posts_conditions = new WP_query($args);

			if (!$or_groups_posts_conditions->have_posts()) {
				return "";
			}

			$or_groups_posts_conditions->posts = array_reverse($or_groups_posts_conditions->posts);
			while ($or_groups_posts_conditions->have_posts()) {

				$or_groups_posts_conditions->the_post();

				$title = get_the_title();

				if (empty($title)) {
					$title = __("(Untitled condition)", VEGACORP_CONDITIONS_TEXT_DOMAIN);
				}

				$id = get_the_id();
				//Inserting row with post information for each post
				$this->insert_post_row($id, $title);
			}
		}

		//Function that inserts a row with a link to edit post and a link to delete post
		public function insert_post_row($id, $title) {

			$edit_text        = __("Edit", VEGACORP_CONDITIONS_TEXT_DOMAIN);
			$delete_text      = __("Delete", VEGACORP_CONDITIONS_TEXT_DOMAIN);
			$link_to_edit     = "post.php?post={$id}&action=edit";
			$groups           = get_post_meta($id, $this->conditions_meta_key, true);
			$conditions_count = $this->count_conditions($groups);
			?>
			<tr id = <?php echo esc_attr("condition-{$id}"); ?>>
			     <td>
			     	<span><?php echo esc_html($title); ?></span><br>
			     	<a href = <?php echo esc_attr($link_to_edit); ?>><?php echo $edit_text; ?></a> | <span id = "delete-<?php echo $id; ?>" class = "delete-conditions" style = "color:#c70000; cursor:pointer;" data-delete_action="<?php esc_attr_e('delete_' . $this->conditions_post_key); ?>"><?php echo $delete_text; ?></span>
			     </td>
			     <td>
			     	<span><?php echo $conditions_count; ?></span>
			     </td>
			     <td>
			     	<span><?php echo ( get_post_status($id) === 'publish') ? __('Active', VEGACORP_CONDITIONS_TEXT_DOMAIN) : __('Inactive', VEGACORP_CONDITIONS_TEXT_DOMAIN); ?></span>
			     </td>
			     <td><span><?php echo get_the_date('', $id); ?></span></td> 
			</tr>
			<?php
		}

		//Function that displays a table which contains payment gateway conditions post type information
		public function conditions_option_view() {

			$title_text      = __("Title", VEGACORP_CONDITIONS_TEXT_DOMAIN);
                        $conditions_text = __("Conditions", VEGACORP_CONDITIONS_TEXT_DOMAIN); 
			$button_text     = __("Add conditions", VEGACORP_CONDITIONS_TEXT_DOMAIN);  
                        if(!empty($this->prefix)){
                            do_action($this->prefix . '/settings_page/before_conditions_list');
                        }
			
                        
			?>
			<table class = "widefat vegacorp-conditions-list">
				<thead>
					<tr>
						<td><strong><?php echo $title_text; ?></strong></td>
						<td><strong># <?php echo $conditions_text; ?></strong></td>
						<td><strong><?php _e('Status', VEGACORP_CONDITIONS_TEXT_DOMAIN); ?></strong></td>
						<td><strong><?php _e('Created', VEGACORP_CONDITIONS_TEXT_DOMAIN); ?></strong></td>
					</tr>
				</thead>
				<tbody>               
					<?php echo $this->get_payment_gateway_conditions_posts(); ?>                
				</tbody>
				<tfoot>
				<td>
					<a class = "button-primary" href = "post-new.php?post_type=<?php echo $this->conditions_post_key; ?>"><?php echo $button_text; ?></a>
				</td>  
				<td></td>
				<td></td>
				<td></td>
			</tfoot>
			</table>
			
			<?php
                        if(!empty(!$this->prefix)){
                            do_action($this->prefix . '/settings_page/after_conditions_list');
                        }
			
		}

		//Conditions count 
		public function count_conditions($groups) {

			$conditions_count = 0;

			if (empty($groups)) {
				return $conditions_count;
			}

			foreach ($groups as $group) {

				$conditions_count += count($group["conditions"]);
			}

			return $conditions_count;
		}

	}

}