<?php

///channel class
/**
 * @todo Need to take another look at the schema
 * 
 */
class BP_Chat_Channel {

	var $is_open = false;
	var $is_private = true;
	var $users;
	var $total_users; //total user connected to the channel at this moment
	var $last_message;
	var $last_message_time;
	var $is_multichat = false;
	var $title; //the title of the channel, may be chatting with xyz or group chat  or what ever

	public function __construct ( $id = null ) {

		if ( ! empty( $id ) ) {
			$this->populate( $id );
		}
	}

	public function populate ( $id ) {

		global $wpdb;

		$bpchat = bp_chat();

		$query = "SELECT * FROM {$bpchat->table_name_channels}  WHERE id=%d";

		if ( $row = $wpdb->get_row( $wpdb->prepare( $query, $id ) ) ) {

			$this->id					= $row->id;
			$this->is_open				= $row->is_open;
			$this->status				= $row->status;
			$this->is_multichat			= $row->is_multichat;

			$this->last_message_time	= $row->last_message_time;
			$this->time_created			= $row->time_created;
		}
	}

	public function save () {

		global $wpdb;

		$bpchat = bp_chat();

		do_action( 'bpchat_channels_data_before_save', $this );

		if ( $this->id ) {
			// Update
			$result = $wpdb->query( $wpdb->prepare(
				"UPDATE {$bpchat->table_name_channels} SET
					time_created=%s,
					is_open = %d,
					status=%s,
					is_multichat= %d

				WHERE id = %d", $this->time_created, $this->is_open, $this->status, $this->is_multichat, $this->id
			) );
				
		} else {

			// Save,Insert new

			$query = $wpdb->prepare(
				"INSERT INTO {$bpchat->table_name_channels}
				(
					time_created,
					is_open,
					status,
					is_multichat

				) VALUES ( %s, %d, %s, %d )",
				$this->time_created, //to change
				$this->is_open,
				$this->status,
				$this->is_multichat
			);



			$result = $wpdb->query( $query );
		}

		if ( false === $result ) {// because in updates it will retun Zero and false for something went wrong 
			return false;
		}	

		if ( ! $this->id ) {
			$this->id = $wpdb->insert_id;
		}

		do_action( 'bpchat_channels_data_after_save', $this );

		return $result;
	}

	/**
	 * Get all users on current channel
	 * 
	 * @global type $bp
	 * @global type $wpdb
	 * @param type $channel_id
	 * @return type
	 */
	public static function get_all_users ( $channel_id ) {

		global $wpdb;

		$bpchat = bp_chat();

		$query = "SELECT user_id from {$bpchat->table_name_channel_users}  WHERE channel_id=%d";

		$users = $wpdb->get_col( $wpdb->prepare( $query, $channel_id ) );

		return $users; ///return array of message objects
	}

	public static function get_all_messages ( $channel_id ) {
		//get all channel messages
		global $wpdb;

		$bpchat = bp_chat();

		$sql = "SELECT * FROM {$bpchat->table_name_messages} where channel_id=%d ORDER BY sent_at";
		$res = $wpdb->get_results( $wpdb->prepare( $sql, $channel_id ) );

		return $res;
	}

	public static function get_messages_after ( $channel_id, $time ) {

		global $bp, $wpdb;

		$bpchat = bp_chat();
		$query = "SELECT msg.channel_id, msg.message, msg.sender_id,msg.message,msg.sent_at FROM {$bpchat->table_name_messages} msg WHERE msg.channel_id=%d AND msg.sent_at > {$time} ORDER BY msg.sent_at ASC ";


		$q = $wpdb->prepare( $query, $channel_id );


		$messages = $wpdb->get_results( $q ); //array of message objects

		return $messages;
	}

	public static function get_all_open_channels_for_user ( $user_id ) {

		global $wpdb;

		$bpchat = bp_chat();


		$query = "SELECT msg.channel_id, msg.message, msg.sender_id,msg.message,msg.sent_at FROM {$bpchat->table_name_messages} msg WHERE msg.channel_id IN( SELECT channel_id FROM {$bpchat->table_name_channel_users} where user_id=%d and status <> 'closed')  ORDER BY channel_id DESC, msg.sent_at ASC ";


		$channels = array();

		$channel_messages = $wpdb->get_results( $wpdb->prepare( $query, $user_id ) );


		foreach ( $channel_messages as $message ) {

			$channel_id = $message->channel_id;


			$channels[$channel_id][] = $message;
		}


		return $channels; //_messages;
	}

