/* 
 * List of open channels for the current user
 * This file needs a lot of cleanup, the code form 2008/2009 is a mess
 */
bpchat_channel_status = new Array();//global object used for channel status on current page
/**
 * ChatSettings Object helps in mnipulating friend list and settings window/action
 */


/**
 * This object handles following
 * -Updates the online users count
 * -Updates Online Users List
 * -Initializes timers for polling
 *  -
 *  */
var ChatSettings = {
	
	update_online_count: function () {
		jQuery.post( ajaxurl, {
			action	: 'bpchat_get_online_users_count',
			cookie	: encodeURIComponent( document.cookie )
		},
		function ( ret ) {
			var oc = jQuery( "div#chat_buddylist" ).find( "span.online_count" ).get( 0 );
			jQuery( oc ).html( ret );

		} )//end of post
	},
	update_online_list: function () {
		
		ChatHelper.reset_intervals();
		ChatSettings.update_online_users_list( { action: 'bpchat_update_online_users_list', 'cookie': encodeURIComponent( document.cookie ), output: 'html', value: 0, fetch: 1 } );
	},
	
	update_online_users_list: function ( obj ) {
		jQuery.post( ajaxurl, obj, function ( ret ) {
			jQuery( "div#chat_buddylist .win_content" ).html( ret );
		} );//end of post
	},
	
	check_chat_init: function () {

		jQuery.post( ajaxurl, {
			action		: 'bpchat_check_updates',
			fetch_time	: ChatSettings.get_fetch_time(),
			cookie		: encodeURIComponent( document.cookie ) 
			},
			
			function ( ret ) {

				var msgs = JSON.parse( ret );
				var messages = '';
				var last_fetch_time = '';
				var channel_user_status = '';
				messages = msgs.messages;
				last_fetch_time = msgs.fetch_time;
				channel_user_status = msgs.status;
				// alert(channel_user_status.toString());
				ChatSettings.update_fetch_time( last_fetch_time );//update last fetch time

				for ( var i = 0; i < messages.length; i++ ) {
					var chat_win = jQuery( "#chat_channel_" + messages[i].channel_id );//refrence to chat win object

					if ( !ChatWindow.exists( chat_win ) )
						chat_win = ChatWindow.create( messages[i].name, messages[i].thumb, messages[i].sender_id, messages[i].channel_id );

					//ChatWindow.maximize(chat_win);	//may be the window may be hidden/closed/minimized
					ChatWindow.update_message( chat_win, messages[i].id, messages[i].name, messages[i].message, messages[i].sender_id );

					ChatWindow.update_new_message_count( chat_win );
				}
				//update channel/user status
				for ( var k = 0; k < channel_user_status.length; k++ ) {
					//
					//if(channel_user_status[k].status!='open'||channel_user_status[k].is_online!=1)
					ChatWindow.update_other_user_status( channel_user_status[k].channel_id, channel_user_status[k].status, channel_user_status[k].is_online, channel_user_status[k].user_status );
				}
			} );//end of post

		},
	get_id: function ( elem ) {
		var e = jQuery( elem );
		//alert(e.html());
		// console.log("called by"+ChatSettings.get_id.caller);
		return jQuery( e ).attr( "id" ).split( "_" ).pop();//return the id as the last thing after _
	},
	get_name: function ( friend ) {
		var f = jQuery( friend );
		var temp = f.find( ".friend_list_item_name" ).get( 0 );

		return jQuery( temp ).text();//return the name
	},
	get_avatar_src: function ( friend ) {
		var f = jQuery( friend );
		var temp = f.find( ".friend_list_item_orig_avatar_src" ).get( 0 );

		return jQuery( temp ).text();//return the name
	},
	get_friend_id: function ( elem ) {
		var friend = jQuery( elem );

		return ChatSettings.get_id( friend );
	},
	update_fetch_time: function ( time ) {
		//store time
		jQuery( "#chat_buddylist #fetch_time" ).val( time );
	},
	get_fetch_time: function () {
		return  jQuery( "#chat_buddylist #fetch_time" ).val();
	}

}

