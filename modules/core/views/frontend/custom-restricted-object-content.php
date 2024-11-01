<?php
get_header();

do_action('vpr_before_restricted_category_error_message');

$error_message = get_option('vpr_error_message', '');
$default_message = '<p>' . __('Sorry, you are not allowed to view this page', 'wp-conditional-post-restrictions') . '</p>';

$content = apply_filters(
		'vpr_restricted_category_message', !empty($error_message) ? $error_message : $default_message
);
?>
<style>
	.cpr-page-not-available-wrapper {
		width: 100%;
		max-width: 1100px;
		margin: 0 auto;
		float: none;
		text-align: center;
	}
</style>
<div class = "cpr-page-not-available-wrapper">
	<?php echo apply_filters('the_content', wp_kses_post($content)); ?>
</div>
<?php
do_action('vpr_after_restricted_category_error_message');

get_footer();


