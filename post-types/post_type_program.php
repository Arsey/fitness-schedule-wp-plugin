<?php

if (!class_exists('Post_Type_Program')) {

    /**
     * A PostTypeProgram class that provides 2 additional meta fields
     */
    class Post_Type_Program
    {

        const POST_TYPE = "kivi_schedule_prog";
        const CAT_TAXONOMY = 'kiwi_schedule_program_category';

        private $_meta = array(
            'program_description',
            'program_is_active',
            'program_details_link'
        );

        /**
         * The Constructor
         */
        public function __construct()
        {
            // register actions
            add_action('init', array(&$this, 'init'));
            add_action('admin_init', array(&$this, 'admin_init'));
        }

// END public function __construct()

        /**
         * hook into WP's init action hook
         */
        public function init()
        {
            // Initialize Post Type
            $this->create_post_type();
            add_action('save_post', array(&$this, 'save_post'));
        }

// END public function init()

        /**
         * Create the post type and taxonomy
         */
        public function create_post_type()
        {
            register_post_type(
                self::POST_TYPE,
                array(
                    'labels' => array(
                        'name' => __('Programs', WP_Kivi_Schedule_Plugin::textdomain),
                        'singular_name' => __('Program', WP_Kivi_Schedule_Plugin::textdomain),
                        'add_new' => __('Add Program', WP_Kivi_Schedule_Plugin::textdomain),
                        'view_item' => __('View', WP_Kivi_Schedule_Plugin::textdomain),
                        'search_items' => __('Find Program', WP_Kivi_Schedule_Plugin::textdomain),
                        'add_new_item' => __('Add Program', WP_Kivi_Schedule_Plugin::textdomain)
                    ),
                    'public' => true,
                    'has_archive' => true,
                    'show_in_menu' => 'time_table',
                    'supports' => array(
                        'title',
                        'thumbnail'
                    ),
                    'taxonomies' => array('kiwi_schedule_program_category'),
                )
            );

            $labels = array(
                'name' => __('Program Categories', WP_Kivi_Schedule_Plugin::textdomain),
                'singular_name' => __('Program Category', WP_Kivi_Schedule_Plugin::textdomain),
                'search_items' => __('Search Programs Categories', WP_Kivi_Schedule_Plugin::textdomain),
                'popular_items' => __('Popular Categories', WP_Kivi_Schedule_Plugin::textdomain),
                'all_items' => __('All Programs Categories', WP_Kivi_Schedule_Plugin::textdomain),
                'parent_item' => __('Parent Category', WP_Kivi_Schedule_Plugin::textdomain),
                'parent_item_colon' => __('Parent Category:', WP_Kivi_Schedule_Plugin::textdomain),
                'edit_item' => __('Edit Category', WP_Kivi_Schedule_Plugin::textdomain),
                'update_item' => __('Update Category', WP_Kivi_Schedule_Plugin::textdomain),
                'add_new_item' => __('Add New Category', WP_Kivi_Schedule_Plugin::textdomain),
                'new_item_name' => __('New Category Name', WP_Kivi_Schedule_Plugin::textdomain),
                'separate_items_with_commas' => __('Separate categories with commas', WP_Kivi_Schedule_Plugin::textdomain),
                'add_or_remove_items' => __('Add or remove categories', WP_Kivi_Schedule_Plugin::textdomain),
                'choose_from_most_used' => __('Choose from the most used categories', WP_Kivi_Schedule_Plugin::textdomain),
                'menu_name' => __('Programs Categories', WP_Kivi_Schedule_Plugin::textdomain),
            );

            $args = array(
                'labels' => $labels,
                'public' => true,
                'show_in_nav_menus' => true,
                'show_ui' => true,
                'show_tagcloud' => false,
                'hierarchical' => false,
                'query_var' => true
            );

            register_taxonomy(self::CAT_TAXONOMY, array(self::POST_TYPE), $args);
        }

        /**
         * Save the metaboxes for this custom post type
         */
        public function save_post($post_id)
        {
            // verify if this is an auto save routine. 
            // If it is our form has not been submitted, so we dont want to do anything
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            if (defined('DOING_AJAX')) return;//to prevent deleting the post meta on quick edit

            if (isset($_POST['post_type']) && $_POST['post_type'] == self::POST_TYPE && current_user_can('edit_post', $post_id)) {
                foreach ($this->_meta as $field_name) {
                    // Update the post's meta field
                    update_post_meta($post_id, $field_name, $_POST[$field_name]);
                }
            } else {
                return;
            } // if($_POST['post_type'] == self::POST_TYPE && current_user_can('edit_post', $post_id))
        }

// END public function save_post($post_id)

        /**
         * hook into WP's admin_init action hook
         */
        public function admin_init()
        {
            // Add metaboxes
            add_action('add_meta_boxes', array(&$this, 'add_meta_boxes'));
        }

// END public function admin_init()

        /**
         * hook into WP's add_meta_boxes action hook
         */
        public function add_meta_boxes()
        {
            // Add this metabox to every selected post
            add_meta_box(
                sprintf('wp_plugin_template_%s_section', self::POST_TYPE), __('Additional info', WP_Kivi_Schedule_Plugin::textdomain), array(&$this, 'add_inner_meta_boxes'), self::POST_TYPE
            );
        }

// END public function add_meta_boxes()

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

    // END class Post_Type_Program
} // END if(!class_exists('Post_Type_Program'))
