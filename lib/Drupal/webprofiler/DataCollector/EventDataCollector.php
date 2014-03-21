<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\EventDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\DrupalDataCollectorInterface;
use Symfony\Component\HttpKernel\DataCollector\EventDataCollector as BaseEventDataCollector;

class EventDataCollector extends BaseEventDataCollector implements DrupalDataCollectorInterface {

  /**
   * {@inheritdoc}
   */
  public function getMenu() {
    return \Drupal::translation()->translate('Events');
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    return NULL;
  }


}
