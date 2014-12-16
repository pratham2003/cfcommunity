<?php
/*
*	Plugin Name: Heroic Voting
*	Plugin URI:  http://wordpress.org/plugins/hero-themes-voting
*	Description: Heroic Voting is a simple voting plugin
*	Author: Hero Themes
*	Version: 1.2
*	Author URI: http://www.herothemes.com/
*	Text Domain: ht-voting
*/

/*
* +1 	upvote
* -1 	downvote
*  0	neutral
*
*/


if( !class_exists('HT_Voting') ){
	if(!defined('HT_VOTING_KEY')){
		define('HT_VOTING_KEY', '_ht_voting');
	}

	if(!defined('HT_USEFULNESS_KEY')){
		define('HT_USEFULNESS_KEY', '_ht_kb_usefulness');
	}
		


	class HT_Voting {

		

		//constructor
		function __construct(){
			$this->add_script = false;
			load_plugin_textdomain('ht-voting', false, basename( dirname( __FILE__ ) ) . '/languages' );

			add_action( 'init', array( $this, 'register_ht_voting_shortcode_scripts_and_styles' ) );
			add_action( 'wp_footer', array( $this, 'print_ht_voting_shortcode_scripts_and_styles' ) );
			add_shortcode( 'ht_voting', array( $this , 'ht_voting_post_shortcode' ) );
			add_shortcode( 'ht_voting_comment', array( $this , 'ht_voting_comment_shortcode' ) );
			add_action( 'wp_head', array( $this, 'ht_voting_head' ) );

			//ajax filters
        	add_action( 'wp_ajax_ht_voting', array( $this, 'ht_ajax_voting_callback' ) );
        	add_action( 'wp_ajax_nopriv_ht_voting', array( $this, 'ht_ajax_voting_callback' ) );
			include_once('php/ht-vote-class.php');
		}

		/**
		* 
		* @param array $attrs The shortcode passed attribute
		* @param array $content The shortcode passed content (this will always be ignored in this context)
		*/
		function ht_voting_post_shortcode($atts, $content = null){
			global $post;
			//shortcode used so scripts and styles required
			$this->add_script = true;
			
			//extract arttributes
			extract(shortcode_atts(array(  
	                'display' => 'standard',
	                'allow' => 'user',
	            ), $atts));

			ob_start();

			$this->ht_voting_post_display($post->ID, $allow, $display);
			//return whatever has been passed so far
			return ob_get_clean();
		}

		/**
		* 
		* @param Int $post_id
		* @param String $allow
		* @param String $display
		*/
		function ht_voting_post_display($post_id, $allow='user', $display='standard'){
				//get votes so far
				$votes = $this->get_post_votes($post_id);
			?>
				<div class="ht-voting" id ="ht-voting-post-<?php echo $post_id ?>">
				<?php
					$this->ht_voting_post_render($post_id, $allow, $votes, $display);
				?>
				</div><!-- /ht-voting -->
			<?php
		}



		/**
		* 
		* @param array $attrs The shortcode passed attribute
		* @param array $content The shortcode passed content (this will always be ignored in this context)
		*/
		function ht_voting_comment_shortcode($atts, $content = null){
			//shortcode used so scripts and styles required
			$this->add_script = true;
			
			//extract arttributes
			extract(shortcode_atts(array( 
					'comment_id' => 0, 
	                'display' => 'standard',
	                'allow' => 'user',
	            ), $atts));	

			//return if no comment id set
			if($comment_id == 0)
				return;

			ob_start();
			
			$this->ht_voting_comment_display($comment_id, $allow, $display);
			//return whatever has been passed so far
			return ob_get_clean();
		}

		/**
		* 
		* @param String $comment_id
		* @param String $allow
		* @param String $display
		*/
		function ht_voting_comment_display($comment_id, $allow='user', $display='standard'){
			?>

				<div class="ht-voting comment" id ="ht-voting-comment-<?php echo $comment_id; ?>">
				<?php

					//get votes so far
					$votes = $this->get_comment_votes($comment_id);
					if( true ){
						$this->ht_voting_comment_render($comment_id, $allow, $votes, $display);
					}
				?>
			</div><!-- /ht-voting -->

			<?php
		}

		function get_post_votes($post_id){
			$votes = get_post_meta( $post_id, HT_VOTING_KEY, false);
			return $votes;
		}

		function get_comment_votes($comment_id){
			$votes = get_comment_meta( $comment_id, HT_VOTING_KEY, false);
			return $votes;
		}


		function ht_voting_post_render($post_id, $allow, $votes, $display='standard'){

			//load font awesome
			wp_enqueue_style( 'font-awesome', plugins_url( 'css/font-awesome.min.css', __FILE__ ) );

			//enqueue script
			wp_enqueue_script( 'ht-voting-frontend-script'); 

			//add localization if required         

			$number_of_votes = is_array($votes) ? count($votes) : 0;
			$number_of_helpful = 0;
			foreach ((array)$votes as $vote) {
				if($vote->magnitude==10)
					$number_of_helpful++;
			}

			//get current user votes
			$user_vote = $this->get_users_post_vote( $post_id );


			$user_vote_direction = 'none';

			if( is_a( $user_vote, 'HT_Vote_Up' ) )
				$user_vote_direction = 'up';

			if( is_a( $user_vote, 'HT_Vote_Down' ) )
				$user_vote_direction = 'down';		


			$nonce = ( $allow!='anon' && !is_user_logged_in() ) ? '' : wp_create_nonce('ht-voting-post-ajax-nonce');
			$vote_enabled_class = ( $allow!='anon' && !is_user_logged_in() ) ? 'disabled' : 'enabled';

			?>
			<?php if($display=='lowprofile'): ?>
				<div class="ht-voting-links ht-voting-<?php echo $user_vote_direction; ?>">
					<a class="ht-voting-upvote <?php echo $vote_enabled_class; ?>" data-direction="up" data-type="post" data-nonce="<?php echo $nonce; ?>" data-id="<?php echo $post_id; ?>" data-allow="<?php echo $allow; ?>" data-display="<?php echo $display; ?>" href="<?php echo $this->vote_post_link('up', $post_id, $allow); ?>"></a>
					<a class="ht-voting-downvote <?php echo $vote_enabled_class; ?>" data-direction="down" data-type="post" data-nonce="<?php echo $nonce; ?>" data-id="<?php echo $post_id; ?>" data-allow="<?php echo $allow; ?>" data-display="<?php echo $display; ?>" href="<?php echo $this->vote_post_link('down', $post_id, $allow); ?>"></a>
				</div>
			<?php else: ?>
				<?php if($allow!='anon' && !is_user_logged_in()): ?>	
					<div class="voting-login-required">
					<?php _e('You must log in to vote', 'ht-voting'); ?>
					</div>
				<?php endif; ?>
				<div class="ht-voting-links ht-voting-<?php echo $user_vote_direction; ?>">
					<a class="ht-voting-upvote <?php echo $vote_enabled_class; ?>" data-direction="up" data-type="post" data-nonce="<?php echo $nonce; ?>" data-id="<?php echo $post_id; ?>" data-allow="<?php echo $allow; ?>" data-display="<?php echo $display; ?>" href="<?php echo $this->vote_post_link('up', $post_id, $allow); ?>"><?php _e('Helpful', 'ht-voting'); ?></a>
					<a class="ht-voting-downvote <?php echo $vote_enabled_class; ?>" data-direction="down" data-type="post" data-nonce="<?php echo $nonce; ?>" data-id="<?php echo $post_id; ?>" data-allow="<?php echo $allow; ?>" data-display="<?php echo $display; ?>" href="<?php echo $this->vote_post_link('down', $post_id, $allow); ?>"><?php _e('Not Helpful', 'ht-voting'); ?></a>
				</div>

				<div class="ht-voting-how-helpful" id="how-helpful-<?php echo $post_id; ?>">
					(<?php printf(__('%s out of %s people found this article helpful', 'ht-voting'), $number_of_helpful, $number_of_votes );?>)
				</div>
			<?php endif; ?>

			<?php
		}

		function ht_voting_comment_render($comment_id, $allow, $votes, $display='standard'){

			
			//load font awesome
			wp_enqueue_style( 'font-awesome', plugins_url( 'css/font-awesome.min.css', __FILE__ ) );

			$number_of_votes = is_array($votes) ? count($votes) : 0;
			$number_of_helpful = 0;
			foreach ((array)$votes as $vote) {
				if($vote->magnitude==10)
					$number_of_helpful++;
			}

			//get current user votes
			$user_vote = $this->get_users_comment_vote( $comment_id );

			$user_vote_direction = 'none';

			if( is_a( $user_vote, 'HT_Vote_Up' ) )
				$user_vote_direction = 'up';

			if( is_a( $user_vote, 'HT_Vote_Down' ) )
				$user_vote_direction = 'down';	

			$nonce = ( $allow!='anon' && !is_user_logged_in() ) ? '' :  wp_create_nonce('ht-voting-comment-ajax-nonce');
			$vote_enabled_class = ( $allow!='anon' && !is_user_logged_in() ) ? 'disabled' : 'enabled';		

			?>
			<?php if($display=='numbers'): ?>
				<?php if($allow!='anon' && !is_user_logged_in()): ?>	
					<div class="ht-voting-login-required">
					<?php _e('You must log in to vote', 'ht-voting'); ?>
					</div>
				<?php endif; ?>
				<div class="ht-voting-links ht-voting-<?php echo $user_vote_direction; ?>">
					<a class="ht-voting-upvote <?php echo $vote_enabled_class; ?>" data-direction="up" data-display="numbers" data-type="comment" data-nonce="<?php echo $nonce; ?>" data-id="<?php echo $comment_id; ?>"  href="<?php echo $this->vote_comment_link('up', $comment_id, $allow); ?>"><?php echo $number_of_helpful; ?></a>
					<a class="ht-voting-downvote <?php echo $vote_enabled_class; ?>" data-direction="down" data-display="numbers" data-type="comment" data-nonce="<?php echo $nonce; ?>" data-id="<?php echo $comment_id; ?>"  href="<?php echo $this->vote_comment_link('down', $comment_id, $allow); ?>"><?php echo $number_of_votes-$number_of_helpful; ?></a>
				</div>
			<?php else: ?>
				<?php if($allow!='anon' && !is_user_logged_in()): ?>	
					<div class="ht-voting-login-required">
					<?php _e('You must log in to vote', 'ht-voting'); ?>
					</div>
				<?php endif; ?>
				<div class="ht-voting-links ht-voting-<?php echo $user_vote_direction; ?>">
					<a class="ht-voting-upvote <?php echo $vote_enabled_class; ?>" data-direction="up" data-type="comment" data-nonce="<?php echo $nonce; ?>" data-id="<?php echo $comment_id; ?>"  href="<?php echo $this->vote_comment_link('up', $comment_id, $allow); ?>"><?php _e('Up', 'ht-voting'); ?></a>
					<a class="ht-voting-downvote <?php echo $vote_enabled_class; ?>" data-direction="down" data-type="comment" data-nonce="<?php echo $nonce; ?>" data-id="<?php echo $comment_id; ?>"  href="<?php echo $this->vote_comment_link('down', $comment_id, $allow); ?>"><?php _e('Down', 'ht-voting'); ?></a>
				</div>

				<div class="ht-voting-how-helpful" id="how-helpful-<?php echo $comment_id; ?>">
					(<?php printf(__('%s out of %s people found this comment helpful', 'ht-voting'), $number_of_helpful, $number_of_votes );?>)
				</div>
			<?php endif; ?>

			<?php
		}

		/**
		* Get the voting link
		*/
		function vote_post_link($direction, $post_id, $allow='anon'){
			$bookmark = 'ht-voting-post-'.$post_id;
			if($allow!='anon' && !is_user_logged_in())
				return '?' . '#' . $bookmark ;
			$security = wp_create_nonce( 'ht-post-vote' );
			return '?' . 'vote=' . $direction . '&post=' . $post_id . '&_htvotenonce=' . $security . '#' . $bookmark ;
		}

		/**
		* Get the voting link
		*/
		function vote_comment_link($direction, $comment_id, $allow='anon'){
			$bookmark = 'ht-voting-comment-'.$comment_id;
			if($allow!='anon' && !is_user_logged_in())
				return '?' . '#' . $bookmark ;
			//todo add security nonce
			return '?' . 'vote=' . $direction . '&comment=' . $comment_id . '#' . $bookmark ;
		}


		/**
		*
		*/
		function get_users_post_vote($post_id, $votes=null){
			//create a dummy vote to compare
			if(class_exists('HT_Vote_Up')){
				$comp_vote = new HT_Vote_Up();
			} else {
				return;
			}
			//get all votes
			$votes = ( empty($votes) ) ? get_post_meta($post_id, HT_VOTING_KEY) : $votes;
			//loop through and compare users vote
			if($votes && !empty($votes)){
				foreach ($votes as $key => $vote) {
					//if user id is same (and not 0), return vote
					if( $vote->user_id > 0 && $vote->user_id == $comp_vote->user_id )
						return $vote;
					//if ip is same, return vote
					if( $vote->ip == $comp_vote->ip )
						return $vote;
					//else try next one
					continue;
				}
			} else {
				return;
			}
		}

		/**
		*
		*/
		function get_users_comment_vote($comment_id, $votes=null){
			//create a dummy vote to compare
			if(class_exists('HT_Vote_Up')){
				$comp_vote = new HT_Vote_Up();
			} else {
				return;
			}
			//get all votes
			$votes = ( empty($votes) ) ? get_comment_meta($comment_id, HT_VOTING_KEY) : $votes;
			//loop through and compare users vote
			if($votes && !empty($votes)){
				foreach ($votes as $key => $vote) {
					//if user id is same (and not 0), return vote
					if( $vote->user_id > 0 && $vote->user_id == $comp_vote->user_id )
						return $vote;
					//if ip is same, return vote
					if( $vote->ip == $comp_vote->ip )
						return $vote;
					//else try next one
					continue;
				}
			} else {
				return;
			}
		}

		/**
		* Test whether the user has voted
		*/
		function has_user_voted($post_id, $votes=null){
			$user_vote = $this->get_users_post_vote( $post_id, $votes );
			$voted = (empty( $user_vote )) ? false : true;
			return $voted;
		}

		/**
	    * Register scripts and styles
	    */
	    public function register_ht_voting_shortcode_scripts_and_styles(){
	           if( !current_theme_supports( 'hero-voting-frontend-styles' ) ){	           		
	                wp_enqueue_style( 'ht-voting-frontend-style', plugins_url( 'css/ht-voting-frontend-style.css', __FILE__ ), false, true );
	           }

	          
	           wp_register_script( 'ht-voting-frontend-script', plugins_url( 'js/ht-voting-frontend-script.js', __FILE__ ), array('jquery') , 1.0, true );
	            
	           
	            wp_localize_script( 'ht-voting-frontend-script', 'voting', array( 
	            		'log_in_required' => __('You must be logged in to vote on this', 'ht-voting'), 
                		'ajaxurl' => admin_url( 'admin-ajax.php' ), 
                		'ajaxnonce' => wp_create_nonce('ht-voting-ajax-nonce') 
	                ));
	                
				
	    }

	    /**
	    * Print scripts and styles
	    */
	    public function print_ht_voting_shortcode_scripts_and_styles(){
	           if( $this->add_script ){
	                wp_print_styles( 'ht-voting-frontend-style' );
	           }
	            

	    }

	    /*
	    * HT Voting Head
	    */
	    public function ht_voting_head(){
	    	global $_GET;
	    	$direction = array_key_exists('vote', $_GET) ? $_GET['vote'] : '';
	    	$post_id = array_key_exists('post', $_GET) ? $_GET['post'] : '';
	    	$comment_id = array_key_exists('comment', $_GET) ? $_GET['comment'] : '';
	    	$nonce = array_key_exists('_htvotenonce', $_GET) ? $_GET['_htvotenonce'] : '';
	    	if(!empty($direction)){
	    		//verify security
	    		if ( ! wp_verify_nonce( $nonce, 'ht-post-vote' ) ) {
	    			die( 'Security check' ); 
	    		} else {
	    			if(!empty($post_id) ){
			    		//vote	post    		
			    		$this->vote_post($post_id, $direction);
			    	}
			    	if(!empty($comment_id) ){
			    		//vote	comment		
			    		$this->vote_comment($comment_id, $direction);
			    	}
	    		}   				
	    	}	    	
	    }

	     /**
	    * Ajax Voting
	    */
	    public function ht_ajax_voting_callback(){
	        global $_POST;
	    	$direction = array_key_exists('direction', $_POST) ? sanitize_text_field($_POST['direction']) : '';
	    	//type - either post or comment
	    	$type = array_key_exists('type', $_POST) ? sanitize_text_field($_POST['type']) : '';
	    	$nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
	    	$id = array_key_exists('id', $_POST) ? sanitize_text_field($_POST['id']) : '';
	    	$allow = array_key_exists('allow', $_POST) ? sanitize_text_field($_POST['allow']) : '';
	    	$display = array_key_exists('display', $_POST) ? sanitize_text_field($_POST['display']) : '';


	        //security passed
	        if(!empty($direction)){
	    			if( $type=='post' ){
	    				 if ( ! wp_verify_nonce( $nonce, 'ht-voting-post-ajax-nonce' ) ){
	    				 	die( 'Security check' );
	    				 } else {
	    				 	//vote	post    		
			    			$this->vote_post($id, $direction);
							$this->ht_voting_post_display($id, $allow, $display);
	    				 }	
			    	}
			    	if( $type=='comment' ){
			    		if ( ! wp_verify_nonce( $nonce, 'ht-voting-comment-ajax-nonce' ) ){
	    				 	die( 'Security check' );
	    				 } else {
				    		//vote	comment	
				    		$this->vote_comment($id, $direction);
							$this->ht_voting_comment_display($id, $allow, $display);
						}
			    	}				
	    	}	  
	        die(); // this is required to return a proper result
	    }

	    /*
	    * Perform the voting action
	    */
	    public function vote_post($post_id, $direction){
	    	//get the users vote and delete it
	    	$user_vote = $this->get_users_post_vote($post_id);
	    	if($user_vote && !empty($user_vote)){
	    		//delete the old use vote
	    		delete_post_meta($post_id, HT_VOTING_KEY, $user_vote);
	    		//update the helpfulness
	    		if( is_a( $user_vote, 'HT_Vote_Up' ) )
					$this->update_article_helpfulness($post_id, -1);

				if( is_a( $user_vote, 'HT_Vote_Down' ) )
					$this->update_article_helpfulness($post_id, +1);
	    	}

	    	switch($direction){
	    		case 'up':
	    			if(class_exists('HT_Vote_Up')){
	    				$vote = new HT_Vote_Up();
	    				add_post_meta($post_id, HT_VOTING_KEY, $vote, false);
	    				$this->update_article_helpfulness($post_id, +1);
	    			}
	    			break;
	    		case 'down':
	    			if(class_exists('HT_Vote_Down')){
	    				$vote = new HT_Vote_Down();
	    				add_post_meta($post_id, HT_VOTING_KEY, $vote, false);
	    				$this->update_article_helpfulness($post_id, -1);
	    			}
	    			break;
	    		case 'neutral':
	    			if(class_exists('HT_Vote_Neutral')){
	    				$vote = new HT_Vote_Neutral();
	    				add_post_meta($post_id, HT_VOTING_KEY, $vote, false);
	    			}
	    			break;
	    		default:
	    			//numeric value
	    			if(is_numeric($direction)&&class_exists('HT_Vote_Value')){
						$vote_val = intval($direction);
						$vote = new HT_Vote_Value( $vote_val );
						echo " ADDING POST META ";
						var_dump($vote);
						echo HT_VOTING_KEY;
						echo $post_id;
						add_post_meta($post_id, HT_VOTING_KEY, $vote, false);
	    			}
	    			break;
	    	}
	    }

	     /*
	    * Perform the voting action
	    */
	    public function vote_comment($comment_id, $direction){
	    	//get the users vote and delete it
	    	$user_vote = $this->get_users_comment_vote($comment_id);
	    	if($user_vote && !empty($user_vote)){
	    		//delete the old use vote
	    		delete_comment_meta($comment_id, HT_VOTING_KEY, $user_vote);
	    		//update the helpfulness
	    		if( is_a( $user_vote, 'HT_Vote_Up' ) )
					$this->update_comment_helpfulness($comment_id, -1);

				if( is_a( $user_vote, 'HT_Vote_Down' ) )
					$this->update_comment_helpfulness($comment_id, +1);
	    	}

	    	switch($direction){
	    		case 'up':
	    			if(class_exists('HT_Vote_Up')){
	    				$vote = new HT_Vote_Up();
	    				add_comment_meta($comment_id, HT_VOTING_KEY, $vote, false);
	    				$this->update_comment_helpfulness($comment_id, +1);
	    			}
	    			break;
	    		case 'down':
	    			if(class_exists('HT_Vote_Down')){
	    				$vote = new HT_Vote_Down();
	    				add_comment_meta($comment_id, HT_VOTING_KEY, $vote, false);
	    				$this->update_comment_helpfulness($comment_id, -1);
	    			}
	    			break;
	    		case 'neutral':
	    			if(class_exists('HT_Vote_Neutral')){
	    				$vote = new HT_Vote_Neutral();
	    				add_comment_meta($comment_id, HT_VOTING_KEY, $vote, false);
	    			}
	    			break;
	    		default:
	    			//numeric value
	    			if(is_numeric($direction)&&class_exists('HT_Vote_Value')){
						$vote_val = intval($direction);
						$vote = new HT_Vote_Value( $vote_val );
						echo " ADDING POST META ";
						var_dump($vote);
						echo HT_VOTING_KEY;
						echo $comment_id;
						add_comment_meta($comment_id, HT_VOTING_KEY, $vote, false);
	    			}
	    			break;
	    	}
	    }

	    /**
		* +1 or -1
		*/
	    function update_article_helpfulness($post_id, $value){
	    	//get existing helpfulness
	    	$helpfulness = get_post_meta( $post_id, HT_USEFULNESS_KEY, true);

	    	//if not yet set, set it
	    	if(empty($helpfulness))
	    		$helpfulness = 0;

	    	//update the helpfulness
	    	$helpfulness = $helpfulness + $value;

	    	//save the post meta
	    	update_post_meta( $post_id, HT_USEFULNESS_KEY, $helpfulness );

	    	//also update the user helpfulness
	    	$post = get_post( $post_id );
	    	$this->update_user_helpfulness( $post->post_author, $value );
	    }

	     /**
		* +1 or -1
		*/
	    function update_comment_helpfulness($comment_id, $value){
	    	//get existing helpfulness
	    	$helpfulness = get_comment_meta( $comment_id, HT_USEFULNESS_KEY, true);

	    	//if not yet set, set it
	    	if(empty($helpfulness))
	    		$helpfulness = 0;

	    	//update the helpfulness
	    	$helpfulness = $helpfulness + $value;

	    	//save the comment meta
	    	update_comment_meta( $comment_id, HT_USEFULNESS_KEY, $helpfulness );

	    	//also update the user helpfulness
	    	$comment = get_comment( $comment_id );
	    	$this->update_user_helpfulness( $comment->user_id, $value );
	    }

	    /**
		* +1 or -1
		*/
	    function update_user_helpfulness($user_id, $value){
	    	//get existing helpfulness
	    	$helpfulness = get_user_meta( $user_id, HT_USEFULNESS_KEY, true);

	    	//if not yet set, set it
	    	if(empty($helpfulness))
	    		$helpfulness = 0;

	    	//update the helpfulness
	    	$helpfulness = $helpfulness + $value;

	    	//save the user meta
	    	update_user_meta( $user_id, HT_USEFULNESS_KEY, $helpfulness );
	    }

	    



	} //end class
} //end class exists

