<?php
/**
 * Get all possible user states
 * 
 * @since 1.1.0
 * 
 * @return type
 */
function bpchat_get_all_user_states() {
	
	$states = array (
		'online'	=> __( 'Online', 'bp-chat' ),
		'away'		=> __( 'Away', 'bp-chat' ),
		'offline'	=> __( 'Offline', 'bp-chat' ),
		'busy'		=> __( 'Busy', 'bp-chat' ),
		'idle'		=> __('Idle', 'bp-chat' ),
	);
	
	return apply_filters( 'bpchat_user_states', $states );
	
}
/**
 * Get the user state key
 * 
 * Possible values are online|away|offline|busy
 * 
 * @since 1.1.0
 * 
 * @param type $user_id
 * @return string
 */
function bpchat_get_user_state( $user_id ) {
	
	$state = bp_get_user_meta( $user_id, 'bpchat_state',  true );
	
	if( empty( $state ) ) {
		$state = 'offline';//if no state is set, the user is offline
	}
	
	return $state;
}
/**
 * Update user state
 * 
 * @since 1.1.0
 * 
 * @param type $user_id
 * @param type $state possible values online|offline|busy|away
 * @return type
 */
function bpchat_update_user_state( $user_id, $state ) {
	
	$all_status = bpchat_get_all_user_states();
	
	if( empty( $state ) || ! is_string( $state ) || ! isset( $all_status[$state] ) ) {
		return false;
	}
	
	bp_update_user_meta( $user_id, 'bpchat_state', $state );
}
/**
 * Get the label for current user state
 * 
 * @since 1.1.0
 * 
 * @param type $state
 * @return string
 */
function bpchat_get_user_state_label( $state ) {
	
	if( empty( $state ) ) {
		return '';
	}	
	
	$all_states = bpchat_get_all_user_states();
	
	if( isset( $all_states[ $state ] ) ) {
	
		return $all_states[$state];
	}	
	
	return __( 'Unknown', 'bp-chat' );
	
}
/**
 * Get the user status message
 * 
 * @since 1.1.0
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
 * @since 1.1.0
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
 * @since 1.1.0
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
	
	if( bpchat_get_user_chat_preference( $user_id ) == 'friends' ) {
			$enabled = true;
	}		
	
   return apply_filters( 'bpchat_has_friend_only_enabled', $enabled, $user_id  );
}

/**
 * Updates last active time for the given user
 * 
 * @since 1.1.0
 * @param type $user_id
 * @return type
 */
function bpchat_update_last_active( $user_id ) {
	
	$current_time = current_time( 'timestamp' );
	
	return bp_update_user_meta( $user_id, 'bpchat_last_active', $current_time );
	
}
/**
 * Get the last active time as timestamp for the current user
 * 
 * @since 1.1.0
 * 
 * @param type $user_id
 * @return type
 */
function bpchat_get_last_active( $user_id ) {
	
	return  bp_get_user_meta( $user_id, 'bpchat_last_active', true );
	
}
/**
 * Update When the last time a fetch request was made?
 * 
 * @since 1.1.0
 * 
 * @param type $user_id
 * @return type
 */
function bpchat_update_fetch_time( $user_id ) {
	
	$current_time = current_time( 'timestamp' );
	
	return bp_update_user_meta( $user_id, 'bpchat_last_fetch_time', $current_time );

}
/**
 * Get when last fetch mesage was called
 * 
 * @param type $user_id
 * @return type
 */
function bpchat_get_last_fetch_time( $user_id ) {
	
	return bp_get_user_meta( $user_id, 'bpchat_last_fetch_time', true );
}


/**
 * Mark a user as offline
 * 
 * @param type $user_id
 * @return type
 */
function bpchat_logout_user( $user_id ) {
	
	return bpchat_update_user_state( $user_id, 'offline' );
	
}
/**
 * Mark a user online
 * 
 * @param type $user_id
 * @return type
 */
function bpchat_login_user( $user_id ) {
	
	bpchat_update_last_active( $user_id );//update last active time
	
	return bpchat_update_user_state( $user_id, 'online' );//may be we should remember the last state?
}
/**
 * @since 1.1.0
 * 
 * @internal Used for generating the query args to fetch online users
 * @param type $limit
 * @param type $page
 * @return type
 */
function bpchat_get_online_user_query_args( $limit = null, $page = 1 ) {

	$user_query_args = array(
			'page'				=> $page,
			'per_page'			=> $limit,
			'count_total'		=> false,
			);

	
	$meta_query = array(
		
		array(
			'key'	=> 'bpchat_state',
			'value' => array( 'online', 'idle', 'busy', 'away' ),
			'compare'	=> 'IN'
		),
		
	);
	
	$user_query_args['meta_query'] = $meta_query;
	
		
	$user_id = get_current_user_id() ;
	
	if( $user_id ) {
		
		$user_query_args['exclude'] = (array) $user_id;
	}	
		
	$chat_buddy_prefernce = bpchat_get_user_chat_preference( $user_id );
		// Only return matches of friends of this user.
		
	if ( $chat_buddy_prefernce == 'friends' && $user_id ) {
		//we need to work on it as wp will include all frinds which is not what we want
		$user_query_args['include'] = friends_get_friend_user_ids( $user_id );
	}

		
	$user_query_args = apply_filters( 'bp_chat_online_users_query_args', $user_query_args );
		
	
	return $user_query_args;	
}
/**
 * @since 1.0.0
 * @since 1.1.0 implementation changed to use Wp_Query
 * 
 * @return type
 */
function bpchat_get_online_users() {

	$user_query = new WP_User_Query( bpchat_get_online_user_query_args() );//BP_User_Query will not work because of it's use of user_ids clause
	
	return $user_query->get_results();

}

function bpchat_get_online_users_count() {
	
	if( ! is_user_logged_in() ) {
		return 0;
	}	
	
	$args = bpchat_get_online_user_query_args();
	
	$args['count_total'] = true;
	$args['fields'] = 'ID';//
	
	$user_query = new WP_User_Query( $args );
	
	return $user_query->get_total();
}

function bpchat_get_online_users_list( $echo = true ) {

    $users = bpchat_get_online_users( null, 0 ); //$users;
   
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

/**
 * Mark users idle if the user is not active for last 2 minutes
 * @since 1.0.0
 * @since 1.1.0 updated to use usermeta table
 * 
 * @global type $wpdb
 */
function bpchat_mark_users_idle() {

	      //any user who did not fetch the message for last time, means he has closed the browser or has a network disconnection or has logged out, sio let us clean the table
      global $wpdb;
	  
	
	//for all users who have been inactive for last 2 minutes, set them away
	  
	  $checked_time = current_time('timestamp') - 120;//two minutes ago
	  
	  $query = $wpdb->prepare( "UPDATE {$wpdb->usermeta} SET bpchat_state=%s WHERE user_id IN ( SELECT user_id FROM {$wpdb->usermeta}  WHERE meta_key = %s AND meta_value < %d ) ", 'idle', 'bpchat_last_active_time', $checked_time );
      
	  $wpdb->query( $query );
      
}