//for polling
var ChatHelper = {
	chat_count_interval_id: 0,
	chat_interval_id: 0,
	chat_offline: 0,
	clear_intervals: function () {
		clearInterval( this.chat_count_interval_id );
		clearInterval( this.chat_interval_id );

	},
	set_intervals: function () {
		this.chat_count_interval_id = setInterval( "ChatSettings.update_online_count()", 10000 );//10 sec
		this.chat_interval_id = setInterval( "ChatSettings.check_chat_init()", 5000 );//5 sec

	},
	reset_intervals: function () {
		this.clear_intervals();
		this.set_intervals();
	},
	store_message: function ( msg_id ) {
		//store the currently read message id
		var message_ids = jQuery( "#mesage_store" ).val();//existing values
		var msgs = "";
		if ( message_ids.length > 0 )
			msgs = message_ids + "," + msg_id;
		else
			msgs = msg_id;
		jQuery( "#mesage_store" ).val( msgs );
	},
	is_message_shown: function ( message_id ) {
		//check if the message is is shown.
		var message_ids = jQuery( "#mesage_store" ).val();
		//  alert(message_ids);
		var msgs = message_ids.split( "," );
		if ( jQuery.inArray( message_id, msgs ) != -1 )
			return true;
		return false;
	},
	restore_chat_window: function () {

		var win_id = jQuery.cookie( "maximized_chat_tab_id" );
		//  console.log("restroing window at init"+win_id);
		if ( win_id ) {
			var chat_win = jQuery( "#chat_channel_" + win_id );
			ChatWindow.maximize( chat_win );


			//scroll the message
			jQuery( ".win_body", chat_win ).scrollTo( 'max', { offset: -125, easing: 'easeOutQuad' } );

		}
	}

}//end of helper

