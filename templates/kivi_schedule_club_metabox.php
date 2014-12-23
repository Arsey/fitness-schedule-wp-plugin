<?php
$post_meta = get_post_meta($post->ID);

$club_phone = isset($post_meta['club_phone']) ? $post_meta['club_phone'][0] : '';
$club_email = isset($post_meta['club_email']) ? $post_meta['club_email'][0] : '';
$club_map_link = isset($post_meta['club_map_link']) ? $post_meta['club_map_link'][0] : '';
$club_video_link = isset($post_meta['club_video_link']) ? $post_meta['club_video_link'][0] : '';
$club_tour_link = isset($post_meta['club_tour_link']) ? $post_meta['club_tour_link'][0] : '';
$club_slider_shortcode = isset($post_meta['club_slider_shortcode']) ? $post_meta['club_slider_shortcode'][0] : '';
$club_is_active = isset($post_meta['club_is_active']) ? $post_meta['club_is_active'][0] : null;
$club_programs = isset($post_meta['club_programs']) ? $post_meta['club_programs'][0] : '';
$club_services = isset($post_meta['club_services']) ? $post_meta['club_services'][0] : '';

$towns_query = new WP_Query(array('post_type' => Post_Type_City::POST_TYPE));
$towns = array();

/**
 * fetch the towns
 */
if (count($towns_query->posts)) {
    foreach ($towns_query->posts as $post)
        $towns[$post->ID] = $post->post_title;
}
wp_reset_query();

/**
 * fetch the services
 */
$services = null;
if (defined('POST_TYPE_KIWI_SERVICES')) {
    $services = new WP_Query(array('post_type' => POST_TYPE_KIWI_SERVICES, 'post_status' => 'publish', 'posts_per_page' => 1000));
}

/**
 * fetch the programs
 */
$programs = WP_Kivi_Schedule_Plugin::fetch_programs();

?>

