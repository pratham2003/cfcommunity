<?php
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class BPGT_Types extends  WP_List_Table {

    var $per_page = 25;
    var $_column_headers;

    function __construct(){
        parent::__construct( array(
            'singular'  => __( 'type', 'bpgt' ),   // singular name of the listed records
            'plural'    => __( 'types', 'bpgt' ),  // plural name of the listed records
            'ajax'      => false                   // does this table support ajax?
        ) );
    }

    /**
     * Get all types
     *
     * @param array $params
     * @return WP_Query $types
     */
    static function get($params = array()){
        $r = wp_parse_args($params, array(
            'post_type'      => BPGT_CPT_TYPE,
            'posts_per_page' => 999,
            'order'          => 'ASC',
            'orderby'        => 'title',
            'paged'          => 1
        ));

        $types = new WP_Query( $r );

        return $types;
    }

    function no_items() {
        _e( 'No group types found, sorry.', 'bpgt' );
    }

    /**
     * Columns registration
     */
    function column_default( $item, $column_name ) {
        switch ( $column_name ) {
            case 'post_title':
            case 'post_name':
            case 'post_excerpt':
            case 'comment_count':
                return $item->$column_name;
            default:
                return print_r( $item, true ); // Show the whole array for troubleshooting purposes
        }
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'post_title'   => array('post_title', false),
            'comment_count' => array('comment_count', false)
        );

        return $sortable_columns;
    }

    function get_columns() {
        $columns = array(
            'cb'            => '<input type="checkbox" />',
            'post_excerpt'  => __( 'Default Avatar', 'bpgt' ),
            'post_title'    => __( 'Title', 'bpgt' ),
            'post_name'     => __( 'Slug', 'bpgt' ),
            'comment_count' => __( '# Groups', 'bpgt' ),
        );

        return $columns;
    }

    /**
     * Columns content
     */
    function column_cb($item) {
        return '<input type="checkbox" name="'.$this->_args['plural'].'[]" value="'.$item->ID.'" />';
    }

    function column_post_title($item){
        $actions = array(
            'edit'   => sprintf('<a href="?page=%s&mode=%s&type_id=%s">Edit</a>',   $_REQUEST['page'], 'edit_type',   $item->ID),
            'delete' => sprintf('<a href="?page=%s&mode=%s&type_id=%s">Delete</a>', $_REQUEST['page'], 'delete_type', $item->ID),
        );

        return sprintf('%1$s %2$s', $item->post_title, $this->row_actions($actions) );
    }

    function column_post_name($item){
        $page = get_post($item->post_parent);
        if ( !empty($page) ) {
            return '/' . $page->post_name;
        }

        return '';
    }

    function column_post_excerpt($item){
        $image = '';

        if ( !empty($item->post_excerpt) ) {
            $image_url = wp_get_attachment_image_src($item->post_excerpt, 'full');
            if ( is_array($image_url) && isset($image_url[0]) && !empty($image_url[0]) ) {
                $image = '<img class="preview_avatar" src="' . $image_url[0] . '" alt="" />';
            }
        }

        return $image;
    }

    /**
     * Bulk actions
     */
    function get_bulk_actions() {
        $actions = array(
            'delete' => __('Delete', 'bpgt')
        );

        return $actions;
    }

    function process_bulk_action() {
        // Detect when a bulk action is being triggered...
        if( 'delete' === $this->current_action() ) {
            if ( isset($_POST['types']) && !empty($_POST['types']) ) {
                foreach ( $_POST[ 'types' ] as $type_id ) {
                    BPGT_Type::delete($type_id);
                }
            }
        }
    }

    protected function process_orderby_str(){
        $orderby = 'title';

        if ( !empty($_REQUEST['orderby']) ) {
            switch($_REQUEST['orderby']) {
                case 'title':
                case 'comment_count':
                case 'menu_order':
                    $orderby = $_REQUEST['orderby'];
                    break;

                case 'post_title':
                default:
                    $orderby = 'title';
            }
        }

        return $orderby;
    }

    /**
     * Get all group types
     */
    function prepare_items() {
        $this->process_bulk_action();

        $this->_column_headers = array( $this->get_columns(), array(), $this->get_sortable_columns() );

        $data = self::get(array(
                                 'order'          => (!empty($_REQUEST['order'])) ? $_REQUEST['order'] : 'ASC',
                                 'orderby'        => $this->process_orderby_str(),
                                 'paged'          => $this->get_pagenum()
                             ));

        $this->items = $data->posts;

        $this->set_pagination_args( array(
                                        'total_items' => $data->found_posts,
                                        'per_page'    => $this->per_page,
                                        'total_pages' => $data->max_num_pages
                                    ) );
    }

} // end of BPGT_Types

/**
 * Class BPGT_Type
 */
class BPGT_Type {

    public $ID;
    public $title;
    public $content;
    public $name;
    public $avatar_id;
    public $order;
    public $page;

    protected $type = BPGT_CPT_TYPE;

    /**
     * Get group type data by ID
     *
     * @param bool|int $type_id
     */
    function __construct($type_id = false){
        /** @var $wpdb WPDB */
        global $wpdb;
        $data = new Stdclass;

        if ( is_numeric($type_id) ) {
            $this->ID = (int) $type_id;
            $data = $wpdb->get_row($wpdb->prepare(
                       "SELECT * FROM {$wpdb->posts}
                        WHERE ID = %d
                          AND post_type = %s",
                        $this->ID,
                        BPGT_CPT_TYPE
            ));
        }

        if ( empty($data) ) {
            $this->make_empty();
        } else {
            foreach( $data as $key => $value ) {
                $key = str_replace( array('post_', 'menu_'), '', $key );

                switch ($key) {
                    case 'title':
                    case 'content':
                    case 'name':
                    case 'order':
                        $this->$key = $value;
                        break;
                    case 'parent':
                        $this->page = $value;
                        break;
                    case 'excerpt':
                        $this->avatar_id = $value;
                        break;
                }
            }
        }

        return apply_filters( 'bpgt_types_get_type', $this, $type_id );
    }

    /**
     * Return the default empty object of data
     *
     * @return object
     */
    function make_empty(){
        $this->title     = '';
        $this->content   = '';
        $this->order     = '';
        $this->avatar_id = '';
        $this->page      = '';

        return apply_filters( 'bpgt_types_get_empty', $this );
    }

    function get_avatar_img_src(){
        $src = '';

        if ( !empty($this->avatar_id) ) {
            $image_url = wp_get_attachment_image_src($this->avatar_id, 'full');
            if ( is_array($image_url) && isset($image_url[0]) && !empty($image_url[0]) ) {
                $src = $image_url[0];
            }
        }

        return $src;
    }

    function save(){
        $saved = wp_insert_post(array(
            'ID'           => $this->ID,
            'post_title'   => $this->title,
            'post_content' => $this->content,
            'post_excerpt' => $this->avatar_id,
            'post_parent'  => $this->page,
            'menu_order'   => $this->order,
            'post_type'    => $this->type,
            'post_status'  => 'publish'
        ));

        if ( is_wp_error($saved) || $saved == 0 ) {
            return false;
        }

        return $saved;
    }

    static function delete($type_id, $force = true){
        $deleted = wp_delete_post( $type_id, $force );
        if ( $deleted !== false ) { // it's not always true/false, sometimes might be an object
            return true;
        }

        return $deleted;
    }
}
