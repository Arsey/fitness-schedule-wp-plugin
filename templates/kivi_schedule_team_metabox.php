<?php
$post_meta = get_post_meta($post->ID);

$team_club_id = isset($post_meta['team_club_id']) ? $post_meta['team_club_id'][0] : null;
$team_clubs = explode(Post_Type_Team::TEAM_MEMBER_CLUB_ID_DELIMITER, $team_club_id);

$first_name = isset($post_meta['team_first_name']) ? $post_meta['team_first_name'][0] : '';
$team_last_name = isset($post_meta['team_last_name']) ? $post_meta['team_last_name'][0] : '';
$team_description = isset($post_meta['team_description']) ? $post_meta['team_description'][0] : '';
$team_methodist_description = isset($post_meta['team_methodist_description']) ? $post_meta['team_methodist_description'][0] : '';
$team_facebook_link = isset($post_meta['team_facebook_link']) ? $post_meta['team_facebook_link'][0] : '';
$team_vk_link = isset($post_meta['team_vk_link']) ? $post_meta['team_vk_link'][0] : '';
$team_is_active = (isset($post_meta['team_is_active']) && $post_meta['team_is_active'][0] !== '') ? $selected = 'checked' : $selected = '';
$team_is_methodist = (isset($post_meta['team_is_methodist']) && $post_meta['team_is_methodist'][0] !== '') ? $selected = 'checked' : $selected = '';


$cities = WP_Kivi_Schedule_Plugin::fetch_cities();
$cities_ids = array();
foreach ($cities as $city) {
    $cities_ids[] = $city['id'];
}

$clubs = WP_Kivi_Schedule_Plugin::fetch_clubs_by_city($cities_ids);

if ($clubs) {
    foreach ($clubs as $club) {
        if (!$default_club) {
            $default_club = $club;
        } else if ($default_club == $club['club_id']) {
            $default_club = $club;
        }
    }
}
?>
<table class="form-table">


    <!--Club-->
    <tr valign="top">
        <th width="30%">
            <label for="team_club_id"><?php echo __('Club', WP_Kivi_Schedule_Plugin::textdomain); ?></label>
        </th>
        <td>
            <?php
            if ($cities) {
                foreach ($cities as $city) {
                    ?>
                    <div class="one-city-list">
                                <span>
                                    <?php echo $city['name']; ?>
                                </span>
                        <?php if ($clubs) { ?>
                            <ul>
                                <?php foreach ($clubs as $club) { ?>
                                    <?php if ($club['club_city_id'] == $city['id']) { ?>
                                        <li>
                                            <?php $checked = (is_array($team_clubs) && in_array($club['club_id'], $team_clubs)) ? 'checked' : ''; ?>

                                            <label>
                                                <input type="checkbox" name="team_club_id_part"
                                                       value="<?php echo $club['club_id']; ?>" <?php echo $checked; ?>/><?php echo $club['club_name']; ?>
                                            </label>
                                        </li>
                                    <?php } ?>
                                <?php } ?>
                            </ul>
                        <?php } ?>
                    </div>
                <?php } ?>
            <?php } ?>
            <input type="hidden" name="team_club_id" id="team_club_id" value="<?php echo $team_club_id; ?>"/>
        </td>
    </tr>

    <!--Description-->
    <tr>
        <th>
            <label for="program_description"><?php echo __('Description', WP_Kivi_Schedule_Plugin::textdomain); ?> </label>
        </th>
        <td>
            <textarea rows="5" cols="50" id="team_description"
                      name="team_description"><?php echo $team_description ?></textarea>
        </td>
    </tr>

    <!--Facebook Link-->
    <tr valign="top">
        <th width="30%">
            <label for="team_facebook_link"><?php echo __('Facebook Link', WP_Kivi_Schedule_Plugin::textdomain); ?></label>
        </th>
        <td>
            <input type="text" id="team_facebook_link" name="team_facebook_link"
                   value="<?php echo $team_facebook_link; ?>">
        </td>
    </tr>

    <!--Vk Link-->
    <tr valign="top">
        <th width="30%">
            <label for="team_vk_link"><?php echo __('VK Link', WP_Kivi_Schedule_Plugin::textdomain); ?></label>
        </th>
        <td>
            <input type="text" id="team_vk_link" name="team_vk_link" value="<?php echo $team_vk_link; ?>">
        </td>
    </tr>

    <!--Is Methodist-->
    <tr>
        <th>
            <label for="team_is_methodist"><?php echo __('Is Methodist', WP_Kivi_Schedule_Plugin::textdomain); ?></label>
        </th>
        <td>
            <input id="team_is_methodist" name="team_is_methodist" type="checkbox" <?php echo $team_is_methodist; ?>/>
        </td>
    </tr>

    <!--Methodist Description-->
    <tr id="team_methodist_description-row">
        <th>
            <label
                for="team_methodist_description"><?php echo __('Methodist Description', WP_Kivi_Schedule_Plugin::textdomain); ?> </label>
        </th>
        <td>
            <textarea rows="5" cols="50" id="team_methodist_description"
                      name="team_methodist_description"><?php echo $team_methodist_description; ?></textarea>
        </td>
    </tr>

    <!--Is Active-->
    <tr>
        <th>
            <label for="team_is_active"><?php echo __('Is Active', WP_Kivi_Schedule_Plugin::textdomain); ?></label>
        </th>
        <td>
            <input id="team_is_active" name="team_is_active" type="checkbox" <?php echo $team_is_active; ?>/>
        </td>
    </tr>
</table>

<script>
    (function ($) {
        $(function () {
            $('input[name="team_club_id_part"]').change(function () {
                updateTeamClubIds();
            });

            $('input[name="team_is_methodist"]').change(function () {
                toggleMethodistDescription();
            });

            function updateTeamClubIds() {
                var isAnyChecked = false;
                var ids = [];
                $('input[name="team_club_id_part"]').each(function () {
                    var $el = $(this);
                    if ($el.is(':checked')) {
                        ids.push($el.val());
                        isAnyChecked = true;
                    }
                });
                if (isAnyChecked) {
                    $('#team_club_id').val('##' + ids.join('<?php echo Post_Type_Team::TEAM_MEMBER_CLUB_ID_DELIMITER;?>') + '##');
                } else {
                    $('#team_club_id').val('');
                }
            }

            function toggleMethodistDescription() {
                var $textareaRow = $('#team_methodist_description-row');
                if ($('input[name="team_is_methodist"]').is(':checked')) {
                    $textareaRow.show();
                } else {
                    $textareaRow.hide();
                }
            };

            toggleMethodistDescription();

            updateTeamClubIds();
        })
    })(jQuery)
</script>