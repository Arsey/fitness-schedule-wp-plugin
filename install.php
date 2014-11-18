<?php

function kivi_schedule_install() {
    global $wpdb, $kivi_schedule_settings;
    $table_create_query = 'CREATE TABLE IF NOT EXISTS ' . $kivi_schedule_settings['kivi_schedule_table'] . ' (
   `time` TIME NOT NULL,
     `id` INT(11) NOT NULL AUTO_INCREMENT,
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
  PRIMARY KEY ( `id`),
   CONSTRAINT PK_time_hall_id UNIQUE (`time`, `hall_id`));';

    $wpdb->query($table_create_query);
}