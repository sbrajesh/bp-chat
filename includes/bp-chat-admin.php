<?php
/**
 * Installations/admin functions
 */
/**
 * Check if bpchat is installed or not
 */
class BPChatInstaller{
    private static $instance;
    private function __construct(){
        add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', array($this,'check_installed') );//support for bp 1.2.8+wp 3.1

    }
     function get_instance(){
        if(!isset(self::$instance))
                self::$instance=new self();
        return self::$instance;
    }
    function install(){
    global $wpdb, $bp;
    $sql=array();
    $charset_collate='';
    //check if tables exist,
    //just update one table in that case
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	/* BuddyPress component DB schema */
	if ( !empty($wpdb->charset) )
		$charset_collate= "DEFAULT CHARACTER SET $wpdb->charset";


        $bp_prefix = bp_core_get_table_prefix();
    //install
    $sql_table_exists_check="SHOW TABLES LIKE '".$bp_prefix."_bp_chat_%'";
    if($wpdb->get_col($sql_table_exists_check))////just update the user table
       $sql[]="ALTER TABLE {$bp->chat->table_chat_users} MODIFY last_active_time DATETIME ";
     else{//if tables do not exist

            

            $sql[] = "CREATE TABLE {$bp->chat->table_chat_users} (
                                  user_id bigint(20) NOT NULL,
                                  is_online tinyint(4) NOT NULL,
                                  last_active_time datetime NOT NULL,
                                  status_message varchar(255) NOT NULL,
                                  last_fetch_time datetime NOT NULL ,
                                  friends_only tinyint(1) NOT NULL DEFAULT '0',
                                  PRIMARY KEY (user_id)
                                ) {$charset_collate};";
  

            $sql[] = "CREATE TABLE {$bp->chat->table_chat_channels} (
                          id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                          last_message_time timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                          time_created datetime NOT NULL,
                          status tinyint(3) unsigned NOT NULL,
                          is_multichat tinyint(3) NOT NULL,
                          is_open tinyint(4) NOT NULL,
                          PRIMARY KEY (id)
                       ) {$charset_collate};";

            $sql[] = "CREATE TABLE {$bp->chat->table_channel_users} (
                            channel_id bigint(20) NOT NULL,
                            user_id bigint(20) NOT NULL,
                            status varchar(32) NOT NULL,
                            has_initiated tinyint(4) NOT NULL,
                            KEY channel_id (channel_id,user_id)
                       ) {$charset_collate};";

              //meta data
              $sql[] = "CREATE TABLE {$bp->chat->table_chat_messages} (
                              id bigint(20) NOT NULL AUTO_INCREMENT,
                              sender_id bigint(20) NOT NULL,
                              channel_id bigint(20) NOT NULL,
                              message text NOT NULL,
                              sent_at timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                              PRIMARY KEY (id),
                              KEY channel_id (channel_id)
                       ) {$charset_collate};";

     }
          
            dbDelta($sql);



            update_site_option( 'bp-chat-db-version', BP_CHAT_DB_VERSION );

    }
/**
 * Install bpchat tables
 */
function check_installed(){
/* Need to check db tables exist, activate hook no-worky in mu-plugins folder. */
	if ( get_site_option('bp-chat-db-version') < BP_CHAT_DB_VERSION )
            self::install();
}

}//end of class

BPChatInstaller::get_instance();
class BPChatSettings{
     
     
     function get_volume(){
         //for now, please use the below filter for volume control
            return apply_filters("bpchat_get_notification_volume",20);
       }
     function is_sound_enabled(){
          //later customize it for each user, currently make it default
          return apply_filters("bpchat_has_sound_notification_enabled",1);//return 0 to disable it
      }
      
      function update_user_preference($user_id,$prefs){
             BPChat_User::set_pref($user_id,$prefs);
       }
}

?>