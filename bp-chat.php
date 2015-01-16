<?php
/*
Plugin Name: Bp Chat
Plugin URI: http://buddydev.com/plugins/bp-chat/
Description:  just a begining, this beta version just showcases some of the features, More features coming by end of March 2011
Version: 1.1 trunk
Revision Date:February 13, 2013
Requires at least: wp 3.3, BuddyPress 1.5+
Tested up to:WordPress 3.5+BuddyPress 1.6.2
Author: Brajesh Singh
Author URI: http://buddydev.com/members/sbrajesh
*/

define ( 'BP_CHAT_IS_INSTALLED', 1 );
define ( 'BP_CHAT_VERSION', '1.0' );
define ( 'BP_CHAT_DB_VERSION', 36 );

if ( !defined( 'BP_CHAT_SLUG' ) )
	define ( 'BP_CHAT_SLUG', 'chats' );

define( 'BP_CHAT_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );//WITH TRAILING SLASH..MIND IT
define( 'BP_CHAT_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );//WITH TRAILING SLASH..MIND IT


class BP_Chat_Helper {
	
	private static $instance;
	
	private $url;
	private $path;
	
	public $table_name_users;
	public $table_name_channels;
	public $table_name_messages;
	public $table_name_channel_users;
	
	private function __construct() {
		
		
		add_action( 'bp_loaded', array( $this, 'setup' ), 5 );
		add_action( 'bp_loaded', array( $this, 'load' ) );
		
	}

	/**
	 * 
	 * @return type
	 */
	public static function get_instance(){
		
		if( ! isset( self:: $instance ) )
			self::$instance = new self();
		
		return self::$instance;
		
	}
	
	public function setup() {
		
		$this->path = plugin_dir_path( __FILE__ );
		
		$this->url = plugin_dir_url( __FILE__ );
		
		//setup tables
		$this->setup_table_names();
		
		
	}
	
	
	private function setup_table_names() {
		
		$table_prefix = bp_core_get_table_prefix();
		
		$this->table_name_users				= $table_prefix . 'bp_chat_users';
		$this->table_name_channels			= $table_prefix . 'bp_chat_channels';
		$this->table_name_channel_users		= $table_prefix . 'bp_chat_channel_users'; 
		$this->table_name_messages			= $table_prefix . 'bp_chat_messages';  
	}
	/**
	 * Load required files
	 * 
	 */
	public function load() {
		//if we are on a multisite environment and this is not the main site
		//do not load this plugin
		if( is_multisite() && ! is_main_site() )
			return;//do not load chat plugin
		
		
		$path = $this->get_path();
		
		$files = array(
			'core/cron.php',
			'core/helper.php',
			'includes/bp-chat-classes.php',
			'includes/bp-chat-ajax.php',
			'includes/chat-bar.php',
			'includes/bp-chat-business-functions.php',
			'includes/bp-chat-admin.php' 
		);
		
		
		if( is_admin() ) {
			
		}
		
		foreach( $files as $file )
			require_once $path . $file ;
		
		do_action( 'bpchat_loaded' );
	}
	
	/**
	 * URL to the bp-chat plugin directory with trailing slash
	 * 
	 * @return string url
	 */
	public function get_url() {
		
		return $this->ur;
	}
	/**
	 * File system absolute path to the bp chat plugin directory with trailing slash
	 * @return string
	 */
	public function get_path() {
		
		return $this->path;
	}
}

/**
 * Singleton instance
 * 
 * @return BP_Chat_Helper
 */
function bp_chat() {
	
	return BP_Chat_Helper::get_instance();
	
}

bp_chat();

