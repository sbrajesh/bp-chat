<?php

/**
 * @todo make independent of BP
 * 
 * @return array
 */
function bpchat_get_all_options() {

	$default = array(
		'notification_volume'			=> 20,
		'notification_enabled'			=> true,
		'notification_sound_enabled'	=> true,
		'allow_prefernce_change'		=> true, 
		'is_disabled'					=> false,
		
	);


	if ( function_exists( 'bp_get_option' ) )
		$options = bp_get_option( 'bpchat-settings', $default );
	else
		$options = get_option( 'bpchat-settings', $default );

	return apply_filters( 'bpchat_settings', $options );
}
/**
 * Save the settings to database ( Inoptions table ) 
 * @param type $options
 * @return type
 */
function bpchat_save_options( $options ) {

	if ( function_exists( 'bp_update_option' ) )
		$callback	 = 'bp_update_option';
	else
		$callback	 = 'update_option';
	//both function have same signature, so no need to worry

	return $callback( 'bpchat-settings', $options );
}

/**
 * Get the BP Chat settings for a perticular option
 * 
 * @param string $option the name of the chat specific option 
 * @return mixed (array|int|string) depending on the option  
 */
function bpchat_get_option( $option ) {


	$options = bpchat_get_all_options();

	return isset( $options[ $option ] ) ? $options[ $option ] : ''; //may be a bad idea but we are going to keep it unless we implement the admin panel
}

/**
 * Update individual Chat option and save that to database( in options table )
 * 
 * @param type $option_name
 * @param type $value
 */
function bpchat_update_option( $option_name, $value ) {

	$options				 = bpchat_get_all_options();
	$options[$option_name]	 = $value;
	
	bpchat_save_options( $options );
}


/**
 * Is notification sound enabled?
 * 
 * @param type $user_id
 * @return type
 */
function bpchat_is_notification_sound_enabled( $user_id ) {
	
	return apply_filters( 'bpchat_is_notification_sound_enabled', bpchat_get_option( 'notification_sound_enabled' ), $user_id );
	
}
/**
 * Get teh volume for notification sound
 * 
 * @param type $user_id
 * @return type
 */
function bpchat_get_notification_volume( $user_id ) {
	
	return apply_filters( 'bpchat_get_notification_volume', bpchat_get_option( 'notification_volume' ), $user_id );
	
}
/**
 * Update User chat preference (sitewide|friends only )
 * @param type $user_id
 * @param type $prefs
 */
function bpchat_update_user_preference( $user_id, $prefs ) {
	
	BPChat_User::set_pref( $user_id,$prefs );
}
/**
 * Show users the options for changing preference?
 * 
 * @return boolean
 */
function bpchat_show_user_preference() {
	
    return apply_filters( 'bpchat_show_user_preference', bpchat_get_option( 'allow_prefernce_change' ) );
}

function bpchat_is_disabled() {
	
	return apply_filters( 'bpchat_is_disabled' ,  bpchat_get_option( 'is_disabled' ) );
}
