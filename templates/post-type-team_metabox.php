
<table class="form-table">
    <tr valign="top">
        <th  width="30%" class="metabox_label_column">
            <label for="team_group"><?php echo __( 'Группа ' ); ?></label>
        </th>
        <td>
            <?php $teams = $this->team_group?>
            <select id = "team_group" name="team_group">
              <?php
                foreach ($teams as $team) {
                    @get_post_meta($post->ID, 'team_group', true) == $team ? $selected = 'selected' : $selected = '';
                    ?>
                    <option value="<?php echo $team; ?>" <?php echo $selected ?> > <?php echo $team; ?> </option>
                <?php }
                ?>
            </select>
        </td>
    </tr>
    <tr>
        <th>
            <label for="program_description"> <?php echo __( 'Описание ' ); ?> </label>
        </th>
        <td>
            <textarea rows="5" cols="50" id="team_description" name="team_description" ><?php echo @get_post_meta($post->ID, 'team_description', true); ?></textarea>
        </td>
    </tr>
    <tr>
        <th>
            <label for="team_is_active"> <?php echo __( 'Активный ' ); ?> </label>
        </th>
        <td>
            <?php @get_post_meta($post->ID, 'team_is_active', true) !== "" ? $selected = 'checked' : $selected = ''; ?>
            <input id="team_is_active" name="team_is_active" type="checkbox" <?php echo $selected ?>/>
        </td>
    </tr>
</table>
