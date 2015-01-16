<?php
/**
 * Installations/admin functions
 */
/**
 * Bp Chat Installer class
 */
class BP_Chat_Installer {
	
    private static $instance;
    
	private function __construct() {
		
        add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', array( $this, 'maybe_install' ) );//support for bp 1.2.8+wp 3.1

    }
	/**
	 * 
	 * @return BP_Chat_Installer
	 */
    public static function get_instance() {
		
        if( ! isset( self::$instance ) )
                self::$instance = new self();
		
        return self::$instance;
    }
	
	
    private function install() {
		
		global $wpdb;

		$sql				= array();
		$charset_collate	= '';

		//check if tables exist,
		//just update one table in that case
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		/* BuddyPress component DB schema */
		if ( ! empty( $wpdb->charset ) )
			$charset_collate =  "DEFAULT CHARACTER SET $wpdb->charset";


		$bp_prefix = bp_core_get_table_prefix();

		$bp_chat = bp_chat();

		//install
		$sql_table_exists_check = "SHOW TABLES LIKE '" . $bp_prefix . "_bp_chat_%'";

		if( $wpdb->get_col( $sql_table_exists_check ) ) {////just update the user table

			$sql[] = "ALTER TABLE {$bp_chat->table_name_users} MODIFY last_active_time DATETIME ";

		} else {//if tables do not exist

				
			$sql[] = "CREATE TABLE {$bp_chat->table_name_users} (
						user_id bigint(20) NOT NULL,
						is_online tinyint(4) NOT NULL,
						last_active_time datetime NOT NULL,
						status_message varchar(255) NOT NULL,
						last_fetch_time datetime NOT NULL ,
						friends_only tinyint(1) NOT NULL DEFAULT '0',
						PRIMARY KEY (user_id)
					  ) {$charset_collate};";


				
			$sql[] = "CREATE TABLE {$bp_chat->table_name_channels} (
						id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
						last_message_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
						time_created datetime NOT NULL,
						status tinyint(3) unsigned NOT NULL,
						is_multichat tinyint(3) NOT NULL,
						is_open tinyint(4) NOT NULL,
						PRIMARY KEY (id)
					 ) {$charset_collate};";

			$sql[] = "CREATE TABLE {$bp_chat->table_name_channel_users} (
						channel_id bigint(20) NOT NULL,
						user_id bigint(20) NOT NULL,
						status varchar(32) NOT NULL,
						has_initiated tinyint(4) NOT NULL,
						KEY channel_id (channel_id,user_id)
				   ) {$charset_collate};";

			//meta data
			$sql[] = "CREATE TABLE {$bp_chat->table_name_messages} (
							id bigint(20) NOT NULL AUTO_INCREMENT,
							sender_id bigint(20) NOT NULL,
							channel_id bigint(20) NOT NULL,
							message text NOT NULL,
							sent_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
							PRIMARY KEY (id),
							KEY channel_id (channel_id)
					 ) {$charset_collate};";

		}

		//execute sql
		dbDelta($sql);

		update_site_option( 'bp-chat-db-version', BP_CHAT_DB_VERSION );

    }
	/**
	 * Install bp chat tables
	 */
	public function maybe_install() {
		
		// Need to check db tables exist, activate hook not working in mu-plugins folder. 
		if ( get_site_option( 'bp-chat-db-version' ) < BP_CHAT_DB_VERSION )
			$this->install();
	}

}//end of class

BP_Chat_Installer::get_instance();


class BPChatSettings {
     
     
     
	public function get_volume() {
         //for now, please use the below filter for volume control
            return apply_filters( 'bpchat_get_notification_volume', 20 );
       }
	   
    public function is_sound_enabled() {
          //later customize it for each user, currently make it default
          return apply_filters( 'bpchat_has_sound_notification_enabled', 1 );//return 0 to disable it
      }
      
    public function update_user_preference( $user_id, $prefs ) {
		  
             BPChat_User::set_pref( $user_id,$prefs );
    }
}
