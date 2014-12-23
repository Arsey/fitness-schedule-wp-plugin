<div id="shcedule">
    <?php
    $programs = WP_Kivi_Schedule_Plugin::fetch_programs();

    $programs_options = '<option value=""></option>';
    foreach ($programs as $program) {
        $programs_options .= '<option value="' . $program['id'] . '">' . $program['title'] . '</option>';
    }
    ?>
    <div id="shcedule_filters">
        <?php
        $params = array(
            'page' => 'kivi_schedule_city',
            'wp_kivischedule_excel' => true);

        $new_url = add_query_arg($params, admin_url('admin.php'));
        ?>

        <form id='shcedule_filters_form' action="<?php echo $new_url; ?>" Method='POST'>
            <?php $cities = WP_Kivi_Schedule_Plugin::fetch_cities(); ?>


            <?php $is_user_current_club_manager = WP_Kivi_Schedule_Plugin::is_user_current_club_manager(); ?>


            <?php if (!$is_user_current_club_manager) { ?>
                <label
                    for="select_cities"><?php echo __('City filter', WP_Kivi_Schedule_Plugin::textdomain); ?> </label>
                <select name='kivi_schedule_cities_filter' id="select_cities">
                    <option value="" selected></option>
                    <?php foreach ($cities as $city) { ?>
                        <option value="<?php echo $city['id']; ?>"><?php echo $city['name']; ?></option>
                    <?php } ?>
                </select>
            <?php } else { ?>
                <?php $city_id = get_post_meta($is_user_current_club_manager['user_club'], 'club_city_id', true); ?>
                <input type="hidden" name='kivi_schedule_cities_filter' value="<?php echo $city_id; ?>">
            <?php } ?>


            <?php if (!$is_user_current_club_manager) { ?>
                <label for="select_clubs"> <?php echo __('Club filter', WP_Kivi_Schedule_Plugin::textdomain); ?></label>
                <select name="kivi_schedule_clubs_filter" id="select_clubs"></select>
            <?php } else { ?>
                <input type="hidden" name='kivi_schedule_clubs_filter'
                       value="<?php echo $is_user_current_club_manager['user_club']; ?>">
            <?php } ?>


            <label for="select_hall"> <?php echo __('Hall filter', WP_Kivi_Schedule_Plugin::textdomain); ?> </label>
            <select name="kivi_schedule_halls_filter" id="select_halls">
                <option value=""><?php _e('Select', WP_Kivi_Schedule_Plugin::textdomain); ?></option>
                <?php if ($is_user_current_club_manager) {
                    $halls_query_args = array('post_type' => Post_Type_Hall::POST_TYPE);
                    $halls_query_args['meta_query'] = array(
                        array(
                            'key' => 'hall_club_id',
                            'value' => $is_user_current_club_manager['user_club']
                        )
                    );
                    $Halls = new WP_Query($halls_query_args);
                    $Halls_posts = $Halls->get_posts();
                    if ($Halls_posts) {
                        foreach ($Halls_posts as $hall) {
                            ?>
                            <option value="<?php echo $hall->ID; ?>"><?php echo $hall->post_title; ?></option>
                        <?php
                        }
                    }
                } ?>
            </select>

            <input type="submit" class="download-excel-file"
                   value='<?php echo __('Export', WP_Kivi_Schedule_Plugin::textdomain); ?>'/>
        </form>

    </div>
    <div id="kivischedule">
        <?php $data = WP_Kivi_Schedule_Plugin::fetch_schedule_data(); ?>
    </div>
</div>