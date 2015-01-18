<?php
/**
 * Loads all the assets for the BP Chat
 * 
 */
class BP_Chat_Asset_Loader {
	
	private static $instance;
	
	private $url;
	
	private function __construct() {
		
		$this->url = bp_chat()->get_url();
		        
		add_action( 'bp_enqueue_scripts', array( $this, 'load_js' ) );
		add_action( 'bp_enqueue_scripts', array( $this, 'load_css' ) );
		
		//for sound manager
		add_action( 'wp_footer', array( $this, 'load_soundmanager_js' ) );
	}
	/**
	 * 
	 * @return BP_Chat_Asset_Loader
	 */
	public static function get_instance() {
		
		if( ! isset( self::$instance ) )
			self::$instance = new self();
		
		return self::$instance;
	}
	
	
	public function load_js() {
		
        if( bpchat_is_disabled() )
            return;
		//if user is online, load the javascript
        
        if( is_user_logged_in() && ! is_admin() ) {//has issues while loading on admin pages a 0 is appeneded still not sure why ?
            
			$base_url = bp_chat()->get_url();
            
			wp_enqueue_script( 'poshytip', $base_url . 'assets/vendors/tip/jquery.poshytip.js', array( 'jquery' ) );
            
            if( BPChatSettings::is_sound_enabled() )
                wp_enqueue_script( 'soundmanager', $base_url. 'assets/vendors/soundmanager/script/soundmanager2.js' );

            wp_enqueue_script( 'chatjs', $base_url . 'assets/js/bpchat.js', array( 'jquery', 'json2' ) );
			
		}
    
	}
	
	public function load_css() {
		
        if( bpchat_is_disabled () )
            return;
         
        if( is_user_logged_in() ) {
            
			$url =  bp_chat()->get_url() . 'assets/css/chat.css';
			
            wp_enqueue_style( 'chatcss', $url );
        }
	}


    public function load_soundmanager_js() {
        
        if( ! is_user_logged_in() || bpchat_is_disabled() )//allow to disable for mobile browsers
            return;//do not bother if the user is not logged in

		?>
    
	<script type="text/javascript">
        bpchat = {};
        bpchat.plugin_url = "<?php echo bp_chat()->get_url();?>";
        bpchat.current_user_id = "<?php  echo get_current_user_id();  ?>";
        bpchat.sound_notification_enabled = "<?php echo BPChatSettings::is_sound_enabled();?>";
        
        <?php if( BPChatSettings::is_sound_enabled() ):?>
			soundManager.url = bpchat.plugin_url+"assets/vendors/soundmanager/swf/soundmanager2.swf"; // directory where SM2 .SWFs live
			soundManager.debugMode = false;
			//in future will have volume control feature, currently allow site admin to set it via the php
			soundManager.defaultOptions.volume = <?php echo BpChatSettings::get_volume();?>;
		  //  soundManager.useFlashBlock = false;
		<?php endif;?>
    </script>
    <?php
    }
}

BP_Chat_Asset_Loader::get_instance();