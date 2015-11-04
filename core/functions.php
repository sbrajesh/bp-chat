<?php

/**
 * @todo make independent of BP
 * 
 * @since 1.1.0
 * 
 * @return array
 */
function bpchat_get_all_options() {

	$default = array(
		'notification_volume'			=> 20,
		'notification_enabled'			=> true,
		'notification_sound_enabled'	=> true,
		'allow_prefernce_change'		=> true, 
		'default_chat_preference'		=> 'all',//who should be listed in the buddy list
		'is_disabled'					=> false,
		
	);


	if ( function_exists( 'bp_get_option' ) ) {
		
		$options = bp_get_option( 'bpchat-settings', $default );
		
	} else {
		
		$options = get_option( 'bpchat-settings', $default );
		
	}
	
	return apply_filters( 'bpchat_settings', $options );
}
/**
 * Save the settings to database ( Inoptions table ) 
 * @param type $options
 * @return type
 */
function bpchat_save_options( $options ) {

	if ( function_exists( 'bp_update_option' ) ) {
		
		$callback	 = 'bp_update_option';
		
	} else {
		
		$callback	 = 'update_option';
		
	}//both function have same signature, so no need to worry

	return $callback( 'bpchat-settings', $options );
}

/**
 * Get the BP Chat settings for the given option
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
 * @since 1.0.0 
 * 
 * @param string $option_name
 * @param mixed $value
 */
function bpchat_update_option( $option_name, $value ) {

	$options				 = bpchat_get_all_options();
	$options[$option_name]	 = $value;
	
	bpchat_save_options( $options );
}


/**
 * Is notification sound enabled?
 * 
 * @since 1.0.0
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
 * @since 1.0.0
 * 
 * @param type $user_id
 * @return type
 */
function bpchat_get_notification_volume( $user_id ) {
	
	return apply_filters( 'bpchat_get_notification_volume', bpchat_get_option( 'notification_volume' ), $user_id );
	
}

/**
 * Check if the chat UI is disabled
 * 
 * @since 1.0.0
 * 
 * @return type
 */
function bpchat_is_disabled() {
	
	return apply_filters( 'bpchat_is_disabled' ,  bpchat_get_option( 'is_disabled' ) );
}
