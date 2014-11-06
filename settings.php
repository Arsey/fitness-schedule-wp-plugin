<?php

return array(
    'required_php_version' => '5.3.0',
    'sdam_folder' => 'scheduleplugin',
    'path_to_kivi_schedule_folder' => realpath(__DIR__),
    'kivi_chedule_root_url' => plugins_url('', __FILE__),
    'kivi_schedule_table' => 'wp_db_kivi_schedule'
);