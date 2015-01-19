<?php

if ( ! defined( 'ABSPATH' ) ) exit;

class Superfero {

	/**
	 * The single instance of Superfero.
	 * @var 	object
	 * @access  private
	 * @since 	1.0.0
	 */
	private static $_instance = null;

	/**
	 * Settings class object
	 * @var     object
	 * @access  public
	 * @since   1.0.0
	 */
	public $settings = null;

	/**
	 * The version number.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_version;

	/**
	 * The token.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $_token;

	/**
	 * The main plugin file.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $file;

	/**
	 * The main plugin directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $dir;

	/**
	 * The plugin assets directory.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_dir;

	/**
	 * The plugin assets URL.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $assets_url;

	/**
	 * Suffix for Javascripts.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
	public $script_suffix;

	/**
	 * The page title.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
    public $page_title;

	/**
	 * The page name.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
    public $page_name;

	/**
	 * The page id.
	 * @var     string
	 * @access  public
	 * @since   1.0.0
	 */
    public $page_id;

	public function __construct ( $file = '' ) {
		$this->_version = SUPERFERO_VERSION;
		$this->_token = SUPERFERO_TOKEN;
		$this->script_suffix = '';
		$this->page_title = SUPERFERO_PAGE_TITLE;
		$this->page_name  = SUPERFERO_TOKEN;
		$this->page_id    = '0';

		$this->file = $file;
		$this->dir = dirname( $this->file );
		$this->assets_dir = trailingslashit( $this->dir ) . 'assets';
		$this->assets_url = esc_url( trailingslashit( plugins_url( '/assets/', $this->file ) ) );


		register_activation_hook( $this->file, array( $this, 'activate' ) );
		register_deactivation_hook( $this->file, array( $this, 'deactivate' ) );
		register_uninstall_hook( $this->file, array( $this, 'uninstall' ) );

		add_filter('parse_query', array($this, 'query_parser'));
	    
		// Load frontend JS & CSS
		add_action(	'wp_head', array( $this, 'superfero_custom_js' ), 0 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 10 );

		// Handle localisation
		$this->load_plugin_textdomain();
		add_action( 'init', array( $this, 'load_localisation' ), 0 );

		add_action("wp_ajax_superfero_campaign",  array( $this, 'superfero_campaign' ));
		add_action("wp_ajax_nopriv_superfero_campaign", array( $this, 'superfero_campaign' ));

	} // Edn __construct ()

	public function superfero_custom_js() {
		global $wp_query;
		$script = '';
    	$page_id = get_option( SUPERFERO_OPTION . 'page_id' );
		$current_page_id = $wp_query->get_queried_object_id();
	    if ($page_id && $current_page_id == $page_id) {
			$lang = get_option( SUPERFERO_OPTION . 'language' );
			if ( empty( $lang ) )
				$lang = 'EN';
			$script = '<script type="text/javascript">var superfero_language = "' . trim($lang) . '", superfero_host = "' . SUPERFERO_API . '";</script>';
		}
		print $script;
	}

    public function superfero_campaign()
    {
    	$email = get_option( SUPERFERO_OPTION . 'email' );
		//$lang = get_option( SUPERFERO_OPTION . 'language' );
      	//$superfero_api_url = SUPERFERO_API . 'api/campaignwordpress?author=' . $email . '&lang=' . $lang;
      	$superfero_api_url = SUPERFERO_API . 'api/campaignwordpress?author=' . $email ;
		
		$response = wp_remote_retrieve_body( wp_remote_get( $superfero_api_url, array( 'sslverify' => false ) ) );
		
		echo  $response ;
		die();
    }

	public function activate()
    {
		delete_option( SUPERFERO_OPTION . 'page_title' );
		add_option( SUPERFERO_OPTION . 'page_title', $this->page_title, '', 'yes' );

		delete_option( SUPERFERO_OPTION . 'page_name' );
		add_option( SUPERFERO_OPTION . 'page_name', $this->page_name, '', 'yes' );

		delete_option( SUPERFERO_OPTION . 'page_id' );
		add_option( SUPERFERO_OPTION . 'page_id', $this->page_id, '', 'yes' );

		$the_page = get_page_by_title($this->page_title);
		
		if (!$the_page)
		{
			// Create post object
			$_p = array();
			$_p['post_title']     = $this->page_title;
			$_p['post_content']   = SUPERFERO_PAGE_CONTENT;
			$_p['post_status']    = SUPERFERO_PAGE_STATUS;
			$_p['post_type']      = SUPERFERO_PAGE_TYPE;
			$_p['comment_status'] = SUPERFERO_COMMENT_STATUS;
			$_p['ping_status']    = SUPERFERO_PING_STATUS;
			$_p['post_category'] = array(1); // the default 'Uncatrgorised'

			// Insert the post into the database
			$this->page_id = wp_insert_post($_p);
		}
		else
		{
			// the plugin may have been previously active and the page may just be trashed...
			$this->page_id = $the_page->ID;

			//make sure the page is not trashed...
			$the_page->post_status = SUPERFERO_PAGE_STATUS;
			$the_page->post_content = SUPERFERO_PAGE_CONTENT;
			$the_page->post_type = SUPERFERO_PAGE_TYPE;
			$the_page->comment_status = SUPERFERO_COMMENT_STATUS;
			$the_page->ping_status = SUPERFERO_PING_STATUS;
			$the_page->post_category = array(1); // the default 'Uncatrgorised'
			$this->page_id = wp_update_post($the_page);
		}

		delete_option( SUPERFERO_OPTION . 'page_id' );
		add_option( SUPERFERO_OPTION . 'page_id', $this->page_id);

		$this->_log_version_number();
    }

