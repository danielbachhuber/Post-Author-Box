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
		global $wpdb;
		
		$this->options = get_option( $this->options_group_name );
	}
	
	function init() {		
		
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
		
		/* General */
		add_settings_section( 'general', 'General', array(&$this, 'settings_section'), $this->settings_page );
		add_settings_field( 'enable', 'Enable Post Author Box', array(&$this, 'settings_enabled_option'), $this->settings_page, 'general' );
		add_settings_field( 'post_author_box', 'Display configuration', array(&$this, 'settings_post_author_box_option'), $this->settings_page, 'general' );		
		
		//add_settings_field( 'default_workflow_status', 'Default workflow status', array(&$this, 'default_workflow_status_option'), $this->settings_page, 'general' );
		
	}
	
	function settings_page() {
		$msg = null;
		if ( array_key_exists( 'updated', $_GET ) && $_GET['updated']=='true' ) { 
			$msg = __('Settings saved', 'post-author-box');
		}

		?>                                   
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br/></div>

			<?php if ( $msg ) : ?>
 				<div class="updated fade" id="message">
					<p><strong><?php echo $msg ?></strong></p>
				</div>
			<?php endif; ?>

			<h2><?php _e('Post Author Box Settings', 'post-author-box') ?></h2>

			<form action="options.php" method="post">

				<?php settings_fields( $this->options_group ); ?>
				<?php do_settings_sections( $this->settings_page ); ?>

				<p class="submit"><input name="submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes'); ?>" /></p>

			</form>
		</div>

	<?php
		
	}
	
	function settings_section() {
		
	}
	
	/**
	 * Setting for whether the post author box is enabled or not
	 */
	function settings_enabled_option() {
		$options = $this->options;
		echo '<select id="post_author_box_enabled" name="' . $this->options_group_name . '[post_author_box_enabled]">';
		echo '<option value="0">Disabled</option>';
		echo '<option value="1"';
		if ( $options['post_author_box_enabled'] == 1 ) { echo ' selected="selected"'; }
		echo '>Prepend to post</option>';
		echo '<option value="2"';
		if ( $options['post_author_box_enabled'] == 2 ) { echo ' selected="selected"'; }
		echo '>Append to post</option>';		
		echo '</select>';
	}
	
	function settings_post_author_box_option() {
		$options = $this->options;

	}	
	
	function settings_validate() {
		
	}
	
} // END: class post_author_box

global $post_author_box;
$post_author_box = new post_author_box();

// Core hooks to initialize the plugin
add_action( 'init', array( &$post_author_box, 'init' ) );
add_action( 'admin_init', array( &$post_author_box, 'admin_init' ) );


}

?>