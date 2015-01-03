<?php
/**
 * Represents the view for the administration dashboard.
 *
 * This includes the header, options, and other information that should provide
 * The User Interface to the end user.
 */
?>

<div class="wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<form method="post" action="options.php">

		<?php settings_fields( 'afpsdg-settings-group' ); ?>

		<?php do_settings_sections( 'amazon-fps-for-digital-goods' ); ?>

		<?php submit_button(); ?>

	</form>
</div>
