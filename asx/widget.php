<?php
class Asx_Widget extends WP_Widget {
		
	/**
	 * Register widget with WordPress.
	 */
	function __construct() {
		parent::__construct(
			false, $name = 'ASX Widget');
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		$data = get_option('asx_data');
		if($data == false){
			get_asx_data();
			$data = get_option('asx_data');
		}
		foreach($data as $asx_data){
			if($asx_data->symbol == $instance['asx_code']){
				echo $args['before_widget'];
				if ( ! empty( $instance['title'] ) ) {
					echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ). $args['after_title'];
				}
				$result .= '<div class="asx-summary"><div class="quote-summary"><div class="hd"><div class="title"><h3 style="display: inline-block">';
				$result .= $asx_data->symbol;
				$result .= "</h3>";
				
				$result .= "<span> - ";
				$result .= $asx_data->StockExchange;
				$result .='</span></div></div><div class="asx-quote"><div><span class="ticker">';
				$result .=$asx_data->LastTradePriceOnly;
				if($asx_data->Change > 0){
					$changecolor = 'green';
					$changeimage = plugins_url('/images/up.gif', __FILE__);
				} else {
					$changecolor = 'red';
					$changeimage = plugins_url('/images/down.gif', __FILE__);
				}
				$result .='</span><span class=“change">';
				$result .='<img class="change-image" alt="up" src="';
				$result .=$changeimage;
				$result .='"></img><span class="change-';

				$result .=$changecolor;
				$result .='">';
				$result .=$asx_data->Change;
				$result .='</span><span class="change-';
				$result .=$changecolor;
				$result .='">(';
				$result .=$asx_data->ChangeinPercent;
				$result .=')</span></div><div><span class="trade-time">';
				$result .=$asx_data->LastTradeTime;
				$result .='</span><span class="time-zone">';
				date_default_timezone_set(get_option('timezone_string'));
				$result .=date('T');
				$result .= "</span></div></div></div></div>";
				echo $result;
				echo $args['after_widget'];
			}	
		}
	}
	
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		
		$instance = $old_instance;
		
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['asx_code'] = ( ! empty( $new_instance['asx_code'] ) ) ? strip_tags( $new_instance['asx_code'] ) : '';

		return $instance;
	}
	
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		
		if(isset($instance['title'])){
    		$title	= esc_attr($instance['title']);
    	}else{
    		$title = '';
    	}
    	
    	if(isset($instance['asx_code'])){
    		$asx_code	= esc_attr($instance['asx_code']);
    	}else{
    		$asx_code = '';
    	}    

		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<p>
		<label for="<?php echo $this->get_field_id( 'asx_code' ); ?>"><?php _e( 'ASX Code:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'asx_code' ); ?>" name="<?php echo $this->get_field_name( 'asx_code' ); ?>" type="text" value="<?php echo esc_attr( $asx_code ); ?>">
		</p>
		<?php 
	}


}
	add_action('widgets_init', create_function('', 'return register_widget("Asx_Widget");'));
?>