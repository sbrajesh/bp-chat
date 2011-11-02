<?php
//ajax binding

//send updated online users list
add_action("wp_ajax_update_online_users_list", "bpchat_get_online_users_list");

//send the updated online users count
add_action("wp_ajax_get_online_users_count", "bpchat_show_online_users_count");
//process request for new channel
add_action("wp_ajax_request_channel", "bpchat_request_channel");
add_action("wp_ajax_request_channel_reopen", "bpchat_request_channel_reopen");

//save messages
add_action("wp_ajax_save_chat_msg", "bpchat_save_messages");


//get updates for user

add_action("wp_ajax_chat_check_updates", "bpchat_get_updates_for_user");

//close channel for user
add_action("wp_ajax_close_channel", "bpchat_close_chat_channel");


/*
 * List online users
 */

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

if(is_admin ())
        die();
}


/* send the number of online users back */

function bpchat_show_online_users_count() {
    echo bpchat_get_online_users_count();
    if(is_admin ())
        die();//some day, I will add the chatbox to appear in the wp backend too
}


/**
 * Create a new channel for the user  if a channel is not allocated or if already a channel exists, jsut return the channel id
 */

function bpchat_request_channel() {

    global $bp, $wpdb;
    $initiator = $bp->loggedin_user->id;//the person who request a new chat
    $other_user = $_POST['user_id']; //the user id of the person being chatting
    //make sure to check if the user belongs to his/her friend list
    $channel_id = bpchat_obtain_channel($initiator, $other_user);
    //  print_r($chat);
    if (!empty($channel_id)) {
        bpchat_update_channel_user($channel_id, $initiator, "open"); //($chat, $user1); //keep the chat open on current user's side
    }
    echo $channel_id;
    if(is_admin ())
        die();
}
/**
 * Reopen existing channel for user xyz
 * just set the channel_status=open for the requesting user
 */
function bpchat_request_channel_reopen(){
     global $bp, $wpdb;
    $initiator = $bp->loggedin_user->id;//the person who request a new chat
    $channel_id = $_POST['channel_id']; //the user id of the person being chatting
     
    if (!empty($channel_id)) {
        bpchat_update_channel_user($channel_id, $initiator, "open"); //($chat, $user1); //keep the chat open on current user's side
    }
    echo $channel_id;
    if(is_admin ())
        die();
}
/* save chat message to database */

function bpchat_save_messages() {
    global $wpdb, $bp;
    $new_message = new BPChat_Messages();
    $new_message->message = esc_html($_POST["message"]);
    $new_message->channel_id = $_POST["channel_id"];
    $new_message->sender_id = $bp->loggedin_user->id;
    
    $new_message->save();
   // open this channel, we don't care anymore who are subscribed to this channel
    bpchat_update_all_channel_user($new_message->channel_id,"open");//status of the channel
   
    //update senders last activity time
    bpchat_update_last_active($new_message->sender_id);//update last active time for sender
   
    echo json_encode(array("name"=>bp_get_loggedin_user_fullname(),"id"=>$new_message->id));
    if(is_admin ())
        die();
}



/* * ***************** Close chat window update status */

function bpchat_close_chat_channel() {
    global $bp,$wpdb;
    //close_chat_win;
    $channel_id = intval($_POST["channel_id"]);
    $user_id = $bp->loggedin_user->id;
    if (empty($channel_id))
        return;
 //update channel status for me to closed?
    bpchat_update_channel_user($channel_id, $user_id, 'closed');//extra query may be ?
}




/** check for the new chat requests, list which which we are chatting currently or the messages we have recieved for the user*/
function bpchat_get_updates_for_user() {
    
    global $bp, $wpdb;
    $user_id = $bp->loggedin_user->id;

   // $last_fetch_time = bpchat_get_last_fetch_time($user_id); //get the last fetch time as mysql date time expression
    $last_fetch_time=$_POST["fetch_time"];
//$time=gmdate("Y-m-d H:i:s",  time());
  

   // $query = "SELECT msg.id,msg.channel_id, msg.message, msg.sender_id,msg.message,msg.sent_at FROM {$bp->chat->table_chat_messages} msg, WHERE msg.channel_id IN( SELECT channel_id FROM {$bp->chat->table_channel_users} where user_id=%d and status <> 'closed') and msg.sent_at >= '".$last_fetch_time."'  ORDER BY msg.sent_at ASC ";
 $query = "SELECT msg.id,msg.channel_id, msg.message, msg.sender_id,msg.message,msg.sent_at FROM {$bp->chat->table_chat_messages} msg WHERE msg.channel_id IN( SELECT channel_id FROM {$bp->chat->table_channel_users} where user_id=%d and status <> 'closed') and msg.sent_at >= '".$last_fetch_time."' ORDER BY msg.sent_at ASC ";


    $q = $wpdb->prepare($query, $user_id);

    $messages = $wpdb->get_results($q); //array of message objects
     $time=bpchat_get_current_mysql_time();
    $messages = bpchat_extend_messages($messages);

    $query_status = "SELECT c.channel_id,c.status, c.user_id,u.is_online,IF (DATE_ADD( u.last_active_time, INTERVAL 30 SECOND ) >= NOW(), 'active','idle') as user_status  FROM {$bp->chat->table_channel_users} c,{$bp->chat->table_chat_users} u WHERE c.channel_id IN( SELECT channel_id FROM {$bp->chat->table_channel_users} where user_id=%d and status <> 'closed') AND c.user_id!=%d and u.user_id=c.user_id ORDER BY channel_id DESC ";
    
    $status=$wpdb->get_results($wpdb->prepare($query_status,$user_id,$user_id));
    //update last fetch time for user
    bpchat_update_fetch_time($user_id); //update the fetch time
    
    $response = array("messages" => $messages, "fetch_time" => $time,"status"=>$status);
    
    echo json_encode($response);
    if(is_admin ())
        die();
}


//change preference
function bpchat_change_preference(){
$preference=$_POST["prefrence"];
if(!is_user_logged_in())
    return;
global $bp;
if($preference=="friend_users")
    $friend_only=1;
else
    $friend_only=0;//user meta does not allow stroing false/0 values
bpchat_set_pref($bp->loggedin_user->id,$friend_only);
}


add_action("wp_ajax_bpchat_change_preference","bpchat_change_preference");
?>