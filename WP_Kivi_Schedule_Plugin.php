<?php
/*
Plugin Name: WP_Kivi_Schedule_Plugin
Version: 1.0
Author: Arsey
Text Domain: scheduleplugin
*/

defined('ABSPATH') OR exit;
if (!class_exists('WP_Kivi_Schedule_Plugin')) {
    /* define the constants plugin_id and plugin folder */
    define('WP_Kivi_Schedule_Plugin_id', 'WP_Kivi_Schedule_Plugin'); // plugin id

    /* the main class */

    class WP_Kivi_Schedule_Plugin
    {

        const textdomain = 'scheduleplugin';

        public function __construct()
        {

            include_once 'functions.php';

            error_reporting(E_ALL);
            global $kivi_schedule_settings;
            $kivi_schedule_settings = require_once 'settings.php';

            require_once(sprintf("%s/post-types/post_type_city.php", dirname(__FILE__)));
            require_once(sprintf("%s/post-types/post_type_club.php", dirname(__FILE__)));
            require_once(sprintf("%s/post-types/post_type_program.php", dirname(__FILE__)));
            require_once(sprintf("%s/post-types/post_type_team.php", dirname(__FILE__)));
            require_once(sprintf("%s/post-types/post_type_hall.php", dirname(__FILE__)));

            $Post_Type_City = new Post_Type_City();
            $Post_Type_Club = new Post_Type_Club();
            $Post_Type_Program = new Post_Type_Program();
            $Post_Type_Team = new Post_Type_Team();
            $Post_Type_Hall = new Post_Type_Hall();

            /* Actions */
            add_action('init', array(&$this, 'function_init'));
            add_action('admin_enqueue_scripts', array(&$this, 'add_style_js'));
            add_action('admin_menu', array(&$this, 'add_menu'));


            /* ajax actions */
            add_action("wp_ajax_fetch_clubs_by_city", array(&$this, "fetch_clubs_by_city"));
            add_action("wp_ajax_fetch_hall_by_club", array(&$this, "fetch_hall_by_club"));
            add_action("wp_ajax_fetch_schedule_data", array(&$this, "fetch_schedule_data"));
            add_action("wp_ajax_save_schedule_data", array(&$this, "save_schedule_data"));
            add_action("wp_ajax_fetch_schedule_data", array(&$this, "fetch_schedule_data"));
            add_action("wp_ajax_remove_schedule", array(&$this, "remove_schedule"));

            /* ajax action that returns the list of cities */
            add_action("wp_ajax_nopriv_ksp_fetch_cities", array(&$this, "fetch_cities"));
            add_action("wp_ajax_ksp_fetch_cities", array(&$this, "ajax_fetch_cities"));

            load_plugin_textdomain(self::textdomain, false, dirname(plugin_basename(__FILE__)) . '/languages/');
        }

        function add_style_js()
        {
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui', plugins_url('/js/jquery-ui-1.11.2/jquery-ui.min.js', __FILE__), array('jquery'));
            wp_enqueue_script('date', plugins_url('/js/jquery-week-calendar-master/libs/date.js', __FILE__), array('jquery'));
            wp_enqueue_script('jquery-week-calendar-master', plugins_url('/js/jquery-week-calendar-master/jquery.weekcalendar.js', __FILE__), array('jquery', 'jquery-ui-core'));
            wp_enqueue_script('jonthornton-jquery-timepicker', plugins_url('/js/jonthornton-jquery-timepicker/jquery.timepicker.min.js', __FILE__), array('jquery'));
            wp_enqueue_script('tablesorter', plugins_url('/js/tablesorter/jquery.tablesorter.js', __FILE__), array('jquery'));
            //wp_enqueue_script('jkivi_schedule_main', plugins_url('/js/kivi_schedule_main.js', __FILE__), array('jquery'));
            //styles
            wp_enqueue_style('jonthornton-jquery-timepicker', plugins_url('/js/jonthornton-jquery-timepicker/jquery.timepicker.css', __FILE__));
            wp_enqueue_style('jquery-week-calendar-master', plugins_url('/js/jquery-week-calendar-master/jquery.weekcalendar.css', __FILE__));
            wp_enqueue_style('jkivi_schedule_main-style', plugins_url('/css/kivi_schedule_main.css', __FILE__));

            $img_path = array('template_url' => plugins_url('/img/', __FILE__));
            wp_register_script('imgicons-config', plugins_url('/js/kivi_schedule_main.js', __FILE__), array('jquery')); // Custom scripts
            wp_enqueue_script('imgicons-config');
            wp_localize_script('imgicons-config', 'img_path', $img_path);
        }

        function add_menu()
        {
            $capability = apply_filters('scheduleplugin_capability', 'edit_others_posts');

            //menu
            add_menu_page(
                __('Schedule', self::textdomain),
                __('Schedule', self::textdomain),
                $capability,
                'time_table',
                array(&$this, 'menu_kivi_schedule')
            );

            add_submenu_page(
                'time_table',
                __('Schedule', self::textdomain),
                __('Schedule', self::textdomain),
                $capability,
                'kivi_schedule_city',
                array(&$this, 'menu_kivi_schedule')
            );
        }

        function menu_kivi_schedule()
        {
            include_once 'view/schedule_page.php';
        }

        function menu_kivi_schedule_post_types()
        {
            include_once 'view/schedule_post_types.php';
        }

        function function_init()
        {
            if (isset($_REQUEST['wp_kivischedule_excel'])) {
                self::convert_data_to_excel();
                die();
            }
        }

        function special_nav_class($css_class = array(), $page = false)
        {
            if (get_post_type() == 'kivi_schedule_city') {
                if ($page->ID == get_option('page_for_posts')) {
                    foreach ($css_class as $k => $v) {
                        if ($v == 'current_page_parent')
                            unset($css_class[$k]);
                    }
                }
            }
            return $css_class;
        }

        static function fetch_cities()
        {
            $query = new WP_Query(array('post_type' => Post_Type_City::POST_TYPE, 'orderby' => 'title', 'order' => 'ASC'));
            $cities = array();

            if (count($query->posts)) {
                foreach ($query->posts as $post) {
                    $query->the_post();
                    $cities[] = array(
                        'id' => $post->ID,
                        'name' => $post->post_title
                    );
                }
            }
            wp_reset_query();

            return $cities;
        }

        function ajax_fetch_cities()
        {
            echo json_encode(self::fetch_cities());
            exit();
        }

        static function fetch_clubs_by_city($city_id = null)
        {
            /* get city_id from request if it is AJAX request */
            if (defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['kivischedule_city_id']))
                $city_id = $_REQUEST['kivischedule_city_id'];

            $clubs_array = array();

            $args = array(
                'post_type' => Post_Type_Club::POST_TYPE,
                'orderby' => 'menu_order',
                'order' => 'ASC'
            );
            /* add query by club_city_id meta key */
            if ($city_id) {
                $args['meta_query'] = array(
                    array(
                        'key' => 'club_city_id',
                        'value' => $city_id
                    )
                );

                if (is_array($city_id))
                    $args['meta_query'][0][0]['compare'] = 'IN';
            }

            $query = new WP_Query($args);

            if (count($query->posts)) {
                foreach ($query->posts as $post) {

                    $this_post_id = $post->ID;
                    $meta = get_post_meta($this_post_id);

                    $club = array(
                        'club_id' => $this_post_id,
                        'club_name' => $post->post_title,
                    );
                    foreach ($meta as $key => $arr) {
                        $club[$key] = $arr[0];
                    }
                    $clubs_array[] = $club;
                }
            }
            wp_reset_query();

            if (defined('DOING_AJAX') && DOING_AJAX)
                exit(json_encode($clubs_array));

            return $clubs_array;
        }

        static function fetch_club_schedule($club_id)
        {
            global $wpdb;
            global $kivi_schedule_settings;
            $halls = self::fetch_hall_by_club($club_id, 'menu_order', 'ASC');

            if ($halls) {
                $halls_ids = array();
                $halls_by_ids = array();
                foreach ($halls as $key => $hall) {
                    $halls_ids[] = $hall['hall_id'];
                    $halls_by_ids[$hall['hall_id']] = $key;
                }

                $schedule_for_all_halls = $wpdb->get_results('SELECT * FROM ' . $kivi_schedule_settings['kivi_schedule_table'] . ' WHERE hall_id IN(\'' . implode("','", $halls_ids) . '\')  ORDER BY TIME_TO_SEC(time) ASC', ARRAY_A);

                if ($schedule_for_all_halls) {
                    foreach ($schedule_for_all_halls as $hall_schedule) {
                        if (isset($halls_by_ids[$hall_schedule['hall_id']])) {
                            $k = $halls_by_ids[$hall_schedule['hall_id']];
                            $halls[$k]['schedule'][] = $hall_schedule;
                        }
                    }
                }

                return $halls;
            }

            return null;
        }

        static function fetch_hall_by_club($club_id = null, $orderby = 'title', $order = 'DESC')
        {
            /* get city_id from request if it is AJAX request */
            if (defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['kivischedule_club_id']))
                $club_id = $_REQUEST['kivischedule_club_id'];

            $halls_array = array();

            $args = array(
                'post_type' => Post_Type_Hall::POST_TYPE,
                'orderby' => $orderby,
                'order' => $order
            );
            /* add query by club_city_id meta key */
            if ($club_id) {
                $args['meta_query'] = array(
                    array(
                        'key' => 'hall_club_id',
                        'value' => $club_id
                    )
                );
            }

            $query = new WP_Query($args);
            if (count($query->posts)) {
                foreach ($query->posts as $post) {
                    $halls_array[] = array(
                        'hall_name' => $post->post_title,
                        'hall_id' => $post->ID
                    );
                }
            }

            if (defined('DOING_AJAX') && DOING_AJAX)
                exit(json_encode($halls_array));

            return $halls_array;
        }

        /**
         * return full information about programs including meta
         */
        static function fetch_programs($with_thumbnails = false, $thumbnail_size = 'thumbnail', $per_page = 100)
        {
            $programs = array();
            $query = new WP_Query(array(
                'post_type' => Post_Type_Program::POST_TYPE,
                'posts_per_page' => $per_page,
                'orderby' => 'title',
                'order' => 'ASC'
            ));

            if (count($query->posts)) {
                foreach ($query->posts as $post) {

                    $program = array(
                        'id' => $post->ID,
                        'title' => $post->post_title
                    );

                    $meta = get_post_meta($post->ID);
                    if ($meta) {
                        foreach ($meta as $key => $arr) {
                            $program[$key] = $arr[0];
                        }
                    }

                    if ($with_thumbnails)
                        $program['thumbnail'] = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), $thumbnail_size);

                    $programs[] = $program;
                }
            }
            wp_reset_query();

            return $programs;
        }

        static function fetch_programs_dictionary()
        {
            $programs = array();
            $query = new WP_Query(array('post_type' => Post_Type_Program::POST_TYPE, 'posts_per_page' => 1000));
            if (count($query->posts)) {
                foreach ($query as $post) {
                    $program = array(
                        'id' => $post->ID,
                        'title' => $post->post_title
                    );

                    $programs[$post->ID] = $program;
                }
            }

            $programs[0] = array(
                'id' => 0,
                'title' => ''
            );

            return $programs;
        }

        static function get_programs_in_categories()
        {
            global $wpdb;
            $results = $wpdb->get_results("SELECT p.ID as post_id,p.post_title,x.term_taxonomy_id,t.term_id,t.name AS term_name FROM {$wpdb->prefix}posts p
                LEFT OUTER JOIN wp_term_relationships r ON r.object_id = p.ID
                LEFT OUTER JOIN wp_term_taxonomy x ON x.term_taxonomy_id = r.term_taxonomy_id
                LEFT OUTER JOIN wp_terms t ON t.term_id = x.term_id
                WHERE p.post_status = 'publish'
                AND p.post_type = '" . Post_Type_Program::POST_TYPE . "'
                AND x.taxonomy = '" . Post_Type_Program::CAT_TAXONOMY . "' LIMIT 0,1000", ARRAY_A);


            $taxonomies = array();
            if ($results) {

                $postsIds = array();
                foreach ($results as $r) {
                    $postsIds[] = $r['post_id'];
                }

                $sql = "SELECT p.ID as post_id,p.post_title FROM {$wpdb->prefix}posts p WHERE p.ID NOT IN('" . implode("','", $postsIds) . "') AND p.post_status = 'publish' AND p.post_type = '" . Post_Type_Program::POST_TYPE . "'";
                $posts_not_in_categories = $wpdb->get_results($sql, ARRAY_A);

                foreach ($results as $r) {
                    if (!isset($taxonomies[$r['term_id']]))
                        $taxonomies[$r['term_id']] = array(
                            'term_id' => $r['term_id'],
                            'name' => $r['term_name'],
                            'programs' => array()
                        );

                    $program = array('ID' => $r['post_id'], 'post_title' => $r['post_title']);

                    $taxonomies[$r['term_id']]['programs'][] = $program;
                }

                if ($posts_not_in_categories) {
                    $taxonomies[0] = array(
                        'term_id' => 0,
                        'name' => __('Not in category', self::textdomain),
                        'programs' => array()
                    );
                    foreach ($posts_not_in_categories as $p) {
                        $program = array('ID' => $p['post_id'], 'post_title' => $p['post_title']);
                        $taxonomies[0]['programs'][] = $program;
                    }
                }
            }

            return $taxonomies;
        }

        static function create_select_program($selected_value, $data, $select_class)
        {
            $select = '<select class="schedule_program_select ' . $select_class . '"><option value="0"></option>';
            foreach ($data as $value) {
                if ($selected_value == $value['id']) {
                    $select .= '<option value = "' . $value['id'] . '" selected>' . $value['title'] . '</option>';
                } else {
                    $select .= '<option value = "' . $value['id'] . '">' . $value['title'] . '</option>';
                }
            }
            $select .= '</select>';
            return $select;
        }

        static function get_methodists($per_page = 100, $with_thumbnails = false, $thumbnail_size = 'full')
        {
            $methodists = array();

            $query = new WP_Query(array(
                'post_type' => Post_Type_Team::POST_TYPE,
                'posts_per_page' => $per_page,
                'orderby' => 'title',
                'order' => 'ASC',
                'meta_query' => array(
                    array(
                        'key' => 'team_is_methodist',
                        'value' => 'on'
                    )
                )
            ));
            if (count($query->posts)) {
                foreach ($query->posts as $post) {
                    $methodist = array(
                        'id' => $post->ID,
                        'title' => $post->post_title
                    );

                    $meta = get_post_meta($post->ID);
                    if ($meta) {
                        foreach ($meta as $key => $arr)
                            $methodist[$key] = $arr[0];
                    }

                    if ($with_thumbnails)
                        $methodist['thumbnail'] = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), $thumbnail_size);

                    $methodists[] = $methodist;
                }
            }
            wp_reset_query();

            return $methodists;
        }

        static function get_team_in_categories($club_id = null)
        {
            global $wpdb;

            $club_where_sql = '';
            if ($club_id) {
                $club_where_sql = " AND p.ID IN(SELECT post_id FROM `{$wpdb->prefix}postmeta` WHERE meta_key='team_club_id'
AND meta_value LIKE '%" . Post_Type_Team::TEAM_MEMBER_CLUB_ID_DELIMITER . $club_id . Post_Type_Team::TEAM_MEMBER_CLUB_ID_DELIMITER . "%')";
            }

            $results = $wpdb->get_results("SELECT p.ID as post_id,p.post_title,x.term_taxonomy_id,t.term_id,t.name AS term_name FROM {$wpdb->prefix}posts p
                LEFT OUTER JOIN wp_term_relationships r ON r.object_id = p.ID
                LEFT OUTER JOIN wp_term_taxonomy x ON x.term_taxonomy_id = r.term_taxonomy_id
                LEFT OUTER JOIN wp_terms t ON t.term_id = x.term_id
                WHERE p.post_status = 'publish'
                AND p.post_type = '" . Post_Type_Team::POST_TYPE . "'
                AND x.taxonomy = '" . Post_Type_Team::CAT_TAXONOMY . "'" . $club_where_sql, ARRAY_A);


            $taxonomies = array();
            if ($results) {
                foreach ($results as $r) {
                    if (!isset($taxonomies[$r['term_id']]))
                        $taxonomies[$r['term_id']] = array(
                            'term_id' => $r['term_id'],
                            'name' => $r['term_name'],
                            'programs' => array()
                        );

                    $team_member = array('ID' => $r['post_id'], 'post_title' => $r['post_title'], 'permalink' => get_the_permalink($r['post_id']));

                    $taxonomies[$r['term_id']]['team'][] = $team_member;
                }
            }

            return $taxonomies;
        }

        static function get_team()
        {
            $query = new WP_Query(array(
                'post_type' => Post_Type_Team::POST_TYPE,
                'posts_per_page' => 1000,
                'orderby' => 'title',
                'order' => 'ASC',
            ));

            $team = array();
            if (count($query->posts)) {
                foreach ($query->posts as $post) {
                    $team[] = array(
                        'ID' => $post->ID,
                        'post_title' => $post->post_title
                    );
                }
            }

            wp_reset_query();

            return $team;
        }

        static function checkbox_status($value)
        {
            if (is_null($value) || $value == 0) {
                return '';
            } else {
                return 'checked';
            }
        }

        static function fetch_schedule_data()
        {
            global $wpdb;
            global $kivi_schedule_settings;
            if (defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['kivischedule_city_id'])) {

            }
            $schedule_header = '';

            $programs = self::fetch_programs(false, 'thumbnail', 1000);

            $schedule_header .= '<thead><tr> <th class="headerSortUp">' . __('Time', self::textdomain) . '</th>';
            $schedule_header .= '<th>' . __('Monday', self::textdomain) . '</th>';
            $schedule_header .= '<th>' . __('Tuesday', self::textdomain) . '</th>';
            $schedule_header .= '<th>' . __('Wednesday', self::textdomain) . '</th>';
            $schedule_header .= '<th>' . __('Thursday', self::textdomain) . '</th>';
            $schedule_header .= '<th>' . __('Friday', self::textdomain) . '</th>';
            $schedule_header .= '<th>' . __('Saturday', self::textdomain) . '</th>';
            $schedule_header .= '<th>' . __('Sunday', self::textdomain) . '</th>';
            $schedule_header .= '<th>' . __('Delete', self::textdomain) . '</th></tr></thead>';

            $Cities = new WP_Query(array('post_type' => Post_Type_City::POST_TYPE));
            $Cities_posts = $Cities->get_posts();

            $is_current_user_club_manager = WP_Kivi_Schedule_Plugin::is_user_current_club_manager();


            $clubs_query_args = array(
                'post_type' => Post_Type_Club::POST_TYPE,
            );
            if ($is_current_user_club_manager) {
                $clubs_query_args['p'] = $is_current_user_club_manager['user_club'];
            }
            $Clubs = new WP_Query($clubs_query_args);
            $Clubs_posts = $Clubs->get_posts();


            $halls_query_args = array('post_type' => Post_Type_Hall::POST_TYPE);
            if ($is_current_user_club_manager) {
                $halls_query_args['meta_query'] = array(
                    array(
                        'key' => 'hall_club_id',
                        'value' => $is_current_user_club_manager['user_club']
                    )
                );
            }
            $Halls = new WP_Query($halls_query_args);
            $Halls_posts = $Halls->get_posts();


            $Schedule_data = $wpdb->get_results('SELECT * FROM ' . $kivi_schedule_settings['kivi_schedule_table'] . ' ORDER BY time', ARRAY_A);
            $Citie_content_block = '';

            foreach ($Cities_posts as $city) {
                $city_id = $city->ID;

                $Citie_content_block .= '<section class="schedule-city" data-city-id="' . $city_id . '"><h2 class="schedule-cities">' . __('City', self::textdomain) . ' ' . $city->post_title . '</h2>';

                foreach ($Clubs_posts as $club) {
                    $club_id = $club->ID;
                    $club_city_id = get_post_meta($club_id, 'club_city_id');
                    if ($club_city_id[0] == $city_id) {

                        $Citie_content_block .= '<article class="schedule-club-name" data-club-id ="' . $club_id . '"><h3 class="schedule-clubs-in-city"> ' . __('Club', self::textdomain) . ' ' . $club->post_title . '</h3>';

                        foreach ($Halls_posts as $hall) {
                            $hall_club_id = get_post_meta($hall->ID, 'hall_club_id');
                            $hall_id = $hall->ID;
                            if ($club_id == $hall_club_id[0]) {
                                $Citie_content_block .= '<div class="hall-schedule" data-hall-id="' . $hall_id . '">';
                                $Citie_content_block .= '<h4 class="schedule-halls-in-clubs"><span>' . $hall->post_title . '</span><a href="javascript:void(0)" class="add-new-schedule-row"> ' . __('Add New Schedule', self::textdomain) . '</a></h4>';
                                $Citie_content_block .= '<table class="schedule-table">';
                                $Citie_content_block .= $schedule_header;

                                // Schedule template
                                $Citie_content_block .= '<tbody>';
                                $Citie_content_block .= '<tr class="schedule-template" data-schedule-id = "" data-hall-id="' . $hall_id . '" >';
                                $Citie_content_block .= '<td><input type="text"name="sched_time" class="timePicker" value="" /></td>';
                                $Citie_content_block .= '<td>' . self::create_select_program(0, $programs, 'sched_1') . '<input type="checkbox" class="program-status monday_program_status" /></td>';
                                $Citie_content_block .= '<td>' . self::create_select_program(0, $programs, 'sched_2') . '<input type="checkbox" class="program-status tuesday_program_status" /></td>';
                                $Citie_content_block .= '<td>' . self::create_select_program(0, $programs, 'sched_3') . '<input type="checkbox" class="program-status wednesday_program_status" /></td>';
                                $Citie_content_block .= '<td>' . self::create_select_program(0, $programs, 'sched_4') . '<input type="checkbox" class="program-status thursda_program_status" /></td>';
                                $Citie_content_block .= '<td>' . self::create_select_program(0, $programs, 'sched_5') . '<input type="checkbox" class="program-status friday_program_status" /></td>';
                                $Citie_content_block .= '<td>' . self::create_select_program(0, $programs, 'sched_6') . '<input type="checkbox" class="program-status saturday_program_status" /></td>';
                                $Citie_content_block .= '<td>' . self::create_select_program(0, $programs, 'sched_7') . '<input type="checkbox" class="program-status sunday_program_status" /></td>';
                                $Citie_content_block .= '<td><a href="javascript:void(0)" class="delete-schedule-row"></a></td></tr>';

                                //Schedule rendering

                                foreach ($Schedule_data as $value) {
                                    if ($value['hall_id'] == $hall_id) {
                                        $Citie_content_block .= '<tr data-schedule-id = "' . $value['id'] . '" >';
                                        $Citie_content_block .= '<td><input type="text"name="sched_time" class="timePicker" value="' . $value['time'] . '" /></td>';
                                        $Citie_content_block .= '<td>' . self::create_select_program($value['monday_program_id'], $programs, 'sched_1') . '<input type="checkbox" class="program-status monday_program_status" ' . self::checkbox_status($value['monday_program_status']) . '></td>';
                                        $Citie_content_block .= '<td>' . self::create_select_program($value['tuesday_program_id'], $programs, 'sched_2') . '<input type="checkbox" class="program-status tuesday_program_status"' . self::checkbox_status($value['tuesday_program_status']) . '></td>';
                                        $Citie_content_block .= '<td>' . self::create_select_program($value['wednesday_program_id'], $programs, 'sched_3') . '<input type="checkbox" class="program-status wednesday_program_status"' . self::checkbox_status($value['wednesday_program_status']) . '></td>';
                                        $Citie_content_block .= '<td>' . self::create_select_program($value['thursday_program_id'], $programs, 'sched_4') . '<input type="checkbox" class="program-status thursday_program_status"' . self::checkbox_status($value['thursday_program_status']) . '></td>';
                                        $Citie_content_block .= '<td>' . self::create_select_program($value['friday_program_id'], $programs, 'sched_5') . '<input type="checkbox" class="program-status friday_program_status"' . self::checkbox_status($value['friday_program_status']) . '></td>';
                                        $Citie_content_block .= '<td>' . self::create_select_program($value['saturday_program_id'], $programs, 'sched_6') . '<input type="checkbox" class="program-status saturday_program_status"' . self::checkbox_status($value['saturday_program_status']) . '></td>';
                                        $Citie_content_block .= '<td>' . self::create_select_program($value['sunday_program_id'], $programs, 'sched_7') . '<input type="checkbox" class="program-status sunday_program_status"' . self::checkbox_status($value['sunday_program_status']) . '></td>';
                                        $Citie_content_block .= '<td><a href="javascript:void(0)" class="delete-schedule-row"></a></td></tr>';
                                    }
                                }


                                $Citie_content_block .= '</tbody>';
                                $Citie_content_block .= '</table>';
                                $Citie_content_block .= '</div>';
                            }
                        }
                        $Citie_content_block .= '</article>';
                    }
                }
                $Citie_content_block .= '</section>';
            }
            $programs[0] = "";

            echo $Citie_content_block;
            // }
            // die();
        }

        function save_schedule_data()
        {
            global $wpdb;
            global $kivi_schedule_settings;

            $schedule_id = $_REQUEST['schedule_id'];
            $time = $_REQUEST['kivischedule_time'];
            isset($_REQUEST['kivischedule_hall_id']) ? $hall_id = $_REQUEST['kivischedule_hall_id'] : $hall_id = 0;
            isset($_REQUEST['kivischedule_sched_1']) ? $sched_1 = $_REQUEST['kivischedule_sched_1'] : $sched_1 = 0;
            isset($_REQUEST['kivischedule_sched_2']) ? $sched_2 = $_REQUEST['kivischedule_sched_2'] : $sched_2 = 0;
            isset($_REQUEST['kivischedule_sched_3']) ? $sched_3 = $_REQUEST['kivischedule_sched_3'] : $sched_3 = 0;
            isset($_REQUEST['kivischedule_sched_4']) ? $sched_4 = $_REQUEST['kivischedule_sched_4'] : $sched_4 = 0;
            isset($_REQUEST['kivischedule_sched_5']) ? $sched_5 = $_REQUEST['kivischedule_sched_5'] : $sched_5 = 0;
            isset($_REQUEST['kivischedule_sched_6']) ? $sched_6 = $_REQUEST['kivischedule_sched_6'] : $sched_6 = 0;
            isset($_REQUEST['kivischedule_sched_7']) ? $sched_7 = $_REQUEST['kivischedule_sched_7'] : $sched_7 = 0;
            isset($_REQUEST['kivischedule_sched_status_1']) ? $status_1 = $_REQUEST['kivischedule_sched_status_1'] : $status_1 = 0;
            isset($_REQUEST['kivischedule_sched_status_2']) ? $status_2 = $_REQUEST['kivischedule_sched_status_2'] : $status_2 = 0;
            isset($_REQUEST['kivischedule_sched_status_3']) ? $status_3 = $_REQUEST['kivischedule_sched_status_3'] : $status_3 = 0;
            isset($_REQUEST['kivischedule_sched_status_4']) ? $status_4 = $_REQUEST['kivischedule_sched_status_4'] : $status_4 = 0;
            isset($_REQUEST['kivischedule_sched_status_5']) ? $status_5 = $_REQUEST['kivischedule_sched_status_5'] : $status_5 = 0;
            isset($_REQUEST['kivischedule_sched_status_6']) ? $status_6 = $_REQUEST['kivischedule_sched_status_6'] : $status_6 = 0;
            isset($_REQUEST['kivischedule_sched_status_7']) ? $status_7 = $_REQUEST['kivischedule_sched_status_7'] : $status_7 = 0;

            if ($schedule_id != "") {
                $wpdb->update($kivi_schedule_settings['kivi_schedule_table'], array(
                    'time' => $time,
                    'monday_program_id' => $sched_1,
                    'monday_program_status' => $status_1,
                    'tuesday_program_id' => $sched_2,
                    'tuesday_program_status' => $status_2,
                    'wednesday_program_id' => $sched_3,
                    'wednesday_program_status' => $status_3,
                    'thursday_program_id' => $sched_4,
                    'thursday_program_status' => $status_4,
                    'friday_program_id' => $sched_5,
                    'friday_program_status' => $status_5,
                    'saturday_program_id' => $sched_6,
                    'saturday_program_status' => $status_6,
                    'sunday_program_id' => $sched_7,
                    'sunday_program_status' => $status_7
                ), array('id' => $schedule_id), array('%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d')
                );
                echo $schedule_id;
            } else {
                $wpdb->insert($kivi_schedule_settings['kivi_schedule_table'], array(
                    'time' => $time,
                    'hall_id' => $hall_id,
                    'monday_program_id' => $sched_1,
                    'monday_program_status' => $status_1,
                    'tuesday_program_id' => $sched_2,
                    'tuesday_program_status' => $status_2,
                    'wednesday_program_id' => $sched_3,
                    'wednesday_program_status' => $status_3,
                    'thursday_program_id' => $sched_4,
                    'thursday_program_status' => $status_4,
                    'friday_program_id' => $sched_5,
                    'friday_program_status' => $status_5,
                    'saturday_program_id' => $sched_6,
                    'saturday_program_status' => $status_6,
                    'sunday_program_id' => $sched_7,
                    'sunday_program_status' => $status_7
                ), array('%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d')
                );

                echo $wpdb->insert_id;
            }

            die();
        }

        function remove_schedule()
        {
            global $wpdb;
            global $kivi_schedule_settings;
            if (isset($_REQUEST['schedule_id'])) {
                $schedule_id = $_REQUEST['schedule_id'];

                $wpdb->query(
                    $wpdb->prepare(
                        "DELETE FROM " . $kivi_schedule_settings['kivi_schedule_table'] . " WHERE id = %d", $schedule_id)
                );
            }
        }

        static function is_user_current_club_manager()
        {
            $user = wp_get_current_user();
            $user_club = esc_attr(get_the_author_meta('user_manages_club', $user->ID));
            if (in_array('club_editor', $user->roles) && is_numeric($user_club))
                return array('user' => $user, 'user_club' => $user_club);
            return false;
        }

        static function convert_data_to_excel()
        {
            global $wpdb;
            global $kivi_schedule_settings;

            include_once 'PHPExcel/Classes/PHPExcel.php';
            include_once 'PHPExcel/Classes/PHPExcel/Writer/Excel5.php';

            if (isset($_REQUEST['kivi_schedule_cities_filter']) && ($_REQUEST['kivi_schedule_cities_filter'] != "")) {
                $city_id = trim($_REQUEST['kivi_schedule_cities_filter']);
            } else {
                $city_id = null;
            }
            if (isset($_REQUEST['kivi_schedule_clubs_filter']) && ($_REQUEST['kivi_schedule_clubs_filter'] != "")) {
                $club_id = trim($_REQUEST['kivi_schedule_clubs_filter']);
            } else {
                $club_id = null;
            }
            if (isset($_REQUEST['kivi_schedule_halls_filter']) && ($_REQUEST['kivi_schedule_halls_filter'] != "")) {
                $hall_id = trim($_REQUEST['kivi_schedule_halls_filter']);
            } else {
                $hall_id = null;
            }

            $clubs = self:: fetch_clubs_by_city($city_id);
            $schedule_data = $wpdb->get_results('SELECT * FROM ' . $kivi_schedule_settings['kivi_schedule_table'] . ' ORDER BY time', ARRAY_A);
            $programs = self:: fetch_programs_dictionary();

            $xls = new PHPExcel();
            $new_clubs = array();
            if ($city_id != null) {
                if ($club_id != null) {
                    $club_id_to_fetch = $club_id;
                    for ($club_index = 0; $club_index < count($clubs); $club_index++) {
                        $club = $clubs[$club_index];
                        if ($club['club_id'] == $club_id) {
                            $new_clubs[] = array(
                                'club_id' => $club['club_id'],
                                'club_name' => $club['club_name']
                            );
                        }
                    }
                    $clubs = $new_clubs;
                }
            }
            $new_halls = array();
            for ($club_index = 0; $club_index < count($clubs); $club_index++) {
                $club = $clubs[$club_index];
                $row_index = 1;

                $xls->createSheet(NULL, $club_index);
                $xls->setActiveSheetIndex($club_index);

                $sheet = $xls->getActiveSheet();
                $sheet->setTitle($club['club_name']);
                $sheet->mergeCells('A' . $row_index . ':I' . $row_index);
                $sheet->setCellValue("A" . $row_index, $club['club_name']);
                $sheet->getRowDimension($row_index)->setRowHeight(20);

                $sheet->getStyle('A' . $row_index)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                $sheet->getStyle('A' . $row_index)->getFill()->getStartColor()->setRGB('EEEEEE');
                $sheet->getStyle('A' . $row_index)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('A' . $row_index)->getFont()->setBold(true);
                $sheet->getStyle('A' . $row_index)->getFont()->setSize(14);

                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('C')->setAutoSize(true);
                $sheet->getColumnDimension('D')->setAutoSize(true);
                $sheet->getColumnDimension('E')->setAutoSize(true);
                $sheet->getColumnDimension('F')->setAutoSize(true);
                $sheet->getColumnDimension('G')->setAutoSize(true);
                $sheet->getColumnDimension('H')->setAutoSize(true);
                $sheet->getColumnDimension('I')->setAutoSize(true);

                $row_index++;
                if ($city_id != null) {
                    if ($club_id != null) {
                        $club_id_to_fetch = $club_id;
                    } else {
                        $club_id_to_fetch = $club['club_id'];
                    }
                } else {
                    $club_id_to_fetch = $club['club_id'];
                }
                $halls = self::fetch_hall_by_club($club_id_to_fetch, 'menu_order', 'ASC');

                if ($hall_id != null) {
                    for ($hall_index = 0; $hall_index < count($halls); $hall_index++) {
                        $hall = $halls[$hall_index];
                        if ($hall['hall_id'] == $hall_id) {
                            $new_halls[] = array(
                                'hall_id' => $hall['hall_id'],
                                'hall_name' => $hall['hall_name']
                            );
                        }
                    }
                    $halls = $new_halls;
                }

                for ($hall_index = 0; $hall_index < count($halls); $hall_index++) {
                    $hall = $halls[$hall_index];

                    $sheet->mergeCells('A' . $row_index . ':I' . $row_index);
                    $sheet->setCellValue("A" . $row_index, $hall['hall_name']);

                    $sheet->getStyle('A' . $row_index)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $sheet->getStyle('A' . $row_index)->getFill()->getStartColor()->setRGB('FAD646');
                    $sheet->getStyle('A' . $row_index)->getFont()->setBold(true);
                    $sheet->getStyle('A' . $row_index)->getFont()->setSize(12);
                    $sheet->getStyle('A' . $row_index)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                    $row_index++;

                    $sheet->getStyle('A' . $row_index . ':I' . $row_index)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle('A' . $row_index . ':I' . $row_index)->getFont()->setBold(true);

                    $sheet->setCellValue("A" . $row_index, '#');
                    $sheet->setCellValue("B" . $row_index, __('Time', self::textdomain));
                    $sheet->setCellValue("C" . $row_index, __('Monday', self::textdomain));
                    $sheet->setCellValue("D" . $row_index, __('Tuesday', self::textdomain));
                    $sheet->setCellValue("E" . $row_index, __('Wednesday', self::textdomain));
                    $sheet->setCellValue("F" . $row_index, __('Thursday', self::textdomain));
                    $sheet->setCellValue("G" . $row_index, __('Friday', self::textdomain));
                    $sheet->setCellValue("H" . $row_index, __('Saturday', self::textdomain));
                    $sheet->setCellValue("I" . $row_index, __('Sunday', self::textdomain));

                    $row_index++;

                    $schedule_counter = 1;

                    foreach ($schedule_data as $value) {
                        if ($value['hall_id'] == $hall['hall_id']) {
                            $sheet->getStyle('A' . $row_index)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);

                            $sheet->setCellValue("A" . $row_index, $schedule_counter);
                            $sheet->setCellValue("B" . $row_index, $value['time']);
                            $sheet->setCellValue("C" . $row_index, $programs[$value['monday_program_id']]['title']);
                            $sheet->setCellValue("D" . $row_index, $programs[$value['tuesday_program_id']]['title']);
                            $sheet->setCellValue("E" . $row_index, $programs[$value['wednesday_program_id']]['title']);
                            $sheet->setCellValue("F" . $row_index, $programs[$value['thursday_program_id']]['title']);
                            $sheet->setCellValue("G" . $row_index, $programs[$value['friday_program_id']]['title']);
                            $sheet->setCellValue("H" . $row_index, $programs[$value['saturday_program_id']]['title']);
                            $sheet->setCellValue("I" . $row_index, $programs[$value['sunday_program_id']]['title']);

                            $schedule_counter++;

                            $row_index++;
                        }
                    }

                    $row_index++;
                }
            }

            header("Content-type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=kivi_schedule.xls");

            $objWriter = new PHPExcel_Writer_Excel5($xls, "Excel5");
            $objWriter->save('php://output');
        }

        /**
         * Plugin activation function
         */
        public static function activate()
        {
            global $kivi_schedule_settings;
            $path_to_installiation_file = $kivi_schedule_settings['path_to_kivi_schedule_folder'] . '/install.php';
            include_once($path_to_installiation_file);
            kivi_schedule_install();
        }

    }

    //End main class
    //
    //wordpress hooks
    register_activation_hook(__FILE__, array('WP_Kivi_Schedule_Plugin', 'activate'));
    $WP_Kivi_Schedule_Plugin = new WP_Kivi_Schedule_Plugin();
}
// registered hooks
register_activation_hook(__FILE__, array('WP_Kivi_Schedule_Plugin', 'activate'));
register_uninstall_hook(__DIR__ . '/uninstall.php', 'drop_kivi_schedule_tables');