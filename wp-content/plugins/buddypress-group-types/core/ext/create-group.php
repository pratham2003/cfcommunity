<?php

/**
 * Add extra params to create group link
 *
 * @param $button_args
 *
 * @return mixed
 */
function bpgt_get_group_create_button( $button_args ) {
	if ( $type = bpgt_is_directory() ) {
		$button_args['link_href'] = add_query_arg( 'type', $type->ID, $button_args['link_href'] );
	}

	return $button_args;
}

//add_filter( 'bp_get_group_create_button', 'bpgt_get_group_create_button' );

function bpgt_set_create_type_cookie() {
	if ( bp_is_groups_component() || bp_is_current_action( 'create' ) ) {
		if ( isset( $_GET['type'] ) && is_numeric( $_GET['type'] ) ) {
			setcookie( 'bpgt-type-id', base64_encode( (int) $_GET['type'] ), time() + 60 * 60 * 24, COOKIEPATH ); // save for 1 day only
		}
	}
}

add_action( 'init', 'bpgt_set_create_type_cookie' );

if ( class_exists( 'BP_Group_Extension' ) ) :
	/**
	 * This is the main class, that process the group creation logic
	 */
	class BPGT_Create_Group extends BP_Group_Extension {
		public $enable_nav_item = false;
		public $types;

		function __construct() {
			if ( bp_is_groups_component() && bp_is_current_action( 'create' ) ) {
				//if ( ! bp_is_first_group_creation_step() ) {
				//	$this->check_group_type();
				//}

				// Check that the type ID alreasy saved
				// If it is - skip further loading of a class - so no extra quieries, no tab, etc
				//if ( ! bp_is_first_group_creation_step() ) {
				//	$type_id = groups_get_groupmeta(bp_get_new_group_id(), 'bpgt_group_type', true);
				//	if ( !empty($type_id) ) {
				//		return false;
				//	}
				//}

				$this->types = BPGT_Types::get();
			}

			$args = array(
				'class_name' => 'BPGT_Create_Group',
				'slug'       => 'group-type',
				'name'       => __( 'Type', 'bpgt' ),
				'screens'    => array(
					'create' => array(
						'position' => 5 // after Details, before Settings
					)
				)
			);

			parent::init( $args );
		}

		function create_screen( $group_id = null ) {
			$default_type_id = $this->get_default_type();

			if ( $this->types->have_posts() ) { ?>
				<?php while ( $this->types->have_posts() ): $this->types->the_post() ?>
					<div class="bpgt_create">
						<label>
							<input type="radio" name="bpgt_create_type_id"
							       value="<?php the_ID() ?>" <?php checked( $default_type_id, get_the_ID() ) ?>/>&nbsp;
							<?php the_title(); ?>
						</label>

						<div class="bpgt_create_image">
							<?php
							$type   = new BPGT_Type( get_the_ID() );
							$avatar = $type->get_avatar_img_src();
							if ( ! empty( $avatar ) ) { ?>
								<img src="<?php echo $avatar; ?>" alt="<?php the_title_attribute(); ?>"/>
							<?php } ?>
						</div>

						<div class="bpgt_create_description"><?php the_content(); ?></div>
					</div>
				<?php endwhile; ?>

				<div class="bpgt_create bpgt_create_default">
					<label>
						<input type="radio" name="bpgt_create_type_id"
						       value="0" <?php checked( $default_type_id, get_the_ID() ) ?>/>&nbsp;
						<?php _e( 'Ordinary Group', 'bpgt' ); ?>
					</label>

					<div class="bpgt_create_image"></div>

					<div class="bpgt_create_description">
						<?php _e( 'Groups allow your users to organize themselves into specific public, private or hidden sections with separate activity streams and member listings.', 'bpgt' ); ?>
					</div>
				</div>

			<?php
			}
		}

		/**
		 * Save the group type submitted on group creation
		 *
		 * @param int|null $group_id
		 */
		function create_screen_save( $group_id = null ) {
			$type_id = isset( $_POST['bpgt_create_type_id'] ) ? (int) $_POST['bpgt_create_type_id'] : '';

			// Save group type and delete the cookie
			if ( ! empty( $type_id ) ) {
				$this->save_type( $group_id, $type_id );
				$this->clear_type_memo();
			}
		}

		/**
		 * Actualy save the type
		 *
		 * @param int $group_id
		 * @param int $type_id
		 */
		function save_type( $group_id, $type_id ) {
			groups_update_groupmeta( (int) $group_id, 'bpgt_group_type', (int) $type_id );
		}

		/**
		 * Get the type id based on cookie. Default is 0 - ordinary group
		 * @return int
		 */
		private function get_default_type() {
			$default_type_id = 0;

			if ( ! empty( $_COOKIE['bpgt-type-id'] ) ) {
				$default_type_id = (int) base64_decode( $_COOKIE['bpgt-type-id'] );
			}

			return $default_type_id;
		}

		/**
		 * If we have in cookie the type id - we can process it right away,
		 * without actually displaying the step to users
		 */
		private function check_group_type() {
			global $bp;

			$next_step = bp_get_groups_current_create_step();
			$keys      = array_keys( $bp->groups->group_creation_steps );

			foreach ( $keys as $key ) {
				if ( $key == bp_get_groups_current_create_step() ) {
					$next = 1;
					continue;
				}

				if ( isset( $next ) ) {
					$next_step = $key;
					break;
				}
			}

			// get cookie
			$type_id = $this->get_default_type();

			// redirect with saving & clearing cookie
			if ( $type_id !== 0 ) {
				$this->save_type( bp_get_new_group_id(), $type_id );
				$this->clear_type_memo();

				bp_core_redirect( bp_get_root_domain() . '/' . bp_get_groups_root_slug() . '/create/step/' . $next_step . '/' );
			}
		}

		/**
		 * Delete the cookie that stores the type id
		 */
		private function clear_type_memo() {
			setcookie( 'bpgt-type-id', false, time() - 1000, COOKIEPATH );
		}
	}

	bp_register_group_extension( 'BPGT_Create_Group' );

endif;