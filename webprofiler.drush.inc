<?php

/**
 * Implements hook_drush_command().
 */
function webprofiler_drush_command() {

  $items['drush-webprofiler-statistics'] = array(
    'description' => 'Run webprofiler on an url to gather statistics.',
    'aliases' => array('dws'),
    'arguments' => array(
      'route' => 'The route to benchmark.',
    ),
    'options' => array(
      'count' => 'The number of request counts.',
      'purge' => 'Purge profiles after benchmarking.',
      'cache' => 'Clear cache before run.',
    ),
  );

  return $items;
}

/**
 *
 */
function drush_webprofiler_drush_webprofiler_statistics($route) {
  $count = drush_get_option('count', 1);
  $purge = drush_get_option('purge', TRUE);
  $cache = drush_get_option('cache', TRUE);

  drupal_set_message($route);
  drupal_set_message($count);
  drupal_set_message($purge);
  drupal_set_message($cache);
}
