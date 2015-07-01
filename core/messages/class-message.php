<?php

/**
 * BP_Chat_Messages class to handle messages
 */

class BP_Chat_Message {

    var $id;
    var $sender_id;
    var $channel_id;//the current channel
    var $message;
    var $sent_at;
    

    public function __construct( $id = null ) {
        
		if ( $id ) {
            $this->id = $id;
            $this->populate( $this->id );
        }
		
    }

    public function populate() {
        
		global $wpdb;

		$bpchat = bp_chat();
		
        if ( $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$bpchat->table_name_messages} WHERE id = %d", $this->id ) ) ) {
            
			$this->id			= $row->id;
            $this->sender_id	= $row->sender_id;
            $this->channel_id	= $row->channel_id;
            $this->message		= esc_js($row->message);

            $this->sent_at		= $row->sent_at;
           
        }
    }

    public function save() {
		
        global $wpdb;
		
		$bpchat = bp_chat();
		
        do_action( 'bpchat_message_data_before_save', $this );
		

        if( $this->id ) {
            // Update
            $result =	$wpdb->query( $wpdb->prepare(
                                   
								"UPDATE {$bpchat->table_name_messages} SET
									sender_id	= %d,
									channel_id	= %d,
									message		= %s
								WHERE	id = %d",

								$this->sender_id,
								$this->channel_id,
								$this->message,
								$this->id
                        ));
        } else {
            
			$query =	$wpdb->prepare(
                                
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

        if ( false === $result )
            return false;

        if ( ! $this->id ) {
			
            $this->id = $wpdb->insert_id;
        }

       
		do_action( 'bpchat_message_data_after_save', $this );

      
		return $result;
    }


	public static function get_all_messages_for_channel( $channel_id ) {
      
		global $wpdb;

		$bpchat = bp_chat();
      
		$sql = "SELECT * FROM  {$bpchat->table_name_messages} msg WHERE msg.channel_id = %d ";
      
		$msgs = $wpdb->get_results( $wpdb->prepare( $sql, $channel_id ) );
      
		return $msgs; ///return array of message objects
	}


	public static function get_messages_after_time( $channel_id, $time ) {

		global $wpdb;
	
		$bpchat = bp_chat();
	
		$sql = "SELECT * FROM  {$bpchat->table_name_messages} msg WHERE msg.channel_id = %d and msg.sent_at > FROM_UNIXTIME($time)";
		$msgs = $wpdb->get_results($wpdb->prepare($sql,$channel_id));

		return $msgs; ///return array of message objects
	}

}