    public function deactivate()
    {
      $this->deletePage(true);
      $this->deleteOptions();
    }


    public function uninstall()
    {
      $this->deletePage();
      $this->deleteOptions();
    }

    public function query_parser($q)
    {
      if(isset($q->query_vars['page_id']) AND (intval($q->query_vars['page_id']) == $this->page_id ))
      {
        $q->set( SUPERFERO_OPTION . 'page_is_called', true);
      }
      elseif(isset($q->query_vars['pagename']) AND (($q->query_vars['pagename'] == $this->page_name) OR ($_pos_found = strpos($q->query_vars['pagename'],$this->page_name.'/') === 0)))
      {
        $q->set( SUPERFERO_OPTION . 'page_is_called', true);
      }
      else
      {
        $q->set( SUPERFERO_OPTION . 'page_is_called', false);
      }
    }

    private function deletePage($hard = false)
    {
      $id = get_option( SUPERFERO_OPTION . 'page_id' );
      if($id && $hard == true)
        wp_delete_post($id, true);
      elseif($id && $hard == false)
        wp_delete_post($id);
    }

    private function deleteOptions()
    {
      delete_option( SUPERFERO_OPTION . 'page_title' );
      delete_option( SUPERFERO_OPTION . 'page_name' ); 
      delete_option( SUPERFERO_OPTION . 'page_id' );
      delete_option( SUPERFERO_OPTION . 'email' );
      delete_option( SUPERFERO_OPTION . 'language' );
    }

	/**
	 * Load frontend CSS.
	 * @access  public
	 * @since   1.0.0
	 * @return void
	 */
	public function enqueue_styles () {
		global $wp_query;
		$page_id = get_option( SUPERFERO_OPTION . 'page_id' );
		$current_page_id = $wp_query->get_queried_object_id();
	    if ($page_id && $current_page_id == $page_id) {
			wp_register_style( $this->_token . '-superfero', esc_url( $this->assets_url ) . 'css/superfero.css', array(), $this->_version );
			wp_enqueue_style( $this->_token . '-superfero' );	
		}

	} // End enqueue_styles ()

	/**
	 * Load frontend Javascript.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function enqueue_scripts () {
		global $wp_query;
		$page_id = get_option( SUPERFERO_OPTION . 'page_id' );
		$current_page_id = $wp_query->get_queried_object_id();
	    if ($page_id && $current_page_id == $page_id) {
			wp_register_script( $this->_token . '-superfero', esc_url( $this->assets_url ) . 'js/superfero' . $this->script_suffix . '.js', array( 'jquery' ), $this->_version );
			wp_localize_script( $this->_token . '-superfero', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));        
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( $this->_token . '-superfero' );
		}
		
	} // End enqueue_scripts ()

	/**
	 * Load plugin localisation
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_localisation () {
		load_plugin_textdomain( 'superfero', false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_localisation ()

	/**
	 * Load plugin textdomain
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function load_plugin_textdomain () {
	    $domain = 'superfero';

	    $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

	    load_textdomain( $domain, WP_LANG_DIR . '/' . $domain . '/' . $domain . '-' . $locale . '.mo' );
	    load_plugin_textdomain( $domain, false, dirname( plugin_basename( $this->file ) ) . '/lang/' );
	} // End load_plugin_textdomain ()

	/**
	 * Main Superfero Instance
	 *
	 * Ensures only one instance of Superfero is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 * @static
	 * @see Superfero()
	 * @return Main Superfero instance
	 */
	public static function instance ( $file = '', $version = '1.0.0' ) {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self( $file, $version );
		}
		return self::$_instance;
	} // End instance ()

	/**
	 * Cloning is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __clone () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __clone ()

	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @since 1.0.0
	 */
	public function __wakeup () {
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?' ), $this->_version );
	} // End __wakeup ()

	/**
	 * Installation. Runs on activation.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	public function install () {
		$this->_log_version_number();
	} // End install ()

	/**
	 * Log the plugin version number.
	 * @access  public
	 * @since   1.0.0
	 * @return  void
	 */
	private function _log_version_number () {
		update_option( $this->_token . '_version', $this->_version );
	} // End _log_version_number ()

}