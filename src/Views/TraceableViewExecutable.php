<?php

namespace Drupal\webprofiler\Views;

use Drupal\views\ViewExecutable;

/**
 * Class TraceableViewExecutable
 */
class TraceableViewExecutable extends ViewExecutable {

  /**
   * {@inheritdoc}
   */
  public function executeDisplay($display_id = NULL, $args = array()) {
    return parent::executeDisplay($display_id, $args);
  }
}
