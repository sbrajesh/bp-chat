<?php
/**
 * Get all possible user states
 * 
 * @return type
 */
function bpchat_get_all_user_states() {
	
	$states = array (
		'online'	=> __( 'Online', 'bp-chat' ),
		'away'		=> __( 'Away', 'bp-chat' ),
		'offline'	=> __( 'Offline', 'bp-chat' ),
		'busy'		=> __( 'Busy', 'bp-chat' ),
	);
	
	return apply_filters( 'bpchat_user_states', $states );
	
}
/**
 * Get the user state key
 * Possible values are online,|away|offline|busy
 * @param type $user_id
 * @return string
 */
function bpchat_get_user_state( $user_id ) {
	
	$state = bp_get_user_meta( $user_id, 'bpchat_state',  true );
	
	if( empty( $state ) )
		$state = 'offline';//if no state is set, the user is offline
	
	return $state;
}
/**
 * Update User state
 * 
 * @param type $user_id
 * @param type $state possible values online|offline|busy|away
 * @return type
 */
function bpchat_update_user_state( $user_id, $state ) {
	
	$all_status = bpchat_get_all_user_states();
	if( empty( $state ) || ! is_string( $state ) || ! isset( $all_status[$state] ) )
		return ;
	
	bp_update_user_meta( $user_id, 'bpchat_state', $state );
}
/**
 * Get the label for current user state
 * 
 * @param type $state
 * @return string
 */
function bpchat_get_user_state_label( $state ) {
	
	if( empty( $state ) )
		return '';
	
	$all_states = bpchat_get_all_user_states();
	
	if( isset( $all_states[$state] ) )
			return $all_states[$state];
	
	return __( 'Unknown', 'bp-chat' );
	
}
/**
 * Get the user status message
 * 
 * @param type $user_id
 * @return type
 */
function bpchat_get_user_status_message( $user_id ) {
	
	return apply_filters( 'bpchat_user_status_message', bp_get_user_meta( $user_id, 'bpchat_status_message', true ), $user_id );
}
/**
 * Set User status message 
 * 
 * @param type $user_id
 * @param type $message
 * @return type
 */
function bpchat_update_user_status_message( $user_id, $message ) {
	
	return bp_update_user_meta( $user_id, 'bpchat_status_message',  $message );
}
/**
 * Clear a user status message
 * 
 * @param type $user_id
 * @return type
 */
function bpchat_clear_user_status_message( $user_id ) {
	
	return bpchat_update_user_status_message( $user_id, '' );//or should we delete using bp_delete_user_meta?
}
  
/**
 * Show users the options for changing preference?
 * 
 * @return boolean
 */
function bpchat_show_user_preference() {
	
    return apply_filters( 'bpchat_show_user_preference', bpchat_get_option( 'allow_prefernce_change' ) );
}

/**
 * Get the users chat preference
 * We use it to create the roaster
 * 
 * @param type $user_id
 * @return type
 */
function bpchat_get_user_chat_preference( $user_id ) {
	
	$preference = bp_get_user_meta( $user_id, 'bpchat_preference', true );
	if( empty( $preference ) )
		$preference = bpchat_get_option ( 'default_chat_preference' );
	
	return $preference;
	
}
/**
 * Update User chat preference (all|friends|followers only )
 * @param type $user_id
 * @param type $prefs possible values all|friends|followers
 */
function bpchat_update_user_chat_preference( $user_id, $prefs ) {
	
	return bp_update_user_meta( $user_id, 'bpchat_preference', $prefs );
	
}

function bpchat_has_friends_only_enabled( $user_id ) {
	
	$enabled = false;
	
	if( bpchat_get_user_chat_preference( $user_id ) == 'friends' )
			$enabled = true;
	
   return apply_filters( 'bpchat_has_friend_only_enabled', $enabled, $user_id  );
}


function bpchat_update_last_active( $user_id ) {
	
	$current_time = current_time( 'timestamp' );
	
	return bp_update_user_meta( $user_id, 'bpchat_last_active', $current_time );
	
}

function bpchat_get_last_active( $user_id ) {
	
	return  bp_get_user_meta( $user_id, 'bpchat_last_active', true );
	
}

function bpchat_update_fetch_time( $user_id ) {
	
	$current_time = current_time( 'timestamp' );
	
	return bp_update_user_meta( $user_id, 'bpchat_last_fetch_time', $current_time );

}

function bpchat_get_last_fetch_time( $user_id ) {
	
	return bp_get_user_meta( $user_id, 'bpchat_last_fetch_time', true );
}


/* logout a user from chat session*/
function bpchat_logout_user( $user_id ) {
	
	return bpchat_update_user_state( $user_id, 'offline' );
	
}

function bpchat_login_user( $user_id ) {
	
	bpchat_update_last_active( $user_id );//update last active time
	
	return bpchat_update_user_state( $user_id, 'online' );//may be we should remember the last state?
}


function bpchat_get_online_users( $limit = null, $page = 1 ) {

	$user_query_args = array(
			'page'				=> $page,
			'per_page'			=> $limit,
			);

	
	$meta_query = array(
		
		array(
			'key'	=> 'bpchat_state',
			'value' => 'offline',
			'compare'	=> '!='
		),
		
	);
	
	$user_query_args['meta_query'] = $meta_query;
	
		
	$user_id = get_current_user_id() ;
	
	if( $user_id ) {
		
		$user_query_args['exclude'] = $user_id;
	}	
		
	$chat_buddy_prefernce = bpchat_get_user_chat_preference( $user_id );
		// Only return matches of friends of this user.
		
	if ( $chat_buddy_prefernce == 'friends' && $user_id ) {
			$user_query_args['include'] = friends_get_friend_user_ids( $user_id );
	}

		$user_query_args = apply_filters( 'bp_chat_online_users_query_args', $user_query_args );
		
		if ( is_wp_error( $user_query_args ) ) {
			return $user_query_args;
		}
		
	$user_query = new WP_User_Query( $user_query_args );//BP_User_Query will not work because of it's use of user_ids clause
	
	return $user_query->get_results();

}

function bpchat_get_online_users_count(){
	return BPChat_User::get_online_users_count();
}

function bpchat_get_online_users_list( $echo = true) {

    $users = bpchat_get_online_users( null, 0 ); //$users;
    $total = 10;//bpchat_get_online_users_count();//total online users
    //something to sniff only those who are allowed to chat
    $my_id = get_current_user_id();

    $html = '';
	
    
    foreach ( (array) $users as $user ) {
		
        $html .= "<div class='friend_list_item'>";
        $html .= '<a class="online_friend" id="chat_with_' . $user->ID . '">';
        $html .= bp_core_fetch_avatar(array('item_id' => $user->ID, 'type' => 'thumb', 'width' => 32, 'height' => 32, 'class' => 'friend-avatar'));
        $html .= "<span class='disabled friend_list_item_orig_avatar_src'>" . bp_core_fetch_avatar(array('item_id' => $user->ID, 'type' => 'thumb', 'width' => 50, 'height' => 50, 'html' => false)) . "</span>";
        $html .= '<span class="friend_list_item_name">' . $user->display_name . '</span>';
        $html .= "<span class='clear'></span>";
        $html .= "</a><div class='clear'></div></div>";
    }

    echo $html;


  
}