if(class_exists('HT_Voting')){
	$ht_voting_init = new HT_Voting();

	if(!function_exists('ht_voting_post')){
		function ht_voting_post( $post_id=null, $allow='user', $display='standard' ){
			global $post, $ht_voting_init;
			$post_id = ( empty( $post_id ) ) ? $post->ID : $post_id;
			$ht_voting_init->ht_voting_post_display( $post_id, $allow, $display );
		}
	} //end if ht_voting_post

	if(!function_exists('ht_voting_comment')){
		function ht_voting_comment( $comment_id=null, $allow='user', $display='standard' ){
			global $ht_voting_init;
			if( empty( $comment_id ) )
				return;

			$ht_voting_init->ht_voting_comment_display( $comment_id, $allow, $display );
		}
	} //end if ht_voting_comment

	if(!function_exists('ht_usefulness')){
		function ht_usefulness( $post_id=null ){
			global $post;
			//set the post id
			$post_id = ( empty( $post_id ) ) ? $post->ID : $post_id;
			//get the post usefulness meta
			$post_usefulness = get_post_meta( $post_id, HT_USEFULNESS_KEY, true );
			//convert to integer
			$post_usefulness_int = empty($post_usefulness) ? 0 : intval($post_usefulness);
			//return as integer
			return $post_usefulness_int;
		}
	} //end if ht_usefulness




}
