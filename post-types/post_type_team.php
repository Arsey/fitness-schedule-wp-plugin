<?php

if (!class_exists('Post_Type_Team')) {

    /**
     * A PostTypeTeam class that provides 2 additional meta fields
     */
    class Post_Type_Team
    {

        const POST_TYPE = "kivi_schedule_team";
        const CAT_TAXONOMY = "kiwi_schedule_team_category";
        const TEAM_MEMBER_CLUB_ID_DELIMITER = '##';

        private $_meta = array(
            'team_club_id',
            'team_group',
            'team_description',
            'team_is_active',
            'team_is_methodist',
            'team_methodist_description',
            'team_facebook_link',
            'team_vk_link',
        );

        /**
         * The Constructor
         */
        public function __construct()
        {
            $this->team_group = array('Тренеры', 'Администраторы', 'Релакс-зона');
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
            register_post_type(
                self::POST_TYPE,
                array(
                    'labels' => array(
                        'name' => __('Teams', WP_Kivi_Schedule_Plugin::textdomain),
                        'singular_name' => __('Team', WP_Kivi_Schedule_Plugin::textdomain),
                        'add_new' => __('Add team', WP_Kivi_Schedule_Plugin::textdomain),
                        'view_item' => __('View', WP_Kivi_Schedule_Plugin::textdomain),
                        'search_items' => __('Find team', WP_Kivi_Schedule_Plugin::textdomain),
                        'add_new_item' => __('Add team', WP_Kivi_Schedule_Plugin::textdomain),
                        'edit_item' => __('Edit Team Member', WP_Kivi_Schedule_Plugin::textdomain),
                    ),
                    'public' => true,
                    'has_archive' => true,
                    'show_in_menu' => 'time_table',
                    'supports' => array(
                        'title', 'thumbnail', 'editor'
                    ),
                    'taxonomies' => array(self::CAT_TAXONOMY),
                )
            );

            $labels = array(
                'name' => __('Team Categories', WP_Kivi_Schedule_Plugin::textdomain),
                'singular_name' => __('Category', WP_Kivi_Schedule_Plugin::textdomain),
                'search_items' => __('Search Team Categories', WP_Kivi_Schedule_Plugin::textdomain),
                'popular_items' => __('Popular Team Categories', WP_Kivi_Schedule_Plugin::textdomain),
                'all_items' => __('All Team Categories', WP_Kivi_Schedule_Plugin::textdomain),
                'parent_item' => __('Parent Category', WP_Kivi_Schedule_Plugin::textdomain),
                'parent_item_colon' => __('Parent Category:', WP_Kivi_Schedule_Plugin::textdomain),
                'edit_item' => __('Edit Category', WP_Kivi_Schedule_Plugin::textdomain),
                'update_item' => __('Update Category', WP_Kivi_Schedule_Plugin::textdomain),
                'add_new_item' => __('Add New Category', WP_Kivi_Schedule_Plugin::textdomain),
                'new_item_name' => __('New Category Name', WP_Kivi_Schedule_Plugin::textdomain),
                'separate_items_with_commas' => __('Separate team categories with commas', WP_Kivi_Schedule_Plugin::textdomain),
                'add_or_remove_items' => __('Add or remove team categories', WP_Kivi_Schedule_Plugin::textdomain),
                'choose_from_most_used' => __('Choose from the most used team categories', WP_Kivi_Schedule_Plugin::textdomain),
                'menu_name' => __('Team Categories', WP_Kivi_Schedule_Plugin::textdomain),
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

            register_taxonomy('kiwi_schedule_team_category', array(self::POST_TYPE), $args);
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
            add_action('pre_get_posts', array(&$this, 'alter_posts_query'));
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
         * hook into WP's pre_get_posts
         */
        public function alter_posts_query($query)
        {
            $screen = get_current_screen();
            $user = wp_get_current_user();
            $user_club = esc_attr(get_the_author_meta('user_manages_club', $user->ID));
            if ($screen->id === 'edit-kivi_schedule_team' && in_array('club_editor', $user->roles) && is_numeric($user_club)) {
                $query->set('author', $user->ID);

            }
            return $query;
        }

// END public function alter_posts_query()

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

    // END class Post_Type_Team
} // END if(!class_exists('Post_Type_Team'))
