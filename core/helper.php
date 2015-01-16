<?php



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
            
			$base_url = bp_chat()->get_url();
            
			$url =  $base_url . 'includes/js/bchat.js';
            wp_enqueue_script( "poshytip", $base_url . 'includes/js/tip/jquery.poshytip.js', array( "jquery" ) );
            
            if(BPChatSettings::is_sound_enabled())
                wp_enqueue_script( "soundmanager", $base_url. 'assets/soundmanager/script/soundmanager2.js' );

            wp_enqueue_script( "chatjs", $url, array( "jquery", "json2" ) );
    }
    
    }

     //add_action("admin_print_styles","bpchat_load_css");
    function load_css(){
        
        if( bpchat_is_disabled () )
            return;
         
        if( is_user_logged_in() ) {
            $url =  bp_chat()->get_url() . "/includes/css/chat.css";
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

