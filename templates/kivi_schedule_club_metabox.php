<?php
global $wpdb;
$query = new WP_Query(array('post_type' => 'kivi_schedule_city'));
$towns = array();

while ($query->have_posts()) {
    $query->the_post();
    $towns[get_the_id()] = get_the_title();
}
?>

<table class="form-table">
    <tr valign="top">
        <th width="30%">
            <label for="meta_a"><?php echo __('City'); ?></label>
        </th>
        <td>
            <select id="club_city_id" name="club_city_id">
                <?php
                foreach ($towns as $town => $town_id) {
                    @get_post_meta($post->ID, 'club_city_id', true) == $town ? $selected = 'selected' : $selected = '';
                    ?>
                    <option value="<?php echo $town; ?>" <?php echo $selected ?> > <?php echo $town_id; ?> </option>
                <?php
                }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <th>
            <label for="club_phone"> <?php echo __('Phone'); ?> </label>
        </th>
        <td>
            <input id="club_phone" name="club_phone" type="text"
                   value="<?php echo @get_post_meta($post->ID, 'club_phone', true); ?>"/>
        </td>
    </tr>
    <!--club_map_link-->
    <tr>
        <th>
            <label for="club_map_link"> <?php echo __('Map Link'); ?> </label>
        </th>
        <td>
            <input id="club_map_link" name="club_map_link" type="text"
                   value="<?php echo @get_post_meta($post->ID, 'club_map_link', true); ?>"/>
        </td>
    </tr>
    <!--club_video_link-->
    <tr>
        <th>
            <label for="club_video_link"> <?php echo __('Video Link'); ?> </label>
        </th>
        <td>
            <input id="club_video_link" name="club_video_link" type="text"
                   value="<?php echo @get_post_meta($post->ID, 'club_video_link', true); ?>"/>
        </td>
    </tr>
    <!--club_tour_link-->
    <tr>
        <th>
            <label for="club_tour_link"> <?php echo __('Virtual Tour Link'); ?> </label>
        </th>
        <td>
            <input id="club_tour_link" name="club_tour_link" type="text"
                   value="<?php echo @get_post_meta($post->ID, 'club_tour_link', true); ?>"/>
        </td>
    </tr>
    <!--club_slider_shortcode-->
    <tr>
        <th>
            <label for="club_slider_shortcode"> <?php echo __('Meta Slider Short Code'); ?> </label>
        </th>
        <td>
            <input id="club_slider_shortcode" name="club_slider_shortcode" type="text"
                   value="<?php echo @get_post_meta($post->ID, 'club_slider_shortcode', true); ?>"/>
        </td>
    </tr>
    <tr>
        <th>
            <label for="club_is_active"> <?php echo __('Is Active '); ?>  </label>
        </th>
        <td>
            <?php @get_post_meta($post->ID, 'club_is_active', true) !== "" ? $selected = 'checked' : $selected = ''; ?>
            <input id="club_is_active" name="club_is_active" type="checkbox" <?php echo $selected ?>/>
        </td>
    </tr>
</table>
