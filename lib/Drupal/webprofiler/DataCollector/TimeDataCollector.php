<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\TimeDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\DrupalDataCollectorInterface;
use Symfony\Component\HttpKernel\DataCollector\TimeDataCollector as BaseTimeDataCollector;

class TimeDataCollector extends BaseTimeDataCollector implements DrupalDataCollectorInterface {

  /**
   * {@inheritdoc}
   */
  public function getMenu() {
    return \Drupal::translation()->translate('Timeline');
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    return NULL;
  }

}
