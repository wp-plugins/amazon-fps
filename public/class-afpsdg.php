<?php

/**
 * Plugin class. This class should ideally be used to work with the
 * public-facing side of the WordPress site.
 *
 * If you're interested in introducing administrative or dashboard
 * functionality, then refer to `class-plugin-name-admin.php`
 *
 *
 */
class AFPSDG {

	/**
	 * Plugin version, used for cache-busting of style and script file references.
	 *
	 * @since   1.0.0
	 *
	 * @var     string
	 */
	const VERSION = '1.0.0';

	/**
	 *
	 * Unique identifier for your plugin.
	 *
	 * The variable name is used as the text domain when internationalizing strings
	 * of text. Its value should match the Text Domain file header in the main
	 * plugin file.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_slug = 'amazon-fps-for-digital-goods';

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	private $settings = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {
		$this->settings = (array) get_option('afpsdg-settings');

		// Load plugin text domain
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		// Activate plugin when new blog is added
		add_action( 'wpmu_new_blog', array( $this, 'activate_new_site' ) );

		// Load public-facing style sheet and JavaScript.
		// add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		// add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'after_switch_theme', array( $this, 'rewrite_flush' ) );
	}

	public function get_setting($field) {
		if(isset($this->settings[$field]))
			return $this->settings[$field];
		return false;
	}

	/**
	 * Return the plugin slug.
	 *
	 * @since    1.0.0
	 *
	 * @return    Plugin slug variable.
	 */
	public function get_plugin_slug() {
		return $this->plugin_slug;
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
	 * Fired when the plugin is activated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Activate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       activated on an individual blog.
	 */
	public static function activate( $network_wide ) {
 
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide  ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {
					switch_to_blog( $blog_id );
					self::single_activate();
				}

				restore_current_blog();

			} else {
				self::single_activate();
			}

		} else {
			self::single_activate();
		}

	}

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 *
	 * @param    boolean    $network_wide    True if WPMU superadmin uses
	 *                                       "Network Deactivate" action, false if
	 *                                       WPMU is disabled or plugin is
	 *                                       deactivated on an individual blog.
	 */
	public static function deactivate( $network_wide ) {

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {

			if ( $network_wide ) {

				// Get all blog ids
				$blog_ids = self::get_blog_ids();

				foreach ( $blog_ids as $blog_id ) {

					switch_to_blog( $blog_id );
					self::single_deactivate();

				}

				restore_current_blog();

			} else {
				self::single_deactivate();
			}

		} else {
			self::single_deactivate();
		}

	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @since    1.0.0
	 *
	 * @param    int    $blog_id    ID of the new blog.
	 */
	public function activate_new_site( $blog_id ) {

		if ( 1 !== did_action( 'wpmu_new_blog' ) ) {
			return;
		}

		switch_to_blog( $blog_id );
		self::single_activate();
		restore_current_blog();

	}

	/**
	 * Get all blog ids of blogs in the current network that are:
	 * - not archived
	 * - not spam
	 * - not deleted
	 *
	 * @since    1.0.0
	 *
	 * @return   array|false    The blog ids, false if no matches.
	 */
	private static function get_blog_ids() {

		global $wpdb;

		// get an array of blog ids
		$sql = "SELECT blog_id FROM $wpdb->blogs
			WHERE archived = '0' AND spam = '0'
			AND deleted = '0'";

		return $wpdb->get_col( $sql );

	}

	/**
	 * Fired for each blog when the plugin is activated.
	 *
	 * @since    1.0.0
	 */
	private static function single_activate() {
        // Check if its a first install
            $default = array (
                'is_live' => 0,
                'currency_code' => 'USD',
                'button_text' => 'Buy Now',
                'api_username' => 'xyz.biz_api1.abc.com',
                'api_password' => '1234567891',
                'api_signature' => 'xxxxxxx.xxxxxxxxxxxxxxx.xxxxxxxxxxxxxx-xxxxxxx',
                'checkout_url' => site_url('checkout' )
            );
	    $alreadyInstalled =get_option('afpsdg-settings');
	    if(empty($alreadyInstalled)){
	        add_option( 'afpsdg-settings', $default );
	    }	
            //create checkout page           
            $args = array(
                'post_type' => 'page'
            );
            $pages = get_pages($args);
            $checkout_page_id = '';
            foreach ($pages as $page) {
                if(strpos($page->post_content,'afpsdg_checkout') !== false){     
                    $checkout_page_id = $page->ID;
                }
            }
            if ($checkout_page_id == '') {
                $checkout_page_id = AFPSDG::create_post('page', 'Checkout', 'afpsdg-checkout', '[afpsdg_checkout]');
                $checkout_page = get_post($checkout_page_id);
                $checkout_page_url = $checkout_page->guid;
                $afpsdg_settings = get_option('afpsdg-settings');
                if(!empty($afpsdg_settings)){
                    $afpsdg_settings['checkout_url'] = $checkout_page_url;
                    update_option('afpsdg-settings', $afpsdg_settings);
                }
            }
            
	}

        public static function create_post($postType, $title, $name, $content, $parentId = NULL)
        {
            $post = array(
                'post_title' => $title,
                'post_name' => $name,
                'comment_status' => 'closed',
                'ping_status' => 'closed',
                'post_content' => $content,
                'post_status' => 'publish',
                'post_type' => $postType
            );

            if ($parentId !== NULL){
                    $post['post_parent'] = $parentId;
            }        
            $postId = wp_insert_post($post);
            return $postId;
        }
	/**
	 * Fired for each blog when the plugin is deactivated.
	 *
	 * @since    1.0.0
	 */
	private static function single_deactivate() {
		// @TODO: Define deactivation functionality here
	}

	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		$domain = $this->plugin_slug;
		$locale = apply_filters( 'plugin_locale', get_locale(), $domain );

		load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );
		load_plugin_textdomain( $domain, FALSE, basename( plugin_dir_path( dirname( __FILE__ ) ) ) . '/languages/' );

	}

	/**
	 * @since    1.0.0
	 */
	public function rewrite_flush()
	{
		flush_rewrite_rules();
	}

	// public function get_plugin_slug()
	// {
	// 	return $this->plugin_slug;
	// }

}