	public static function get_open_channel_for_user ( $user_id ) {

		global $bp, $wpdb;

		//we may sacrifice a few queries here to have some better experience, because this is called only once per page load


		$bpchat = bp_chat();


		$query = "SELECT channel_id, user_id, status  FROM {$bpchat->table_name_channel_users} WHERE channel_id IN( SELECT channel_id FROM {$bpchat->table_name_channel_users} WHERE user_id=%d AND status = 'open') AND user_id!=%d ORDER BY channel_id DESC ";


		$channels = $wpdb->get_results( $wpdb->prepare( $query, $user_id, $user_id ) ); //all chaneels even with semi cose status

		return $channels;
	}

	//close a channel
	public static function close ( $channel_id ) {
		//close channel
		//close all current connections
	}

	public static function add_user ( $channel_id, $user_id, $status ) {

		global $wpdb, $bp;

		$bpchat = bp_chat();

		$query = "INSERT INTO {$bpchat->table_name_channel_users} (channel_id,user_id,status) values(%d,%d,%s)";

		$wpdb->query( $wpdb->prepare( $query, $channel_id, $user_id, $status ) );

		return true;
	}

	public static function update_user ( $channel_id, $user_id, $status ) {

		global $wpdb;

		$bpchat = bp_chat();

		$query = "UPDATE {$bpchat->table_name_channel_users} SET status=%s WHERE channel_id=%d AND user_id=%d";

		$wpdb->query( $wpdb->prepare( $query, $status, $channel_id, $user_id ) );

		return true;
	}

	public static function update_channel_for_all ( $channel_id, $status ) {

		global $wpdb;

		$bpchat = bp_chat();

		$query = "UPDATE {$bpchat->table_name_channel_users} SET status=%s WHERE channel_id=%d ";

		$res = $wpdb->query( $wpdb->prepare( $query, $status, $channel_id ) );

		return true;
	}

	public static function close_channel_for_user ( $channel_id, $user_id, $status = 'closed' ) {


		global $wpdb;

		$bpchat = bp_chat();

		$query = "UPDATE {$bpchat->table_name_channel_users} SET status=%s WHERE channel_id=%d AND user_id=%d";

		$wpdb->query( $wpdb->prepare( $query, $status, $channel_id, $user_id ) );

		return true;
	}

	public static function get_channel_between ( $initiator, $invited ) {

		global $wpdb;

		$bpchat = bp_chat();

		$user_list = "({$initiator},{$invited})";

		$query = "SELECT i.channel_id FROM {$bpchat->table_name_channel_users} i,{$bpchat->table_name_channel_users} o  WHERE  i.channel_id=o.channel_id AND i.user_id IN {$user_list} AND o.user_id IN {$user_list} AND i.user_id <> o.user_id AND (i.status <> 'closed' OR o.status <> 'closed')";

		$channel_id = $wpdb->get_var( $query );

		return $channel_id;
	}

	public static function keep_channels_open ( $channels, $user_id ) {

		global $wpdb;

		if ( empty( $channels ) )
			return;

		$bpchat = bp_chat();

		$channel_list = '(' . join( ',', $channels ) . ')';

		$query = "UPDATE {$bpchat->table_name_channel_users} SET status='open' WHERE channel_id IN {$channel_list} AND user_id=%d";
		$wpdb->query( $wpdb->prepare( $query, $user_id ) );
	}
	
	/**
	 * Find all channel ids in the given se of messages
	 * 
	 * @param type $messages
	 * @return type
	 */
	public static function find_channels_in_message ( $messages ) {

		$channel_ids = wp_list_pluck( $messages, 'channel_id' );

		return array_unique( $channel_ids ); //unique channels
	}

	/**
	 * Is channel open?
	 * 
	 * @return type
	 */
	public function is_channel_open () {

		return $this->is_open;
	}
	
	/**
	 * Is it a private channel
	 * 
	 * @return type
	 */
	public function is_private () {

		return $this->is_private;
	}
	/**
	 * Is it a channel that is allowing more than 2 people to communicate
	 * 
	 * @return type
	 */
	public function is_multichat () {

		return $this->multichat;
	}

}
