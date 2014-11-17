<?php
global $wpdb;

$query = new WP_Query(array('post_type' => Post_Type_Club::POST_TYPE));

$team_club_id = @get_post_meta($post->ID, 'team_club_id', true);
?>
<table class="form-table">
    <tr valign="top">
        <th width="30%">
            <label for="meta_a"><?php echo __('Club'); ?></label>
        </th>
        <td>
            <select id="team_club_id" name="team_club_id">
                <?php
                while ($query->have_posts()) {
                    $query->the_post();
                    $selected = get_the_ID() == $team_club_id ? 'selected' : '';
                    ?>
                    <option value="<?php the_ID(); ?>" <?php echo $selected ?> > <?php echo the_title(); ?> </option>
                <?php } ?>
            </select>
        </td>
    </tr>
    <tr>
        <th>
            <label for="program_description"> <?php echo __('Description '); ?> </label>
        </th>
        <td>
            <textarea rows="5" cols="50" id="team_description"
                      name="team_description"><?php echo @get_post_meta($post->ID, 'team_description', true); ?></textarea>
        </td>
    </tr>
    <tr>
        <th>
            <label for="team_is_active"> <?php echo __('Is Active'); ?> </label>
        </th>
        <td>
            <?php @get_post_meta($post->ID, 'team_is_active', true) !== "" ? $selected = 'checked' : $selected = ''; ?>
            <input id="team_is_active" name="team_is_active" type="checkbox" <?php echo $selected ?>/>
        </td>
    </tr>
</table>
