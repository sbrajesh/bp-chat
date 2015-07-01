<?php

/**
 * Extend chat messages by injecting the username, avatar image and escaping the message
 * 
 * @param array $messages
 * @param type $uid
 * @return type
 */    
function bpchat_extend_messages( $messages, $uid = 'sender_id' ) {

    if( empty( $messages ) )
        return $messages;
	
	$message_count = count( $messages );
    
	for ( $i = 0; $i < $message_count; $i++ ) {
		
            $messages[$i]->name		= bp_core_get_user_displayname( $messages[$i]->{$uid} );
            $messages[$i]->message	= stripslashes( $messages[$i]->message );
           
            $messages[$i]->thumb	= bp_core_fetch_avatar( array( 'item_id' => $messages[$i]->{$uid}, 'type' => 'thumb', 'width' => 50, 'height'=> 50, 'html' => false ) );
    }
    
	return $messages;

}