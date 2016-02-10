<?php
/**
 * @package ASX
 * @version 0.1
 */
/*
Plugin Name: ASX_Quote
Plugin URI: http://wordpress.org/plugins/
Description: Shows the ASX price ticker on sidebar
Author: Philip
Version: 0.1
Author URI: http://wordpress.org/plugins/
*/

add_action('admin_menu','asx_submenu');
function asx_submenu() {
	add_options_page( 'ASX Setting', 'ASX Setting', 'administrator','asx-setting','asx_options');
	
//	add_action('admin_init', 'register_asx_setting');
}

/*function register_asx_setting() {
	
	register_setting( 'asx-options', 'time-frame');
	register_setting( 'asx-options', 'asx-code');

}*/

function asx_options() {
    /*$asx_time_frame = esc_attr(get_option('asx_time_frame'));
    $asx_code = esc_attr(get_option('asx_code'));*/
/*    $settings = get_option('widget_asx_widget');
	
	foreach($settings as $number => $setting) {
		
		if (is_null($setting)){
			continue;
		} 
		
		$title = $setting['title'];
		$asx_time_frame = $setting['asx_time_frame'];
		$asx_code = $setting['asx_code'];
		$num = $number;		
		break;
	
	}


	if($_POST['update_asx_option']){
		
		if ($asx_code !== $_POST['asx_code'] || $asx_time_frame !== $_POST['asx_time_frame'] || $title !== $_POST['asx_title']){

			$setting['title'] = $_POST['asx_title'];
			$setting['asx_time_frame'] = $_POST['asx_time_frame'];
			$setting['asx_code'] = $_POST['asx_code'];
			$settings[$num] = $setting;

			update_option('widget_asx_widget', $settings);*/?><!--
			
			<div id="message" style="background-color: green; color: #ffffff;">Update Successfully!</div>
	--><?php	/*
		}
		$settings = get_option('widget_asx_widget');
		$title = $settings[$num]['title'];
		$asx_time_frame = $settings[$num]['asx_time_frame'];
		$asx_code = $settings[$num]['asx_code'];
	}*/

	if($_POST['update_asx_interval']){
		if(get_option('asx_interval') !== $_POST['asx_interval']){
			update_option('asx_interval', $_POST['asx_interval']);

		}?>
		<div id="message" style="background-color: green; color: #ffffff;">Update Successfully!</div>

	<?php
	}
	if($_POST['refresh_asx_data']){
		get_asx_data();
		?>
		<div id="message" style="background-color: green; color: #ffffff;">Refresh Successfully!</div>

		<?php
	}
	if($_POST['clear_asx_data']){
		delete_option('asx_data');
		?>
		<div id="message" style="background-color: green; color: #ffffff;">Clear Successfully!</div>

	<?php
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
			'timely' => array('interval' => 180, 'display' => 'Timely'),
		);
	}
	
	add_filter('cron_schedules', 'asx_set_recurrency');
	
	if(!wp_next_scheduled('asx_get_data_by_three')){
		wp_schedule_event(time(),'timely','asx_get_data_by_three');
	}

/*	function layout($title, $data){
		$result = "";
		$result .= "<h1>";
		$result .= $title;
		$result .= "</h1>";
		$result .= '<div class="asx-summary"><div class="quote-summary"><div class="hd"><div class="title"><h2>';
		$result .= $data->symbol;
		$result .= "</h2>";

		$result .= "<span><span>-</span>";
		$result .= $data->StockExchange;
		$result .='</span></div></div><div class="asx-quote"><div><span class="ticker">';
		$result .=$data->LastTradePriceOnly;
		$result .='</span><span class=“change">';
		$result .=$data->Change;
		$result .='</span><span class=“change-percent">';
		$result .=$data->ChangeinPercent;
		$result .='</span><span class=“trade-time">';
		$result .=$data->LastTradeTime;
		$result .= "</span></div></div></div></div>";
		echo $result;
	}
	add_action('asx_get_data_by_three','get_asx_data');

	function asx_shortcode_handler($atts, $content='') {
		extract( shortcode_atts( array(
				'asx_code'  => ''
		), $atts ) );

		$settings= get_option('widget_asx_widget');

		if(isset($settings)) {
			foreach ($settings as $number => $setting) {

				if (isset($asx_code)) {
					if ($asx_code == $setting['asx_code']) {
						$flag = 1;
						break;
					} else {
						$num_s = $number;
						continue;
					}
				}
			}
		}

		$data = get_option('asx_data');

		if($flag !== 1){
			$setting['asx_code'] = $asx_code;
			$setting['title'] = $content;
			$num_s = $num_s + 1;
			$settings[$num_s] = $setting;
			update_option('widget_asx_widget', $settings);

			$count = count($data);
			$count = $count + 1;

			$yql_base_url = "http://query.yahooapis.com/v1/public/yql";
			$yql_query = "select * from yahoo.finance.quotes where symbol = '$asx_code'";
			$yql_query_url = $yql_base_url . "?q=" . urlencode($yql_query) . "&format=json&env=http%3A%2F%2Fdatatables.org%2Falltables.env&callback=";

			$session = curl_init($yql_query_url);

			curl_setopt($session, CURLOPT_RETURNTRANSFER,true);
			$json = curl_exec($session);

			$dataObj =  json_decode($json);

			if(!is_null($dataObj->query->results)){
				$data[$count] = $dataObj->query->results->quote;
				update_option('asx_data', $data);
			}
			layout($content, $data[$count]);
		}


		foreach($data as $asx_data){
			//$asx_data = get_asx_data($instance);
			if($asx_data->symbol == $asx_code){
				layout($content,$asx_data);
			}
		}

	}
	add_shortcode('asx', 'asx_shortcode_handler');*/
	function add_asx_style(){
		wp_register_style('asx_stylesheet', plugins_url('/css/asx_style.css', __FILE__));
		wp_enqueue_style('asx_stylesheet');
	}

add_action('wp_enqueue_scripts', 'add_asx_style' );
require( dirname( __FILE__ ) . '/widget.php' );
?>