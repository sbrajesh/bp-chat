<?php

class BPChatComponent extends BP_Component{
    private static $instance;
    private function __construct(){
        parent::start( 'chat',  __( 'BP Chat', 'chat' ), BP_CHAT_PLUGIN_DIR );
      }
    
     function get_instance(){
         
        if( ! isset( self::$instance ) )
                self::$instance = new self();
        return self::$instance;
    }  
    /**
     * include files
     */
    function includes() {
      

    }
    
    
    function setup_globals() {
	global $bp, $wpdb;

	// Define a slug, if necessary
		if ( !defined( 'BP_CHAT_SLUG' ) )
			define( 'BP_CHAT_SLUG', $this->id );

	// Global tables for Chat component
		$global_tables = array(
			'table_chat_channels'   => $bp->table_prefix . 'bp_chat_channels',
			'table_chat_messages'   => $bp->table_prefix . 'bp_chat_messages',
			'table_chat_users'      => $bp->table_prefix . 'bp_chat_users',
			'table_channel_users'   => $bp->table_prefix . 'bp_chat_channel_users'
		);

		// All globals for messaging component.
		// Note that global_tables is included in this array.
		$globals = array(
			'path'                  => BP_CHAT_PLUGIN_DIR,
			'slug'                  => BP_CHAT_SLUG,
			'root_slug'             => isset( $bp->pages->chat->slug ) ? $bp->pages->chat->slug : BP_CHAT_SLUG,
			'has_directory'         => false,
			'notification_callback' => 'chat_format_notifications',
			'global_tables'         => $global_tables
		);

		parent::setup_globals( $globals );
        	
        
        }

        function setup_nav(){
            //don't do anything
        }
        
        //don't do anything
        function setup_title(){
            
        }
        
}//end of BPChatComponent


add_action( 'bp_setup_components', 'bpchat_setup_component', 6 );

function bpchat_setup_component(){
	
	$bp = buddypress();
	$bp->chat = BPChatComponent::get_instance();
}
