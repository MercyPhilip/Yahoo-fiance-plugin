<?php
/*
Plugin Name: ASX_Quote
Plugin URI: http://wordpress.org/plugins/
Description: Shows the ASX price ticker on sidebar
Author: Philip
Version: 0.1
Author URI: http://wordpress.org/plugins/
*/

add_action('admin_menu','asx_submenu');
function asx_submenu(){
	add_options_page('ASX Setting', 'ASX Setting', 'administrator', 'asx-setting', 'asx_options');
}
function asx_options() {
	if(isset($_POST['update_asx_interval'])){
		if($_POST['update_asx_interval']){
			if(get_option('asx_interval') !== $_POST['asx_interval']){
				update_option('asx_interval', $_POST['asx_interval']);

			}?>
			<div id="message" style="background-color: green; color: #ffffff;">Update Successfully!</div>

	<?php
		}
	}	
	
	if(isset($_POST['refresh_asx_data'])){
		if($_POST['refresh_asx_data']){
			get_asx_data();
	?>
			<div id="message" style="background-color: green; color: #ffffff;">Refresh Successfully!</div>

	<?php
		}
	}
	if(isset($_POST['clear_asx_data'])){
		if($_POST['clear_asx_data']){
			delete_option('asx_data');
	?>
			<div id="message" style="background-color: green; color: #ffffff;">Clear Successfully!</div>

	<?php
		}
	}
?>

	<div class="wrap">
		<h2>ASX Setting</h2>
	 
		<form method="post" action="">
			<table class="form-table">
				<tr valign="top">
				<th scope="row">ASX Interval</th>
				<td><input type="text" name="asx_interval" value="<?php echo get_option('asx_interval'); ?>" />
					<input type="submit" name="update_asx_interval" value="Submit &raquo;" /></td>
				</tr>
				
				<tr valign="top">
				<th scope="row">Refresh ASX Data</th>
				<td><input type="submit" name="refresh_asx_data" value="Refresh &raquo;" /></td>
				</tr>
				 
				<tr valign="top">
				<th scope="row">Clear Cache</th>
				<td><input type="submit" name="clear_asx_data" value="Clear &raquo;" /></td>
				</tr>
				
			</table>
		</form>
	</div>

<?php
}

	function get_asx_data(){
		$settings= get_option('widget_asx_widget');
		
		foreach($settings as $number => $setting) {
	
			if (is_null($setting)){
				continue;
			} 
			
			$asx_code = $setting['asx_code'];
			$num = $number;		

			$yql_base_url = "http://query.yahooapis.com/v1/public/yql";
			$yql_query = "select * from yahoo.finance.quotes where symbol = '$asx_code'";
			$yql_query_url = $yql_base_url . "?q=" . urlencode($yql_query) . "&format=json&env=http%3A%2F%2Fdatatables.org%2Falltables.env&callback=";

			$session = curl_init($yql_query_url);

			curl_setopt($session, CURLOPT_RETURNTRANSFER,true);
			$json = curl_exec($session);

			$dataObj =  json_decode($json);

			if(!is_null($dataObj->query->results)){
				$asx_data[$num] = $dataObj->query->results->quote;

			}
		update_option('asx_data', $asx_data);
		}
	}
	
	function asx_set_recurrency(){
		return array(
			'timely' => array('interval' => get_option('asx_interval'), 'display' => 'Timely'),
		);
	}
	
	add_filter('cron_schedules', 'asx_set_recurrency');
	
	if(!wp_next_scheduled('asx_get_data_by_timely')){
		wp_schedule_event(time(),'timely','asx_get_data_by_timely');
	}

	add_action('asx_get_data_by_timely','get_asx_data');

	function add_asx_style(){
		wp_register_style('asx_stylesheet', plugins_url('/css/asx_style.css', __FILE__));
		wp_enqueue_style('asx_stylesheet');
	}

add_action('wp_enqueue_scripts', 'add_asx_style' );

function update_asx_deactivation(){
	wp_clear_scheduled_hook('asx_get_data_by_timely');
}

register_deactivation_hook(basename(__FILE__),'update_asx_deactivation');

require( dirname( __FILE__ ) . '/widget.php' );
?>