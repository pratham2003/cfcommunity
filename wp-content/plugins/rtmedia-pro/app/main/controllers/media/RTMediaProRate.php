<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaProRate
 *
 * @author ritz
 */
class RTMediaProRate extends RTMediaUserInteraction {

    function __construct () {
        // must set action and label
        if($this->check_disable())
            return true;

        $args = array(
            'action' => 'rating',
            'label' => 'rating',
            'privacy' => 20
            );
        parent::__construct ($args);
        remove_filter('rtmedia_action_buttons_before_delete', array($this,'button_filter'));        
        
        add_action('rtmedia_actions_before_description', array( $this, 'button_filter' ));
    }


    function button_filter($buttons){
		if(empty($this->media)){
			$this->init();
		}
                echo "<div class='rtmedia-pro-rating'>";
		$this->render();
                echo "</div>";
	}

    function check_disable(){
         global $rtmedia;
        $options = $rtmedia->options;
        if(! (isset($options['general_enableRatings']) && ($options['general_enableRatings'] == "1")))
            return true;
        else
            return false;
    }
    function render ( $media_id = false ) {
        if($this->check_disable() || apply_filters( 'rtmedia_render_media_rate', false ) )
            return true;
        
        $default_rating = 0;
        $action = $this->action;
        $user_id = $this->interactor;
        $media_id = $this->action_query->id;
        $rtmediainteraction = new RTMediaInteractionModel();
        $media_result = $this->model->get(array('id' => $media_id));
        $curr_avg = 0;
        if($media_result && $media_result != "") {
            $curr_avg = $media_result[0]->ratings_average;
        }
        $results = $rtmediainteraction->get_row($user_id, $media_id, $action);
        if($results && !empty($results) && is_array($results) && count($results) > 0){
            $row = $results[0];
            if(is_numeric($row->value) && intval($row->value)>=0){
                $default_rating = $row->value;
            }
        }
        $link = trailingslashit(get_rtmedia_permalink($this->media->id)).$this->action.'/';
?>
        <form method = 'post' action='<?php echo $link ?>'>

            <?php if(is_user_logged_in()) { ?>
            <div class="rtmedia-pro-rates">
                <label class="rtmedia-pro-average-rating" >
                    (
                    <span class='rtmedia-avg-rate'>
                        <?php echo __('Rating : '); ?><span id="rtmedia_pro_media_average_rating"><?php echo ($curr_avg>0)?__(round($curr_avg,1)):__('NA'); ?></span>
                    </span>
                    <span class='rtmedia-user-rate'>
                        <?php echo __('Your Rating : '); ?><span id="rtmedia_pro_media_user_rating"><?php echo ($default_rating>0)?__($default_rating):__('NA'); ?></span></span>
                    )
                </label>
            </div>
            <input name='rtmedia_pro_rate_media' value='<?php echo ($curr_avg>0)?$curr_avg:$default_rating; ?>' id='rtmedia_pro_rate_media' type='hidden' />
            <?php }
                else { ?>
                <ul class="webwidget_rating_simple disabled_rating">
                <?php
                $star_image_url = RTMEDIA_PRO_URL."lib/rating-simple/";
                for($i=0,$k=1;$i<5;$i++)
                {
                    ($k<=$curr_avg)? $image="sth.gif" : $image="nst.gif";
                    ?>
                        <li style="background-image: url('<?php echo $star_image_url.$image ?>')"><span><?php echo $k ?></span></li>
                   <?php
                   $k++;
                }
            ?>
            </ul>
<!--                <div style="clear:both"></div>-->
<!--            <label class="rtmedia-pro-average-rating"> ( <?php _e('Rating', 'rtmedia');?> : <span id="rtmedia_pro_media_average_rating"><?php echo ($curr_avg>0)?round($curr_avg,2):"NA"; ?></span> )</label>-->
            <?php } ?>
        </form>
<?php
    }

    function process() {
        if($this->check_disable())
            return true;
        $rtmediainteraction = new RTMediaInteractionModel();
        $action = $this->action_query->action;
        $user_id = $this->interactor;
        $media_id = $this->action_query->id;
        $check_action = $rtmediainteraction->check($user_id, $media_id, $action);
        $value = $_REQUEST['value'];
        $media_result = $this->model->get( array('id' => $media_id));
        if($media_result && $media_result != "")
        {
            $curr_count = $media_result[0]->ratings_count;
            $curr_total = $media_result[0]->ratings_total;
            $curr_avg = $media_result[0]->ratings_average;
        }
        if($check_action) {
            $results = $rtmediainteraction->get_row($user_id, $media_id, $action);
            $row = $results[0];
            $curr_value = $row->value;
            $update_data = array('value' => $value);
            $where_columns = array(
                'user_id' =>  $user_id,
                'media_id' => $media_id,
                'action' => $action,
            );
            $update = $rtmediainteraction->update($update_data, $where_columns);
            $curr_total  = $curr_total - $curr_value + $value;
            $curr_avg = $curr_total / $curr_count;
        }
        else {
            $columns = array(
                'user_id' =>  $user_id,
                'media_id' => $media_id,
                'action' => $action,
                'value' => $value
            );
            $insert_id = $rtmediainteraction->insert($columns);
            $curr_count++;
            $curr_total  = $curr_total + $value;
            $curr_avg = $curr_total / $curr_count;

        }
         $update_count = $this->model->update( array( 'ratings_count' => $curr_count , 'ratings_total' => $curr_total, 'ratings_average' => $curr_avg ), array( 'id' => $this->action_query->id ));
	 global $rtmedia_points_media_id;
	 $rtmedia_points_media_id = $this->action_query->id;
	 do_action("rtmedia_pro_after_rating_media", $this);
         $data = array( "average" => $curr_avg );
         $data_json = json_encode($data);
         echo $data_json;
        die();
    }
}
