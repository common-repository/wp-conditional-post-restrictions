<?php

add_filter( 'vpr_conditions_groups_html_options', 'wpcpr_tease_premium_condition_options' );
function wpcpr_tease_premium_condition_options(  $options_html  ) {
    $teaser = '<optgroup class="posts" label="Posts (Pro)">
      <option disabled value="post_viewed_count">Post viewed count</option>
   </optgroup>
   <optgroup class="user" label="User (Pro)">
      <option disabled value="days_since_registration_date">Days since registration date</option>
      <option disabled value="user_email">User email</option>
      <option disabled value="user_role">User role</option>
      <option disabled value="users">User</option>
   </optgroup>
   <optgroup class="date" label="Date (Pro)">
      <option disabled value="full_date">Date (YYYY-MM-DD)</option>
      <option disabled value="hour_day">Hour of the day (number from 0 to 23)</option>
      <option disabled value="month_day">Day of the month (number from 1 to 31)</option>
      <option disabled value="week_day">Day of the week</option>
   </optgroup>
';
    if ( function_exists( 'bp_xprofile_get_groups' ) ) {
        $teaser .= '<optgroup class="buddypress" label="BuddyPress (Pro)">
      <option disabled value="bp_user_belongs_group">User belongs to group</option>
   </optgroup>';
    }
    if ( defined( 'LEARNDASH_VERSION' ) ) {
        $teaser .= '
   <optgroup class="learndash" label="LearnDash (Pro)">
      <option disabled value="ld_user_enrolled_course">User is enrolled to course</option>
      <option disabled value="learndash_total_enrolled_courses">User enrolled courses count</option>
   </optgroup>';
    }
    if ( class_exists( 'LearnPress' ) ) {
        $teaser .= '<optgroup class="learnpress" label="LearnPress (Pro)">
      <option disabled value="lp_user_enrolled_course">User is enrolled to course</option>
      <option disabled value="learnpress_total_enrolled_courses">User enrolled courses count</option>
   </optgroup>';
    }
    if ( defined( 'TUTOR_VERSION' ) ) {
        $teaser .= '  <optgroup class="tutorlms" label="TutorLMS (Pro)">
      <option disabled value="tlms_user_enrolled_course">User is enrolled to course</option>
      <option disabled value="tutorlms_total_enrolled_courses">User enrolled courses count</option>
   </optgroup>';
    }
    if ( class_exists( 'Easy_Digital_Downloads' ) ) {
        $teaser .= '   <optgroup class="edd" label="Easy Digital Downloads (Pro)">
      <option disabled value="edd_gross_revenue_from_the_customer">Gross revenue from the customer</option>
      <option disabled value="edd_total_orders_from_the_customer">Total orders from the customer</option>
      <option disabled value="edd_user_has_purchased_product">User has purchased product</option>
   </optgroup>';
    }
    if ( class_exists( 'Give' ) ) {
        $teaser .= '   <optgroup class="givewp" label="GiveWP (Pro)">
      <option disabled value="give_total_donations_from_user">User donations count</option>
      <option disabled value="give_total_money_donated_by_user">User donations amount</option>
      <option disabled value="give_user_donated_form">User has donated for form</option>
   </optgroup>';
    }
    if ( function_exists( 'WC' ) ) {
        $teaser .= '<optgroup class="woocommerce" label="WooCommerce (Pro)"><option disabled value="city">Billing city</option><option disabled value="billing_company">Billing company</option><option disabled value="country">Billing country</option><option disabled value="billing_email">Billing email</option><option disabled value="state">Billing state</option><option disabled value="zipcode">Billing zipcode</option><option disabled value="gross_revenue_from_the_customer">Gross revenue from the customer</option><option disabled value="shipping_city">Shipping city</option><option disabled value="shipping_company">Shipping company</option><option disabled value="shipping_country">Shipping Country</option><option disabled value="shipping_state">Shipping state</option><option disabled value="shipping_zipcode">Shipping zipcode</option><option disabled value="total_orders_from_the_customer">Total orders from the customer</option><option disabled value="total_reviews_from_the_customer">Total reviews from the customer</option><option disabled value="user_has_purchased_product">User has purchased product</option></optgroup>';
    }
    if ( class_exists( 'WP_Ultimo' ) ) {
        $teaser .= '   <optgroup class="wpultimo" label="WP Ultimo (Pro)">
      <option disabled value="wpu_site_plan">User site plan</option>
      <option disabled value="wpu_sites_count">User sites count</option>
   </optgroup>';
    }
    $options_html .= $teaser;
    return $options_html;
}

