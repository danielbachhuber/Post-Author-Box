<?php
/*
Plugin Name: Post Author Box
Plugin URI: http://www.danielbachhuber.com/
Description: Append or prepend author information to a post
Author: Daniel Bachhuber
Version: 0.1
Author URI: http://www.danielbachhuber.com/
*/

if ( !class_exists('post_author_box') ) {

class post_author_box {
	
	var $options_group = 'post_author_box_';
	var $options_group_name = 'post_author_box_options';
	var $settings_page = 'post_author_box_settings';	
	
	function __construct() {

	}
	
	function init() {

		$this->options = get_option( $this->options_group_name );
		
		if ( is_admin() ) {
			add_action( 'admin_menu', array(&$this, 'add_admin_menu_items'));
		}
		
	}
	
	function admin_init() {
		
		$this->register_settings();
		
	}
	
	function add_admin_menu_items() {
		
		add_submenu_page( 'options-general.php', 'Post Author Box Settings', 'Post Author Box', 'manage_options', 'post-author-box', array( &$this, 'settings_page' ) );			
		
	}
	
	function register_settings() {
		
		register_setting( $this->options_group, $this->options_group_name, array( &$this, 'settings_validate' ) );
		
		add_settings_section( 'post_author_box_default', 'Settings', null, $this->settings_page );
		add_settings_field( 'enabled', 'Enable Post Author Box', array(&$this, 'settings_enabled_option'), $this->settings_page, 'post_author_box_default' );
		add_settings_field( 'display_configuration', 'Display configuration', array(&$this, 'settings_display_configuration_option'), $this->settings_page, 'post_author_box_default' );		
		
		//add_settings_field( 'default_workflow_status', 'Default workflow status', array(&$this, 'default_workflow_status_option'), $this->settings_page, 'general' );
		
	}
	
	function settings_page() {

		?>                                   
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br/></div>

			<h2><?php _e('Post Author Box', 'post-author-box') ?></h2>

			<form action="options.php" method="post">

				<?php settings_fields( $this->options_group ); ?>
				<?php do_settings_sections( $this->settings_page ); ?>

				<p class="submit"><input name="submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" /></p>

			</form>
		</div>

	<?php
		
	}
	
	/**
	 * Setting for whether the post author box is enabled or not
	 */
	function settings_enabled_option() {
		$options = $this->options;
		echo '<select id="enabled" name="' . $this->options_group_name . '[enabled]">';
		echo '<option value="0">Disabled</option>';
		echo '<option value="1"';
		if ( $options['enabled'] == 1 ) { echo ' selected="selected"'; }
		echo '>Prepend to post</option>';
		echo '<option value="2"';
		if ( $options['enabled'] == 2 ) { echo ' selected="selected"'; }
		echo '>Append to post</option>';		
		echo '</select>';
	}
	
	/**
	 * Configure the output of the post author box using tokens
	 */
	function settings_display_configuration_option() {
		$options = $this->options;
		
		echo '<textarea id="display_configuration" name="' . $this->options_group_name . '[display_configuration]"';
		echo ' rows="5" cols="40">' . $options['display_configuration'] . '</textarea><br />';
		echo '<span class="description">Use tokens to determine the output of the author box.</span>';

	}	
	
	function settings_validate( $input ) {
		return $input;
	}
	
} // END: class post_author_box

global $post_author_box;
$post_author_box = new post_author_box();

// Core hooks to initialize the plugin
add_action( 'init', array( &$post_author_box, 'init' ) );
add_action( 'admin_init', array( &$post_author_box, 'admin_init' ) );


}

?>