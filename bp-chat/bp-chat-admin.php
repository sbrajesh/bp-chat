<?php
/**
 * Installations/admin functions
 */
/**
 * Check if bpchat is installed or not
 */
function bpchat_install(){
global $wpdb, $bp;
//check if tables exist,
//just update one table in that case

//install
$sql_table_exists_check="SHOW TABLES LIKE '{$wpdb->prefix}_bp_chat_%' ";
if($wpdb->get_col($wpdb->prepare($sql_table_exists_check)))////just update the user table
   $sql[]="ALTER TABLE {$bp->chat->table_chat_users} MODIFY last_active_time DATETIME ";
 else{//if tables do not exist

	if ( !empty($wpdb->charset) )
		$charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";

	$sql[] = "CREATE TABLE {$bp->chat->table_chat_users} (
                              user_id bigint(20) NOT NULL,
                              is_online tinyint(4) NOT NULL,
                              last_active_time datetime NOT NULL,
                              status_message varchar(255) NOT NULL,
                              last_fetch_time datetime NOT NULL ,
                              friends_only tinyint(1) NOT NULL DEFAULT '0',
                              PRIMARY KEY (user_id)
                            ) {$charset_collate};";
//for media table too

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
	require_once(ABSPATH . 'wp-admin/upgrade-functions.php');
	dbDelta($sql);
    


	update_site_option( 'bp-chat-db-version', BP_CHAT_DB_VERSION );

}
/**
 * Install bpchat tables
 */
function bpchat_check_installed(){
/* Need to check db tables exist, activate hook no-worky in mu-plugins folder. */
	if ( get_site_option('bp-chat-db-version') < BP_CHAT_DB_VERSION )
            bpchat_install();
}
add_action( is_multisite() ? 'network_admin_menu' : 'admin_menu', 'bpchat_check_installed' );//support for bp 1.2.8+wp 3.1

?>