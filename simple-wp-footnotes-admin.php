<?php

if ( ! class_exists( 'Simple_WP_Footnotes_Admin' ) ) {

/**
 * So that themes and other plugins can customise the text domain, the Simple_WP_Footnotes_Admin should
 * not be initialized until after the plugins_loaded and after_setup_theme hooks.
 * However, it also needs to run early on the init hook.
 *
 * @author Jason Conroy <jason@findingsimple.com>
 * @package Simple WP Footnotes
 * @since 1.0
 */
function initialize_simple_wp_footnotes_admin() {
	Simple_WP_Footnotes_Admin::init();
}
add_action( 'init', 'initialize_simple_wp_footnotes_admin', -1 );


class Simple_WP_Footnotes_Admin {

	public static function init() {  

		/* create custom plugin settings menu */
		add_action( 'admin_menu',  __CLASS__ . '::simple_wp_footnotes_create_menu' );

	}

	public static function simple_wp_footnotes_create_menu() {

		//create new top-level menu
		add_options_page( 'Footnotes Settings', 'Footnotes', 'administrator', 'simple_wp_footnotes', __CLASS__ . '::simple_wp_footnotes_settings_page' );

		//call register settings function
		add_action( 'admin_init',  __CLASS__ . '::register_mysettings' );

	}


	public static function register_mysettings() {
	
		$page = 'simple_wp_footnotes-settings'; 

		// General settings
		
		add_settings_section( 
			'simple_wp_footnotes-general', 
			'General Settings',
			__CLASS__ . '::simple_wp_footnotes_general_callback',
			$page
		);
		
		add_settings_field(
			'simple_wp_footnotes-placement',
			'Placement',
			__CLASS__ . '::simple_wp_footnotes_placement_callback',
			$page,
			'simple_wp_footnotes-general'
		);
		
		// Includes settings
		
		add_settings_section( 
			'simple_wp_footnotes-includes', 
			'CSS and JS Includes',
			__CLASS__ . '::simple_wp_footnotes_includes_callback',
			$page
		);
		
		add_settings_field(
			'simple_wp_footnotes-toggle-css-include',
			'Toggle CSS enqueue in Head',
			__CLASS__ . '::simple_wp_footnotes_toggle_css_include_callback',
			$page,
			'simple_wp_footnotes-includes'
		);
		
		add_settings_field(
			'simple_wp_footnotes-toggle-js-include',
			'Toggle JS enqueue in Head',
			__CLASS__ . '::simple_wp_footnotes_toggle_js_include_callback',
			$page,
			'simple_wp_footnotes-includes'
		);

		//register our settings
		
		register_setting( $page, 'simple_wp_footnotes-placement' );

		register_setting( $page, 'simple_wp_footnotes-toggle-css-include' );
		register_setting( $page, 'simple_wp_footnotes-toggle-js-include' );

	}

	public static function simple_wp_footnotes_settings_page() {
	
		$page = 'simple_wp_footnotes-settings'; 
	
	?>
	<div class="wrap">
	
		<div id="icon-options-general" class="icon32"><br /></div><h2>Simple WP Footnotes Settings</h2>
		
		<?php settings_errors(); ?>
	
		<form method="post" action="options.php">
			
			<?php settings_fields( $page ); ?>
			
			<?php do_settings_sections( $page ); ?>
		
			<p class="submit">
				<input type="submit" class="button-primary" value="Save Changes" />
			</p>
		
		</form>
		
	</div>
	
	<?php 
	} 
	
	// General Settings Callbacks

	public static function simple_wp_footnotes_general_callback() {
		
		//do nothing
		
	}

	public static function simple_wp_footnotes_placement_callback() {
		
		$selected = ( get_option('simple_wp_footnotes-placement') ) ? esc_attr( get_option('simple_wp_footnotes-placement') ) : 'content';
		
		echo '<select name="simple_wp_footnotes-placement">';
		
		foreach ( Simple_WP_Footnotes_Admin::$placements as $placement )  :
		
			echo '<option value="' . $placement . '"';
			 if ( $placement == $selected ) echo ' selected="selected"';
			echo ' >' . $placement . '</option>';
		
		endforeach;
		
		echo '</select>';
		
	}
	
	// Includes Settings Callbacks
	
	public static function simple_wp_footnotes_includes_callback() {
		
		echo '<p>Use the checkboxes below to toggle whether or not you wish include the Simple WP Footnotes css and js. You may want to include them within an existing stylesheet or js file for performance reasons.</p>';
		
	}

	public static function simple_wp_footnotes_toggle_css_include_callback() {
	
		echo '<input name="simple_wp_footnotes-toggle-css-include" id="simple_wp_footnotes-toggle-css-include" type="checkbox" value="1" class="code" ' . checked( 1, get_option('simple_wp_footnotes-toggle-css-include'), false ) . ' /> Do <strong>not</strong> include CSS in <code>&lt;head&gt;</code>';
		
	}
	
	public static function simple_wp_footnotes_toggle_js_include_callback() {
	
		echo '<input name="simple_wp_footnotes-toggle-js-include" id="simple_wp_footnotes-toggle-js-include" type="checkbox" value="1" class="code" ' . checked( 1, get_option('simple_wp_footnotes-toggle-js-include'), false ) . ' /> Do <strong>not</strong> include JS before <code>&lt;/body&gt;</code>';
		
	}

	/**
	 * Placement Options
	 *
	 */	
	public static $placements = array( 
		'content',
		'page_links'
  	);

}

}


