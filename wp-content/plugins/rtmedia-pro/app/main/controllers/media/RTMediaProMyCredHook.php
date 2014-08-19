<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of RTMediaProMyCred
 *
 * @author ritz
 */
class RTMediaProMyCredHook extends myCRED_Hook {

    public function __construct($hook_prefs) {
	if ( ! class_exists ( "myCRED_Hook" ) ) {
	    return;
	}
	$rtmedia_points = maybe_unserialize(get_site_option("rtmedia_points"));

	if(!is_array($rtmedia_points) || sizeof($rtmedia_points) <= 0 ) {
	    return;
	}
	$rtmedia_mycred_config = array( 'id' => 'rtmedia_media' );
	foreach($rtmedia_points as $key => $val) {
	    $rtmedia_mycred_config['defaults'][$key] = array(
						    'creds' => 0,
						    'log' => '%plural% for '.ucfirst(str_replace("_", " ", $key))
						);
	}
	parent::__construct( $rtmedia_mycred_config , $hook_prefs );
    }

    public function preferences() {
	$prefs = $this->prefs;
	$rtmedia_points = maybe_unserialize(get_site_option("rtmedia_points"));
	if(!is_array($rtmedia_points) || sizeof($rtmedia_points) <= 0 ) {
	    return;
	}
	foreach ($rtmedia_points as $key => $val) {
    ?>
	    <label for="<?php echo $this->field_id( array( $key, 'creds' ) ); ?>" class="subheader"><?php echo $this->core->template_tags_general( __( '%plural% for '.ucfirst(str_replace("_", " ", $key)), 'mycred' ) ); ?></label>
	    <ol>
		    <li>
			    <div class="h2"><input type="text" name="<?php echo $this->field_name( array( $key, 'creds' ) ); ?>" id="<?php echo $this->field_id( array( $key, 'creds' ) ); ?>" value="<?php echo $this->core->format_number( $prefs[$key]['creds'] ); ?>" size="8" /></div>
		    </li>
		    <li class="empty">&nbsp;</li>
		    <li>
			    <label for="<?php echo $this->field_id( array( $key, 'log' ) ); ?>"><?php _e( 'Log template', 'mycred' ); ?></label>
			    <div class="h2"><input type="text" name="<?php echo $this->field_name( array( $key, 'log' ) ); ?>" id="<?php echo $this->field_id( array( $key, 'log' ) ); ?>" value="<?php echo $prefs[$key]['log']; ?>" class="long" /></div>
			    <span class="description"><?php _e( 'Available template tags: General', 'mycred' ); ?></span>
		    </li>
	    </ol>
    <?php
	}
    }

    public function sanitise_preferences( $data ) {
	if( isset( $data['creds'] ) ) {
	    unset( $data['creds'] );
	}
	if( isset( $data['log'] ) ) {
	    unset( $data['log'] );
	}
	$new_data = $data;
	foreach($data as $key=>$value) {
	    $new_data[$key]['creds'] = ( !empty( $data[$key]['creds'] ) ) ? $data[$key]['creds'] : $this->defaults[$key]['creds'];
	    $new_data[$key]['log'] = ( !empty( $data[$key]['log'] ) ) ? sanitize_text_field( $data[$key]['log'] ) : $this->defaults[$key]['log'];
	}
	return $new_data;
    }

    public function run() {
	$rtmedia_points = maybe_unserialize(get_site_option("rtmedia_points"));
	if( !is_array($rtmedia_points) || sizeof($rtmedia_points) <= 0 ) {
	    return;
	}
	foreach ($rtmedia_points as $key => $val) {
	    add_action($val['action'],array($this,$key));
	}
    }

    function __call($name, $arguments) {
	$rtmedia_points = maybe_unserialize(get_site_option("rtmedia_points"));
	if(is_array($rtmedia_points) && sizeof($rtmedia_points) > 0 ) {
	    if(class_exists("myCRED_Hook")) {
		$user = get_current_user_id();
		global $rtmedia_points_media_id;
		if ( $this->core->exclude_user( $user ) ) {
		    return;
		}
		if ( $rtmedia_points_media_id != "" && $this->core->has_entry( $name, $rtmedia_points_media_id ) ) {
		    return;
		}
		$this->core->add_creds(
				$name,
				$user,
				$this->prefs[$name]['creds'],
				$this->prefs[$name]['log'],
				$rtmedia_points_media_id,
				'rtmedia_media'
			);
	    }
	}
    }
}
