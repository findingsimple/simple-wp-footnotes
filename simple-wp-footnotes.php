<?php 
/*
Plugin Name: Simple WP Footnotes
Plugin URI: http://plugins.findingsimple.com
Description: Adds footnotes to your WP content.
Version: 1.0
Author: Finding Simple (Jason Conroy & Brent Shepherd)
Author URI: http://findingsimple.com
License: GPL2
*/
/*
Copyright 2008 - 2012  Finding Simple  (email : plugins@findingsimple.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! class_exists( 'Simple_WP_Footnotes' ) ) : 

/**
 * So that themes and other plugins can customise the text domain, the Simple_WP_Footnotes
 * should not be initialized until after the plugins_loaded and after_setup_theme hooks.
 * However, it also needs to run early on the init hook.
 *
 * @author Jason Conroy <jason@findingsimple.com>
 * @package Simple WP Footnotes
 * @since 1.0
 */
function initialize_wp_footnotes(){
	Simple_WP_Footnotes::init();
}
add_action( 'init', 'initialize_wp_footnotes', -1 ); 

/**
 * Plugin Main Class.
 *
 * @package Simple WP Footnotes
 * @since 1.0
 */
class Simple_WP_Footnotes {

	const TINYMCE_PLUGIN_NAME = 'simple_wp_footnotes';

	private static $footnote_fields;

	/**
	 * Initialize the class
	 *
	 * @since 1.0
	 */
	public static function init() {
	
		self::$footnote_fields = array(
			'footnote_number'     => __( 'Footnote Number' ),
			'footnote_text'  => __( 'Footnote Text' )
		);
		
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'enqueue_styles_and_scripts'), 100 );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_admin_styles_and_scripts' ) );
		
		add_action( 'admin_footer', array( __CLASS__, 'the_jquery_dialog_markup' ) );
		
		add_filter( 'mce_external_plugins', array( __CLASS__, 'register_tinymce_plugin' ) );

		add_filter( 'mce_buttons', array( __CLASS__, 'register_tinymce_button' ) );

		//add_shortcode( 'ref', array( __CLASS__, 'shortcode_footnote') );

	}

	/**
	 * Add scripts and styles
	 *
	 * @since 1.0
	 */
	public static function enqueue_styles_and_scripts(){
		
		if ( !is_admin() ) {
		
			if ( get_option('simple_wp_footnotes-toggle-js-include') != 1 )
				wp_enqueue_script( 'simple-wp-footnotes', self::get_url( '/js/simple-wp-footnotes.min.js', __FILE__ ) ,'jquery','1.0',true );
			
			if ( get_option('simple_wp_footnotes-toggle-css-include') != 1 )
				wp_enqueue_style( 'simple-wp-footnotes', self::get_url( '/css/simple-wp-footnotes.min.css', __FILE__ ) );
		
		}
		
	}
	
	/**
	 * Add admin scripts and styles
	 *
	 * @since 1.0
	 */
	public static function enqueue_admin_styles_and_scripts() {
		
		global $pagenow;
		
		if ( is_admin() && $pagenow == 'post-new.php' || $pagenow == 'post.php' ) {

			wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_script( 'jquery-ui-tabs' );
			wp_enqueue_style( 'wp-jquery-ui-dialog' );
	
			wp_register_style( 'simple-wp-footnotes-admin', self::get_url( '/css/simple-wp-footnotes-admin.css', __FILE__ ) , false, '1.0' );
			wp_enqueue_style( 'simple-wp-footnotes-admin' );
	
			wp_enqueue_script( 'simple-wp-footnotes-admin', self::get_url( '/js/simple-wp-footnotes-admin.js', __FILE__ ) );
	
			wp_localize_script( 'simple-wp-footnotes-admin', 'footnoteFields', self::$footnote_fields );
		
		}
		
	}
	
	/**
	 * Register TinyMCE button.
	 *
	 * @see wp-includes/class-wp-editor.php
	 * @link http://www.tinymce.com/wiki.php/Buttons/controls
	 * @since 1.0
	 *
	 * @param array $buttons Filter supplied array of buttons to modify
	 * @return array The modified array with buttons
	 */
	public static function register_tinymce_button( $buttons ) {

		array_push( $buttons, 'separator', self::TINYMCE_PLUGIN_NAME );

		return $buttons;
	}

	/**
	 * Register TinyMCE plugin.
	 *
	 * Adds the absolute URL for the TinyMCE plugin to the associative array of plugins. Array structure: 'plugin_name' => 'plugin_url'
	 *
	 * @see		wp-includes/class-wp-editor.php
	 * @since 1.0
	 *
	 * @param	array $plugins Filter supplied array of plugins to modify
	 * @return	array The modified array with plugins
	 */
	public static function register_tinymce_plugin( $plugins ) {

		$plugins[ self::TINYMCE_PLUGIN_NAME ] = self::get_url( '/tinymce/editor_plugin.js?ver=1.0', __FILE__ );

		return $plugins;
	}



	/**
	 * Build jQuery UI Window.
	 *
	 * Creates the jQuery for Post Editor popup window and the
	 * form fields to enter variables.
	 *
	 * @since 1.0
	 */
	public static function the_jquery_dialog_markup() {

		$screen = get_current_screen();

		if ($screen->base != 'post')
				return;
?>
<div class="hidden">
	<div id="simple-wp-footnotes" title="Insert Footnote">
		<div id="footnote-details" style="margin: 1em;">

		<?php foreach ( self::$footnote_fields as $field_id => $field_label ) : ?>
		<?php if ( $field_id != 'footnote_text' ) { ?>
		<label for="simple-<?php echo $field_id; ?>" style="display: inline-block; width: 90%; margin: 2px;">
			<?php echo $field_label; ?>
			<input type="text" id="simple-<?php echo $field_id; ?>" name="simple-<?php echo $field_id; ?>" value=""  style="width: 75%; float: right;"/>
		</label>
		<?php } else { ?>
		<label for="simple-<?php echo $field_id; ?>" style="display: inline-block; width: 90%; margin: 2px;">
			<?php echo $field_label; ?>
			<textarea id="simple-<?php echo $field_id; ?>" name="simple-<?php echo $field_id; ?>" style="width: 75%; float: right;"></textarea>
		</label>		
		<?php } ?>
		<?php endforeach; ?>

		</div><!-- #footnote-details -->
	</div><!-- #simple-wp-footnotes -->
</div><!-- .hidden -->
<?php
	}

	/**
	 * Replaces WP autop formatting 
	 *
	 * @since 1.0
	 */
	public static function simple_remove_wpautop($content) { 
	
		$content = do_shortcode( shortcode_unautop( $content ) ); 
		
		$content = preg_replace( '#^<\/p>|^<br \/>|<p>$#', '', $content);
		
		return $content;
		
	}
	
	/**
	 * Helper function to get the URL of a given file. 
	 * 
	 * As this plugin may be used as both a stand-alone plugin and as a submodule of 
	 * a theme, the standard WP API functions, like plugins_url() can not be used. 
	 *
	 * @since 1.0
	 * @return array $post_name => $post_content
	 */
	public static function get_url( $file ) {

		// Get the path of this file after the WP content directory
		$post_content_path = substr( dirname( __FILE__ ), strpos( __FILE__, basename( WP_CONTENT_DIR ) ) + strlen( basename( WP_CONTENT_DIR ) ) );

		// Return a content URL for this path & the specified file
		return content_url( $post_content_path . $file );
	}	
	
}
 
endif;