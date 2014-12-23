<table>
    <tr>
        <th width="30%" class="metabox_label_column">
            <label for="program_description"> <?php echo __('Short Description', WP_Kivi_Schedule_Plugin::textdomain); ?></label>
        </th>
        <td>
            <textarea rows="5" cols="50" id="program_description"
                      name="program_description"><?php echo @get_post_meta($post->ID, 'program_description', true); ?></textarea>
        </td>
    </tr>
    <tr>
        <th class="metabox_label_column">
            <label for="program_is_active"> <?php echo __('Details Link', WP_Kivi_Schedule_Plugin::textdomain); ?></label>
        </th>
        <td>
            <input id="program_details_link" name="program_details_link" type="text"
                   value="<?php echo @get_post_meta($post->ID, 'program_details_link', true); ?>"/>
        </td>
    </tr>
    <tr>
        <th class="metabox_label_column">
            <label for="program_is_active"> <?php echo __('Is Active', WP_Kivi_Schedule_Plugin::textdomain); ?></label>
        </th>
        <td>
            <?php @get_post_meta($post->ID, 'program_is_active', true) !== "" ? $selected = 'checked' : $selected = ''; ?>
            <input id="program_is_active" name="program_is_active" type="checkbox" <?php echo $selected; ?>/>
        </td>
    </tr>
</table>