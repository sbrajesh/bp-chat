<?php
/* 
 * Business functions
 * 
 */
/**
 * Functions related to the Chat Channel Management
 * Channels are like a multidirectional unique communication link between a set of users, for easier understanding, you can think of channel as a synonym with the Chat Window
 */

/***
 * 
 * 
 * Messages related
 * 
 * 
 */


    function bpchat_get_other_party_ids($channel_id) {
       // return $chat->
      global $wpdb ;
	  $bpchat = bp_chat();
      $user_id=$bp->loggedin_user->id;
	  
      $query="SELECT o.user_id FROM {$bpchat->table_name_channel_users} i, {$bpchat->table_name_channel_users} o where o.channel_id=i.channel_id AND i.user_id=%d";

      $ids=$wpdb->get_results($wpdb->prepare($query,$user_id));
      return $ids;
    }


    /* User manipulations */

    /* get the users in current  room */
    //get active users in current room
    //get friends

    //GET BLOCKED
    //get offline
    //get sitewide online
    //actions
    //invite to chat
    //send message
    //recieve message
    //update room


    /* * ******utility function */
    //extend message to show the user avatar
    function bpchat_extend_messages($msgs, $uid="sender_id") {

    if(empty($msgs))
        return $msgs;
    //add oneextra field to the objects
        for ($i = 0; $i < count($msgs); $i++){
            $msgs[$i]->name = bp_core_get_user_displayname($msgs[$i]->{$uid});
            $msgs[$i]->message =stripslashes($msgs[$i]->message);
           
            $msgs[$i]->thumb = bp_core_fetch_avatar(array('item_id' => $msgs[$i]->{$uid}, 'type' => 'thumb', 'width'=>50,'height'=>50,'html'=>false));
        }
        return $msgs;
    }

