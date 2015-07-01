<?php
/**
 * Get a chnnel object identified by id
 * 
 * @param int $channel_id
 * @return BP_Chat_Channel
 */
function bpchat_get_channel_by_id( $channel_id ) {

	if( $channel = wp_cache_get( 'bp-chat-channel-' . $channel_id, 'bpchat' ) ) {

		return $channel;
	}
	
	$channel = new BP_Chat_Channel( $channel_id );
	
	if( ! empty( $channel ) ) {
		
		wp_cache_set( 'bp-chat-channel-' . $channel_id, $channel, 'bpchat' );
		
	}
	
	return $channel;
		
}


/**
 * Get an exiting channel id or create a new channel id for the communication
 * 
 * @param type $from_user_id
 * @param type $to_user_id
 * @return int
 */
function bpchat_obtain_channel( $from_user_id, $to_user_id ) {
	
	 if( $channel_id =  BP_Chat_Channel::get_channel_between( $from_user_id, $to_user_id ) )
			return $channel_id;
	 else
			return bpchat_create_channel( array( 'initiator_id' => $from_user_id, 'invited_id' => $to_user_id ) );
}
/**
 * Create a new channel
 * 
 * @param type $args
 * @return int|boolean
 */
function bpchat_create_channel( $args ) {

	$default =  array(
		'initiator_id'	=> '',
		'invited_id'	=> '',
		'is_multichat'	=> 0,//is one to one connection
		'is_open'		=> true,//connection is requested so channel is open
		'status'		=> 'initiated'
	);

		
	$arg = wp_parse_args( $args, $default );
	
	extract( $arg );
	
	$channel				= new BP_Chat_Channel();//create a new channel
	$channel->status		= $status;
	$channel->is_open		= $is_open;
	$channel->is_multichat	= $is_multichat;

		//save channel
	
	if( $channel->save() ) {
			
		//if channel created, we have the channel id now
		//make two entry in the user_channels table
		
		bpchat_add_channel_user( $channel->id, $initiator_id, 'open' )  ;   
		bpchat_add_channel_user( $channel->id, $invited_id, 'requested' )  ;  
		
		return $channel->id;
		
	}
	
	return false;
}


function bpchat_close_channel( $channel_id ) {
	
	return BP_Chat_Channel::close( $channel_id );//close channel
}

function bpchat_is_channel_idle( $channel_id ) {

}

function bpchat_is_channel_open( $channel_id ) {

}

function bpchat_is_channel_multichat( $channel_id ) {
	
	$channel = bpchat_get_channel_by_id( $channel_id );
	
	return $channel->is_multichat;
}

	
function bpchat_add_channel_user( $channel_id, $user_id, $status ) {
	
	return BP_Chat_Channel::add_user( $channel_id, $user_id, $status );
}
       

function bpchat_remove_channel_user( $channel_id, $user_id ) {
	
   return BP_Chat_Channel::remove_user( $channel_id, $user_id );
}

function bpchat_close_channel_for_user( $channel_id, $user_id ) {
	
	return BP_Chat_Channel::close_channel_for_user( $channel_id, $user_id );
}

function bpchat_update_channel_user( $channel_id, $user_id, $status ) {
	
	 return BP_Chat_Channel::update_user( $channel_id, $user_id, $status );
}

function  bpchat_update_all_channel_user( $channel_id, $status ) {
	
	return BP_Chat_Channel::update_channel_for_all( $channel_id, $status );
}


function bpchat_get_channel_users( $channel_id ) {
	
	$users = BP_Chat_Channel::get_all_users( $channel_id );
	
	return apply_filters( 'bpchat_get_channel_users', $users, $channel_id );
}

    
/**
 * Get all messages in a channel
 * 
 * @param type $channel_id
 * @return type
 */

function bpchat_get_channel_messages( $channel_id ) {
	
	$messages = BP_Chat_Channel::get_all_messages( $channel_id );
	
	$messages = bpchat_extend_messages( $messages );
	
	return $messages;
}
    
/**
 * Get the messages sent to the channel after a given time
 * 
 * @param type $channel_id
 * @param int $time timestamp
 * @return type
 */
function bpchat_get_recent_channel_messages( $channel_id, $time ) {
	
	$messages = BP_Chat_Channel::get_messages_after( $time );
	
	$messages = bpchat_extend_messages( $messages );
	
	return $messages;
}
/**
 * Get all Open Channels for the user
 * 
 * @param type $user_id
 * @return type
 */
function bpchat_get_channels_for_user( $user_id ) {
	
	$channels = BP_Chat_Channel::get_open_channel_for_user( $user_id );
	
	return $channels;
}

/**
 * Get the ids of other users connected to this channel
 * @global type $wpdb
 * @param type $channel_id
 * @return type
 */
function bpchat_get_other_party_ids( $channel_id ) {
	
  global $wpdb ;
  
  $bpchat = bp_chat();
  
  $user_id = get_current_user_id();

  $query = "SELECT o.user_id FROM {$bpchat->table_name_channel_users} i, {$bpchat->table_name_channel_users} o where o.channel_id=i.channel_id AND i.user_id=%d";

  $ids = $wpdb->get_results( $wpdb->prepare( $query, $user_id ) );
  return $ids;
}