<table class="form-table">
    <tr valign="top">
        <th width="30%">
            <label for="meta_a"><?php echo __('City', WP_Kivi_Schedule_Plugin::textdomain); ?></label>
        </th>
        <td>
            <select id="club_city_id" name="club_city_id">
                <?php foreach ($towns as $town => $town_id) { ?>
                    <option
                        value="<?php echo $town; ?>" <?php echo selected($post_meta['club_city_id'][0], $town); ?>>
                        <?php echo $town_id; ?>
                    </option>
                <?php } ?>
            </select>
        </td>
    </tr>
    <tr>
        <th>
            <label for="club_phone"> <?php echo __('Phone', WP_Kivi_Schedule_Plugin::textdomain); ?> </label>
        </th>
        <td>
            <input id="club_phone" name="club_phone" type="text" value="<?php echo $club_phone; ?>"/>
        </td>
    </tr>
    <!--club_email-->
    <tr>
        <th>
            <label for="club_email"> <?php echo __('Email', WP_Kivi_Schedule_Plugin::textdomain); ?> </label>
        </th>
        <td>
            <input id="club_email" name="club_email" type="text" value="<?php echo $club_email; ?>"/>
        </td>
    </tr>
    <!--club_map_link-->
    <tr>
        <th>
            <label for="club_map_link"> <?php echo __('Map Link', WP_Kivi_Schedule_Plugin::textdomain); ?> </label>
        </th>
        <td>
            <p style="color:#DD0238">Do not put here a shortened link! Only link for an iframe is acceptable!</p>
            <input id="club_map_link" name="club_map_link" type="text" value="<?php echo $club_map_link; ?>"/>
        </td>
    </tr>
    <!--club_video_link-->
    <tr>
        <th>
            <label
                for="club_video_link"> <?php echo __('Video Link', WP_Kivi_Schedule_Plugin::textdomain); ?> </label>
        </th>
        <td>
            <p style="color:#DD0238"><?php echo __('Put here only the video code like "UmJ5tHqpk_I"', WP_Kivi_Schedule_Plugin::textdomain); ?></p>
            <input id="club_video_link" name="club_video_link" type="text" value="<?php echo $club_video_link; ?>"/>
        </td>
    </tr>
    <!--club_tour_link-->
    <tr>
        <th>
            <label
                for="club_tour_link"> <?php echo __('Virtual Tour Link', WP_Kivi_Schedule_Plugin::textdomain); ?> </label>
        </th>
        <td>
            <input id="club_tour_link" name="club_tour_link" type="text" value="<?php echo $club_tour_link; ?>"/>
        </td>
    </tr>
    <!--club_slider_shortcode-->
    <tr>
        <th>
            <label
                for="club_slider_shortcode"> <?php echo __('Meta Slider Short Code', WP_Kivi_Schedule_Plugin::textdomain); ?> </label>
        </th>
        <td>
            <input id="club_slider_shortcode" name="club_slider_shortcode" type="text"
                   value="<?php echo $club_slider_shortcode; ?>"/>
        </td>
    </tr>
    <tr>
        <th>
            <label for="club_is_active"><?php echo __('Is Active', WP_Kivi_Schedule_Plugin::textdomain); ?></label>
        </th>
        <td>
            <input id="club_is_active" name="club_is_active"
                   type="checkbox" <?php echo checked('on', $club_is_active) ?>/>
        </td>
    </tr>
    <tr>
        <th>
            <label for="club_is_active"><?php echo __('Programs', WP_Kivi_Schedule_Plugin::textdomain); ?></label>
        </th>
        <td>
            <input type="hidden" name="club_programs" id="club_programs" value="<?php echo $club_programs; ?>"/>

            <div class="club-programs-wrapper">
                <?php
                if ($programs) {
                    $checked_programs = array();
                    if (!empty($club_programs)) {
                        $checked_programs = explode(Post_Type_Club::CLUB_MULTI_META_DELIMITER, $club_programs);
                    }
                    foreach ($programs as $p) {
                        $checked = '';
                        if (in_array($p['id'], $checked_programs) || $club_programs == '')
                            $checked = 'checked';
                        ?>
                        <label class="club-program-label" for="dummy-club-program-<?php echo $p['id']; ?>">
                            <input type="checkbox"
                                   class="club-program"
                                   name="dummy-club-program[]"
                                   value="<?php echo $p['id']; ?>"
                                   id="dummy-club-program-<?php echo $p['id']; ?>"
                                <?php echo $checked; ?>/>
                            <?php echo $p['title']; ?>
                        </label>
                    <?php
                    }
                }
                ?>
            </div>
            <script>
                (function ($) {
                    var delimiter = '<?php echo Post_Type_Club::CLUB_MULTI_META_DELIMITER;?>';
                    $('input.club-program').change(function () {
                        updateClubPrograms();
                    });

                    function updateClubPrograms() {
                        var isAnyChecked = false;
                        var ids = [];
                        $('input.club-program').each(function () {
                            var $el = $(this);
                            if ($el.is(':checked')) {
                                ids.push($el.val());
                                isAnyChecked = true;
                            }
                        });
                        if (isAnyChecked) {
                            $('#club_programs').val('##' + ids.join(delimiter) + '##');
                        } else {
                            $('#club_programs').val('');
                        }
                    }

                    updateClubPrograms();
                })(jQuery);
            </script>
        </td>
    </tr>
    <?php if (count($services->posts)) { ?>
        <tr>
            <th>
                <label
                    for="club_is_active"><?php echo __('Services', WP_Kivi_Schedule_Plugin::textdomain); ?></label>
            </th>
            <td>
                <input type="hidden" name="club_services" id="club_services" value="<?php echo $club_services; ?>"/>

                <div class="club-services-wrapper">
                    <?php
                    $checked_services = array();
                    if (!empty($club_services)) {
                        $checked_services = explode(Post_Type_Club::CLUB_MULTI_META_DELIMITER, $club_services);
                    }
                    foreach ($services->posts as $post) {
                        $checked = '';
                        if (in_array($post->ID, $checked_services) || $club_services == '')
                            $checked = 'checked';
                        ?>
                        <label class="club-service-label" for="dummy-club-service-<?php echo $post->ID; ?>">
                            <input type="checkbox"
                                   class="club-service"
                                   name="dummy-club-service[]"
                                   value="<?php echo $post->ID; ?>"
                                   id="dummy-club-service-<?php the_ID(); ?>"
                                <?php echo $checked; ?>/>
                            <?php the_title(); ?>
                        </label>
                    <?php } ?>
                    <?php wp_reset_query(); ?>
                </div>
                <script>
                    (function ($) {
                        var delimiter = '<?php echo Post_Type_Club::CLUB_MULTI_META_DELIMITER;?>';
                        $('input.club-service').change(function () {
                            updateClubServices();
                        });

                        function updateClubServices() {
                            var isAnyChecked = false;
                            var ids = [];
                            $('input.club-service').each(function () {
                                var $el = $(this);
                                if ($el.is(':checked')) {
                                    ids.push($el.val());
                                    isAnyChecked = true;
                                }
                            });
                            if (isAnyChecked) {
                                $('#club_services').val('##' + ids.join(delimiter) + '##');
                            } else {
                                $('#club_services').val('');
                            }
                        }

                        updateClubServices();
                    })(jQuery);
                </script>
            </td>
        </tr>
    <?php } ?>
</table>
<style>
    .club-program-label,
    .club-service-label {
        margin: 2px 0;
        display: block;
        padding: 2px 0;
        border-radius: 10px;
        font-size: 12px;;
    }

    .club-programs-wrapper,
    .club-services-wrapper {
        -webkit-column-count: 2;
        -webkit-column-gap: 5px;
        -webkit-column-fill: auto;
        -moz-column-count: 2;
        -moz-column-gap: 5px;
        -moz-column-fill: auto;
        column-count: 2;
        column-gap: 5px;
        column-fill: auto;
    }
</style>