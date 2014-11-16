<?php
global $wpdb;
$query = new WP_Query(array('post_type' => 'kivi_schedule_city'));
$towns = array();
while ($query->have_posts()) {
    $query->the_post();
    $towns[get_the_id()] = get_the_title();
}
$query_club = new WP_Query(array('post_type' => 'kivi_schedule_club'));
$clubs = array();
while ($query_club->have_posts()) {
    $query_club->the_post();
    $clubs[get_the_id()] = get_the_title();
}
?>
<table class="form-table">
    <tr valign="top">
        <th  width="30%" class="metabox_label_column">
            <label for="hall_city"> <?php echo __('City '); ?> </label>
        </th>
        <td>
            <select id = "select_cities" name="hall_city_id">
                <?php
                if (isset($towns)) {
                    $selected_town = 0;
                    $counter = 0;
                    $selected_item = @get_post_meta($post->ID, 'hall_city_id', true);
                    foreach ($towns as $town => $town_id) {
                        if (isset($selected_item) && $selected_item != 0) {
                            if (@get_post_meta($post->ID, 'hall_city_id', true) == $town) {
                                $selected = 'selected';
                                $selected_town = $town;
                            } else {
                                $selected = '';
                            }
                        } else {
                            if ($counter < 1) {
                                $selected_town = $town;
                                $counter++;
                                $selected = '';
                            }
                        }
                        ?>
                        <option value="<?php echo $town; ?>" <?php echo $selected; ?> > <?php echo $town_id; ?> </option>
                        <?php
                    }
                }
                ?>
            </select>
        </td>
    </tr>
    <tr valign="top">
        <th  width="30%" class="metabox_label_column">
            <label for="hall_group"><?php echo __('Club'); ?></label>
        </th>
        <td>
            <select class="hall-clubs-meta" id = "select_clubs" name="hall_club_id">
                <?php
                if (isset($clubs)) {

                    foreach ($clubs as $club_id => $club) {
                        $club_city_id = get_post_meta($club_id, 'club_city_id', true);
                        if ($club_city_id == $selected_town) {
                            @get_post_meta($post->ID, 'hall_club_id', true) == $club_id ? $selected_city = 'selected' : $selected_city = ''
                            ?>
                            <option value='<?php echo $club; ?>' <?php echo $selected_city ?>><?php echo $club; ?></option>
                            <?php
                        }
                    }
                }
                ?>
            </select>
        </td>
    </tr> 
</table>