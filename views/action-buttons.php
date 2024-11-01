<div class="wpcpr-intro">
	<?php 
?>

		<a href="<?php 
echo esc_url( WPCPR()->args['buy_url'] );
?>" class="button"><span class="dashicons dashicons-cart"></span> <?php 
echo esc_html( WPCPR()->args['buy_text'] );
?></a>
		<?php 
if ( !empty( WPCPR()->args['free_offer_expiration_date'] ) && date( 'Y-m-d' ) >= WPCPR()->args['free_offer_expiration_date'] ) {
    ?>
			- <a href="<?php 
    echo esc_url( wpcpr_fs()->checkout_url( WPCPR()->args['default_billing_period'], false ) );
    ?>" class="button button-primary"><span class="dashicons dashicons-cart"></span> <?php 
    _e( 'Buy premium plugin', 'wp-conditional-post-restrictions' );
    ?></a>
		<?php 
}
?>
	<?php 
?>
	<a href="<?php 
echo esc_url( wpcpr_fs()->contact_url() );
?>" class="button"><span class="dashicons dashicons-editor-help"></span> <?php 
_e( 'Need help? Contact us', 'wp-conditional-post-restrictions' );
?></a>
</div>
<?php 
?>
	<?php 
if ( !empty( WPCPR()->args['free_offer_expiration_date'] ) && date( 'Y-m-d' ) < WPCPR()->args['free_offer_expiration_date'] ) {
    ?>
		<p style="text-align: center;">
			<?php 
    _e( '<b style=" font-size: 16px; ">Attention: Use our PREMIUM PLUGIN FOR FREE for one year.</b>
			<br>We only ask you to report to us any feedback or features that you need so we can improve the plugin. This offer will end on October 31st, 2020 and it\'s limited to 100 users.', 'wp-conditional-post-restrictions' );
    ?></p>
	<?php 
}
?>
<style>
	h2 {
		text-align: center;
	}
	.wpcpr-conditions-list {
		max-width: 670px;
	}
	.wpcpr-intro {
		border-top: 1px solid #d2d2d2;
		border-bottom: 1px solid #d2d2d2;
		text-align: center;
		padding: 8px 0;
	}
	.wpcpr-intro .dashicons {
		margin-top: 3px; 
	}
</style>