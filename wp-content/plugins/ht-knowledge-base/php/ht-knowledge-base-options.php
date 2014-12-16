<?php

/**
 * ReduxFramework  Config file for HT Knowledge Base
 */

if (!class_exists('Redux_Framework_Knowledge_Base_Settings')) {

    class Redux_Framework_Knowledge_Base_Settings {

        public $args        = array();
        public $sections    = array();
        public $theme;
        public $ReduxFramework;

        //Constructor
        public function __construct() {

            if (!class_exists('ReduxFramework')) {
                return;
            }

            // This is needed. Bah WordPress bugs.  ;)
            if (  true == Redux_Helpers::isTheme(__FILE__) ) {
                $this->initSettings();
            } else {
                add_action('plugins_loaded', array($this, 'initSettings'), 10);
            }

        }

        /**
        * Initialize Redux framework
        */
        public function initSettings() {

            // Set the default arguments
            $this->setArguments();

            // Create the sections and fields
            $this->setSections();

            if (!isset($this->args['opt_name'])) { // No errors please
                return;
            }

            $this->ReduxFramework = new ReduxFramework($this->sections, $this->args);
        }

        /**
        * Set Redux sections
        */
        public function setSections() {

            $requires_voting = '';

            //if the voting plugin is not installed, remind the user
            if(!class_exists('HT_Voting')){
                $requires_voting = ' <b>' . __( 'Note: Requires Heroic Voting plugin (currently inactive)', 'ht-knowledge-base' ) . '</b>';
            }


            $general_settings_fields = array(

                    array(
                        'id'        => 'breadcrumbs-display',
                        'type'      => 'switch',
                        'title'     => __('Breadcrumbs', 'ht-knowledge-base'),
                        'subtitle'  => __('Display breadcrumbs in knowledge base', 'ht-knowledge-base'),
                        'default'   => true,
                    ),
                    array(
                        'id'        => 'meta-display',
                        'type'      => 'switch',
                        'title'     => __('Entry Meta', 'ht-knowledge-base'),
                        'subtitle'  => __('Show meta information user, date, etc on article page', 'ht-knowledge-base'),
                        'default'   => true,
                    ),
                    array(
                        'id'        => 'voting-display',
                        'type'      => 'switch',
                        'title'     => __('Enable Voting', 'ht-knowledge-base'),
                        'subtitle'  => __( 'Allow readers to vote', 'ht-knowledge-base' ) . $requires_voting,
                        'default'   => true,
                    ),
                    array(
                        'id'        => 'anon-voting',
                        'type'      => 'switch',
                        'title'     => __('Enable Anonymous', 'ht-knowledge-base'),
                        'subtitle'  => __('Allow users to vote that are not logged in', 'ht-knowledge-base'),
                        'default'   => true,
                        'required'  => array('voting-display', "=", 1),
                    ),
                    array(
                        'id'        => 'article-comments',
                        'type'      => 'switch',
                        'title'     => __('Enable Comments', 'ht-knowledge-base'),
                        'subtitle'  => __('Allow readers to comment on articles', 'ht-knowledge-base'),
                        'default'   => true,
                    ),
                    array(
                        'id'        => 'kb-home-info',
                        'type'      => 'info',
                        'title'     => __('Knowledge Base Home', 'ht-knowledge-base'),
                        'subtitle'  => __('Use the knowledge base as your homepage', 'ht-knowledge-base'),
                        'desc'      => sprintf(__('This option is no longer in use, please use %sWordPress Settings > Reading%s', 'ht-knowledge-base'), '<a href="' . admin_url('options-reading.php') . '">', '</a>'),
                        'default'   => false,
                        'required'  => array('kb-home', "=", 1),
                    ),
                    array(
                        'id'        => 'kb-home',
                        'type'      => 'switch',
                        'title'     => __('Knowledge Base Home', 'ht-knowledge-base'),
                        'subtitle'  => __('Use the knowledge base as your homepage', 'ht-knowledge-base'),
                        'default'   => false,
                    ),
                    array(
                        'id'       => 'sort-by',
                        'type'     => 'select',
                        'title'    => __('Sort By', 'ht-knowledge-base'), 
                        'subtitle' => __('Sort knowledge base articles by', 'ht-knowledge-base'),
                        // Must provide key => value pairs for select options
                        'options'  => array(
                            'date' => __('Date', 'ht-knowledge-base'),
                            'title' => __('Title', 'ht-knowledge-base'),
                            'comment_count' => __('Comment Count', 'ht-knowledge-base'),
                            'rand' => __('Random', 'ht-knowledge-base'),
                            'modified' => __('Modified', 'ht-knowledge-base'),
                            'popular' => __('Popular', 'ht-knowledge-base'),
                            'helpful' => __('Helpful', 'ht-knowledge-base'),
                        ),
                        'default'  => 'date',
                    ),
                     array(
                        'id'       => 'sort-order',
                        'type'     => 'select',
                        'title'    => __('Sort Order', 'ht-knowledge-base'), 
                        'subtitle' => __('Sort knowledge base articles order', 'ht-knowledge-base'),
                        // Must provide key => value pairs for select options
                        'options'  => array(
                            'asc' => __('Ascending', 'ht-knowledge-base'),
                            'desc' => __('Descending', 'ht-knowledge-base'),
                        ),
                        'default'  => 'asc',
                    ),
                    array(
                        'id'       => 'tax-cat-article-number',
                        'type'     => 'spinner', 
                        'title'    => __('Number of Articles to Display in Category or Tag', 'ht-knowledge-base'),
                        'subtitle' => __('Number of articles to display for each category from category or tag listing','ht-knowledge-base'),
                        'default'  => '5',
                        'min'      => '1',
                        'step'     => '1',
                        'max'      => '100',
                    ),
                    array(
                        'id'        => 'related-rating',
                        'type'      => 'switch',
                        'title'     => __('Display Usefulness in Related Posts', 'ht-knowledge-base'),
                        'subtitle'  => __('Display the article usefulness in related posts', 'ht-knowledge-base') . $requires_voting,
                        'default'   => true,
                    ),
                    array(
                        'id'       => 'archive-columns',
                        'type'     => 'spinner', 
                        'title'    => __('Knowledge Base Columns', 'ht-knowledge-base'),
                        'subtitle' => __('Number of columns for the knowledge base home','ht-knowledge-base'),
                        'default'  => '2',
                        'min'      => '1',
                        'step'     => '1',
                        'max'      => '4',
                    ),
                    array(
                        'id'        => 'sub-cat-article-count',
                        'type'      => 'switch',
                        'title'     => __('Display Category Article Count', 'ht-knowledge-base'),
                        'subtitle'  => __('Display the number of articles in categories', 'ht-knowledge-base') . $requires_voting,
                        'default'   => true,
                    ),
                    array(
                        'id'       => 'sub-cat-article-number',
                        'type'     => 'spinner', 
                        'title'    => __('Number of Articles to Display in Home', 'ht-knowledge-base'),
                        'subtitle' => __('Number of articles to display for each category from knowledge base home','ht-knowledge-base'),
                        'default'  => '5',
                        'min'      => '1',
                        'step'     => '1',
                        'max'      => '100',
                    ),   
                    array(
                        'id'        => 'sub-cat-display',
                        'type'      => 'switch',
                        'title'     => __('Display Sub Categories', 'ht-knowledge-base'),
                        'subtitle'  => __('Display the sub categories from knowledge base home', 'ht-knowledge-base') . $requires_voting,
                        'default'   => true,
                    ),
                    array(
                        'id'       => 'sub-cat-depth',
                        'type'     => 'spinner', 
                        'title'    => __('Sub Category Depth', 'ht-knowledge-base'),
                        'subtitle' => __('Number of sub categories to display from knowledge base home','ht-knowledge-base'),
                        'default'  => '1',
                        'min'      => '0',
                        'step'     => '1',
                        'max'      => '2',
                        'required'  => array('sub-cat-display', "=", 1),
                    ),
                    array(
                        'id'        => 'sub-cat-article-display',
                        'type'      => 'switch',
                        'title'     => __('Display Sub Category Articles', 'ht-knowledge-base'),
                        'subtitle'  => __('Display the articles in sub categories from knowledge base home', 'ht-knowledge-base') . $requires_voting,
                        'default'   => true,
                        'required'  => array('sub-cat-display', "=", 1),
                    ),                 
                );


            //general sections
            $this->sections[] = array(
                'title'     => __('General Settings', 'ht-knowledge-base'),
                'desc'      => __('Set various general options for the knowledge base from this page.', 'ht-knowledge-base'),
                'icon'      => 'el-icon-home',
                // 'submenu' => false, // Setting submenu to false on a given section will hide it from the WordPress sidebar menu!
                'fields'    => $general_settings_fields
            );

            $live_search_settings_fields = array(
                                            array(
                                                'id'        => 'search-display',
                                                'type'      => 'switch',
                                                'title'     => __('Live Search', 'ht-knowledge-base'),
                                                'subtitle'  => __('Display search box on knowledge base pages', 'ht-knowledge-base'),
                                                'default'   => true,
                                            ),
                                            array(
                                                'id'        => 'search-placeholder-text',
                                                'type'      => 'text',
                                                'title'     => __('Search Placeholder', 'ht-knowledge-base'),
                                                'subtitle'  => __('The placeholder text for the search box', 'ht-knowledge-base'),
                                                'default'   => __('Search the Knowledge Base', 'ht-knowledge-base'),
                                                'required'  => array( 'search-display', "=", 1 ),
                                            ),
                                            array(
                                                'id'        => 'search-types',
                                                'type'      => 'select',
                                                'title'     => __('Search Posts Types', 'ht-knowledge-base'),
                                                'subtitle'  => __('Choose post types to include in search results', 'ht-knowledge-base') . $requires_voting,
                                                'default'   => 'ht_kb',
                                                'data'      => 'callback',
                                                'args'      => array('ht_kb_get_post_types'),
                                                'multi'     => true,
                                                
                                            ),
                                            array(
                                                'id'        => 'search-excerpt',
                                                'type'      => 'switch',
                                                'title'     => __('Search Result Excerpt', 'ht-knowledge-base'),
                                                'subtitle'  => __('Display an excerpt of knowledge base articles in search results', 'ht-knowledge-base'),
                                                'default'   => true,
                                            ),                                            
                                            array(
                                                'id'        => 'usefulness-display',
                                                'type'      => 'switch',
                                                'title'     => __('Display Usefulness in Search Results', 'ht-knowledge-base'),
                                                'subtitle'  => __('Display the article usefulness in search results', 'ht-knowledge-base') . $requires_voting,
                                                'default'   => true,
                                            ),
                                            array(
                                                'id'        => 'viewcount-display',
                                                'type'      => 'switch',
                                                'title'     => __('Display Views in Search Results', 'ht-knowledge-base'),
                                                'subtitle'  => __('Display the article view count in search results', 'ht-knowledge-base'),
                                                'default'   => true,
                                            ),
                                            array(
                                                'id'        => 'comments-display',
                                                'type'      => 'switch',
                                                'title'     => __('Display Comment Count', 'ht-knowledge-base'),
                                                'subtitle'  => __('Display the comments count in search results', 'ht-knowledge-base'),
                                                'default'   => true,
                                            ),                                            
                                        );

            //live search sections
            $this->sections[] = array(
                'title'     => __('Search Options', 'ht-knowledge-base'),
                'desc'      => __('Set various options for the knowledge base search functionality from this page.', 'ht-knowledge-base'),
                'icon'      => 'el-icon-search',
                // 'submenu' => false, // Setting submenu to false on a given section will hide it from the WordPress sidebar menu!
                'fields'    => $live_search_settings_fields
            );


            $slugs_settings_fields = array(
                                        array(
                                            'id'                => 'ht-kb-slug',
                                            'type'              => 'text',
                                            'title'             => __('Knowledge Base Article Slug', 'ht-knowledge-base'),
                                            'subtitle'          => __('Defines the text slug for articles.', 'ht-knowledge-base'),
                                            'desc'              => __('Eg. www.example.com/<b>articles</b>/my-article', 'ht-knowledge-base'),
                                            'validate_callback' => 'redux_validate_slug',
                                            'default'           => __('knowledge-base', 'ht-knowledge-base')
                                        ),
                                        array(
                                            'id'                => 'ht-kb-cat-slug',
                                            'type'              => 'text',
                                            'title'             => __('Knowledge Base Category Slug', 'ht-knowledge-base'),
                                            'subtitle'          => __('Defines the text slug for knowledge base categories.', 'ht-knowledge-base'),
                                            'desc'              => __('Eg. www.example.com/<b>article-categories</b>/account-setup', 'ht-knowledge-base'),
                                            'validate_callback' => 'redux_validate_slug',
                                            'default'           => __('article-categories', 'ht-knowledge-base')
                                        ),
                                        array(
                                            'id'                => 'ht-kb-tag-slug',
                                            'type'              => 'text',
                                            'title'             => __('Knowledge Base Tag Slug', 'ht-knowledge-base'),
                                            'subtitle'          => __('Defines the text slug for knowledge base tags.', 'ht-knowledge-base'),
                                            'desc'              => __('Eg. www.example.com/<b>article-tags</b>/payment', 'ht-knowledge-base'),
                                            'validate_callback' => 'redux_validate_slug',
                                            'default'           => __('article-tags', 'ht-knowledge-base')
                                        ),
                                    );
            //slugs sections
            $this->sections[] =  array( 'title'     => __('Slugs', 'ht-knowledge-base'),
                'desc'      => sprintf( __('Sets paths and urls for the knowledge base. <b>Remember to visit the <a href="%s">Permalinks Page</a> every time these settings are changed to ensure WordPress uses the new slugs.</b>', 'ht-knowledge-base') , admin_url('options-permalink.php') ),
                'icon'      => 'el-icon-cogs',
                // 'submenu' => false, // Setting submenu to false on a given section will hide it from the WordPress sidebar menu!
                'fields'    => $slugs_settings_fields,
                );
        }

        /**
        * Set Redux arguments
        */
        public function setArguments() {

            $theme = wp_get_theme(); // For use with some settings. Not necessary.           
            
            /* enable/disable tracking on Redux Framework option panel */
            $ht_redux_framework_options = get_option('redux-framework-tracking'); // get the array
            $ht_redux_framework_options['allow_tracking'] = 'no'; // set the value to yes or no
            
            update_option('redux-framework-tracking', $ht_redux_framework_options); // update the array

            $this->args = array(
                'opt_name'          => 'ht_knowledge_base_options',            // This is where your data is stored in the database and also becomes your global variable name.
                'display_name'      => __( 'Heroic Knowledge Base', 'ht-knowledge-base' ),     // Name that appears at the top of your panel
                'display_version'   => '',  // Version that appears at the top of your panel
                'menu_type'         => 'submenu',                  //Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
                'allow_sub_menu'    => true,                    // Show the sections below the admin menu item or not
                'menu_title'        => __('Settings', 'ht-knowledge-base'),
                'page_title'        => __('Heroic Knowledge Base Settings', 'ht-knowledge-base'),
                
                // You will need to generate a Google API key to use this feature.
                // Please visit: https://developers.google.com/fonts/docs/developer_api#Auth
                'google_api_key' => '', // Must be defined to add google fonts to the typography module
                
                'async_typography'  => false,                    // Use a asynchronous font on the front end or font string
                'admin_bar'         => false,                    // Show the panel pages on the admin bar
                'global_variable'   => '',                      // Set a different name for your global variable other than the opt_name
                'dev_mode'          => false,                    // Show the time the page took to load, etc
                'customizer'        => false,                    // Enable basic customizer support
                
                // OPTIONAL -> Give you extra features
                'page_priority'     => null,                    // Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
                'page_parent'       => 'edit.php?post_type=ht_kb',            // For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
                'page_permissions'  => 'manage_options',        // Permissions needed to access the options panel.
                'menu_icon'         => '',                      // Specify a custom URL to an icon
                'last_tab'          => '',                      // Force your panel to always open to a specific tab (by id)
                'page_icon'         => 'icon-themes',           // Icon displayed in the admin panel next to your menu_title
                'page_slug'         => '_ht_kb_options',              // Page slug used to denote the panel
                'save_defaults'     => true,                    // On load save the defaults to DB before user clicks save or not
                'default_show'      => false,                   // If true, shows the default value next to each field that is not the default value.
                'default_mark'      => '',                      // What to print by the field's title if the value shown is default. Suggested: *
                'show_import_export' => true,                   // Shows the Import/Export panel when not used as a field.
                
                // CAREFUL -> These options are for advanced use only
                'transient_time'    => 60 * MINUTE_IN_SECONDS,
                'output'            => true,                    // Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
                'output_tag'        => true,                    // Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
                // 'footer_credit'     => '',                   // Disable the footer credit of Redux. Please leave if you can help it.
                
                // FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
                'database'              => '', // possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
                'system_info'           => false, // REMOVE

                // HINTS
                'hints' => array(
                    'icon'          => 'icon-question-sign',
                    'icon_position' => 'right',
                    'icon_color'    => 'lightgray',
                    'icon_size'     => 'normal',
                    'tip_style'     => array(
                        'color'         => 'light',
                        'shadow'        => true,
                        'rounded'       => false,
                        'style'         => '',
                    ),
                    'tip_position'  => array(
                        'my' => 'top left',
                        'at' => 'bottom right',
                    ),
                    'tip_effect'    => array(
                        'show'          => array(
                            'effect'        => 'slide',
                            'duration'      => '500',
                            'event'         => 'mouseover',
                        ),
                        'hide'      => array(
                            'effect'    => 'slide',
                            'duration'  => '500',
                            'event'     => 'click mouseleave',
                        ),
                    ),
                )
            );


            // SOCIAL ICONS -> Setup custom links in the footer for quick links in your panel footer icons.
            $this->args['share_icons'][] = array(
                'url'   => 'https://www.facebook.com/herothemes',
                'title' => 'Like us on Facebook',
                'icon'  => 'el-icon-facebook'
            );
            $this->args['share_icons'][] = array(
                'url'   => 'https://twitter.com/herothemes',
                'title' => 'Follow us on Twitter',
                'icon'  => 'el-icon-twitter'
            );

            // Panel Intro text -> before the form
            if (!isset($this->args['global_variable']) || $this->args['global_variable'] !== false) {
                if (!empty($this->args['global_variable'])) {
                    $v = $this->args['global_variable'];
                } else {
                    $v = str_replace('-', '_', $this->args['opt_name']);
                }
                $this->args['intro_text'] = sprintf(__('<p>Control settings for Heroic Knowledge Base</p>', 'ht-knowledge-base'), $v);
            } else {
                $this->args['intro_text'] = __('<p>This text is displayed above the options panel. It isn\'t required, but more info is always better! The intro_text field accepts all HTML.</p>', 'ht-knowledge-base');
            }

            // Add content after the form.
            $this->args['footer_text'] = '';
        }

        function ht_kb_get_post_types() {
            $data = $this->ReduxFramework->get_wordpress_data('post_types');
            return $data;
        } 

    }
    
    global $ht_kb_redux_config;
    $ht_kb_redux_config = new Redux_Framework_Knowledge_Base_Settings();
}

 /**
*  Custom function for the callback validation referenced above
 */
