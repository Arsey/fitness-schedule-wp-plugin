<?php
global $wpdb;
$query = new WP_Query(array('post_type' => 'post-type-city'));
$towns = array();

while ($query->have_posts()) {
    $query->the_post();
    $towns[get_the_id()] = get_the_title();
}
?>

<table class="form-table">
    <tr valign="top">
        <th  width="30%">
            <label for="meta_a"><?php echo __( 'Город ' ); ?></label>
        </th>
        <td>
            <select id ="club_city_id" name="club_city_id">
                <?php
                foreach ($towns as $town => $town_id) {
                    @get_post_meta($post->ID, 'club_city_id', true) == $town ? $selected = 'selected' : $selected = '';
                    ?>
                    <option value="<?php echo $town; ?>" <?php echo $selected ?> > <?php echo $town_id; ?> </option>
                <?php }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <th>
            <label for="club_phone"> <?php echo __( 'Телефон ' ); ?> </label>
        </th>
        <td>
            <input id="club_phone" name="club_phone" type="text" value="<?php echo @get_post_meta($post->ID, 'club_phone', true); ?>" />
        </td>
    </tr>
    <tr>
        <th>
            <label for="club_is_active"> <?php echo __( 'Активный ' ); ?>  </label>
        </th>
        <td>
            <?php @get_post_meta($post->ID, 'club_is_active', true) !== "" ? $selected = 'checked' : $selected = ''; ?>
            <input id="club_is_active" name="club_is_active" type="checkbox" <?php echo $selected ?>/>
        </td>
    </tr>
</table>
