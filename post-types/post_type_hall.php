<?php

if (!class_exists('Post_Type_Hall')) {

    /**
     * A PostTypeHall class that provides 2 additional meta fields
     */
    class Post_Type_Hall
    {

        const POST_TYPE = "kivi_schedule_hall";

        private $_meta = array(
            'hall_club_id',
            'hall_city_id'
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
         * Create the post type
         */
        public function create_post_type()
        {
            register_post_type(self::POST_TYPE, array(
                    'labels' => array(
                        'name' => __('Halls', WP_Kivi_Schedule_Plugin::textdomain),
                        'singular_name' => __('Hall', WP_Kivi_Schedule_Plugin::textdomain),
                        'add_new' => __('Add Hall', WP_Kivi_Schedule_Plugin::textdomain),
                        'view_item' => __('View', WP_Kivi_Schedule_Plugin::textdomain),
                        'search_items' => __('Find Hall', WP_Kivi_Schedule_Plugin::textdomain),
                        'add_new_item' => __('Add Hall', WP_Kivi_Schedule_Plugin::textdomain)
                    ),
                    'hierarchical' => true,
                    'public' => true,
                    'has_archive' => true,
                    'show_in_menu' => 'time_table',
                    'capability_type' => 'post',
                    'supports' => array(
                        'title'
                    ),
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
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            if (defined('DOING_AJAX')) return;//to prevent deleting the post meta on quick edit

            if (isset($_POST['post_type']) && $_POST['post_type'] == self::POST_TYPE && current_user_can('edit_post', $post_id)) {
                foreach ($this->_meta as $field_name) {
                    // Update the post's meta field
                    update_post_meta($post_id, $field_name, $_POST[$field_name]);
                }

                /**
                 * if current user is club manager,
                 * update club id and city id forced
                 */
                if ($data = WP_Kivi_Schedule_Plugin::is_user_current_club_manager()) {
                    $city_id = get_post_meta($data['user_club'], 'club_city_id', true);
                    update_post_meta($post_id, 'hall_club_id', $data['user_club']);
                    update_post_meta($post_id, 'hall_city_id', $city_id);
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
            add_filter('manage_edit_' . self::POST_TYPE . '_posts_custom_column', array(&$this, 'add_custom_columns'), 10, 2);

            add_filter('manage_edit-' . self::POST_TYPE . '_columns', array(&$this, 'set_custom_edit_columns'));
            add_action('manage_' . self::POST_TYPE . '_posts_custom_column', array(&$this, 'custom_column'), 10, 2);

            add_action('pre_get_posts', array(&$this, 'alter_posts_query'));
        }

        /**
         * hook into WP's pre_get_posts
         */
        public function alter_posts_query($query)
        {
            $screen = get_current_screen();
            $user = wp_get_current_user();
            $user_club = esc_attr(get_the_author_meta('user_manages_club', $user->ID));
            if ($screen->id === 'edit-' . self::POST_TYPE && in_array('club_editor', $user->roles) && is_numeric($user_club)) {
                $meta_query = $query->get('meta_query');

                $meta_query[] = array(
                    'key' => 'hall_club_id',
                    'value' => $user_club,
                );

                $query->set('meta_query', $meta_query);
            }
            return $query;
        }

// END public function alter_posts_query()

        function set_custom_edit_columns($columns)
        {
            return array(
                'cb' => $columns['cb'],
                'title' => $columns['title'],
                'city' => __('City', WP_Kivi_Schedule_Plugin::textdomain),
                'club' => __('Club', WP_Kivi_Schedule_Plugin::textdomain),
                'date' => $columns['date']
            );
        }

        function custom_column($column, $post_id)
        {
            $meta = get_post_meta($post_id);
            switch ($column) {
                case 'city' :
                    echo get_the_title($meta['hall_city_id'][0]);
                    break;
                case 'club':
                    echo get_the_title($meta['hall_club_id'][0]);
                    break;
            }
        }
// END public function admin_init()

        /**
         * hook into WP's add_meta_boxes action hook
         */
        public function add_meta_boxes()
        {
            if (WP_Kivi_Schedule_Plugin::is_user_current_club_manager())
                return;
            // Add this metabox to every selected post
            add_meta_box(
                sprintf('wp_plugin_template_%s_section', self::POST_TYPE), __('Hall', WP_Kivi_Schedule_Plugin::textdomain), array(&$this, 'add_inner_meta_boxes'), self::POST_TYPE
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

    // END class Post_Type_Hall
} // END if(!class_exists('Post_Type_Hall'))
