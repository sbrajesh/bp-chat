<?php
function bp_chat_show_chat_bar() {
    if( bpchat_is_disabled () )
        return;
    
 if( is_user_logged_in() ): ?>

<div class="bp_chatbar" id="bp_chatbar">
        <div id="bp_chat_base">
            <!--chat template-->
            <div id="chat_template" class="chat_tab disabled">
                   
                <div  class="chat_button">
                    <div class="rule">
                        <div class="tab_content bc_block">
                            <span class="tab_name">
                                <strong>some_name</strong>
					        </span>
                            <span class="tab_count disabled">0</span>
                            <label class="close close_button">
                                <span>x</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="chat_window">
                    <div class="win_titlebar">
                        <label class="close close_button">
                            <span>x</span>
                         </label>
                        <label title="Minimize" class="minimize">
                                 <span>-</span>
                        </label>
                        <div class="win_title_text">
                            <a class="win_title_text_link" href="#">name</a>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="win_header">
                        <a class="win_header_image_link" href="#">
                            <img title="View Profile" class="img" src="" alt="" width="32" height="32" />
                        </a>
                        <div class="win_header_info"></div>
                        <div class="win_toolbox">
                            <div class="win_toolbar_items disabled"></div>
                        </div>

                     </div>
                     <div class="win_body" style="height: 130px;">
                        <div class="win_content">
                        </div>
                     </div>
                     <div class="win_footer">
                        <div class="chat_input_div">
                            <textarea class="chat_input"></textarea>
                            <button class="chat_send_message_btn">Go</button>
                            <div class="chat_input_icon"></div>
                        </div>
                     </div>
                     <input type="hidden" class="chatting_with_user" value="" />
                </div><!-- end of chat window -->
                          
            </div> <!-- end of chat template-->
            
            <div id="chat_tabs_slider" class="chat_tabs_slider">
                <div class="next disabled">
                </div>
                <div id="active_chat_tabs_wrapper" class="active_chat_tabs_wrapper">
                    <div id="active_chat_tabs" class="active_chat_tabs">
                   <!--current chat tabs here -->
                   <?php
                   global $bp;
                   $channels =  bpchat_get_channels_for_user( $bp->loggedin_user->id );

                  
                   foreach( $channels as $channel )
                         create_chat_window( $channel );
                   ?>
                                      
                   </div>
                </div><!--end of active chat tabs wrapper-->
                <div class="prev disabled"></div>
                
            </div><!-- end of tab slider-->
                
            <div class="chat_buddylist" id="chat_buddylist">
                <div id="settings_tab" class="chat_tab" >
                    <div  class="chat_button">
                        <div class="rule">
                            <div class="tab_content bc_block">
                                <span class="tab_name">
                                    Who's Online (<span class="online_count"><?php echo bpchat_get_online_users_count();?></span>)
                                </span>
								
                            </div>
                        </div>
                     </div>
                     
                    <div class="chat_window">
                        <div class="win_titlebar">
                            <label class="close close_button">
                                <span>x</span>
                            </label>
                             <label title="Minimize" class="minimize">
                                   <span>-</span>
                             </label>
                             <div class="win_title_text">
                                 <?php if( bpchat_show_user_preference() ): ?>
                                    <a class="win_title_text_link" id="win_title_text_link_settings" href="#">Chat Options</a>
                                    <div class="disabled chat_buddy_list_settings" id="chat_buddylist_settings">

                                         <ul>
                                             <li><a href="#" class="sitewide_users <?php if(!bpchat_has_friends_only_enabled($bp->loggedin_user->id)):?> chat_option_active <?php endif;?>" id="sitewide_users"  >Sitewide</a></li>
                                             <li><a href="#" class="friend_users <?php if(bpchat_has_friends_only_enabled($bp->loggedin_user->id)):?> chat_option_active <?php endif;?>" id="friend_users">Friends Only</a></li>
                                         </ul>

                                        </div>
                                  <?php endif;?>
                             </div>
                             <div class="clear"></div>
                         </div>
                         <div class="win_header">
                              <div class="win_header_info">Chat settings</div>
                               <div class="win_toolbox">
                                    <div class="win_toolbar_items disabled"></div>
                               </div>
                           </div>
                            <div class="win_body" style="height: 130px;">
                               <div class="win_content">
                                    <div class="friend_list_container" id="friend_list_container">
                                          <?php bpchat_get_online_users_list();?>
                                    </div>
                                </div>

                             </div>
                             <div class="win_footer">
                             </div>
                       </div><!-- end of chat window -->
                       <input type="hidden" id="fetch_time" value="<?php echo bpchat_get_current_mysql_time();?>" />
                       <input type="hidden" id="mesage_store" value="" />
                                    
                   </div> <!-- end of chat tab-->
		
                        
               
	</div><!--end buddylist/settings win -->
       
</div><!-- end of chat_base -->
</div><!-- end of chat bar -->
  
<?php endif ;?>

<?php

}

 
function create_chat_window($channel,$toggle_class=''){
    ?>
 <div id="chat_channel_<?php echo $channel->channel_id;?>" class="chat_tab   <?php echo $toggle_class;?> " style="width: 136px;">
	
	<div class="chat_button">
		<div class="rule">
                    <div class="tab_content bc_block">
			<span class="tab_name">
                            <strong><?php echo bp_core_get_user_displayname($channel->user_id);?></strong>
                            <!-- <img src="" class="tab_availability img"> -->
			</span>
                    	<span class="tab_count disabled">0</span>
			<label class="close close_button">
                            <span>x</span>
                        </label>
                     </div>
                  </div>
          </div>
          <div class="chat_window">
            <div class="win_titlebar">
                <label class="close close_button">
                	<span>x</span>
                </label>
                <label title="minimize" class="minimize">
                        <span>-</span>
                </label>
                <div class="win_title_text">
                    <a class="win_title_text_link" href="<?php echo bp_core_get_user_domain($channel->user_id);?>"><?php echo bp_core_get_user_displayname($channel->user_id);?></a>
                </div>
                <div class="clear"></div>
            </div>
            <div class="win_header">
                <a class="win_header_image_link" href="<?php echo bp_core_get_user_domain($channel->user_id);?>">
                       <?php echo  bp_core_fetch_avatar(array('item_id' => $channel->user_id, 'type' => 'thumb', 'width' => 32, 'height' =>32, 'html' => true));?>
                </a>
                <div class="win_header_info">....</div>
                 <div class="win_toolbox">
                       <div class="win_toolbar_items disabled"></div>
                  </div>
             </div>
             <div class="win_body" style="height: 130px;">
                  <div class="win_content">
                          <?php
                                $messages=bpchat_get_channel_messages($channel->channel_id);
                                 foreach($messages as $message)
                                    echo "<div><span class='user_name'>".bp_core_get_user_displayname ($message->sender_id)."</span>:<span class='msg'>".stripslashes ($message->message)."</span></div>";?>
                     </div>
               </div>
               <div class="win_footer">
                    <div class="chat_input_div">
                        <textarea class="chat_input"></textarea>
                         <button class="chat_send_message_btn">Go</button>
                        <div class="chat_input_icon"></div>
                    </div>
                 </div>
               <input type="hidden" class="chatting_with_user" id="chatting_with_user_<?php echo $channel->user_id;?>" value="<?php echo $channel->user_id;?>" />
            </div><!-- end of chat window -->
            
      </div> <!-- end of chat tab-->
    <?php
}
 