<?php
global $wpdb;
$query = new WP_Query(array('post_type' => 'post-type-city'));
$towns = array();
while ($query->have_posts()) {
    $query->the_post();
    $towns[get_the_id()] = get_the_title();
}
$query_club = new WP_Query(array('post_type' => 'post-type-club'));
$clubs = array();
while ($query_club->have_posts()) {
    $query_club->the_post();
    $clubs[get_the_id()] = get_the_title();
}
?>
<table class="form-table">
    <tr valign="top">
        <th  width="30%" class="metabox_label_column">
            <label for="hall_city">Город</label>
        </th>
        <td>
            <select id = "hall_city_id" name="hall_city_id">
                <?php
                foreach ($towns as $town => $town_id) {
                    @get_post_meta($post->ID, 'hall_city_id', true) == $town ? $selected = 'selected' : $selected = '';
                    ?>
                    <option value="<?php echo $town; ?>" <?php echo $selected; ?> > <?php echo $town_id; ?> </option>
                <?php }
                ?>
            </select>
        </td>
    </tr>
    <tr valign="top">
        <th  width="30%" class="metabox_label_column">
            <label for="hall_group">Группа</label>
        </th>
        <td>
            <select id = "hall_club_id" name="hall_club_id">
                <?php
                foreach ($clubs as $club_id => $club) {
                    @get_post_meta($post->ID, 'hall_club_id', true) == $club_id ? $selected = 'selected' : $selected = '';
                    ?>
                    <option value="<?php echo $club_id; ?>" <?php echo $selected; ?> > <?php echo $club; ?> </option>
                <?php }
                ?>
            </select>
        </td>
    </tr> 
</table>