if(!function_exists('redux_validate_slug')){
    /**
    *
    * Slug validation function
    * @param (String) $field The field to validate
    * @param (String) $value The new value of the slug
    * @param (String) $existing_value The exisiting value of the slug
    * @param (Array) The validation array
    */
    function redux_validate_slug($field, $value, $existing_value) {
        $error = false;

        //get the santized title
        $value = sanitize_title($value);

        if( strlen($value) < 2 ){
            $error = true;
            $field['msg'] =  __( 'Your slug is too short, it must be at least two characters', 'ht-knowledge-base' );
        }

        $return['value'] = $value;
        if ( $error == true ) {
            $return['value'] = $existing_value;
            $return['error'] = $field;
        } else {
            //if no error and value has changed, flag to flush rewrite rules
            if( $value!=$existing_value )
                add_option('ht_kb_flush_rewrite_required', 'true');
        }
        return $return;
    }  
 
}

if(!function_exists('redux_validate_cpt_as_homepage')){
    /**
    *
    * Slug validation function
    * @param (String) $field The field to validate
    * @param (String) $value The new value of the field
    * @param (String) $existing_value The exisiting value of the field
    * @param (Array) The validation array
    * @deprecated
    */
    function redux_validate_cpt_as_homepage($field, $value, $existing_value) {
        $error = false;

        if($value==true){
            //ok, we need update show on front
            update_option( 'show_on_front', 'posts' );
        }
        $return['value'] = $value;
        return $return;
    }    
}


if(!function_exists('ht_kb_get_post_types')){
    /**
    *
    * Replacement function for get_post_types, because bbPress types not being returned correctly
    */
    function ht_kb_get_post_types($args=null) {
        global $wp_post_types;

        $args   = array(
            'public'              => true,
            'exclude_from_search' => false,
        );
        $output     = 'names';
        $operator   = 'and';
        $post_types = get_post_types( $args, $output, $operator );

        /*add forums, overcomes bug where bbPress CPT's from get_post_types
        are not yet available but are included in $wp_post_types */
        if(class_exists('bbPress')){         
            //add bbPress CPTs to post types   
            $post_types[bbp_get_forum_post_type()]=''; 
            $post_types[bbp_get_topic_post_type()]=''; 
            $post_types[bbp_get_reply_post_type()]='';
        }

        foreach ( $post_types as $name => $title ) {
            if ( isset( $wp_post_types[ $name ]->labels->menu_name ) ) {
                $data[ $name ] = $wp_post_types[ $name ]->labels->menu_name;
            } else {
                $data[ $name ] = ucfirst( $name );
            }
        }

        //exclude media/attachment
        if(array_key_exists('attachment', $data)){
            unset($data['attachment']);
        }

        return $data;
    }    
}





