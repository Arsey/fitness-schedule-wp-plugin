<?php

register_post_type('kiviSchedule_clubs', array(
    'labels' => array(
        'name' => __('Club'),
        'singular_name' => __('Club')
    ),
    'public' => true,
    'has_archive' => true,
    'supports' => array('title', 'thumbnail', 'revisions'),
        )
);
register_post_type('kiviSchedule_towns', array(
    'labels' => array(
        'name' => __('Town'),
        'singular_name' => __('Town')
    ),
    'public' => true,
    'has_archive' => true,
    'supports' => array('title', 'revisions'),
        )
);

register_post_type('kiviSchedule_program', array(
    'labels' => array(
        'name' => __('Program'),
        'singular_name' => __('Program')
    ),
    'public' => true,
    'has_archive' => true,
    'supports' => array('title',
        'editor', 'thumbnail', 'revisions'),
        )
);
register_post_type('kiviSchedule_team', array(
    'labels' => array(
        'name' => __('Team'),
        'singular_name' => __('Team')
    ),
    'public' => true,
    'has_archive' => true,
    'supports' => array('title',
        'editor', 'thumbnail', 'revisions'),
        )
);

