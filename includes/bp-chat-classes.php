<?php

class BPChat_User {

    var $id;
    var $status; //available,invisibile,offline etc
    var $prefs; //friends only,sitewide
    var $is_logged;
    var $status_message;
    
    function __construct( $id = null ) {
        if ( $id ) {
            $this->id = $id;
            $this->populate( $this->id );
        }
    }

    function populate() {
        global $wpdb, $bp;

        
    }

//static functions
 
    function get_friends_online() {

    }

    function get_all_friends() {

    }

    function set_status() {

    }

    function get_status() {
        
    }
//static methods

 //user has an entry in chat_user data base
    function user_exists( $user_id ) {
        global $bp,$wpdb;
        $q = "SELECT * FROM {$bp->chat->table_chat_users} WHERE user_id=%d";

        if( $row = $wpdb->get_row( $wpdb->prepare( $q, $user_id ) ) )
            return true;
        else
            return false;
       
    }
    /*
     * set the status of user as loggedin/out
     */
function login_logout( $user_id, $status = 0 ){
    global $wpdb,$bp;
    $query = $wpdb->prepare( "UPDATE  {$bp->chat->table_chat_users} set is_online=%d where user_id=%d", $status, $user_id );
 
    return $wpdb->query( $query );
 
}
/* set the status of user as logged in*/
   function login( $user_id ){
      
       if( BPChat_User::is_user_logged_in( $user_id ) )
          return;//user is already logged in
       //check if user is already online
       //otherwise user may not be online or my not be existing in the

       if( BPChat_User::user_exists( $user_id ) )
         return  BPChat_User::login_logout( $user_id, 1 );
       else
           return BPChat_User::add_user( $user_id ); //first time user
      
   }
function is_user_logged_in( $user_id ){
    global $wpdb, $bp;
    $query = $wpdb->prepare( "SELECT user_id FROM  {$bp->chat->table_chat_users} where is_online = %d AND user_id = %d", 1, $user_id );

    return $wpdb->get_var( $query );
 
}

function add_user( $user_id ){
    global $wpdb, $bp;
    $query = $wpdb->prepare( "INSERT INTO {$bp->chat->table_chat_users} (user_id, is_online) VALUES( %d, %d )",$user_id,1);
   
    return $wpdb->query( $query );
   }

