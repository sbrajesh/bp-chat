<?php
/* 
 * Business functions
 * 
 */
/**
 * Functions related to the Chat Channel Management
 * Channels are like a multidirectional unique communication link between a set of users, for easier understanding, you can think of channel as a synonym with the Chat Window
 */

/**
 * retuns an existing Channel identified by the channel id
 * @param type $channel_id
 * @return BPChat_Channel : Channel Object
 */
    function bpchat_get_channel($channel_id){
        return new BPChat_Channel($channel_id);
    }
/**
 * return channel_id whether existing open channel or create a new one
 * @param type $initiator
 * @param type $requested
 * @return int channel_id
 */

    function bpchat_obtain_channel($initiator,$requested){
         if($channel_id= BPChat_Channel::get_channel_between($initiator,$requested))
                 return $channel_id;
         else
             return bpchat_create_channel (array('initiator_id'=>$initiator,'invited_id'=>$requested));
}

/**
 * Create a Channel and return Channel id
 * @param type $args
 * @return type 
 */
    function bpchat_create_channel($args){

            $default=array('initiator_id'=>'',
                           'invited_id'=>'',
                           'is_multichat'=>0,//is one to one connection
                           'is_open'=>true,//connection is requested so channel is open
                           'status'=>'initiated'

                );

            $arg=wp_parse_args($args, $default);
            extract($arg);
            $channel=new BPChat_Channel();//create a new channel
            $channel->status=$status;
            $channel->is_open=$is_open;
            $channel->is_multichat=$is_multichat;

            //save channel
            if($channel->save()){
                //if channel created, we have the channel id now
                //make two entry in the user_channels table
                bpchat_add_channel_user($channel->id,$initiator_id,"open")  ;   //
                bpchat_add_channel_user($channel->id,$invited_id,"requested")  ;   //
                //   bpchat_add_channel_user($channel->id,); //since the oth
                //other user has not responded yet, let the channel be one sided); //since the other user has not responded yet, let the channel be one sided
                //bpchat_add_channel_user($in_id,$channel->id);
           return $channel->id;
                }
        return false;
    }

/**
 * Close a Chat Channel
 * @param type $channel_id 
 */
    function bpchat_close_channel($channel_id){
        BPChat_Channel::close($channel_id);//close channel
    }
    //have all the users left the channel
    function bpchat_is_channel_idle($channel_id){

    }

    function bpchat_is_channel_open($channel_id){

    }

    function bpchat_is_channel_multichat($channel_id){
        $channel=bpchat_get_channel($channel_id);
        return $channel->is_multichat;
    }
    
    
    /**
     *
     * 
     * 
     *  Functions related to Channel's User management
     * 
     * 
     * 
     */
    
    /**
     * Add a User to an existing Channel. Makes a user participant in a chat
     * @param type $channel_id
     * @param type $user_id
     * @param type $status
     * @return type 
     */
    function bpchat_add_channel_user($channel_id,$user_id,$status){
        return BPChat_Channel::add_user($channel_id,$user_id,$status);
       }
       
    /**
     * removes a user from a Chat channel
     * @param type $channel_id
     * @param type $user_id
     * @return type 
     */
    function bpchat_remove_channel_user($channel_id,$user_id){
       return BPChat_Channel::remove_user($channel_id,$user_id);
    }
    /**
     * Close the opened channel for User
     * @param type $channel_id
     * @param type $user_id
     * @return type 
     */
    function bpchat_close_channel_for_user($channel_id,$user_id){
        return BPChat_Channel::close_channel_for_user($channel_id,$user_id);
    }
    
    function bpchat_update_channel_user($channel_id,$user_id,$status){
         return BPChat_Channel::update_user($channel_id,$user_id,$status);
    }
    
    function  bpchat_update_all_channel_user($channel_id,$status){
         return BPChat_Channel::update_channel_for_all($channel_id,$status);
    }


    ///get channel users
    function bpchat_get_channel_users($channel_id){
        $users=BPChat_Channel::get_all_users($channel_id);
        return apply_filters("bpchat_get_channel_users",$users);
    }
    function bpchat_get_active_channel_users($channel_id){

    }
    /**
     * 
     * 
     *  Functions related to Channel message management
     * 
     * 
     * 
     */    
    
/**
 * Get all messages in current channel
 */

    function bpchat_get_channel_messages($channel_id){
        $messages=BPChat_Channel::get_all_messages($channel_id);
        $messages=bpchat_extend_messages($messages);
        return $messages;
    }
    
    //get recent channel messages,$time is mysql formatted date time
    function bpchat_get_recent_channel_messages($channel_id,$time){
        $messages=BPChat_Channel::get_messages_after($time);
        $messages=bpchat_extend_messages($messages);
        return $messages;
    }

//get unread messages


//get messages after time


    function bpchat_get_channels_for_user($user_id){
        $channels=BPChat_Channel::get_open_channel_for_user($user_id);
        return $channels;
    }


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

