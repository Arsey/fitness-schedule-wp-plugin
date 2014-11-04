jQuery(document).ready(function($) {
    $('.timePicker').timepicker({'timeFormat': 'H:i:s'});
    $('#select_cities').change(function() {
        var city_id = $(this).find('option:selected').val();
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
            }
        });
    }).click(function() {
        if ($('#select_cities option').length == 1) {
            $('#select_cities').change();
        }
    });
    $('#select_clubs').change(function() {
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
                console.log(data);
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
                console.log(data);
                $('#add-new-chedule-row').css('display', 'inline-block');
                $('#schedule_table1').html(data);
            }
        });
    }).click(function() {
        if ($('#select_halls option').length == 1) {
            $('#select_halls').change();
        }
    });
    $('.save_sched_to_db').click(function() {
        var current_row = $(this).parents('tr');
        var time = current_row.find('.timePicker').val();
        var sched_1 = current_row.find('.sched_1 option:selected').val();
        var sched_2 = current_row.find('.sched_2 option:selected').val();
        var sched_3 = current_row.find('.sched_3 option:selected').val();
        var sched_4 = current_row.find('.sched_4 option:selected').val();
        var sched_5 = current_row.find('.sched_5 option:selected').val();
        var sched_6 = current_row.find('.sched_6 option:selected').val();
        var sched_7 = current_row.find('.sched_7 option:selected').val();
        var hall_id = $('#select_halls option:selected').val();
        $.ajax({
            type: "POST",
            data: {
                action: 'save_schedule_data',
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
                console.log(data);
            }
        });
    });
    $('#add-new-chedule-row').click(function() {
        //var table_row = '<tr> <td><input type="text" name="program_time"></td> <td><input type="text" name="mon_programm"></td> <td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>'
        $('.db_add_row').css('display', 'table-row');
    })


});