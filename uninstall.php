<?php

function drop_kiviSchedule_tables() {
    global $wpdb, $kiviSchedule_setting;
    $queryToDropTables = 'DROP TABLE IF EXISTS ' . $kiviSchedule_settings['kiviSchedule_table'] . ';';
    $wpdb->query($queryToDropTables);
}