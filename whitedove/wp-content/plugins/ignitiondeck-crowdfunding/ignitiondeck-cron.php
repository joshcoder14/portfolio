<?php
if ( ! wp_next_scheduled( 'schedule_hourly_id_cron' ) ) {
	wp_schedule_event( time(), 'hourly', 'schedule_hourly_id_cron' );
}

function schedule_hourly_id_cron() {
	$raised  = ID_Project::set_raised_meta();
	$percent = ID_Project::set_percent_meta();
	$days    = ID_Project::set_days_meta();
	$closed  = ID_Project::set_closed_meta();
	$failed  = ID_Project::set_failed_meta();
}

add_action( 'schedule_hourly_id_cron', 'schedule_hourly_id_cron' );

// adding a custom cron to run every 5 minutes
add_filter( 'cron_schedules', 'cron_add_weekly' );
function cron_add_weekly( $schedules ) {
   if(!isset($schedules["5min"])){
       $schedules["5min"] = array(
           'interval' => 5*60,
           'display' => __('Once every 5 minutes')
       );
   }
   return $schedules;
}

// make cron work if the data is not updated works with the option that gets updated from this method: ID_Project::update_date_to_timestamp()
if ( ! wp_next_scheduled( 'schedule_data_update_id_cron' ) && get_option('id_date_data_updated') != 'yes' ) {
	wp_schedule_event( time(), '5min', 'schedule_data_update_id_cron' );
}
function schedule_data_update_id_cron() {
	$raised  = ID_Project::update_date_to_timestamp();
}
add_action( 'schedule_data_update_id_cron', 'schedule_data_update_id_cron' );

// force run cron to update date data via URL
if( is_admin() && isset($_GET['force_data_update_id_cron']) ) add_action( 'wp_loaded', 'schedule_data_update_id_cron' );