<?php

class BP_Chat_Ajax_Helper {
	
	private static $instance;
	
	private function __construct() {
		//get the roaster/buddy list
		add_action( 'wp_ajax_bpchat_get_roaster', array( $this, 'get_roaster' ) );
		
		//get the current open channels/chatbox
		add_action( 'wp_ajax_bpchat_get_current_open_channels', array( $this, 'get_open_channels' ) );
		
		add_action( 'wp_ajax_bpchat_get_current_open_channels', array( $this, 'get_open_channels' ) );
		
		
	}
	
	/**
	 * 
	 * @return BP_Chat_Ajax_Helper
	 */
	public static function get_instance() {
		
		if( ! isset( self::$instance ) )
				self::$instance = new self();
		
		return self::$instance;
	}
	
	
}

BP_Chat_Ajax_Helper::get_instance();