
<table class="form-table">
    <tr>
        <th>
            <label for="program_description"> <?php echo __( 'Description ' ); ?> </label>
        </th>
        <td>
            <textarea rows="5" cols="50" id="team_description" name="team_description" ><?php echo @get_post_meta($post->ID, 'team_description', true); ?></textarea>
        </td>
    </tr>
    <tr>
        <th>
            <label for="team_is_active"> <?php echo  __('Is Active'); ?> </label>
        </th>
        <td>
            <?php @get_post_meta($post->ID, 'team_is_active', true) !== "" ? $selected = 'checked' : $selected = ''; ?>
            <input id="team_is_active" name="team_is_active" type="checkbox" <?php echo $selected ?>/>
        </td>
    </tr>
</table>
