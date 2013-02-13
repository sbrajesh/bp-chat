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



/*users related*/
//get user status
//update user
//login
//logout
//get_conncted channels
//get connected peers
//
/**
 * 
 * 
 * 
 * User Management functions
 * 
 * 
 * 
 */
    function bpchat_get_user($user_id){

    }

    /* logout a user from chat session*/
    function bpchat_logout_user($user_id){
        //clear the is_logged in {$bp->chat->table_users
        return BPChat_User::logout($user_id);
    }

    /* login a user for chat*/
    function bpchat_login_user($user_id){
     //update an entry in bp_chat_users table and set the flag is_logged to 1
        return BPChat_User::login($user_id);

    }
/**
 * Is the user logged in
 * @param <type> $user_id
 * @return <type>
 */
    function bpchat_is_user_logged_in($user_id){
    //is user logged in or not
        $user=bpchat_get_user($user_id);
        return $user->is_logged;
    }

    function bpchat_get_user_status($user_id){
        $user=bpchat_get_user($user_id);
        return $user->status;
    }

    function bpchat_is_user_idle($user_id){

    }

    
    function bpchat_update_user_status($user_id,$status){

        }
    
    function bpchat_update_fetch_time($user_id){
        return BPChat_User::update_fetch_time($user_id);
        
        }

    function bpchat_get_last_fetch_time($user_id){
        return BPChat_User::get_fetch_time($user_id);
    }
    function bpchat_get_channels_for_user($user_id){
        $channels=BPChat_Channel::get_open_channel_for_user($user_id);
        return $channels;
    }

    function bpchat_get_messages_for_user($user_id){

    }

    function bpchat_get_offline_messages($user_id){

    }
    function bpchat_get_user_status_message($user_id){

    }
    function bpchat_set_user_status_message(){

    }

/***
 * 
 * 
 * Messages related
 * 
 * 
 */

    function bpchat_get_message($msg_id){
        //may not use this
    }



    function bpchat_get_other_party_ids($channel_id) {
       // return $chat->
      global $wpdb,$bp;
      $user_id=$bp->loggedin_user->id;
      $query="SELECT o.user_id FROM {$bp->chat->table_channel_users} i, {$bp->chat->table_channel_users} o where o.channel_id=i.channel_id AND i.user_id=%d";

      $ids=$wpdb->get_results($wpdb->prepare($query,$user_id));
      return $ids;
    }


    /* User manipulations */

    function bpchat_get_online_users($limit = null, $page = 1) {

        $users=BPChat_User::get_online_users();
        return $users;

    }

    function bpchat_get_online_users_count(){
        return BPChat_User::get_online_users_count();
    }
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



    ////login/logout functions
    //set the entry in user table
    //current mysql time
    function bpchat_get_current_mysql_time(){
        global $wpdb;
         $time = $wpdb->get_var("SELECT NOW() as time");
         return $time;
    }


    //get the friend list as a set to be used in query

    function bpchat_get_user_friend_list_as_set($user_id){
        global $bp;
    $friends=friends_get_friend_user_ids($user_id);
    if(!empty($friends)){
        $friend_list=join(",", $friends);
        $friend_list="( ".$friend_list." )";
        return  $friend_list;

    }
    return false;
    }

    function bpchat_has_friends_only_enabled($user_id){
       return apply_filters('bpchat_has_friend_only_enabled',BPChat_User::get_pref($user_id));//if 1:friends only, 0: sitewide
    }

  

    /*online user or iofline fix*/
    //get the current user online from their bp activity
    function bpchat_get_users_online_by_time() {
                    global $wpdb, $bp;
    $type='online';
    $limit=0;
                    $sql = array();

    //taken from bp-core-classes.js bp_core_get_users;
                    $sql['select_main'] = "SELECT DISTINCT u.ID as id";
                    $sql['select_active'] = ", um.meta_value as last_activity";
                    $sql['from'] = "FROM " . CUSTOM_USER_TABLE . " u LEFT JOIN " . CUSTOM_USER_META_TABLE . " um ON um.user_id = u.ID";
                    $sql['where'] = 'WHERE ' . bp_core_get_status_sql( 'u.' );
                    $sql['where_active'] = "AND um.meta_key = 'last_activity'";
                    $sql['where_online'] = "AND DATE_ADD( um.meta_value, INTERVAL 5 MINUTE ) >= UTC_TIMESTAMP()";
                    $sql[] = "ORDER BY um.meta_value DESC";


                    if ( $limit && $page )
                            $sql['pagination'] = $wpdb->prepare( "LIMIT %d, %d", intval( ( $page - 1 ) * $limit), intval( $limit ) );

                    /* Get paginated results */
                    $paged_users_sql = apply_filters( 'bpchat_get_users_online_from_bp_sql', join( ' ', (array)$sql ), $sql );
                    $paged_users     = $wpdb->get_col( $paged_users_sql );//we have user ids

                    return $paged_users;
            }
       /**
        * Set a User as Active
        * @param type $user_id 
        */     
      function   bpchat_update_last_active($user_id){
          BPChat_User::update_last_active($user_id);
      }

     
      function bpchat_is_disabled(){
          return apply_filters("bpchat_is_disabled",false);
      }


function bpchat_get_online_users_list($echo =true) {

    global $wpdb, $bp;
   
    $users = bpchat_get_online_users(null, 0); //$users;
    $total = bpchat_get_online_users_count();//total online users
    //something to sniff only those who are allowed to chat
    $my_id = $bp->loggedin_user->id;

    $html = "";
    if(!empty($users))
    foreach ($users as $u) {
        $html.="<div class='friend_list_item'>";
        $html.='<a class="online_friend" id="chat_with_' . $u->user_id . '">';
        $html.=bp_core_fetch_avatar(array('item_id' => $u->user_id, 'type' => 'thumb', 'width' => 32, 'height' => 32, 'class' => 'friend-avatar'));
        $html.="<span class='disabled friend_list_item_orig_avatar_src'>" . bp_core_fetch_avatar(array('item_id' => $u->user_id, 'type' => 'thumb', 'width' => 50, 'height' => 50, 'html' => false)) . "</span>";
        $html.='<span class="friend_list_item_name">' . bp_core_get_user_displayname($u->user_id) . '</span>';
        $html.="<span class='clear'></span>";
        $html.="</a><div class='clear'></div></div>";
    }

    echo $html;


  
}
  

function bpchat_show_user_preference(){
    return apply_filters('bpchat_show_user_preference',true);
}
   
?>