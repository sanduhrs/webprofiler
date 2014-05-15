<?php

namespace Drupal\webprofiler;

/**
 * Interface DrupalDataCollectorInterface
 */
interface DrupalDataCollectorInterface {

  /**
   * @return string
   */
  public function getTitle();

  /**
   * @return string
   */
  public function getPanelSummary();

  /**
   * @return bool
   */
  public function hasPanel();

  /**
   * @return array
   */
  public function getPanel();

  /**
   * Returns the name of the collector.
   *
   * @return string The collector name
   */
  public function getName();

}