//window management or chat box management
var ChatWindow = {
	is_open: function ( win ) {
		if ( jQuery( win ).hasClass( "open_toggler" ) )
			return true;
		return false;
	},
	is_closed: function ( win ) {
		if ( jQuery( win ).hasClass( "disabled" ) )
			return true;
		return false;
	},
	is_minimized: function ( win ) {
		if ( jQuery( win ).hasClass( "open_toggler" ) )
			return false;

		return true;
	},
	is_maximized: function ( win ) {
		if ( jQuery( win ).hasClass( "open_toggler" ) )
			return true;
		return false;
	},
	is_disabled: function () {
	},
	exists: function ( win ) {
		if ( jQuery( win ).get( 0 ) )
			return true;
		else
			return false;
	},
	exists_chat_box_for: function ( friend_id ) {

		//check if a chat box exists for the friend
		if ( jQuery( "#chatting_with_user_" + friend_id ).get( 0 ) )
			return true;//for reference
		return false;
	},
	close: function ( win ) {
		jQuery( win ).addClass( "disabled" );
	},
	maximize: function ( win ) {
		if ( !ChatWindow.exists( win ) )
			return;
		//minimize all other windows except this one
		jQuery( ".active_chat_tabs .chat_tab" ).each( function () {
			if ( jQuery( this ).hasClass( "open_toggler" ) && this != win )
				ChatWindow.minimize( this );

		} );
		jQuery.cookie( 'maximized_chat_tab_id', ChatSettings.get_id( win ), { path: '/' } );
		jQuery( win ).addClass( "open_toggler" );
		ChatWindow.reset_new_message_count( win );
	},
	has_other_win_maximized: function () {
		if ( jQuery( ".active_chat_tabs .open_toggler" ).get( 0 ) )
			return true;
		return false;
	},
	minimize: function ( win ) {
		jQuery( win ).removeClass( "open_toggler" );
		jQuery.cookie( 'maximized_chat_tab_id', null, { path: '/' } );

	},
	toggle_window: function ( win ) {
		if ( jQuery( win ).hasClass( "open_toggler" ) ) { //this window was open
			ChatWindow.minimize( win );
			return;
		}

		ChatWindow.maximize( win );
		jQuery( ".win_body", win ).scrollTo( 'max', { offset: -125, easing: 'easeOutQuad' } );
	},
	open: function () {
	},
	create: function ( name, avatar_src, user_id, channel_id ) {//creating window

		//clone the template

		var win = jQuery( "#chat_template" ).clone();//clone the chat window
		win.prependTo( "div.active_chat_tabs" );//.css("margin-top", "-275px");
		win.attr( "id", "chat_channel_" + channel_id )
		//alert("creating window 1");
		// jQuery("input.chat_with_user",win).val(user_id);//.html(name);
		win.find( ".win_header_image_link img" ).attr( "src", avatar_src );

		win.find( ".tab_name strong" ).html( name );
		win.find( ".win_title_text a" ).html( name );
		win.find( "textarea" ).attr( "id", "chat_input_" + channel_id );
		jQuery( "input.chatting_with_user", win ).val( user_id );//for reference
		win.find( "input.chatting_with_user" ).attr( "id", "chatting_with_user_" + user_id );

		win.removeClass( 'disabled' );
		//check if there does not exist a maximized window, maximize it(will work in both cases when a response is recieved or a new friend name is clicked)
		if ( !ChatWindow.has_other_win_maximized() )
			ChatWindow.maximize( win );
		//win.show('slow');

		return jQuery( win );


	},
	reopen: function ( win ) {
		jQuery( win ).removeClass( "disabled" );
		this.maximize( win );
	},
	hide_all: function () {
		jQuery( ".active_chat_tabs_wrapper .chat_tab" ).removeClass( "open_toggler" );
	},
	update_message: function ( win, id, name, message, sender_id ) {
		//console.log( { win: win, id: id, name: name, message:message, sender_id: sender_id});
		//check if shown or not
		if ( ChatHelper.is_message_shown( id ) ) {
			return;
		}
		jQuery( win ).removeClass( "disabled" );
		jQuery( ".win_content", win ).append( '<div><span class="user_name">' + name + '</span>: <span class="msg">' + message + '</span></div>' );
		jQuery( ".win_body", win ).scrollTo( 'max', { offset: -125, easing: 'easeOutQuad' } );
		//check for current message, do not play sound for my own message
		if ( sender_id != bpchat.current_user_id )
			bpchat_play_notification();
		ChatHelper.store_message( id );
	},
	get_chat_box_for: function ( friend_id ) {
		//get the chat box for friend id if one exists

		var friend_acc = jQuery( "#chatting_with_user_" + friend_id );//.get(0);
		return find_parent_window( friend_acc );
	},
	update_new_message_count: function ( win ) {
		if ( ChatWindow.is_maximized( win ) )
			return;
		//update the new message count
		var count = jQuery( ".tab_content span.tab_count", win ).text() - 0;//just to make it integer
		jQuery( ".tab_content span.tab_count" ).html( count + 1 );//just to make it integer
		//notify
	},
	reset_new_message_count: function ( win ) {
		jQuery( ".tab_content span.tab_count" ).html( "0" );//just to make it integer
	},
	get_new_message_count: function ( win ) {
		return jQuery( ".tab_content span.tab_count", win ).text() - 0;
	},
	create_chat_box: function ( for_user ) {
		var friend = jQuery( for_user );
		var friend_id = ChatSettings.get_id( friend );
		var friend_name = ChatSettings.get_name( friend );
		var friend_avatar_src = ChatSettings.get_avatar_src( friend );

		jQuery.post( ajaxurl, {
			action: 'bpchat_request_channel',
			'user_id': friend_id,
			'cookie': encodeURIComponent( document.cookie )
		},
		function ( res ) {

			var channel_id = res;
			var chat_win = jQuery( "#chat_channel_" + channel_id );//refrence to chat win object
			if ( !ChatWindow.exists( chat_win ) ) {
				ChatWindow.create( friend_name, friend_avatar_src, friend_id, res );
			}
			else
				ChatWindow.maximize( chat_win );
			//just maximize


		}
		);


	},
	reopen_closed_chat_box: function ( win_id ) {
		var win = jQuery( win_id );
		var channel_id = ChatSettings.get_id( win );
		jQuery.post( ajaxurl, {
			action: 'bpchat_request_channel_reopen',
			'channel_id': channel_id,
			'cookie': encodeURIComponent( document.cookie )
		},
		function ( res ) {
			/*
			 var channel_id=res;
			 var chat_win=jQuery("#chat_channel_"+channel_id);//refrence to chat win object
			 if ( !ChatWindow.exists(chat_win)) {
			 ChatWindow.create(friend_name, friend_avatar_src, friend_id,res);
			 }
			 else
			 ChatWindow.maximize(chat_win);
			 //just maximize
			 
			 */
		}
		);
	},
	update_other_user_status: function ( channel_id, channel_status, is_online, user_status ) {
		var channel = jQuery( "#chat_channel_" + channel_id );

		if ( !ChatWindow.exists( channel ) )
			return;//if the chatbox is not open, do not do anything
//if the chat tab exists

		var css_class = "";
//set flag to offline, may be we can update a status image
		if ( is_online == 0 )//check for offline
			css_class = "user_offline";
		else if ( user_status == "idle" )
			css_class = "user_idle";
		else if ( user_status == "active" )
			css_class = "user_online";
		var tab = channel.find( ".tab_name" );
		if ( !tab.hasClass( css_class ) )
			tab.removeClass( "user_offline user_online user_idle" ).addClass( css_class );
//find the tab and set status

		/*else if(channel_status=="closed"){
		 
		 var shown=bpchat_channel_status["channel_"+channel_id];
		 if(shown=='done')
		 return;
		 bpchat_channel_status["channel_"+channel_id]="done";
		 if(status=='closed'){//if the status is not open/requested
		 //tell the user that the other user has left
		 var other_user_name=jQuery(".chat_window .win_title_text a",channel).text();
		 //add to the chat window
		 var message=" has left the chat.";
		 jQuery(".win_content",channel).append('<div class="notice"><span class="user_name">'+other_user_name+'</span> <span class="notice_message">'+message+'</span></div>');
		 jQuery(".win_body",channel).scrollTo( 500, {offset:-125, easing:'easeout'} );
		 }*/
	}
}



