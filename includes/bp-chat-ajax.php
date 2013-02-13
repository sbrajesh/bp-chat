<?php
//ajax binding
class BPChatAjaxHelper{
    private static $instance;
    function __construct(){

        //send updated online users list
        add_action('wp_ajax_bpchat_update_online_users_list', array($this,'show_online_users_list'));

        //send the updated online users count
        add_action('wp_ajax_bpchat_get_online_users_count', array($this,'show_online_users_count'));
      
        //process request for new channel
        add_action('wp_ajax_bpchat_request_channel', array($this,'request_channel'));
        add_action('wp_ajax_bpchat_request_channel_reopen', array($this,'request_channel_reopen'));
        //close channel for user
        add_action('wp_ajax_bpchat_close_channel', array($this,'close_channel'));
        
        //save messages
        add_action('wp_ajax_bpchat_save_message', array($this,'save_messages'));


        //get updates for user

        add_action('wp_ajax_bpchat_check_updates', array($this,'get_updates_for_user'));

        
        add_action('wp_ajax_bpchat_change_preference',array($this,'change_user_preference'));
    }


     function get_instance(){
        if(!isset(self::$instance))
                self::$instance=new self();
        return self::$instance;
    }

    /**
     * Create a new channel for the user  if a channel is not allocated or if already a channel exists, jsut return the channel id
     */

    function request_channel() {

        global $bp, $wpdb;
        
        $initiator = $bp->loggedin_user->id;//the person who request a new chat
        $other_user = $_POST['user_id']; //the user id of the person being chatting
        //make sure to check if the user belongs to his/her friend list
        $channel_id = bpchat_obtain_channel($initiator, $other_user);
        
        if (!empty($channel_id)) {
            bpchat_update_channel_user($channel_id, $initiator, "open"); //($chat, $user1); //keep the chat open on current user's side
        }
        
        echo $channel_id;
            
        exit(0);
        
         
        }
     /**
     * Reopen existing channel for user xyz
     * just set the channel_status=open for the requesting user
     */
    function request_channel_reopen(){
         global $bp, $wpdb;
        $initiator = $bp->loggedin_user->id;//the person who request a new chat
        $channel_id = $_POST['channel_id']; //the user id of the person being chatting

        if (!empty($channel_id)) {
            bpchat_update_channel_user($channel_id, $initiator, "open"); //($chat, $user1); //keep the chat open on current user's side
        }
        echo $channel_id;

           exit(0);
    }
    
    /*
     * Close chat window update status
     * */

    function close_channel() {
        global $bp,$wpdb;
        //close_chat_win;
        $channel_id = intval($_POST["channel_id"]);
        $user_id = $bp->loggedin_user->id;
        if (empty($channel_id))
            return;
        //update channel status for me to closed?
        bpchat_update_channel_user($channel_id, $user_id, 'closed');//extra query may be ?
    }

    /* send the number of online users back */
    function show_online_users_list() {
        echo bpchat_get_online_users_list();

           exit(0);//some day, I will add the chatbox to appear in the wp backend too
    }
    
    function show_online_users_count() {
        echo bpchat_get_online_users_count();

            exit(0);//some day, I will add the chatbox to appear in the wp backend too
    }



    //change preference
    function change_user_preference(){
        $preference=$_POST['prefrence'];
        if(!is_user_logged_in())
            return;
        global $bp;
        if($preference=='friend_users')
            $friend_only=1;
        else
            $friend_only=0;//user meta does not allow stroing false/0 values
        BPChatSettings::update_user_preference($bp->loggedin_user->id,$friend_only);
        exit(0);
    }

    /* save chat message to database */

function save_messages() {
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
   
         exit(0);
}


/** check for the new chat requests, list which which we are chatting currently or the messages we have recieved for the user*/
function get_updates_for_user() {
    
    global $bp, $wpdb;
    $user_id = get_current_user_id();

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
   
         exit(0);
}


}//end of ajax helper

BPChatAjaxHelper::get_instance();

?>