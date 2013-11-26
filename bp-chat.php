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



//Initialize the Chat component
add_action( 'bp_loaded', 'bpchat_init', 1 );
function bpchat_init(){
    global $bp;
    if( is_multisite() && ! is_main_site() )
        return;//do not load chat plugin
    include BP_CHAT_PLUGIN_DIR . 'loader.php';
    $bp->chat =  BPChatComponent::get_instance();
}