//actual binding to dom with events
jQuery(function(){
 
 var j=jQuery;
 
 /*check for Paul Irish's Idle Timer plugin*/
 /*!
 * jQuery idleTimer plugin
 * version 0.9.100511
 * by Paul Irish.
 *   http://github.com/paulirish/yui-misc/tree/
 * MIT license

 * adapted from YUI idle timer by nzakas:
 *   http://github.com/nzakas/yui-misc/
*/
if(!j.idleTimer){
    (function(a){a.idleTimer=function(b,c){var d=false,e=true,f=3e4,g="mousemove keydown DOMMouseScroll mousewheel mousedown touchstart touchmove";c=c||document;var h=function(b){if(typeof b==="number"){b=undefined}var d=a.data(b||c,"idleTimerObj");d.idle=!d.idle;var g=+(new Date)-d.olddate;d.olddate=+(new Date);if(d.idle&&g<f){d.idle=false;clearTimeout(a.idleTimer.tId);if(e)a.idleTimer.tId=setTimeout(h,f);return}var i=jQuery.Event(a.data(c,"idleTimer",d.idle?"idle":"active")+".idleTimer");a(c).trigger(i)},i=function(b){var c=a.data(b,"idleTimerObj")||{};c.enabled=false;clearTimeout(c.tId);a(b).off(".idleTimer")},j=function(){var b=a.data(this,"idleTimerObj");clearTimeout(b.tId);if(b.enabled){if(b.idle){h(this)}b.tId=setTimeout(h,b.timeout)}};var k=a.data(c,"idleTimerObj")||{};k.olddate=k.olddate||+(new Date);if(typeof b==="number"){f=b}else if(b==="destroy"){i(c);return this}else if(b==="getElapsedTime"){return+(new Date)-k.olddate}a(c).on(a.trim((g+" ").split(" ").join(".idleTimer ")),j);k.idle=d;k.enabled=e;k.timeout=f;k.tId=setTimeout(h,k.timeout);a.data(c,"idleTimer","active");a.data(c,"idleTimerObj",k)};a.fn.idleTimer=function(b){if(this[0]){a.idleTimer(b,this[0])}return this}})(jQuery);

}

//jQuery Placeholder plugin
if(!j.placeholder){
    /*! http://mths.be/placeholder v1.8.7 by @mathias */
(function(f,h,c){var a='placeholder' in h.createElement('input'),d='placeholder' in h.createElement('textarea'),i=c.fn,j;if(a&&d){j=i.placeholder=function(){return this};j.input=j.textarea=true}else{j=i.placeholder=function(){return this.filter((a?'textarea':':input')+'[placeholder]').not('.placeholder').bind('focus.placeholder',b).bind('blur.placeholder',e).trigger('blur.placeholder').end()};j.input=a;j.textarea=d;c(function(){c(h).delegate('form','submit.placeholder',function(){var k=c('.placeholder',this).each(b);setTimeout(function(){k.each(e)},10)})});c(f).bind('unload.placeholder',function(){c('.placeholder').val('')})}function g(l){var k={},m=/^jQuery\d+$/;c.each(l.attributes,function(o,n){if(n.specified&&!m.test(n.name)){k[n.name]=n.value}});return k}function b(){var k=c(this);if(k.val()===k.attr('placeholder')&&k.hasClass('placeholder')){if(k.data('placeholder-password')){k.hide().next().show().focus().attr('id',k.removeAttr('id').data('placeholder-id'))}else{k.val('').removeClass('placeholder')}}}function e(){var o,n=c(this),k=n,m=this.id;if(n.val()===''){if(n.is(':password')){if(!n.data('placeholder-textinput')){try{o=n.clone().attr({type:'text'})}catch(l){o=c('<input>').attr(c.extend(g(this),{type:'text'}))}o.removeAttr('name').data('placeholder-password',true).data('placeholder-id',m).bind('focus.placeholder',b);n.data('placeholder-textinput',o).data('placeholder-id',m).before(o)}n=n.removeAttr('id').hide().prev().attr('id',m).show()}n.addClass('placeholder').val(n.attr('placeholder'))}else{n.removeClass('placeholder')}}}(this,document,jQuery));


}

var cb = ChatWindow;//chat Box object
 
 j(document).ready(function(){
     
     //check for libraries and include
     
     j.idleTimer(5000);
    j(document).bind('idle.idleTimer', function(){
     // function you want to fire when the user goes idle
     //console.log("Document is idle...");
     ChatHelper. clear_intervals();//do not send any extra request
    });
 
 
    j(document).bind('active.idleTimer', function(){
     // function you want to fire when the user becomes active again
     //please update the current settings and then set new interval
    // console.log("Document is active...");
     ChatSettings.check_chat_init();
     ChatSettings.update_online_count();
     
     
     ChatHelper.set_intervals();
    });


     
     //bind close button of chat window
   j(".active_chat_tabs .close_button span").live("click",function(evt){
       evt.stopPropagation();
       var window=find_parent_window(this);
      
       var channel_id = ChatSettings.get_id(window);//get the chat channel id
       cb.close(window);//close window

       j.post(ajaxurl,{action:"bpchat_close_channel",channel_id:channel_id},function(){
             //do nothing here
             
         });
      //do all the cleanup/server processing here for closing a channel
   });


 //for toggling on clicking the tab/titlebar

 j("#chat_tabs_slider .tab_content,#chat_tabs_slider .win_titlebar").live("click",function(evt){
     //toggle window

     var win=find_parent_window(this);//find the current window
     evt.stopPropagation();
     evt.preventDefault();

     cb.toggle_window(win);//toggle this chat box
        
 });
//show new messages popup
 j("#chat_tabs_slider .tab_content").live("mouseover",function(evt){
     //toggle window

     var win=find_parent_window(this);//find the current window
     evt.stopPropagation();
     evt.preventDefault();
     if(!ChatWindow.is_maximized(win)){

    
     var count=ChatWindow.get_new_message_count(win);
     //jQuery("#chat_tabs_slider .tab_content").each(function(){
         jQuery(this).poshytip({content:count,
         className: 'tip-twitter',
	showTimeout: 1,
	alignTo: 'target',
	alignX: 'inner-left',
	offsetY: 5,
	offsetX: 10,
	allowTipHover: false,
	fade: false,
	slide: false
});
     }
 
 

  //   });
     
     //cb.toggle_window(win);//toggle this chat box

 });

//for Chat Options,buddy list
j("#chat_buddylist .tab_content,#chat_buddylist .win_titlebar").live("click",function(evt){
     //toggle window
    // if(this==j("#chat_buddylist a.win_title_text_link").get(0))
         if(j(evt.target).is("#win_title_text_link_settings"))
            return;
     var win=find_parent_window(this);
     evt.stopPropagation();
     evt.preventDefault();
    if(!cb.is_maximized(win)){
        //hide if the settings panel is oopen
        j("#chat_buddylist_settings").addClass("disabled");
    }
        j(win).toggleClass("open_toggler");
     if(cb.is_maximized(win)) //if the list is maximized
         ChatSettings.update_online_list();//update user list


    });
 //maximize
j("#chat_buddylist .win_titlebar a.win_title_text_link").live("click",function(evt){
    evt.preventDefault();
    evt.stopPropagation();
    
    j(this).next().removeClass("disabled");

});

//for chat options settings

j("#chat_buddylist_settings ul li a").live("click",function(evt){
    evt.preventDefault();
    evt.stopPropagation();
    var option_selected=j(this).attr("id");
    //if the selected option is not already seletcted earlier, let us select it and highlight the link
    if(!j(this).hasClass("chat_option_active")){
        //if this is not the chat option active, make it active chat option
       //post to server
       
      j("#chat_buddylist_settings li a").removeClass("chat_option_active");
      j(this).addClass("chat_option_active");
        j.post(ajaxurl,{action:"bpchat_change_preference",prefrence:option_selected},function(){
             //do nothing here

         });
    }

   // j(this).next().removeClass("disabled");

});

//open a new chat box when clicking on the friend list item
j("a.online_friend").live("click",function(evt){

    evt.stopPropagation();
    evt.preventDefault();

    var friend_id=ChatSettings.get_friend_id(this);
    
    if(cb.exists_chat_box_for(friend_id)){
       
          var win=cb.get_chat_box_for(friend_id);//get the chat box for this friend if it exists
          //reopen channel for the user
          jQuery(win).removeClass("disabled");
          cb.maximize(win);//maximize
          cb.reopen_closed_chat_box(win);
        }
   else
     cb.create_chat_box(this); //create new chat box
});

//send message
//what happens when presses enter with some text .. should we send empty lines to?
j(".chat_input").live('keydown',
		
               function(event){
               
        	if(event.keyCode == 13){
                 
                    var chat = find_parent_window(this);//get reference to current chat window
                    var channel_id = ChatSettings.get_id(chat);

                     var msg = jQuery(this).val();

                    j(this).val('');//empty current va;lue

                    j.post(ajaxurl,{
                                    "action": 'bpchat_save_message',
                                    "channel_id": channel_id,
                                    "message": msg,
                                    'cookie': encodeURIComponent(document.cookie)
                                     },
                            function(res){

                                     var res = eval('('+res+')');
                                     cb.update_message(chat,res.id, res.name, msg,bpchat.current_user_id);
				 			
                        });//end of post
		}
              //  return false;
	});
//bind send button
j(".chat_send_message_btn").live('click',
               function(event){
                    var chat = find_parent_window(this);//get reference to current chat window
                    var channel_id = ChatSettings.get_id(chat);

                  
                    var msg = jQuery(".chat_input",chat).val();
                   j(".chat_input",chat).val('');//empty current va;lue

                    j.post(ajaxurl,{
                                    "action": 'bpchat_save_message',
                                    "channel_id": channel_id,
                                    "message": msg,
                                    'cookie': encodeURIComponent(document.cookie)
                                     },
                            function(res){

                                     var res = eval('('+res+')');
                                     cb.update_message(chat,res.id, res.name, msg,bpchat.current_user_id);

                        });//end of post
		
                return false;
	});
///setup pollin
if(ChatHelper.chat_offline == 0)
		ChatHelper.set_intervals();
 });//end of document.ready


 //maximize any chat box which has a open status on the page
 ChatHelper.restore_chat_window();

});//end of jquery block


