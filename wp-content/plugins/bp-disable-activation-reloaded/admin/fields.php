<?php
	$options = get_option( $this->options_name );
	/* General Settings
	===========================================*/
	
	$this->settings['enable_login'] = array(
		'title'   => __( 'Enable / Disable Auto Login' , $this->WPB_PREFIX),
		'desc'    => __( 'Enable / Disable auto login after activation.' , $this->WPB_PREFIX),
		'std'     => 'true',
		'type'    => 'select',
		'section' => 'general',
		'choices' => array(
			'true' => __( 'Enabled' , $this->WPB_PREFIX),
			'false' => __( 'Disabled' , $this->WPB_PREFIX)
		)
	);
	
	$this->settings['redirection'] = array(
		'title'   => __( 'Redirection Url' , $this->WPB_PREFIX),
		'desc'    => __( 'Redirect user to this page after activation. Leave empty to disable redirection' , $this->WPB_PREFIX),
		'std'     => '',
		'type'    => 'text',
		'section' => 'general'
	);


	