add_action( 'vpr_/metabox/after_conditions_group', 'wpcpr_tease_invite_pro_after_condition_groups' );
function wpcpr_tease_invite_pro_after_condition_groups() {
    ?>
		<?php 
    printf( __( '<p><b>Go Premium</b>. You can use all conditions, including: Drip or Paywall content, restrict by user information (zip, state, city, address), date ranges, purchase history, integration with LearnDash/LearnPress/TutorLMS, and more. <a href="%s" target="_blank" class="wpcpr-go-premium-link">%s</a></p>', 'wp-conditional-post-restrictions' ), WPCPR()->args['buy_url'], WPCPR()->args['buy_text'] );
    ?>
		<?php 
}

add_filter( 'vpr_supported_post_types', 'wpcpr_tease_post_types' );
function wpcpr_tease_post_types(  $post_types  ) {
    $post_types = array(
        'post' => $post_types['post'],
    );
    return $post_types;
}

add_action( 'vpr/post_types/after_select_rendered', 'wpcpr_after_post_types_select_rendered' );
function wpcpr_after_post_types_select_rendered() {
    $all_post_types = get_post_types( array(
        'show_ui' => true,
    ), 'objects' );
    unset($all_post_types['post']);
    $labels = wp_list_pluck( $all_post_types, 'label' );
    ?>
		<script>
			jQuery(document).ready(function () {
				var $select = jQuery('.vpr-post-type-selector');

				var lockedOptions = <?php 
    echo json_encode( array_values( $labels ) );
    ?>;
				var options = '';
				lockedOptions.forEach(function (label) {
					options += '<option disabled value="' + label + '">' + label + '</option>';
				});
				if (options) {
					$select.append('<optgroup label="Premium">' + options + '</optgroup>');
				}
			});
		</script>
		<?php 
}

add_action( 'add_meta_boxes', 'wpcpr_register_metabox_teasers' );
function wpcpr_register_metabox_teasers() {
    $post_types_keys = get_post_types( array(
        'public' => true,
    ) );
    $post_index = array_search( 'post', $post_types_keys );
    if ( $post_index !== false && isset( $post_types_keys[$post_index] ) ) {
        unset($post_types_keys[$post_index]);
    }
    add_meta_box(
        'vpr_conditions_rules',
        __( 'Restrictions rules', 'wp-conditional-post-restrictions' ),
        'wpcpr_render_meta_box_rules_fields_teaser',
        $post_types_keys
    );
}

function wpcpr_render_meta_box_rules_fields_teaser() {
    ?>
		<div class="vpr-field">
			<input id="_vpr_post_conditions_enabled" name="_vpr_post_conditions_enabled" type="checkbox" value="1" disabled >
			<label for="_vpr_post_conditions_enabled"><?php 
    _e( 'Enable content restrictions?', 'wp-conditional-post-restrictions' );
    ?></label> 
			<?php 
    printf( __( '<p>The free version of the plugin only works with posts. This requires the premium version. <a href="%s" target="_blank" class="wpcpr-go-premium-link">%s *</a></p>', 'wp-conditional-post-restrictions' ), WPCPR()->args['buy_url'], WPCPR()->args['buy_text'] );
    if ( !empty( WPCPR()->args['free_offer_expiration_date'] ) && date( 'Y-m-d' ) < WPCPR()->args['free_offer_expiration_date'] ) {
        ?>
				<p><?php 
        _e( '* The offer to use the premium plugin for free is limited to 100 users', 'wp-conditional-post-restrictions' );
        ?></p>
			<?php 
    }
    ?>
		</div>
		<?php 
}