/*find the parent window*/
function find_parent_window(elem){
    var e=jQuery(elem);// turn to jquery object if not one
    var parent=e.parents(".chat_tab").get(0);
    return parent;
    
}

function bpchat_play_notification(){
 //create global object
 if(bpchat.sound_notification_enabled!="1")
    return;
// soundManager.play('mySound','/path/to/an.mp3');
soundManager.createSound('bpchat_sound', bpchat.plugin_url+"assets/notification.mp3");
  //volume: 50
//});
soundManager.play('bpchat_sound');

//bpchat_notification_sound.play();
}

if( jQuery.scrollTo == undefined ) {
/**
 * Copyright (c) 2007-2014 Ariel Flesler - aflesler<a>gmail<d>com | http://flesler.blogspot.com
 * Licensed under MIT
 * @author Ariel Flesler
 * @version 1.4.14
 */
;(function(k){'use strict';k(['jquery'],function($){var j=$.scrollTo=function(a,b,c){return $(window).scrollTo(a,b,c)};j.defaults={axis:'xy',duration:0,limit:!0};j.window=function(a){return $(window)._scrollable()};$.fn._scrollable=function(){return this.map(function(){var a=this,isWin=!a.nodeName||$.inArray(a.nodeName.toLowerCase(),['iframe','#document','html','body'])!=-1;if(!isWin)return a;var b=(a.contentWindow||a).document||a.ownerDocument||a;return/webkit/i.test(navigator.userAgent)||b.compatMode=='BackCompat'?b.body:b.documentElement})};$.fn.scrollTo=function(f,g,h){if(typeof g=='object'){h=g;g=0}if(typeof h=='function')h={onAfter:h};if(f=='max')f=9e9;h=$.extend({},j.defaults,h);g=g||h.duration;h.queue=h.queue&&h.axis.length>1;if(h.queue)g/=2;h.offset=both(h.offset);h.over=both(h.over);return this._scrollable().each(function(){if(f==null)return;var d=this,$elem=$(d),targ=f,toff,attr={},win=$elem.is('html,body');switch(typeof targ){case'number':case'string':if(/^([+-]=?)?\d+(\.\d+)?(px|%)?$/.test(targ)){targ=both(targ);break}targ=win?$(targ):$(targ,this);if(!targ.length)return;case'object':if(targ.is||targ.style)toff=(targ=$(targ)).offset()}var e=$.isFunction(h.offset)&&h.offset(d,targ)||h.offset;$.each(h.axis.split(''),function(i,a){var b=a=='x'?'Left':'Top',pos=b.toLowerCase(),key='scroll'+b,old=d[key],max=j.max(d,a);if(toff){attr[key]=toff[pos]+(win?0:old-$elem.offset()[pos]);if(h.margin){attr[key]-=parseInt(targ.css('margin'+b))||0;attr[key]-=parseInt(targ.css('border'+b+'Width'))||0}attr[key]+=e[pos]||0;if(h.over[pos])attr[key]+=targ[a=='x'?'width':'height']()*h.over[pos]}else{var c=targ[pos];attr[key]=c.slice&&c.slice(-1)=='%'?parseFloat(c)/100*max:c}if(h.limit&&/^\d+$/.test(attr[key]))attr[key]=attr[key]<=0?0:Math.min(attr[key],max);if(!i&&h.queue){if(old!=attr[key])animate(h.onAfterFirst);delete attr[key]}});animate(h.onAfter);function animate(a){$elem.animate(attr,g,h.easing,a&&function(){a.call(this,targ,h)})}}).end()};j.max=function(a,b){var c=b=='x'?'Width':'Height',scroll='scroll'+c;if(!$(a).is('html,body'))return a[scroll]-$(a)[c.toLowerCase()]();var d='client'+c,html=a.ownerDocument.documentElement,body=a.ownerDocument.body;return Math.max(html[scroll],body[scroll])-Math.min(html[d],body[d])};function both(a){return $.isFunction(a)||$.isPlainObject(a)?a:{top:a,left:a}}return j})}(typeof define==='function'&&define.amd?define:function(a,b){if(typeof module!=='undefined'&&module.exports){module.exports=b(require('jquery'))}else{b(jQuery)}}));
			
}