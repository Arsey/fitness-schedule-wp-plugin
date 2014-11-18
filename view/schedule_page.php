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
        <form id='shcedule_filters_form' action="<?php echo $new_url; ?>" Method ='POST'>
            <?php $cities = WP_Kivi_Schedule_Plugin::fetch_cities(); ?>
            <label for="select_cities"><?php echo __('City filter'); ?> </label>

            <select name='kivi_schedule_cities_filter' id="select_cities">
                <option value="" selected></option>
                <?php foreach ($cities as $city) { ?>
                    <option value="<?php echo $city['id']; ?>"><?php echo $city['name']; ?></option>
                <?php } ?>
            </select>

            <label for="select_clubs"> <?php echo __('Club filter', 'scheduleplugin'); ?> </label> <select name="kivi_schedule_clubs_filter" id="select_clubs"></select>
            <label for="select_hall"> <?php echo  __('Hall filter', 'scheduleplugin'); ?> </label> <select name="kivi_schedule_halls_filter" id="select_halls"></select>
            <input type="submit" class="download-excel-file" value='<?php echo  __('Export', 'scheduleplugin'); ?>' />
        </form>
    </div>
    <div id="kivischedule">
        <?php
        $data = WP_Kivi_Schedule_Plugin::fetch_schedule_data();
        ?>
    </div>
</div>