  function logout( $user_id ){
    //clear the logged in status in the stable
      //if(BPChat_User::user_exists($user_id)) //no need to check the existence
         return BPChat_User::login_logout( $user_id, 0 );
     
  }

//get currently online users, will return all users expect the user for whom the query is made
  function get_online_users( $page = null, $limit = null ){
       global $wpdb, $bp;
       
    if ($limit && $page)
        $pag_sql = $wpdb->prepare(" LIMIT %d, %d", intval(( $page - 1 ) * $limit), intval($limit));
    //let us check if friends component is active and check user preferences for friends only chat
    $friend_list=bpchat_get_user_friend_list_as_set($bp->loggedin_user->id);
    if(bpchat_has_friends_only_enabled($bp->loggedin_user->id)){
       
        if( empty( $friend_list ) )
            return false;

        $paged_users_sql = "SELECT user_id FROM {$bp->chat->table_chat_users} WHERE is_online=1 AND user_id IN {$friend_list} {$pag_sql}";//// "SELECT DISTINCT um.user_id FROM " . CUSTOM_USER_META_TABLE . " um LEFT JOIN " . CUSTOM_USER_TABLE . " u ON u.ID = um.user_id WHERE um.meta_key = 'last_activity' AND u.spam = 0 AND u.deleted = 0 AND u.user_status = 0 AND DATE_ADD( FROM_UNIXTIME(um.meta_value), INTERVAL 5 MINUTE ) >= NOW() ORDER BY FROM_UNIXTIME(um.meta_value) DESC{$pag_sql}", $pag_sql);
        return $wpdb->get_results( $paged_users_sql );
        
     }else{

          //incase of sitewide pref show users with sitewide pref or in the friend_list

          $sql = "SELECT user_id FROM {$bp->chat->table_chat_users} WHERE is_online=1 AND user_id!=%d AND friends_only=0 ";//{$pag_sql}

          if( $friend_list ) {
                $sql2 = "SELECT user_id FROM {$bp->chat->table_chat_users} WHERE is_online=1 AND user_id IN {$friend_list}";
                $sql = $sql . " UNION " . $sql2 ;
          }

          $paged_users_sql = $wpdb->prepare( $sql, $bp->loggedin_user->id );//"SELECT user_id FROM {$bp->chat->table_chat_users} where is_online=1 AND user_id!=%d AND friends_only=0 {$pag_sql}",$bp->loggedin_user->id);//// "SELECT DISTINCT um.user_id FROM " . CUSTOM_USER_META_TABLE . " um LEFT JOIN " . CUSTOM_USER_TABLE . " u ON u.ID = um.user_id WHERE um.meta_key = 'last_activity' AND u.spam = 0 AND u.deleted = 0 AND u.user_status = 0 AND DATE_ADD( FROM_UNIXTIME(um.meta_value), INTERVAL 5 MINUTE ) >= NOW() ORDER BY FROM_UNIXTIME(um.meta_value) DESC{$pag_sql}", $pag_sql);
          $paged_users = $wpdb->get_results( $paged_users_sql );

    return  $paged_users;//, 'total' => $total_users);

    }
    return false;
  }
  
//get current online users count
  function get_online_users_count(){
      global $wpdb,$bp;

      $total_users_sql = "SELECT DISTINCT COUNT(user_id) FROM {$bp->chat->table_chat_users} WHERE is_online=1 and user_id!=%d";

      
      $friend_list = bpchat_get_user_friend_list_as_set( $bp->loggedin_user->id );
      
      if( bpchat_has_friends_only_enabled( $bp->loggedin_user->id ) ) {

            if( empty( $friend_list ) )
                return false;

            $paged_users_sql = "SELECT DISTINCT COUNT(user_id) FROM {$bp->chat->table_chat_users} WHERE is_online=1 AND user_id in {$friend_list} {$pag_sql}";//// "SELECT DISTINCT um.user_id FROM " . CUSTOM_USER_META_TABLE . " um LEFT JOIN " . CUSTOM_USER_TABLE . " u ON u.ID = um.user_id WHERE um.meta_key = 'last_activity' AND u.spam = 0 AND u.deleted = 0 AND u.user_status = 0 AND DATE_ADD( FROM_UNIXTIME(um.meta_value), INTERVAL 5 MINUTE ) >= NOW() ORDER BY FROM_UNIXTIME(um.meta_value) DESC{$pag_sql}", $pag_sql);
            return $wpdb->get_var( $paged_users_sql );

      }else {

          //incase of sitewide pref show users with sitewide pref or in the friend_list

          $sql = "SELECT user_id FROM {$bp->chat->table_chat_users} WHERE is_online=1 AND user_id!=%d AND friends_only=0 ";//{$pag_sql}

          if($friend_list){
            $sql2="SELECT user_id FROM {$bp->chat->table_chat_users} where is_online=1 AND user_id in {$friend_list}";
            $sql=$sql." UNION ".$sql2 ;
          }
     
      $paged_users_sql = $wpdb->prepare($sql,$bp->loggedin_user->id);//"SELECT user_id FROM {$bp->chat->table_chat_users} where is_online=1 AND user_id!=%d AND friends_only=0 {$pag_sql}",$bp->loggedin_user->id);//// "SELECT DISTINCT um.user_id FROM " . CUSTOM_USER_META_TABLE . " um LEFT JOIN " . CUSTOM_USER_TABLE . " u ON u.ID = um.user_id WHERE um.meta_key = 'last_activity' AND u.spam = 0 AND u.deleted = 0 AND u.user_status = 0 AND DATE_ADD( FROM_UNIXTIME(um.meta_value), INTERVAL 5 MINUTE ) >= NOW() ORDER BY FROM_UNIXTIME(um.meta_value) DESC{$pag_sql}", $pag_sql);
     return count($wpdb->get_results($paged_users_sql,ARRAY_A));
            }

//      $total_users = $wpdb->get_var($wpdb->prepare($total_users_sql,$bp->loggedin_user->id));

     // return intval($total_users);
  }
//clean the logged in user table
  function cleanup(){
      //any user who did not fetch the message for last time, means he has closed the browser or has a network disconnection or has logged out, sio let us clean the table
      global $wpdb,$bp;
      $query="UPDATE {$bp->chat->table_chat_users} SET is_online=%d where DATE_ADD( last_active_time, INTERVAL 2 MINUTE ) <= NOW()";
      $wpdb->query($wpdb->prepare($query,0));
      return true;
  }
 /**
  * Update the last message fetch time for the user
  * @param $user_id the user id for which we want to set the fetch time
  */
  function update_fetch_time($user_id){
      global $wpdb,$bp;
      $query="UPDATE {$bp->chat->table_chat_users} SET last_fetch_time= NOW() WHERE user_id=%d";
      $wpdb->query($wpdb->prepare($query,$user_id));
      return true;
  }
  function get_fetch_time($user_id){
      global $wpdb,$bp;
      $query="SELECT last_fetch_time from {$bp->chat->table_chat_users} where user_id=%d";
      $time=$wpdb->get_var($wpdb->prepare($query,$user_id));
      return $time;
  }
function update_last_active($user_id){
    global $wpdb,$bp;
      $query="UPDATE {$bp->chat->table_chat_users} SET last_active_time= NOW() WHERE user_id=%d";
      $wpdb->query($wpdb->prepare($query,$user_id));
      return true;
}
function set_pref($user_id,$pref){
    global $wpdb,$bp;
      $query="UPDATE {$bp->chat->table_chat_users} SET friends_only=%d WHERE user_id=%d";
      $wpdb->query($wpdb->prepare($query,$pref,$user_id));
      return true;
}

function get_pref($user_id){
 global $bp,$wpdb;
 $query="SELECT friends_only from {$bp->chat->table_chat_users} where user_id=%d";
 return $wpdb->get_var($wpdb->prepare($query,$user_id));
}
}
//end of bp_chat_user class
/**
 * BPChat_Messages class to handle messages
 */

