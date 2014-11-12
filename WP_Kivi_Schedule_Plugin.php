<?php

//exit;
// kivi_schedule -for tables;
defined('ABSPATH') OR exit;
/**
 * Plugin Name: WP_Kivi_Schedule_Plugin
 * Plugin URI:
 */
if (!class_exists('WP_Kivi_Schedule_Plugin')) {
    /* define the constants plugin_id and plugin folder */
    define('WP_Kivi_Schedule_Plugin_id', 'WP_Kivi_Schedule_Plugin'); // plugin id

    /* the main class */

    class WP_Kivi_Schedule_Plugin {

        public function __construct() {

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
        }

        function add_style_js() {
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

        function add_menu() {
            //menu

            add_menu_page(__('Schedule'), __('Schedule'), 'switch_themes', 'time_table', array(&$this, 'menu_kivi_schedule'));
            add_submenu_page('time_table', __('Schedule'), __('Schedule'), 'manage_options', 'kivi_schedule_city', array(&$this, 'menu_kivi_schedule'));
        }

        function menu_kivi_schedule() {
            include_once 'view/schedule_page.php';
        }

        function menu_kivi_schedule_post_types() {
            include_once 'view/schedule_post_types.php';
        }

        function function_init() {
            
        }

        function special_nav_class($css_class = array(), $page = false) {
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

        static function fetch_cities() {
            $query = new WP_Query(array('post_type' => Post_Type_City::POST_TYPE, 'orderby' => 'title', 'order' => 'ASC'));
            $cities = array();
            while ($query->have_posts()) {
                $query->the_post();
                $cities[] = array(
                    'id' => get_the_ID(),
                    'name' => get_the_title()
                );
            }
            return $cities;
        }

        function ajax_fetch_cities() {
            echo json_encode(self::fetch_cities());
            exit();
        }

        static function fetch_clubs_by_city($city_id = null) {
            /* get city_id from request if it is AJAX request */
            if (defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['kivischedule_city_id']))
                $city_id = $_REQUEST['kivischedule_city_id'];

            $clubs_array = array();

            $args = array('post_type' => Post_Type_Club::POST_TYPE);
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
            while ($query->have_posts()) {
                $query->the_post();

                $this_post_id = get_the_id();
                $meta = get_post_meta($this_post_id);

                $club = array(
                    'club_id' => $this_post_id,
                    'club_name' => get_the_title(),
                );
                foreach ($meta as $key => $arr) {
                    $club[$key] = $arr[0];
                }
                $clubs_array[] = $club;
            }

            if (defined('DOING_AJAX') && DOING_AJAX)
                exit(json_encode($clubs_array));

            return $clubs_array;
        }

        static function fetch_club_schedule($club_id) {
            global $wpdb;
            global $kivi_schedule_settings;
            $halls = self::fetch_hall_by_club($club_id);

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

        static function fetch_hall_by_club($club_id = null) {
            /* get city_id from request if it is AJAX request */
            if (defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['kivischedule_club_id']))
                $club_id = $_REQUEST['kivischedule_club_id'];

            $halls_array = array();

            $args = array('post_type' => Post_Type_Hall::POST_TYPE);
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
            while ($query->have_posts()) {
                $query->the_post();
                $halls_array[] = array(
                    'hall_name' => get_the_title(),
                    'hall_id' => get_the_id()
                );
            }

            if (defined('DOING_AJAX') && DOING_AJAX)
                exit(json_encode($halls_array));

            return $halls_array;
        }

        /**
         * return full information about programs including meta
         */
        static function fetch_programs($with_thumbnails = false, $thumbnail_size = 'thumbnail') {
            $programs = array();
            $query = new WP_Query(array('post_type' => Post_Type_Program::POST_TYPE));
            while ($query->have_posts()) {
                $query->the_post();

                $program = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title()
                );

                $meta = get_post_meta(get_the_ID());
                if ($meta) {
                    foreach ($meta as $key => $arr) {
                        $program[$key] = $arr[0];
                    }
                }

                if ($with_thumbnails)
                    $program['thumbnail'] = wp_get_attachment_image_src(get_post_thumbnail_id(get_the_ID()), $thumbnail_size);

                $programs[] = $program;
            }

            return $programs;
        }

        static function get_programs_in_categories() {
            global $wpdb;
            $results = $wpdb->get_results("SELECT p.ID as post_id,p.post_title,x.term_taxonomy_id,t.term_id,t.name AS term_name FROM wp_posts p
                LEFT OUTER JOIN wp_term_relationships r ON r.object_id = p.ID
                LEFT OUTER JOIN wp_term_taxonomy x ON x.term_taxonomy_id = r.term_taxonomy_id
                LEFT OUTER JOIN wp_terms t ON t.term_id = x.term_id
                WHERE p.post_status = 'publish'
                AND p.post_type = '" . Post_Type_Program::POST_TYPE . "'
                AND x.taxonomy = '" . Post_Type_Program::CAT_TAXONOMY . "'", ARRAY_A);

            $taxonomies = array();
            if ($results) {
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
            }

            return $taxonomies;
        }

        static function create_select_program($selected_value, $data, $select_class) {
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

        static function get_team_in_categories() {
            global $wpdb;
            $results = $wpdb->get_results("SELECT p.ID as post_id,p.post_title,x.term_taxonomy_id,t.term_id,t.name AS term_name FROM wp_posts p
                LEFT OUTER JOIN wp_term_relationships r ON r.object_id = p.ID
                LEFT OUTER JOIN wp_term_taxonomy x ON x.term_taxonomy_id = r.term_taxonomy_id
                LEFT OUTER JOIN wp_terms t ON t.term_id = x.term_id
                WHERE p.post_status = 'publish'
                AND p.post_type = '" . Post_Type_Program::POST_TYPE . "'
                AND x.taxonomy = '" . Post_Type_Program::CAT_TAXONOMY . "'", ARRAY_A);

            $taxonomies = array();
            if ($results) {
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
            }

            return $taxonomies;
        }

        static function checkbox_status($value) {
            if (is_null($value) || $value == 0) {
                return '';
            } else {
                return 'checked';
            }
        }

        static function fetch_schedule_data() {
            global $wpdb;
            global $kivi_schedule_settings;
            if (defined('DOING_AJAX') && DOING_AJAX && isset($_REQUEST['kivischedule_city_id'])) {
                
            }
            $schedule_header = '';

            $programs = self::fetch_programs();

            $schedule_header .= '<thead><tr> <th>' . __('Time') . '</th>';
            $schedule_header .= '<th>' . __('Monday') . '</th>';
            $schedule_header .= '<th>' . __('Tuesday') . '</th>';
            $schedule_header .= '<th>' . __('Wednesday') . '</th>';
            $schedule_header .= '<th>' . __('Thursday') . '</th>';
            $schedule_header .= '<th>' . __('Friday') . '</th>';
            $schedule_header .= '<th>' . __('Saturday') . '</th>';
            $schedule_header .= '<th>' . __('Sunday') . '</th>';
            $schedule_header .= '<th>' . __('Delete') . '</th></tr></thead>';

            $Cities = new WP_Query(array('post_type' => Post_Type_City::POST_TYPE));
            $Cities_posts = $Cities->get_posts();
            $Clubs = new WP_Query(array('post_type' => Post_Type_Club::POST_TYPE));
            $Clubs_posts = $Clubs->get_posts();
            $Halls = new WP_Query(array('post_type' => Post_Type_Hall::POST_TYPE));
            $Halls_posts = $Halls->get_posts();
            $Schedule_data = $wpdb->get_results('SELECT * FROM ' . $kivi_schedule_settings['kivi_schedule_table'] . ' ORDER BY time', ARRAY_A);
            $Citie_content_block = '';

            foreach ($Cities_posts as $city) {
                $city_id = $city->ID;
                $Citie_content_block .= '<section class="schedule-city" data-city-id="' . $city_id . '"><h2 class="schedule-cities">' . _x('City', 'kiwi_schedule_cities') . ' ' . $city->post_title . '</h2>';
                foreach ($Clubs_posts as $club) {
                    $club_id = $club->ID;
                    $club_city_id = get_post_meta($club_id, 'club_city_id');
                    if ($club_city_id[0] == $city_id) {
                        $Citie_content_block .= '<article class="schedule-club-name" data-club-id ="' . $club_id . '"><h3 class="schedule-clubs-in-city"> ' . _x('Club', 'kiwi_schedule_clubs') . ' ' . $club->post_title . '</h3>';
                        foreach ($Halls_posts as $hall) {
                            $hall_club_id = get_post_meta($hall->ID, 'hall_club_id');
                            $hall_id = $hall->ID;
                            if ($club_id == $hall_club_id[0]) {
                                $Citie_content_block .= '<div class="hall-schedule" data-hall-id="' . $hall_id . '">';
                                $Citie_content_block .= '<h4 class="schedule-halls-in-clubs"><span>' . $hall->post_title . '</span><a href="javascript:void(0)" class="add-new-schedule-row"> ' . __('Add New Schedule') . '</a></h4>';
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

        function save_schedule_data() {
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

        function remove_schedule() {
            global $wpdb;
            global $kivi_schedule_settings;
            if (isset($_REQUEST['schedule_id'])) {
                $schedule_id = $_REQUEST['schedule_id'];
                
                $wpdb->query(
                        $wpdb->prepare(
                                "
        DELETE FROM " . $kivi_schedule_settings['kivi_schedule_table'] .
                                " WHERE id = %d", $schedule_id)
                );
            }
        }

        /**
         * Plugin activation function
         */
        public static function activate() {
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