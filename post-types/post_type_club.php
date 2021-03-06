<?php

if (!class_exists('Post_Type_Club')) {

    /**
     * A PostTypeClub class that provides 3 additional meta fields
     */
    class Post_Type_Club
    {

        const POST_TYPE = "kivi_schedule_club";
        const CLUB_MULTI_META_DELIMITER = '##';

        private $_meta = array(
            'club_city_id',
            'club_is_active',
            'club_phone',
            'club_email',
            'club_map_link',
            'club_video_link',
            'club_tour_link',
            'club_slider_shortcode',
            'club_programs',
            'club_services',
        );

        /**
         * The Constructor
         */
        public function __construct()
        {
            // register actions
            add_action('init', array(&$this, 'init'), 1);
            add_action('admin_init', array(&$this, 'admin_init'));
        }// END public function __construct()

        /**
         * hook into WP's init action hook
         */
        public function init()
        {
            // Initialize Post Type
            $this->create_post_type();
            add_action('save_post', array(&$this, 'save_post'));
        }// END public function init()

        /**
         * Create the post type
         */
        public function create_post_type()
        {
            register_post_type(self::POST_TYPE, array(
                    'labels' => array(
                        'name' => __('Clubs', WP_Kivi_Schedule_Plugin::textdomain),
                        'singular_name' => __('Club', WP_Kivi_Schedule_Plugin::textdomain),
                        'add_new' => __('Add Club', WP_Kivi_Schedule_Plugin::textdomain),
                        'view_item' => __('View', WP_Kivi_Schedule_Plugin::textdomain),
                        'search_items' => __('Find Club', WP_Kivi_Schedule_Plugin::textdomain),
                        'add_new_item' => __('Add Club', WP_Kivi_Schedule_Plugin::textdomain)
                    ),
                    'hierarchical' => true,
                    'public' => true,
                    'has_archive' => true,
                    'show_in_menu' => 'time_table',
                    'supports' => array(
                        'title', 'editor', 'thumbnail'
                    )
                )
            );
        }

        /**
         * Save the metaboxes for this custom post type
         */
        public function save_post($post_id)
        {
            // verify if this is an auto save routine. 
            // If it is our form has not been submitted, so we dont want to do anything
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
                return;

            if (defined('DOING_AJAX')) return;//to prevent deleting the post meta on quick edit

            if (isset($_POST['post_type']) && $_POST['post_type'] == self::POST_TYPE && current_user_can('edit_post', $post_id)) {
                foreach ($this->_meta as $field_name) {
                    // Update the post's meta field
                    update_post_meta($post_id, $field_name, $_POST[$field_name]);
                }
            } else {
                return;
            } // if($_POST['post_type'] == self::POST_TYPE && current_user_can('edit_post', $post_id))
        }// END public function save_post($post_id)

        /**
         * hook into WP's admin_init action hook
         */
        public function admin_init()
        {
            // Add metaboxes
            add_action('add_meta_boxes', array(&$this, 'add_meta_boxes'));
        }// END public function admin_init()

        /**
         * hook into WP's add_meta_boxes action hook
         */
        public function add_meta_boxes()
        {
            // Add this metabox to every selected post
            add_meta_box(
                sprintf('wp_plugin_template_%s_section', self::POST_TYPE), __('Additional info', WP_Kivi_Schedule_Plugin::textdomain), array(&$this, 'add_inner_meta_boxes'), self::POST_TYPE
            );
        }// END public function add_meta_boxes()

        /**
         * called off of the add meta box
         */
        public function add_inner_meta_boxes($post)
        {
            // Render the job order metabox
            include(sprintf("%s/../templates/%s_metabox.php", dirname(__FILE__), self::POST_TYPE));
        }
        // END public function add_inner_meta_boxes($post)
    }
    // END class Post_Type_Club
}// END if(!class_exists('Post_Type_Club'))