class BPChat_Messages {

    var $id;
    var $sender_id;
    var $channel_id;//the current channel
    var $message;
    var $sent_at;
    

    function __construct($id=null) {
        if ($id) {
            $this->id = $id;
            $this->populate($this->id);
        }
    }

    function populate() {
        global $wpdb, $bp;

        if ($row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$bp->chat->table_chat_messages} WHERE id = %d", $this->id))) {
            $this->id=$row->id;
            $this->sender_id = $row->sender_id;
            $this->channel_id = $row->channel_id;
            $this->message = esc_js($row->message);

            $this->sent_at = $row->sent_at;
           
        }
    }

    function save() {
        global $wpdb, $bp;
       /* Call a before save action here */
        do_action('bpchat_messages_data_before_save', $this);

        if ($this->id) {
            // Update
            $result = $wpdb->query($wpdb->prepare(
                                   "UPDATE {$bp->chat->table_chat_messages} SET
                                    sender_id=%d,
                                    channel_id=%d,
                                    message = %s
                                    WHERE id = %d",
                                    
                                    $this->sender_id,
                                    $this->channel_id,
                                    $this->message,
                                    $this->id
                            ));
        } else {
            // Save,Insert new
            $query = $wpdb->prepare(
                                "INSERT INTO {$bp->chat->table_chat_messages}
                                (
                                 sender_id,
                                 channel_id,
                                 message
                                 
				) VALUES ( %d, %d, %s)",
                            
                                $this->sender_id,
                                $this->channel_id,
                                $this->message
                                
            );

          
            $result = $wpdb->query($query);
        }

        if (false === $result)/* because in updates it will retun Zero and false for something went wrong */
            return false;

        if (!$this->id) {
            $this->id = $wpdb->insert_id;
        }

        /* Add an after save action here */
        do_action('bp_chat_messages_data_after_save', $this);

      return $result;
    }

//statuc methids
    function get_messages_for_user($user_id=null) {
        global $wpdb, $bp;
        if (!$user_id)
            $user_id = $bp->loggedin_user->id; //if not set fetch for the login user
   
    }

 
function get_offline_messages($user_id){

}
/**
 * Static method to retrieve the messages in current channel
 */
function get_all_messages_for_channel($channel_id){
      global $wpdb, $bp;

      $sql = "SELECT * FROM  {$bp->chat->table_chat_messages} msg WHERE msg.channel_id = %d ";
      $msgs = $wpdb->get_results($wpdb->prepare($sql,$channel_id));
      return $msgs; ///return array of message objects
}


