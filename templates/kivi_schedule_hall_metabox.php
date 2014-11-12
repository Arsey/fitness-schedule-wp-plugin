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
                    foreach ($towns as $town => $town_id) {
                        @get_post_meta($post->ID, 'hall_city_id', true) == $town ? $selected = 'selected' : $selected = '';
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
            <label for="hall_group"><?php echo __('Group '); ?></label>
        </th>
        <td>
            <select class="hall-clubs-meta" id = "select_clubs" name="hall_club_id">
                <?php isset($clubs [@get_post_meta($post->ID, 'hall_club_id', true)]) ? $selected_city = $clubs [@get_post_meta($post->ID, 'hall_club_id', true)]:$selected_city =''?>
                <option><?php echo $selected_city ; ?></option>
            </select>
        </td>
    </tr> 
</table>