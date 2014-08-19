<?php

if( class_exists ( "myCRED_Module")){
if ( ! class_exists ( 'myCRED_rtMedia' ) ) {

    class myCRED_rtMedia extends myCRED_Module {

        protected
                $hooks ;
        protected
                $settings ;

        public function __construct () {
            parent::__construct ( 'myCRED_rtMedia' , array (
                'module_name' => 'buddypress-media' ,
                'defaults'    => array (
                    'visibility'         => array (
                        'balance' => 0 ,
                        'history' => 0
                    ) ,
                    'balance_location'   => '' ,
                    'balance_template'   => '%plural% balance: %creds%' ,
                    'history_location'   => '' ,
                    'history_menu_title' => array (
                        'me'     => __ ( "My History" , 'mycred' ) ,
                        'others' => __ ( "%s's History" , 'mycred' )
                    ) ,
                    'history_menu_pos'   => 99 ,
                    'history_url'        => 'mycred-history' ,
                    'history_num'        => 10
                ) ,
                'register'    => false ,
                'add_to_core' => true
            ) ) ;
        }

        public function module_init () {
           add_filter ( 'mycred_setup_hooks' , array ( $this , 'register_hooks' ) ) ;
        }

        function register_hooks ( $installed ) {
            $installed[ 'rtmedia_media' ] = array (
                'title'       => __ ( 'Points for rtMedia' , 'rtMedia' ) ,
                'description' => __ ( 'Points for media' , 'rtMedia' ) ,
                'callback'    => array ( 'RTMediaProMyCredHook' )
            ) ;
            return $installed ;
        }

    }

}
}
$buddypress = new myCRED_rtMedia();
$buddypress->load();
