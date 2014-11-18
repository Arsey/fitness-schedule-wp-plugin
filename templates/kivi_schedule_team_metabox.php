<?php
global $wpdb;

$query = new WP_Query(array('post_type' => Post_Type_Club::POST_TYPE));

$post_meta = get_post_meta($post->ID);

$team_club_id = isset($post_meta['team_club_id']) ? $post_meta['team_club_id'][0] : null;;
$first_name = isset($post_meta['team_first_name']) ? $post_meta['team_first_name'][0] : '';
$team_last_name = isset($post_meta['team_last_name']) ? $post_meta['team_last_name'][0] : '';
$team_description = isset($post_meta['team_description']) ? $post_meta['team_description'][0] : '';
$team_facebook_link = isset($post_meta['team_facebook_link']) ? $post_meta['team_facebook_link'][0] : '';
$team_vk_link = isset($post_meta['team_vk_link']) ? $post_meta['team_vk_link'][0] : '';
$team_is_active = (isset($post_meta['team_is_active']) && $post_meta['team_is_active'][0] !== '') ? $selected = 'checked' : $selected = '';
?>
<table class="form-table">


    <!--Club-->
    <tr valign="top">
        <th width="30%">
            <label for="team_club_id"><?php echo __('Club', 'scheduleplugin'); ?></label>
        </th>
        <td>
            <select id="team_club_id" name="team_club_id">
                <?php while ($query->have_posts()) { ?>
                    <?php
                    $query->the_post();
                    $selected = get_the_ID() == $team_club_id ? 'selected' : '';
                    ?>
                    <option value="<?php the_ID(); ?>" <?php echo $selected ?> > <?php echo the_title(); ?></option>
                <?php } ?>
            </select>
        </td>
    </tr>

    <!--First Name-->
    <tr valign="top">
        <th width="30%">
            <label for="team_first_name"><?php echo __('First Name', 'scheduleplugin'); ?></label>
        </th>
        <td>
            <input type="text" id="team_first_name" name="team_first_name" value="<?php echo $first_name; ?>">
        </td>
    </tr>

    <!--Last Name-->
    <tr valign="top">
        <th width="30%">
            <label for="team_last_name"><?php echo __('Last Name', 'scheduleplugin'); ?></label>
        </th>
        <td>
            <input type="text" id="team_last_name" name="team_last_name" value="<?php echo $team_last_name; ?>">
        </td>
    </tr>

    <!--Description-->
    <tr>
        <th>
            <label for="program_description"><?php echo __('Description', 'scheduleplugin'); ?> </label>
        </th>
        <td>
            <textarea rows="5" cols="50" id="team_description"
                      name="team_description"><?php echo $team_description ?></textarea>
        </td>
    </tr>

    <!--Facebook Link-->
    <tr valign="top">
        <th width="30%">
            <label for="team_facebook_link"><?php echo __('Facebook Link', 'scheduleplugin'); ?></label>
        </th>
        <td>
            <input type="text" id="team_facebook_link" name="team_facebook_link"
                   value="<?php echo $team_facebook_link; ?>">
        </td>
    </tr>

    <!--Vk Link-->
    <tr valign="top">
        <th width="30%">
            <label for="team_vk_link"><?php echo __('VK Link', 'scheduleplugin'); ?></label>
        </th>
        <td>
            <input type="text" id="team_vk_link" name="team_vk_link" value="<?php echo $team_vk_link; ?>">
        </td>
    </tr>

    <!--Is Active-->
    <tr>
        <th>
            <label for="team_is_active"><?php echo __('Is Active', 'scheduleplugin'); ?></label>
        </th>
        <td>
            <input id="team_is_active" name="team_is_active" type="checkbox" <?php echo $team_is_active; ?>/>
        </td>
    </tr>
</table>
