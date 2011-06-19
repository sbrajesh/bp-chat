<?php

/**load the chat bar when the user is logged in*/
add_action( 'wp_footer', 'bp_chat_show_chat_bar');
//add_action( 'admin_footer', 'bp_chat_show_chat_bar');


/*enqueue the required script */
add_action( 'wp_print_scripts', 'bpchat_load_js');
//add_action( 'admin_print_scripts', 'bpchat_load_js');

function bpchat_load_js(){
    if(bpchat_is_disabled())
        return;
//if user is online, load the javascript
    if(is_user_logged_in()&&!is_admin()){//has issues while loading on admin pages a 0 is appeneded still not sure why ?
        wp_enqueue_script("json2");
        wp_enqueue_script("jquery");
        $url=BP_CHAT_PLUGIN_URL."bp-chat/js/bchat.js";
        wp_enqueue_script("poshytip",BP_CHAT_PLUGIN_URL."bp-chat/js/tip/jquery.poshytip.js",array("jquery"));
        if(bpchat_has_sound_notification_enabled())
        wp_enqueue_script("soundmanager",BP_CHAT_PLUGIN_URL."assets/soundmanager/script/soundmanager2.js");

        wp_enqueue_script("chatjs",$url,array("jquery","json2"));
	}
}
add_action("wp_print_styles","bpchat_load_css");
//add_action("admin_print_styles","bpchat_load_css");
function bpchat_load_css(){
    if(bpchat_is_disabled ())
        return;
     if(is_user_logged_in()){
    $url=BP_CHAT_PLUGIN_URL."/bp-chat/css/chat.css";
    wp_enqueue_style("chatcss",$url);
    }
}


?>