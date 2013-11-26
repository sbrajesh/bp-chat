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
    function includes(){
        $includes = array( 
                'includes/bp-chat-classes.php',
                'includes/bp-chat-ajax.php',
                'includes/chat-bar.php',
                'includes/bp-chat-business-functions.php',
                'includes/bp-chat-admin.php' 
             );
        parent::includes( $includes );

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


//Helper class
class BPChatHelper{
    private static $instance;
    
    private function __construct(){
        
        add_action( 'wp_login', array( $this, 'update_user_on_login' ), 20 );
        //logout user from chat when user logs out
        add_action( 'wp_logout', array( $this, 'cleanup' ) );

        add_action( 'wp_head', array( $this, 'check_current_user' ) );

        
        add_action( 'wp_enqueue_scripts', array( $this, 'load_js' ) );
        add_action( 'wp_print_styles', array( $this, 'load_css' ) );
        add_action( 'wp_footer', array( $this, 'include_soundmanager_js' ) );
        add_action( 'wp_footer', 'bp_chat_show_chat_bar' );
    }
    
    function get_instance(){
        
        if( ! isset( self::$instance ) )
                self::$instance = new self();
        return self::$instance;
    }
    
        //on login update table
    function check_current_user() {
        if( ! is_user_logged_in () )
            return;//do not cause any more load
        global $bp;
        bpchat_login_user( $bp->loggedin_user->id );//it will solve the login issue
        bpchat_update_last_active( $bp->loggedin_user->id );//update last active time for user
    }
    function update_user_on_login( $user_login ) {
         $user = new WP_User( $user_login );
         bpchat_login_user( $user->ID );
    }

    //add_action("clear_auth_cookie","bp_chat_cleanup");//may be we can use this hook too

    function cleanup(){
        
        bpchat_logout_user( get_current_user_id() );

    }
    
     function load_js(){
         
         if( bpchat_is_disabled() )
            return;
    //if user is online, load the javascript
        
         if( is_user_logged_in() && !is_admin() ) {//has issues while loading on admin pages a 0 is appeneded still not sure why ?
            wp_enqueue_script( "json2" );
            wp_enqueue_script( "jquery" );
            
            $url = BP_CHAT_PLUGIN_URL . "includes/js/bchat.js";
            wp_enqueue_script( "poshytip", BP_CHAT_PLUGIN_URL . "includes/js/tip/jquery.poshytip.js", array( "jquery" ) );
            
            if(BPChatSettings::is_sound_enabled())
                wp_enqueue_script( "soundmanager", BP_CHAT_PLUGIN_URL. "assets/soundmanager/script/soundmanager2.js" );

            wp_enqueue_script( "chatjs", $url, array( "jquery", "json2" ) );
    }
    
    }

     //add_action("admin_print_styles","bpchat_load_css");
    function load_css(){
        
        if( bpchat_is_disabled () )
            return;
         
        if( is_user_logged_in() ) {
            $url = BP_CHAT_PLUGIN_URL . "/includes/css/chat.css";
            wp_enqueue_style( "chatcss", $url );
        }
    }
   
    function include_soundmanager_js() {
        
        if( ! is_user_logged_in() || bpchat_is_disabled() )//allow to disable for mobile browsers
            return;//do not bother if the user is not logged in

    ?>
    <script type="text/javascript">
        bpchat = {};
        bpchat.plugin_url = "<?php echo plugin_dir_url(__FILE__);?>";
        bpchat.current_user_id = "<?php global $bp; echo $bp->loggedin_user->id;  ?>";
        bpchat.sound_notification_enabled = "<?php echo BPChatSettings::is_sound_enabled();?>";
        
        <?php if( BPChatSettings::is_sound_enabled() ):?>
        soundManager.url = bpchat.plugin_url+"assets/soundmanager/swf/soundmanager2.swf"; // directory where SM2 .SWFs live
        soundManager.debugMode = false;
        //in future will have volume control feature, currently allow site admin to set it via the php
        soundManager.defaultOptions.volume = <?php echo BpChatSettings::get_volume();?>;
      //  soundManager.useFlashBlock = false;
    <?php endif;?>
    </script>
    <?php
    }

}//end of helper class

BPChatHelper::get_instance();

class BPChatCronHelper{
   
    private static $instance;
    private function __construct(){
        
       add_action( 'bpchat_user_status_monitor', array( $this, 'user_status_monitor' ) );
       add_action( 'bpchat_user_status_monitor', array( $this, 'user_status_monitor' ) );
      
       /*cron job scheduling for fixing the logout issue*/
       add_action( 'bpchat_logout_check_event', array( $this, 'fix_logout' ) );
       
       add_action( 'wp', array( $this, 'schedule_logout_checker' ) );
       add_filter( 'cron_schedules', array( $this, 'add_5min_interval' ) );
       //register deactivation of scheduled event

       register_deactivation_hook( __FILE__, array( $this, 'unschedule_logout_checker' ) ) ;
       

    }
    
    function get_instance(){
        
        if( ! isset( self::$instance ) )
                self::$instance = new self();
        return self::$instance;
    }


    /** Use cron to fix login/logout issue*/
    function fix_login_logout(){
        //get current online user in chat
        //get current online users in bp
        //set offline=1 for allwho are not in bp-online list
        //what about seetting me offline when I am inactive
         //set any user who is in online list as online
    }


    function schedule_logout_checker() {
            if ( !wp_next_scheduled( 'bpchat_logout_check_event' ) ) {
                    wp_schedule_event( time(), 'minute5', 'bpchat_logout_check_event' );
            }
    }

    function fix_logout() {
            // do something every hour
        //get current online users from the bp
        //what if my browser is open and I am set logged out because I was ainactive, no worries, it should display you were inactive, click togo active
       //update bpchat_users set is_online=0 if the user is not in bp online list or is not active for last couple of minutes
        BPChat_User::cleanup();

    }

    
    function add_5min_interval( $schedules ) {
            // add a 'weekly' schedule to the existing set
            $schedules['minute5'] = array(
                    
                    'interval' => 300,//300 seconds
                    'display' => __( 'Once In 5 minutes' )
            );
            return $schedules;
    }

    
    //on deactivation, remove the cron job
    function unschedule_logout_checker() {
            wp_clear_scheduled_hook( 'bpchat_logout_check_event' );
    }

   

}//end of class

//instantiate cron helper
BPChatCronHelper::get_instance();


