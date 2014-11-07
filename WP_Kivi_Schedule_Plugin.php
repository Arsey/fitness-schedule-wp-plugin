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
            add_filter('nav_menu_css_class', array(&$this, 'special_nav_class'), 10, 2);

            /* ajax actions */
            add_action("wp_ajax_fetch_clubs_by_city", array(&$this, "fetch_clubs_by_city"));
            add_action("wp_ajax_fetch_hall_by_club", array(&$this, "fetch_hall_by_club"));
            add_action("wp_ajax_fetch_schedule_data", array(&$this, "fetch_schedule_data"));
            add_action("wp_ajax_save_schedule_data", array(&$this, "save_schedule_data"));

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
            wp_enqueue_script('jkivi_schedule_main', plugins_url('/js/kivi_schedule_main.js', __FILE__), array('jquery'));

            //styles
            wp_enqueue_style('jonthornton-jquery-timepicker', plugins_url('/js/jonthornton-jquery-timepicker/jquery.timepicker.css', __FILE__));
            wp_enqueue_style('jquery-week-calendar-master', plugins_url('/js/jquery-week-calendar-master/jquery.weekcalendar.css', __FILE__));
            wp_enqueue_style('jkivi_schedule_main-style', plugins_url('/css/kivi_schedule_main.css', __FILE__));

            $img_path = array('template_url' => plugins_url('fitness-schedule-wp-plugin/img/'));
            wp_register_script('imgicons-config', plugins_url('/js/kivi_schedule_main.js', __FILE__), array()); // Custom scripts
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

        function special_nav_class($css_class, $page) {
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
            $query = new WP_Query(array('post_type' => 'kivi_schedule_city', 'orderby' => 'title', 'order' => 'ASC'));
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

            $args = array('post_type' => 'kivi_schedule_club');
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

                $schedule_for_all_halls = $wpdb->get_results('SELECT * FROM ' . $kivi_schedule_settings['kivi_schedule_table'] . ' WHERE hall_id IN(\'' . implode("','", $halls_ids) . '\')  ORDER BY time', ARRAY_A);

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

            $args = array('post_type' => 'kivi_schedule_hall');
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
            $query = new WP_Query(array('post_type' => 'kivi_schedule_program'));
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

        function fetch_schedule_data() {
            global $wpdb;
            global $kivi_schedule_settings;
            if (isset($_REQUEST['kivischedule_hall_id'])) {
                $hall_id = $_REQUEST['kivischedule_hall_id'];
                $query_programm = new WP_Query(array('post_type' => 'kivi_schedule_program'));
                $programs = array();
                $htmlContent = '      <tr>
            <th>Время</th>
            <th>Понедельник</th>
            <th>Вторник</th>
            <th>Среда</th>
            <th>Четверг</th>
            <th>Пятница</th>
            <th>Суббота</th>
            <th>Воскресенье</th>        
            <th></th>
        </tr>';
                while ($query_programm->have_posts()) {
                    $query_programm->the_post();
                    $programs[get_the_id()] = get_the_title();
                }
                $programs[0] = "";
                $table_data = $wpdb->get_results('SELECT * FROM ' . $kivi_schedule_settings['kivi_schedule_table'] . ' WHERE hall_id = "' . $hall_id . '"  ORDER BY time', ARRAY_A);
                if (isset($table_data) and ($table_data != "")) {
                    foreach ($table_data as $table_row => $data) {
                        $monday_program_id = $data['monday_program_id'];
                        $tuesday_program_id = $data['tuesday_program_id'];
                        $wednesday_program_id = $data['wednesday_program_id'];
                        $thursday_program_id = $data['thursday_program_id'];
                        $friday_program_id = $data['friday_program_id'];
                        $saturday_program_id = $data['saturday_program_id'];
                        $sunday_program_id = $data['sunday_program_id'];
                        $htmlContent .= '<tr id="' . $data['id'] . '">';
                        $htmlContent .= '<td><div class="td_content">' . $data['time'] . '</td>';
                        $htmlContent .= '<td><div class="td_content">' . $programs[$monday_program_id] . '</div></td>';
                        $htmlContent .= '<td><div class="td_content">' . $programs[$tuesday_program_id] . '</div></td>';
                        $htmlContent .= '<td><div class="td_content">' . $programs[$wednesday_program_id] . '</div></td>';
                        $htmlContent .= '<td><div class="td_content">' . $programs[$thursday_program_id] . '</div></td>';
                        $htmlContent .= '<td><div class="td_content">' . $programs[$friday_program_id] . '</div></td>';
                        $htmlContent .= '<td><div class="td_content">' . $programs[$saturday_program_id] . '</div></td>';
                        $htmlContent .= '<td><div class="td_content">' . $programs[$sunday_program_id] . '</div></td>';
                        $htmlContent .= '<td><a href="javascript:void(0)" class="save_changes_to_db">Save</a></td>';
                        $htmlContent .= '</tr>';
                    }
                }
                echo $htmlContent;
            }
            die();
        }

        function save_schedule_data() {
            global $wpdb;
            $time = $_REQUEST['kivischedule_time'];
            echo $time;
            isset($_REQUEST['kivischedule_hall_id']) ? $hall_id = $_REQUEST['kivischedule_hall_id'] : $hall_id = 0;
            isset($_REQUEST['kivischedule_sched_1']) ? $sched_1 = $_REQUEST['kivischedule_sched_1'] : $sched_1 = 0;
            isset($_REQUEST['kivischedule_sched_2']) ? $sched_2 = $_REQUEST['kivischedule_sched_2'] : $sched_2 = 0;
            isset($_REQUEST['kivischedule_sched_3']) ? $sched_3 = $_REQUEST['kivischedule_sched_3'] : $sched_3 = 0;
            isset($_REQUEST['kivischedule_sched_4']) ? $sched_4 = $_REQUEST['kivischedule_sched_4'] : $sched_4 = 0;
            isset($_REQUEST['kivischedule_sched_5']) ? $sched_5 = $_REQUEST['kivischedule_sched_5'] : $sched_5 = 0;
            isset($_REQUEST['kivischedule_sched_6']) ? $sched_6 = $_REQUEST['kivischedule_sched_6'] : $sched_6 = 0;
            isset($_REQUEST['kivischedule_sched_7']) ? $sched_7 = $_REQUEST['kivischedule_sched_7'] : $sched_7 = 0;
            if ($wpdb->insert('wp_dbkivi_schedule', array(
                        'time' => $time,
                        'hall_id' => $hall_id,
                        'monday_program_id' => $sched_1,
                        'tuesday_program_id' => $sched_2,
                        'wednesday_program_id' => $sched_3,
                        'thursday_program_id' => $sched_4,
                        'friday_program_id' => $sched_5,
                        'saturday_program_id' => $sched_6,
                        'sunday_program_id' => $sched_7), array('%s', '%d', '%d', '%d', '%d', '%d', '%d', '%d', '%d')
                    )
            ) {
                return 'success';
            } else {
                return 'error';
            }

            die();
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
