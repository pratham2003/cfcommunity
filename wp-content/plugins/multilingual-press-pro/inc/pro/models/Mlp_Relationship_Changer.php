<?php

/**
 * Class Mlp_Relationship_Changer
 *
 * Changes post relationships on AJAX calls.
 *
 * @version 2014.02.17
 * @author  Inpszde GmbH, toscho
 * @license GPL
 */
class Mlp_Relationship_Changer {

	/**
	 * @var string
	 */
	private $table;

	/**
	 * @var int
	 */
	private $source_post_id = 0;

	/**
	 * @var int
	 */
	private $source_blog_id = 0;

	/**
	 * @var int
	 */
	private $remote_post_id = 0;

	/**
	 * @var int
	 */
	private $remote_blog_id = 0;

	/**
	 * @var int
	 */
	private $new_post_id    = 0;

	/**
	 * @var string
	 */
	private $new_post_title = '';

	/**
	 * @param Inpsyde_Property_List_Interface $data
	 */
	public function __construct( Inpsyde_Property_List_Interface $data ) {

		$this->table = $data->link_table;
		$this->prepare_values();
	}

	/**
	 * @return array|int|string|WP_Error
	 */
	public function new_relation() {

		switch_to_blog( $this->source_blog_id );

		$source_post = get_post( $this->source_post_id );

		restore_current_blog();

		if ( ! $source_post )
			return 'source not found';

		switch_to_blog( $this->remote_blog_id );

		$this->kill_hooks( $this->get_real_post_type( $source_post ) );

		$post_id = wp_insert_post(
			array (
				'post_type'   => $source_post->post_type,
				'post_status' => 'draft',
				'post_title'  => $this->new_post_title
			),
			TRUE
		);

		restore_current_blog();

		if ( is_a( $post_id, 'WP_Error' ) )
			return $post_id->get_error_messages();

		$this->new_post_id = $post_id;

		$this->connect_existing();

		return $this->new_post_id;
	}

	/**
	 * Get the real current post type.
	 *
	 * Includes workaround for auto-drafts.
	 *
	 * @param  WP_Post $post
	 * @return string
	 */
	public function get_real_post_type( WP_Post $post ) {

		if ( 'revision' !== $post->post_type )
			return $post->post_type;

		if ( empty ( $_POST[ 'post_type' ] ) )
			return $post->post_type;

		if ( 'revision' === $_POST[ 'post_type' ] )
			return $post->post_type;

		if ( is_string( $_POST[ 'post_type' ] ) )
			return $_POST[ 'post_type' ]; // auto-draft

		return $post->post_type;
	}

	/**
	 * @return false|int
	 */
	public function connect_existing() {

		$this->disconnect();
		return $this->create_new_relation();

	}

	/**
	 * @return false|int
	 */
	private function create_new_relation() {

		global $wpdb;

		return $wpdb->insert(
			 $this->table,
			 array (
				 'ml_source_blogid'    => $this->source_blog_id,
				 'ml_source_elementid' => $this->source_post_id,
				 'ml_blogid'           => $this->remote_blog_id,
				 'ml_elementid'        => $this->new_post_id
			 )
		);
	}

	/**
	 * @return false|int
	 */
	public function disconnect() {

		global $wpdb;

		$sql = '
DELETE FROM ' . $this->table . '
WHERE `ml_source_blogid` = %1$d
	AND `ml_source_elementid` = %2$d
	AND `ml_blogid` = %3$d
	AND `ml_elementid` = %4$d
OR `ml_source_blogid` = %3$d
	AND `ml_source_elementid` = %4$d
	AND `ml_blogid` = %1$d
	AND `ml_elementid` = %2$d';

		// delete all connections for the source and the target
		/*
		$sql = '
DELETE FROM ' . $this->table . '
WHERE `ml_source_blogid` = %1$d AND `ml_source_elementid` = %2$d
OR    `ml_source_blogid` = %3$d AND `ml_source_elementid` = %4$d';
		*/

		$query = sprintf(
			$sql,
			$this->source_blog_id,
			$this->source_post_id,
			$this->remote_blog_id,
			$this->remote_post_id
		);

		return $wpdb->query( $query );
	}

	/**
	 * Fill default values.
	 *
	 * @return void
	 */
	private function prepare_values() {

		$find = array (
			'source_post_id',
			'source_blog_id',
			'remote_post_id',
			'remote_blog_id',
			'new_post_id',
			'new_post_title',
		);

		foreach ( $find as $value ) {
			if ( ! empty ( $_REQUEST[ $value ] ) ) {

				if ( 'new_post_title' === $value )
					$this->$value = (string) $_REQUEST[ $value ];
				else
					$this->$value = (int) $_REQUEST[ $value ];
			}
		}
	}

	/**
	 * @param  string $post_type
	 * @return void
	 */
	private function kill_hooks( $post_type ) {

		remove_all_filters( 'pre_post_update' );
		remove_all_filters( "save_post_$post_type" );
		remove_all_filters( 'save_post' );
		remove_all_filters( 'wp_insert_post' );
	}
}