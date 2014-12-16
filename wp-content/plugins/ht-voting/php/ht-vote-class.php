<?php

define('TESTING_MODE', false);
class HT_Vote {
    /**
     * Constructor
     */
    public function __construct($magnitude) {
        $this->magnitude=$magnitude;
        

        // Retrieve user IP address 
        if(array_key_exists('REMOTE_ADDR', $_SERVER)) {
            $ip = $_SERVER['REMOTE_ADDR']; 
            $this->ip = $ip;
        } else {
            $this->ip = '';
        }

        if(TESTING_MODE)
           $this->ip = rand(0, 10000000000000) ;

        //vote time/date 
        $this->time = time();

        //user
        $current_user = wp_get_current_user();
        if( is_a($current_user, 'WP_User') ) {
            $this->user_id = $current_user->ID;
        } else {
            $this->user_id = '0';
        }

        
    }


} //end class

class HT_Vote_Up extends HT_Vote {
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(10);
    }

} 


class HT_Vote_Down extends HT_Vote {
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(0);
    }

} 

class HT_Vote_Neutral extends HT_Vote {
    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct(5);
    }

}

class HT_Vote_Value extends HT_Vote {
    /**
     * Constructor
     */
    public function __construct($value = 5) {
        parent::__construct($value);
    }

} 