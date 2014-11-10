jQuery(document).ready(function($) {
    $('.timePicker').timepicker({'timeFormat': 'H:i:s'}).on('changeTime', function() {
        var rowToSave = $(this).parents('tr');
        saveSchedule(rowToSave);
    });

    $('.schedule-table').tablesorter({
        textExtraction: function(node) {
            var $node = $(node);
            if ($node.parents('tr').hasClass('schedule-template'))
                return 0;

            var $input = $node.find('.timePicker');
            if ($input.length === 1) {
                var hms = $input.val();   // your input string
                var a = hms.split(':'); // split it at the colons

                // minutes are worth 60 seconds. Hours are worth 60 minutes.
                var seconds = (+a[0]) * 60 * 60 + (+a[1]) * 60 + (+a[2]);

                return seconds;
            }

            return 0;
        }
    });

    $('#advanced-sortables .inside').append('<div id="ajaxBusy"><img src="' + img_path.template_url + '/ajax-loader.gif"></div>');
    $('#select_cities').change(function() {
        var city_id = $(this).find('option:selected').val();
        $('#ajaxBusy').css('display', 'block');
        $.ajax({
            type: "POST",
            data: {
                action: 'fetch_clubs_by_city',
                kivischedule_city_id: city_id
            },
            url: ajaxurl,
            error: function(jqXHR, textStatus, errorThrown, response) {
                console.log(jqXHR);
                console.log(textStatus);
                console.log(errorThrown);
                console.log(response);
            },
            success: function(data) {
                var i = 0;
                var date = JSON.parse(data);
                var select_club_content;
                for (i in date) {
                    select_club_content += '<option value = "' + date[i]['club_id'] + '">' + date[i]['club_name'] + '</option>'
                }
                $('#select_clubs').html("");
                $('#select_halls').html("");
                $('#select_clubs').html(select_club_content);
                $('#ajaxBusy').css('display', 'none');
            }
        });
    }).click(function() {
        if ($('#select_cities option').length == 1) {
            $('#select_cities').change();
        }
    });
    $('#select_clubs').change(function() {
        $('#ajaxBusy').css('display', 'block');
        var club_id = $(this).find('option:selected').val();
        $.ajax({
            type: "POST",
            data: {
                action: 'fetch_hall_by_club',
                kivischedule_club_id: club_id
            },
            url: ajaxurl,
            error: function(jqXHR, textStatus, errorThrown, response) {
                console.log(jqXHR);
                console.log(textStatus);
                console.log(errorThrown);
                console.log(response);
            },
            success: function(data) {
                var i = 0;
                var date = JSON.parse(data);
                var select_club_content;
                for (i in date) {
                    select_club_content += '<option value = "' + date[i]['hall_id'] + '">' + date[i]['hall_name'] + '</option>'
                }
                if (date == "") {
                    select_club_content = ""
                }
                $('#select_halls').html("");
                $('#select_halls').html(select_club_content);
                $('#ajaxBusy').css('display', 'none');
            }
        });
    }).click(function() {
        if ($('#select_clubs option').length == 1) {
            $('#select_clubs').change();
        }
    });
    $('#select_halls').change(function() {
        var hall_id = $(this).find('option:selected').val();
        $.ajax({
            type: "POST",
            data: {
                action: 'fetch_schedule_data',
                kivischedule_hall_id: hall_id
            },
            url: ajaxurl,
            error: function(jqXHR, textStatus, errorThrown, response) {
                console.log(jqXHR);
                console.log(textStatus);
                console.log(errorThrown);
                console.log(response);
            },
            success: function(data) {
                var i = 0;
                //$('#add-new-chedule-row').css('display', 'inline-block');
              //  $('#schedule_table1').html(data);
            }
        });
    }).click(function() {
        if ($('#select_halls option').length == 1) {
            $('#select_halls').change();
        }
    });

    $('.save_sched_to_db').click(function() {
        var rowToSave = $(this).parents('tr');
        saveSchedule(rowToSave);
    });

    $('.schedule_program_select').change(function() {
        var rowToSave = $(this).parents('tr');
        saveSchedule(rowToSave);
    });
    $('.add-new-schedule-row').click(
            function() {
                var scheduleTable = $(this).parents('.hall-schedule').find('.schedule-table');
                var template = scheduleTable.find('.schedule-template');
                var templateToInsert = template.clone(true).removeClass('schedule-template');
                var table_row_first = $(this).parents('.hall-schedule').find('.schedule-table tbody tr:first-child');
                templateToInsert.insertBefore(table_row_first);
            });

    function saveSchedule(current_row) {
        var schedule_id = current_row.attr('data-schedule-id');
        var hall_id = current_row.attr('data-hall-id');


        $('#select_halls option:selected').val();

        var time = current_row.find('.timePicker').val();
        var sched_1 = current_row.find('.sched_1 option:selected').val();
        var sched_2 = current_row.find('.sched_2 option:selected').val();
        var sched_3 = current_row.find('.sched_3 option:selected').val();
        var sched_4 = current_row.find('.sched_4 option:selected').val();
        var sched_5 = current_row.find('.sched_5 option:selected').val();
        var sched_6 = current_row.find('.sched_6 option:selected').val();
        var sched_7 = current_row.find('.sched_7 option:selected').val();

        $.ajax({
            type: "POST",
            data: {
                action: 'save_schedule_data',
                schedule_id: schedule_id,
                kivischedule_time: time,
                kivischedule_hall_id: hall_id,
                kivischedule_sched_1: sched_1,
                kivischedule_sched_2: sched_2,
                kivischedule_sched_3: sched_3,
                kivischedule_sched_4: sched_4,
                kivischedule_sched_5: sched_5,
                kivischedule_sched_6: sched_6,
                kivischedule_sched_7: sched_7
            },
            url: ajaxurl,
            error: function(jqXHR, textStatus, errorThrown, response) {
                console.log(jqXHR);
                console.log(textStatus);
                console.log(errorThrown);
                console.log(response);
            },
            success: function(data) {
                current_row.attr('data-schedule-id', data);

                // TODO: trigger sort
                var $table = current_row.parents('.schedule-table');
                $table.trigger('update');
                var sorting = [[0, 0]];
                $table.trigger('sorton', [sorting]);
                $table.find('.headerSortDown').trigger('click');
            }
        });
    }
    $('.delete-schedule-row').click(
            function() {
                console.log('here');
                var current_row = $(this).parents('tr');
                var schedule_id = current_row.attr('data-schedule-id');
                current_row.remove();
                $.ajax({
                    type: "POST",
                    data: {
                        action: 'remove_schedule',
                        schedule_id: schedule_id
                    },
                    url: ajaxurl,
                    error: function(jqXHR, textStatus, errorThrown, response) {
                        console.log(jqXHR);
                        console.log(textStatus);
                        console.log(errorThrown);
                        console.log(response);
                    },
                    success: function(data) {
                console.log(data);
                    }
                });
            }
    )
    $('#add-new-chedule-row').click(function() {
        //var table_row = '<tr> <td><input type="text" name="program_time"></td> <td><input type="text" name="mon_programm"></td> <td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>'
        $('.db_add_row').css('display', 'table-row');
    });
});