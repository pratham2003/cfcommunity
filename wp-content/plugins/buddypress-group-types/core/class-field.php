<?php

/**
 * Class BPGT_Field
 */
class BPGT_Field {

	public $title;
	public $content;
	public $order;
	public $type = BPGT_CPT_FIELD;

	/**
	 * Get group field data by ID
	 *
	 * @param $id
	 */
	function __construct( $id ) {
		$data = false;

		if ( is_numeric( $id ) ) {
			$data = get_post( $id );
		}

		if ( empty( $data ) ) {
			$field = self::get_empty();
		} else {
			$field = new Stdclass;
			foreach ( $data as $key => $value ) {
				$key         = str_replace( array( 'post_', 'menu_' ), '', $key );
				$field->$key = $value;
			}
		}

		return apply_filters( 'bpgt_fields_get_field', $field, $id );
	}

	/**
	 * Return the default empty object of data
	 *
	 * @return object
	 */
	static function get_empty() {
		$data          = new Stdclass;
		$data->title   = '';
		$data->content = '';
		$data->order   = '';
		$data->type    = BPGT_CPT_FIELD;

		return apply_filters( 'bpgt_fields_get_default', $data );
	}

	function save() {

	}

	static function delete( $id, $force = true ) {

	}
}