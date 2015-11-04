<?php
class BP_Chat_Cron_Helper{
   
    private static $instance;
	
    private function __construct() {
        
       add_action( 'bpchat_user_status_monitor', array( $this, 'user_status_monitor' ) );
      // add_action( 'bpchat_user_status_monitor', array( $this, 'user_status_monitor' ) );
      
       /*cron job scheduling for fixing the logout issue*/
	   
       add_action( 'bpchat_logout_check_event', array( $this, 'fix_logout' ) );
       
       add_action( 'wp', array( $this, 'schedule_logout_checker' ) );
	   
       add_filter( 'cron_schedules', array( $this, 'add_5min_interval' ) );
       
	   /**
	    * @todo move it out
	    * It will have no effect
	    * as the deactivation hook gets callled before plugins_loaded
	    * 
	    */
		//register deactivation of scheduled event
       register_deactivation_hook( __FILE__, array( $this, 'unschedule_logout_checker' ) ) ;
       

    }
    
    public static function get_instance() {
        
        if( ! isset( self::$instance ) ) {
         
			self::$instance = new self();
		}	
		
        return self::$instance;
    }


    /** Use cron to fix login/logout issue*/
    public function fix_login_logout(){
        //get current online user in chat
        //get current online users in bp
        //set offline=1 for allwho are not in bp-online list
        //what about seetting me offline when I am inactive
         //set any user who is in online list as online
    }


    public function schedule_logout_checker() {
		
		if ( ! wp_next_scheduled( 'bpchat_logout_check_event' ) ) {
			
				wp_schedule_event( time(), 'minute5', 'bpchat_logout_check_event' );
		}
    }

    public function fix_logout() {
            // do something every hour
        //get current online users from the bp
        //what if my browser is open and I am set logged out because I was ainactive, no worries, it should display you were inactive, click togo active
       //update bpchat_users set is_online=0 if the user is not in bp online list or is not active for last couple of minutes
       bpchat_mark_users_idle();

    }

    
    public function add_5min_interval( $schedules ) {
		// add a 'weekly' schedule to the existing set
		$schedules['minute5'] = array(

				'interval'	=> 300,//300 seconds
				'display'	=> __( 'Once In 5 minutes', 'bpchat' )
		);

		return $schedules;
    }

    
    //on deactivation, remove the cron job
    public function unschedule_logout_checker() {
		
        wp_clear_scheduled_hook( 'bpchat_logout_check_event' );
    }

   

}//end of class

//instantiate cron helper
BP_Chat_Cron_Helper::get_instance();