function get_messages_after_time($channel_id,$time){
 //from unix_time
    global $wpdb, $bp;
    //get all messages for the channel
      $sql = "SELECT * FROM  {$bp->chat->table_chat_messages} msg WHERE msg.channel_id = %d and msg.sent_at > FROM_UNIXTIME($time)";
      $msgs = $wpdb->get_results($wpdb->prepare($sql,$channel_id));

      return $msgs; ///return array of message objects
}

}
//end of class




///channel class
class BPChat_Channel{
var $is_open;
var $is_private;
var $users;
var $total_users;//total user connected to the channel at this moment
var $last_message;
var $last_message_time;
var $is_multichat=false;
var $title;//the title of the channel, may be chatting with xyz or group chat  or what ever

    function bpchat_channel($id=null){
        if(!empty($id))
            $this->populate($id);
    }

    function populate($id){
           global $wpdb, $bp;

           $query="SELECT * FROM {$bp->chat->table_chat_channels}  WHERE id=%d";
            $this->id=$id;
         if ($row = $wpdb->get_row($wpdb->prepare($query, $this->id))) {
            
            $this->is_open = $row->is_open;
            $this->status = $row->status;
            $this->is_multichat = $row->is_multichat;

            $this->last_message_time = $row->last_message_time;
            $this->time_created = $row->time_created;
            
        }

        //populate users
        
    }

    function save(){
        global $wpdb, $bp;
       /* Call a before save action here */
        do_action('bpchat_channels_data_before_save', $this);

        if ($this->id) {
            // Update
            $result = $wpdb->query($wpdb->prepare(
                                   "UPDATE {$bp->chat->table_chat_channels} SET
                                    time_created=%s,
                                    is_open = %d,
                                    status=%s,
                                    is_multichat= %d
                                   WHERE id = %d",

                                    $this->time_created,
                                    $this->is_open,
                                    $this->status,
                                    $this->is_multichat,
                                    $this->id
                            ));
        } else {
            // Save,Insert new
            $query = $wpdb->prepare(
                                "INSERT INTO {$bp->chat->table_chat_channels}
                                (
                                 time_created,
                                 is_open,
                                 status,
                                 is_multichat
                                 
				) VALUES ( %s, %d, %s, %d )",

                                $this->time_created,//to change
                                $this->is_open,
                                $this->status,
                                $this->is_multichat
                             
            );

          
            $result = $wpdb->query($query);
        }

        if (false === $result)/* because in updates it will retun Zero and false for something went wrong */
            return false;

        if (!$this->id) {
            $this->id = $wpdb->insert_id;
        }

        /* Add an after save action here */
        do_action('bp_chat_channels_data_after_save', $this);

      return $result;
    }
    
    function is_channel_open(){
        return $this->is_open;
    }

    function get_all_users($channel_id){
     global $bp,$wpdb;

     $query="SELECT user_id from {$bp->chat->table_channel_users}  WHERE channel_id=%d";
     $users = $wpdb->get_col($wpdb->prepare($query,$channel_id));
      return $users; ///return array of message objects
    }
    
   function get_active_users(){
       
   }

   function has_unread_message(){
     //if last_msg_timestamp>the recieved_time_stamp
   }

   function get_all_messages($channel_id){
       //get all channel messages
       global $bp,$wpdb;
       $sql="SELECT * FROM {$bp->chat->table_chat_messages} where channel_id=%d order by sent_at";
       $res=$wpdb->get_results($wpdb->prepare($sql,$channel_id));
       return $res;


   }

   function get_messages_after($channel_id,$time){
       global $bp,$wpdb;

       $query = "SELECT msg.channel_id, msg.message, msg.sender_id,msg.message,msg.sent_at FROM {$bp->chat->table_chat_messages} msg WHERE msg.channel_id=%d AND msg.sent_at > {$time} ORDER BY msg.sent_at ASC ";

    $q = $wpdb->prepare($query, $channel_id);

    $messages = $wpdb->get_results($q); //array of message objects
    return $messages;
   }
   
   function get_messages_for(){

   }

  function get_new_messages($time_stamp){
      
  }
  function is_private(){
    return $this->is_private;
  }

