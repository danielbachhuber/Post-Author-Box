<?php
/*
Plugin Name: Post Author Box
Plugin URI: http://www.danielbachhuber.com/
Description: Append or prepend author information to a post
Author: Daniel Bachhuber
Version: 0.9
Author URI: http://www.danielbachhuber.com/
*/

define('POST_AUTHOR_BOX_FILE_PATH', __FILE__);

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
			add_action( 'admin_menu', array(&$this, 'add_admin_menu_items') );
		} else {
			add_filter( 'the_content', array(&$this, 'filter_the_content') );
		}
		
	}
	
	function admin_init() {
		
		$this->register_settings();
		
	}
	
	/**
	 * Default settings for when the plugin is activated for the first time
	 */ 
	function activate_plugin() {
		$options = $this->options;
		if ( $options['activated_once'] != 'on' ) {
			$options['activated_once'] = 'on';
			$options['enabled'] = 0;
			$options['display_configuration'] = '<p>Contact %display_name% at <a href="mailto:%email%">%email%</a></p>';
			$options['apply_to'] = 1;
			update_option( $this->options_group_name, $options );
		}
	}
	
	/**
	 * Any admin menu items we need
	 */
	function add_admin_menu_items() {
		
		add_submenu_page( 'options-general.php', 'Post Author Box Settings', 'Post Author Box', 'manage_options', 'post-author-box', array( &$this, 'settings_page' ) );			
		
	}
	
	/**
	 * Register all Post Author Box settings
	 */
	function register_settings() {
		
		register_setting( $this->options_group, $this->options_group_name, array( &$this, 'settings_validate' ) );
		
		add_settings_section( 'post_author_box_default', 'Settings', null, $this->settings_page );
		add_settings_field( 'enabled', 'Enable Post Author Box', array(&$this, 'settings_enabled_option'), $this->settings_page, 'post_author_box_default' );
		add_settings_field( 'display_configuration', 'Display configuration', array(&$this, 'settings_display_configuration_option'), $this->settings_page, 'post_author_box_default' );
		add_settings_field( 'apply_to', 'Apply to posts, pages, or both', array(&$this, 'settings_apply_to_option'), $this->settings_page, 'post_author_box_default' );	
		
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
		echo ' rows="6" cols="50">' . $options['display_configuration'] . '</textarea><br />';
		echo '<span class="description">Use HTML and tokens to determine the presentation of the author box. Available tokens include: %display_name%, %first_name%, %last_name%, %description%, %email%, %avatar%, %jabber%, %aim%, %post_date%</span>';

	}
	
	/**
	 * Determine whether post author box is applied to a post, page, or both
	 */
	function settings_apply_to_option() {
		$options = $this->options;
		echo '<select id="apply_to" name="' . $this->options_group_name . '[apply_to]">';
		echo '<option value="1"';
		if ( $options['apply_to'] == 1 ) { echo ' selected="selected"'; }
		echo '>Posts</option>';
		echo '<option value="2"';
		if ( $options['apply_to'] == 2 ) { echo ' selected="selected"'; }
		echo '>Pages</option>';
		echo '<option value="3"';
		if ( $options['apply_to'] == 3 ) { echo ' selected="selected"'; }
		echo '>Both</option>';		
		echo '</select>';
	}
	
	/**
	 * Validation and sanitization on the settings field
	 */
	function settings_validate( $input ) {
		
		// Sanitize input for display_configuration
		$allowable_tags = '<div><p><span><a><img><cite><code><h1><h2><h3><h4><h5><h6><hr><br><b><strong><i><em><ol><ul><blockquote><li>';
		$input['display_configuration'] = strip_tags( $input['display_configuration'], $allowable_tags );
		return $input;
		
	}
	
	/**
	 * Append or prepend the Post Author Box on a post or page
	 */
	function filter_the_content( $the_content ) {
		global $post;
		$options = $this->options;
		$user = get_userdata( $post->post_author );
		
		// All of the various tokens we support
		$search = array(	'%display_name%',
							'%first_name%',
							'%last_name%',
							'%description%',
							'%email%',
							'%avatar%',
							'%jabber%',
							'%aim%',
							'%post_date%'
						);
		$replace = array(	$user->display_name,
							$user->first_name,
							$user->last_name,
							$user->description,
							$user->user_email,
							get_avatar( $post->post_author ),
							$user->jabber,
							$user->aim,
							get_the_time( get_option( 'date_format' ), $post->ID )
					);
		
		$post_author_box = str_replace( $search, $replace, $options['display_configuration'] );
		$post_author_box = '<div class="post_author_box">' . $post_author_box . '</div>';
		
		// @todo This is a nast logic mess. Is there a better way to do it?
		if ( $options['enabled'] ) {
			
			if ( (is_single( $post->ID ) && ($options['apply_to'] == 1 || $options['apply_to'] == 3)) || is_page( $post->ID ) && ($options['apply_to'] == 2 || $options['apply_to'] == 3) ) {
				if ( $options['enabled'] == 1 ) {
					$the_content = $post_author_box . $the_content;
				} else if ( $options['enabled'] == 2 ) {
					$the_content .= $post_author_box;
				}
			}
			
		}
		return $the_content;
		
	}
	
} // END: class post_author_box

global $post_author_box;
$post_author_box = new post_author_box();

// Core hooks to initialize the plugin
add_action( 'init', array( &$post_author_box, 'init' ) );
add_action( 'admin_init', array( &$post_author_box, 'admin_init' ) );

// Hook to perform action when plugin activated
register_activation_hook( POST_AUTHOR_BOX_FILE_PATH, array(&$post_author_box, 'activate_plugin') );

}

?>