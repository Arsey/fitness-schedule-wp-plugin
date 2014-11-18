<div id="shcedule">
    <?php
    $programs = WP_Kivi_Schedule_Plugin::fetch_programs();

    $programs_options = '<option value=""></option>';
    foreach ($programs as $program) {
        $programs_options .= '<option value="' . $program['id'] . '">' . $program['title'] . '</option>';
    }
    ?>
    <div id="shcedule_filters">
        <?php $cities = WP_Kivi_Schedule_Plugin::fetch_cities(); ?>
        <label for="select_cities"><?php echo __('City filter', 'scheduleplugin'); ?> </label>

        <select id="select_cities">
            <option value="" selected></option>
            <?php foreach ($cities as $city) { ?>
                <option value="<?php echo $city['id']; ?>"><?php echo $city['name']; ?></option>
            <?php } ?>
        </select>

        <label for="select_clubs"> <?php echo __('Club filter', 'scheduleplugin'); ?> </label> <select name=""
                                                                                                       id="select_clubs"></select>
        <label for="select_hall"> <?php echo __('Hall filter', 'scheduleplugin'); ?> </label> <select name=""
                                                                                                      id="select_halls"></select>
        <?php
        $params = array(
            'page' => 'kivi_schedule_city',
            'wp_kivischedule_excel' => true);

        $new_url = add_query_arg($params, admin_url('admin.php'));
        ?>
        <a href="<?php echo $new_url; ?>" class="download-excel-file"><?php echo __('Export', 'scheduleplugin'); ?></a>
    </div>
    <div id="kivischedule">
        <?php
        $data = WP_Kivi_Schedule_Plugin::fetch_schedule_data();
        ?>
    </div>
</div>