  function is_multichat(){
      return $this->multichat;
  }
  function get_all_open_channels_for_user($user_id){
      global $bp,$wpdb;
    $query = "SELECT msg.channel_id, msg.message, msg.sender_id,msg.message,msg.sent_at FROM {$bp->chat->table_chat_messages} msg WHERE msg.channel_id IN( SELECT channel_id FROM {$bp->chat->table_channel_users} where user_id=%d and status <> 'closed')  ORDER BY channel_id DESC, msg.sent_at ASC ";

    $channels=array();
    $channel_messages=$wpdb->get_results($wpdb->prepare($query,$user_id));

    foreach($channel_messages as $cmessages){
         $channel_id=$cmessages->channel_id;
     
     $channels[$channel_id][]=$cmessages;

 }
    
    return $channels;//_messages;

  }
//return all open channels for the user
  function get_open_channel_for_user($user_id){
   global $bp,$wpdb;
   //we may sacrifice a few queries here to have some better experience, because this is called only once per page load
   
   $query = "SELECT channel_id, user_id, status  FROM {$bp->chat->table_channel_users} WHERE channel_id IN( SELECT channel_id FROM {$bp->chat->table_channel_users} WHERE user_id=%d AND status = 'open') AND user_id!=%d ORDER BY channel_id DESC ";

   $channels=$wpdb->get_results($wpdb->prepare($query,$user_id,$user_id));//all chaneels even with semi cose status

      
   return $channels;
  }

  //close a channel
  function close($channel_id){
  //close channel
  //close all current connections

}

  function add_user($channel_id,$user_id,$status){
      global $wpdb,$bp;
        $query="INSERT INTO {$bp->chat->table_channel_users} (channel_id,user_id,status) values(%d,%d,%s)";
      $res=$wpdb->query($wpdb->prepare($query,$channel_id,$user_id,$status));
      
      return true;
  }

  function update_user($channel_id,$user_id,$status){
      global $wpdb,$bp;
        $query="UPDATE {$bp->chat->table_channel_users} SET status=%s WHERE channel_id=%d AND user_id=%d";
      $res=$wpdb->query($wpdb->prepare($query,$status,$channel_id,$user_id));

      return true;
  }
  function update_channel_for_all($channel_id,$status){
      global $wpdb,$bp;
        $query="UPDATE {$bp->chat->table_channel_users} SET status=%s WHERE channel_id=%d ";
      $res=$wpdb->query($wpdb->prepare($query,$status,$channel_id));

      return true;
  }
  /*close channel for user*/
  function close_channel_for_user($channel_id,$user_id,$status='closed'){
     global $wpdb,$bp;
        $query="UPDATE {$bp->chat->table_channel_users} SET status=%s WHERE channel_id=%d AND user_id=%d";
      $res=$wpdb->query($wpdb->prepare($query,$status,$channel_id,$user_id));

      return true;
  }

  /**
   * Get the current opened channel between the two users
   * @global <type> $wpdb
   * @global <type> $bp
   * @param <type> $initiator
   * @param <type> $invited
   * @return <type>
   */
  function get_channel_between($initiator,$invited){
      //we have to query to channel_users
      global $wpdb,$bp;
      $user_list="(".$initiator.",".$invited.")";
      $query="SELECT i.channel_id FROM {$bp->chat->table_channel_users} i,{$bp->chat->table_channel_users} o  WHERE  i.channel_id=o.channel_id AND i.user_id IN {$user_list} AND o.user_id IN {$user_list} AND i.user_id <> o.user_id AND (i.status <> 'closed' OR o.status <> 'closed')";
      $channel_id=$wpdb->get_var($query);
      return $channel_id;
  }
   //set status for user channel to be open
function keep_channels_open($channels,$user_id){
        global $wpdb,$bp;
        if(empty($channels))
            return;
        $channel_list="(".join(",", $channels).")";
        $query="UPDATE {$bp->chat->table_channel_users} SET status='open' WHERE channel_id IN {$channel_list} AND user_id=%d";
        $wpdb->query($wpdb->prepare($query,$user_id));

    }
    
     function find_channels_in_message($messages){
        $channels=array();
        $message_count=count($messages);
        for($i=0;$i<$message_count;$i++)
         $channels[]=$messages[$i]->channel_id;

        return array_unique($channels);//unique channels
      }
      
}

?>