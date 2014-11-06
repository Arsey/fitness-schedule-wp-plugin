<?php

if (!class_exists('Post_Type_Program')) {

    /**
     * A PostTypeProgram class that provides 2 additional meta fields
     */
    class Post_Type_Program
    {

        const POST_TYPE = "post-type-program";

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
                        'name' => __('Программа'),
                        'singular_name' => __('Программы'),
                        'add_new' => __('Добавить программу'),
                        'view_item' => __('Просмотреть'),
                        'search_items' => __('Найти программу'),
                        'add_new_item' => __('Добавить программу')
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
                'name' => _x('Categories', 'kiwi_schedule_program'),
                'singular_name' => _x('Category', 'kiwi_schedule_program'),
                'search_items' => _x('Search Categories', 'kiwi_schedule_program'),
                'popular_items' => _x('Popular Categories', 'kiwi_schedule_program'),
                'all_items' => _x('All Categories', 'kiwi_schedule_program'),
                'parent_item' => _x('Parent Category', 'kiwi_schedule_program'),
                'parent_item_colon' => _x('Parent Category:', 'kiwi_schedule_program'),
                'edit_item' => _x('Edit Category', 'kiwi_schedule_program'),
                'update_item' => _x('Update Category', 'kiwi_schedule_program'),
                'add_new_item' => _x('Add New Category', 'kiwi_schedule_program'),
                'new_item_name' => _x('New Category Name', 'kiwi_schedule_program'),
                'separate_items_with_commas' => _x('Separate categories with commas', 'kiwi_schedule_program'),
                'add_or_remove_items' => _x('Add or remove categories', 'kiwi_schedule_program'),
                'choose_from_most_used' => _x('Choose from the most used categories', 'kiwi_schedule_program'),
                'menu_name' => _x('Categories', 'kiwi_schedule_program'),
            );

            $args = array(
                'labels' => $labels,
                'public' => true,
                'show_in_nav_menus' => true,
                'show_ui' => true,
                'show_tagcloud' => false,
                'hierarchical' => true,
                'query_var' => true
            );

            register_taxonomy('kiwi_schedule_program_category', array(self::POST_TYPE), $args);
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
                sprintf('wp_plugin_template_%s_section', self::POST_TYPE), __('Additional program info'), array(&$this, 'add_inner_meta_boxes'), self::POST_TYPE
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
