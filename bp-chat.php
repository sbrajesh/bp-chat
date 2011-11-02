<?php
/*
Plugin Name: Bp Chat
Plugin URI: http://buddydev.com/plugins/bp-chat/
Description:  just a begining, this beta version just showcases some of the features, More features coming by end of March 2011
Version: 1.0 beta 5
Revision Date:April 30, 2011
Requires at least: wp 3.0, BuddyPress 1.2.8
Tested up to: WP 3.1, BuddyPress 1.2.8
Author: Brajesh Singh
Author URI: http://buddydev.com/members/sbrajesh
*/

define ( 'BP_CHAT_IS_INSTALLED', 1 );
define ( 'BP_CHAT_VERSION', '1.0' );
define ( 'BP_CHAT_DB_VERSION', 35 );

if ( !defined( 'BP_CHAT_SLUG' ) )
	define ( 'BP_CHAT_SLUG', 'chats' );

$bp_chat_dir =str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
define("BP_CHAT_DIR_NAME",$bp_chat_dir);//the directory name of bp-CHAT
define("BP_CHAT_PLUGIN_DIR",WP_PLUGIN_DIR."/".BP_CHAT_DIR_NAME);//WITH TRAILING SLASH..MIND IT
define("BP_CHAT_PLUGIN_URL",WP_PLUGIN_URL."/".BP_CHAT_DIR_NAME);//WITH TRAILING SLASH..MIND IT


//let us include the loader script here
/* The classes file should hold all database access classes and functions */

function bpchat_load_files(){
require ( BP_CHAT_PLUGIN_DIR. '/bp-chat/bp-chat-classes.php' );
require ( BP_CHAT_PLUGIN_DIR. '/bp-chat/bp-chat-ajax.php' );
require ( BP_CHAT_PLUGIN_DIR. '/bp-chat/bp-chat-templatetags.php' );
require ( BP_CHAT_PLUGIN_DIR. '/bp-chat/bp-chat-cssjs.php' );
require ( BP_CHAT_PLUGIN_DIR. '/bp-chat/bp-chat-business-functions.php' );
require ( BP_CHAT_PLUGIN_DIR. '/bp-chat/bp-chat-admin.php' );

}

add_action("bp_loaded","bpchat_load_files");

/* Setup the global settings etc*/

function bpchat_setup_globals() {
	global $bp, $wpdb;

	/* For internal identification */
	$bp->chat->id = 'chats';
	
	//define tables
	
	$bp->chat->table_chat_channels = $wpdb->base_prefix . 'bp_chat_channels';
	$bp->chat->table_chat_messages = $wpdb->base_prefix . 'bp_chat_messages';
      //  $bp->chat->table_channels=$wpdb->base_prefix."wp_bp_cha"
        $bp->chat->table_chat_users=$wpdb->base_prefix."bp_chat_users";
        $bp->chat->table_channel_users=$wpdb->base_prefix."bp_chat_channel_users";
       // $bp->chat->block_list=$wpdb->base_prefix."bp_chat_blocklist";//users have a blok list
        //channels may have a block list but at channel is temporary the list will be temporary

        $bp->chat->slug = BP_CHAT_SLUG; //we will use it for history
	
	/* Register this in the active components array */
	//$bp->active_components[$bp->gallery->slug] = $bp->gallery->id;
}

add_action( 'bp_init', 'bpchat_setup_globals', 5 );
add_action( 'admin_menu', 'bpchat_setup_globals', 2 );//update for bp 1.2.8




//on login update table
add_action("wp_login","bpchat_update_user_on_login",20);
add_action("wp_head","bpchat_check_current_user");
function bpchat_check_current_user(){
    if(!is_user_logged_in ())
        return;//do not cause any more load
    global $bp;
    bpchat_login_user($bp->loggedin_user->id);//it will solve the login issue
    bpchat_update_last_active($bp->loggedin_user->id);//update last active time for user
}
function bpchat_update_user_on_login($user_login){
     $user= new WP_User($user_login);
     bpchat_login_user($user->ID);
}

//logout user from chat when user logs out
add_action("wp_logout","bpchat_cleanup");
//add_action("clear_auth_cookie","bp_chat_cleanup");//may be we can use this hook too

function bpchat_cleanup(){
    global $current_user;
    bpchat_logout_user($current_user->ID);

}
//now ...

// schedule to check user login status
//if (!wp_next_scheduled('bpchat_user_status_monitor')) {
//	wp_schedule_event(time(), 'hourly', 'bpchat_user_status_monitor');//though it is not good to check ourly, but on shared server we can have a lot of trouble if we check each 5 minutes
//}

// create a hook to the function that should be executed daily
add_action('bpchat_user_status_monitor', 'bpchat_user_status_monitor');

// this will be run daily
function bpchat_user_status_monitor() {
	//get all logged in user
    //if they are not active for last 30 minutes, set them as logged out
}

/** Use cron to fix login/logout issue*/


function bpchat_fix_login_logout(){
 //get current online user in chat
 //get current online users in bp
 //set offline=1 for allwho are not in bp-online list
 //what about seetting me offline when I am inactive

 //set any user who is in online list as online
 

}

/*cron job scheduling for fixing the logout issue*/
add_action('bpchat_logout_check_event', 'bpchat_fix_logout');

function bpchat_schedule_logout_checker() {
	if ( !wp_next_scheduled( 'bpchat_logout_check_event' ) ) {
		wp_schedule_event(time(), 'minute5', 'bpchat_logout_check_event');
	}
}
add_action('wp', 'bpchat_schedule_logout_checker');

function bpchat_fix_logout() {
	// do something every hour
    //get current online users from the bp
    //what if my browser is open and I am set logged out because I was ainactive, no worries, it should display you were inactive, click togo active
   //update bpchat_users set is_online=0 if the user is not in bp online list or is not active for last couple of minutes
    BPChat_User::cleanup();
   
}

//on deactivation, remove the cron job

function bpchat_add_5min_interval( $schedules ) {
	// add a 'weekly' schedule to the existing set
	$schedules['minute5'] = array(
		'interval' => 300,//300 seconds
		'display' => __('Once In 5 minutes')
	);
	return $schedules;
}
add_filter( 'cron_schedules', 'bpchat_add_5min_interval' );

//register deactivation of scheduled event

register_deactivation_hook(__FILE__, 'bpchat_unschedule_logout_checker');

function bpchat_unschedule_logout_checker() {
	wp_clear_scheduled_hook('bpchat_logout_check_event');
}


add_action("wp_footer","bpchat_soundmanager_settings");
function bpchat_soundmanager_settings(){
if(!is_user_logged_in()||bpchat_is_disabled())//allow to disable for mobile browsers
    return;//do not bother if the user is not logged in

?>
<script type="text/javascript">
     bpchat={};
    bpchat.plugin_url="<?php echo plugin_dir_url(__FILE__);?>";
    bpchat.current_user_id="<?php global $bp; echo $bp->loggedin_user->id;  ?>";
    bpchat.sound_notification_enabled="<?php echo bpchat_has_sound_notification_enabled();?>";
    <?php if(bpchat_has_sound_notification_enabled()):?>
    soundManager.url =bpchat.plugin_url+"assets/soundmanager/swf/soundmanager2.swf"; // directory where SM2 .SWFs live
    soundManager.debugMode =false;
    //in future will have volume control feature, currently allow site admin to set it via the php
    soundManager.defaultOptions.volume =<?php echo bpchat_get_notification_volume();?>;
  //  soundManager.useFlashBlock = false;
<?php endif;?>
</script>
<?php
}
?>