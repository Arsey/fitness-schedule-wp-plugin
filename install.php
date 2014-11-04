<?php

function kiviSchedule_install() {
    global $wpdb, $kiviSchedule_settings;
    $table_create_query = 'CREATE TABLE IF NOT EXISTS ' . $kiviSchedule_settings['kiviSchedule_table'] . ' (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
   `time` TIME NOT NULL,
  `hall_id` INT(11) NOT NULL,
  `monday_program_id` INT(11),
`monday_program_status` INT(11),
`tuesday_program_id` INT(11),
`tuesday_program_status` INT(11),
`wednesday_program_id` INT(11),
`wednesday_program_status` INT(11),
`thursday_program_id` INT(11),
`thursday_program_status` INT(11),
`friday_program_id` INT(11),
`friday_program_status` INT(11),
`saturday_program_id` INT(11),
`saturday_program_status` INT(11),
`sunday_program_id` INT(11),
`sunday_program_status` INT(11),
  PRIMARY KEY (id) );';

    $wpdb->query($table_create_query);
}