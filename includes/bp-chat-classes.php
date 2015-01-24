<?php

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

		$bpchat = bp_chat();
		
        if ($row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$bpchat->table_name_messages} WHERE id = %d", $this->id))) {
            $this->id=$row->id;
            $this->sender_id = $row->sender_id;
            $this->channel_id = $row->channel_id;
            $this->message = esc_js($row->message);

            $this->sent_at = $row->sent_at;
           
        }
    }

    function save() {
        global $wpdb, $bp;
		$bpchat = bp_chat();
		
       /* Call a before save action here */
        do_action('bpchat_messages_data_before_save', $this);

        if ($this->id) {
            // Update
            $result = $wpdb->query($wpdb->prepare(
                                   "UPDATE {$bpchat->table_name_messages} SET
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
                                "INSERT INTO {$bpchat->table_name_messages}
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

	  $bpchat = bp_chat();
      $sql = "SELECT * FROM  {$bpchat->table_name_messages} msg WHERE msg.channel_id = %d ";
      $msgs = $wpdb->get_results($wpdb->prepare($sql,$channel_id));
      return $msgs; ///return array of message objects
}


function get_messages_after_time($channel_id,$time){
 //from unix_time
    global $wpdb, $bp;
	$bpchat = bp_chat();
	
    //get all messages for the channel
      $sql = "SELECT * FROM  {$bpchat->table_name_messages} msg WHERE msg.channel_id = %d and msg.sent_at > FROM_UNIXTIME($time)";
      $msgs = $wpdb->get_results($wpdb->prepare($sql,$channel_id));

      return $msgs; ///return array of message objects
}

}
//end of class



