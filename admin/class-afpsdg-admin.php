<?php


if(!class_exists('AFPSDG_Admin')) {

	class AFPSDG_Admin {

		/**
		 * Instance of this class.
		 *
		 * @since    1.0.0
		 *
		 * @var      object
		 */
		protected static $instance = null;

		/**
		 * Slug of the plugin screen.
		 *
		 * @since    1.0.0
		 *
		 * @var      string
		 */
		protected $plugin_screen_hook_suffix = null;

		/**
		 * Initialize the plugin by loading admin scripts & styles and adding a
		 * settings page and menu.
		 *
		 * @since     1.0.0
		 */
		private function __construct() {

			/*
			 * Call $plugin_slug from public plugin class.
			 */
			$plugin = AFPSDG::get_instance();
			$this->plugin_slug = $plugin->get_plugin_slug();

			// Load admin style sheet and JavaScript.
			// add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
			// add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

			// Add the options page and menu item.
			add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

			// Add an action link pointing to the options page.
			$plugin_basename = plugin_basename( plugin_dir_path( realpath( dirname( __FILE__ ) ) ) . $this->plugin_slug . '.php' );
			add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );

			// register custom post type
			$OrdersAFPSDG = OrdersAFPSDG::get_instance();
			add_action( 'init', array($OrdersAFPSDG,'register_post_type'), 0 );

		}

		/**
		 * Return an instance of this class.
		 *
		 * @since     1.0.0
		 *
		 * @return    object    A single instance of this class.
		 */
		public static function get_instance() {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		/**
		 * Register and enqueue admin-specific style sheet.
		 *
		 * @since     1.0.0
		 *
		 * @return    null    Return early if no settings page is registered.
		 */
		public function enqueue_admin_styles() {

			if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
				return;
			}

			$screen = get_current_screen();
			if ( $this->plugin_screen_hook_suffix == $screen->id ) {
				wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), AFPSDG::VERSION );
			}

		}

		/**
		 * Register and enqueue admin-specific JavaScript.
		 *
		 * @TODO:
		 *
		 * - Rename "AFPSDG" to the name your plugin
		 *
		 * @since     1.0.0
		 *
		 * @return    null    Return early if no settings page is registered.
		 */
		public function enqueue_admin_scripts() {

			if ( ! isset( $this->plugin_screen_hook_suffix ) ) {
				return;
			}

			$screen = get_current_screen();
			if ( $this->plugin_screen_hook_suffix == $screen->id ) {
				wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), AFPSDG::VERSION );
			}

		}

		/**
		 * Register the administration menu for this plugin into the WordPress Dashboard menu.
		 *
		 * @since    1.0.0
		 */
		public function add_plugin_admin_menu() {

			/*
			 * Add a settings page for this plugin to the Settings menu.
			 *
			 * NOTE:  Alternative menu locations are available via WordPress administration menu functions.
			 *
			 *        Administration Menus: http://codex.wordpress.org/Administration_Menus
			 *
			 * @TODO:
			 *
			 * - Change 'Page Title' to the title of your plugin admin page
			 * - Change 'Menu Text' to the text for menu item for the plugin settings page
			 * - Change 'manage_options' to the capability you see fit
			 *   For reference: http://codex.wordpress.org/Roles_and_Capabilities
			 */
			$this->plugin_screen_hook_suffix = add_options_page(
				__( 'Amazon FPS for Digital Goods', $this->plugin_slug ),
				__( 'Amazon FPS for Digital Goods', $this->plugin_slug ),
				'manage_options',
				$this->plugin_slug,
				array( &$this, 'display_plugin_admin_page' )
			);
			add_action( 'admin_init', array(&$this,'register_settings') );

		}

		/**
		 * Register Admin page settings
		 *
		 * @since    1.0.0
		 */

		public function register_settings($value='')
		{
	        register_setting( 'afpsdg-settings-group', 'afpsdg-settings',array(&$this,'settings_sanitize_field_callback') );

	        add_settings_section('afpsdg-documentation', 'Plugin Documentation', array(&$this, 'general_documentation_callback'), $this->plugin_slug);
	        
		    add_settings_section( 'afpsdg-global-section', 'Global Settings', null , $this->plugin_slug );
		    add_settings_section( 'afpsdg-credentials-section', 'Aamazon FPS Credentials', null , $this->plugin_slug );

		    add_settings_field( 'checkout_url', 'Checkout Page URL', array(&$this,'settings_field_callback'), $this->plugin_slug, 'afpsdg-global-section', array('field'=>'checkout_url','desc'=>'This page is automatically created for you when you install the plugin.' ));
		    add_settings_field( 'currency_code', 'Currency Code', array(&$this,'settings_field_callback'), $this->plugin_slug, 'afpsdg-global-section', array('field'=>'currency_code','desc'=>'Example: USD, CAD etc', 'size'=>10) );
		    add_settings_field( 'button_text', 'Button Text', array(&$this,'settings_field_callback'), $this->plugin_slug, 'afpsdg-global-section', array('field'=>'button_text','desc'=>'Example: Buy Now, Pay Now etc.' ));

		    add_settings_field( 'is_live', 'Live Mode', array(&$this,'settings_field_callback'), $this->plugin_slug, 'afpsdg-credentials-section', array('field'=>'is_live','desc'=>'Check this to run the transaction in live mode. When unchecked it will run in sandbox mode.' ));
		    add_settings_field( 'aws_access_key_id', 'Aamazon FPS Access Key ID', array(&$this,'settings_field_callback'), $this->plugin_slug, 'afpsdg-credentials-section', array('field'=>'aws_access_key_id' ,'desc'=>'') );
		    add_settings_field( 'aws_secret_access_key', 'Aamazon FPS Secret Access Key', array(&$this,'settings_field_callback'), $this->plugin_slug, 'afpsdg-credentials-section', array('field'=>'aws_secret_access_key','desc'=>'' ));

		}

	        public function general_documentation_callback($args)
	        {
	            ?>
	            <div style="background: none repeat scroll 0 0 #FFF6D5;border: 1px solid #D1B655;color: #3F2502;margin: 10px 0;padding: 5px 5px 5px 10px;text-shadow: 1px 1px #FFFFFF;">
	            <p>Please read the
	            <a target="_blank" href="https://wp-ecommerce.net/wordpress-amazon-fps-plugin">Amazon FPS</a> plugin setup instructions to configure and use it.
	            </p>
	            </div>
	            <?php
	        }

	        /**
		 * Settings HTML
		 *
		 * @since    1.0.0
		 */
		public function settings_field_callback($args)
		{
			$settings = (array) get_option( 'afpsdg-settings' );

			extract($args);

		    $field_value = esc_attr( $settings[$field] );

		    if(empty($size))
		    	$size = 40;

			switch($field) {
				case 'is_live':
				    echo "<input type='checkbox' name='afpsdg-settings[{$field}]' value='1' ".($field_value ? 'checked=checked': '')." /><div style='font-size:11px;'>{$desc}</div>";
				    break;
				default:
				// case 'currency_code':
				// case 'button_text':
				// case 'aws_access_key_id':
				// case 'aws_secret_access_key':
				// case 'api_signature':
				    echo "<input type='text' name='afpsdg-settings[{$field}]' value='{$field_value}' size='{$size}' /> <div style='font-size:11px;'>{$desc}</div>";
					break;
			}

		}

		/**
		 * Validates the admin data
		 *
		 * @since    1.0.0
		 */
		public function settings_sanitize_field_callback($input)
		{
		    $output = get_option( 'afpsdg-settings' );

		    if(empty($input['is_live']))
		    	$output['is_live'] = 0;
		    else
		    	$output['is_live'] = 1;

		    if(empty($input['aws_access_key_id']) || empty($input['aws_secret_access_key']) ) {
		        add_settings_error( 'afpsdg-settings', 'invalid-credentials', 'You must fill all API credentials for plugin to work correctly.' );
		    }


		    if(!empty($input['checkout_url']))
		    	$output['checkout_url'] = $input['checkout_url'];
		    else
		    	add_settings_error( 'afpsdg-settings', 'invalid-checkout_url', 'Please specify a checkout page.' );

		    if(!empty($input['button_text']))
		    	$output['button_text'] = $input['button_text'];
		    else
		    	add_settings_error( 'afpsdg-settings', 'invalid-button-text', 'Button text should not be empty.' );

		    if(!empty($input['currency_code']))
		    	$output['currency_code'] = $input['currency_code'];
		    else
		    	add_settings_error( 'afpsdg-settings', 'invalid-currency-code', 'You must specify payment curency.' );

		    if(!empty($input['aws_access_key_id']))
		    	$output['aws_access_key_id'] = $input['aws_access_key_id'];

		    if(!empty($input['aws_secret_access_key']))
		    	$output['aws_secret_access_key'] = $input['aws_secret_access_key'];

		    return $output;
	   	}

		/**
		 * Render the settings page for this plugin.
		 *
		 * @since    1.0.0
		 */
		public function display_plugin_admin_page() {
			include_once( 'views/admin.php' );
		}

		/**
		 * Add settings action link to the plugins page.
		 *
		 * @since    1.0.0
		 */
		public function add_action_links( $links ) {

			return array_merge(
				array(
					'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
				),
				$links
			);

		}

	}
}
