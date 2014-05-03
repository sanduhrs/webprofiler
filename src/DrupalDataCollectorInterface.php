<?php

namespace Drupal\webprofiler;

/**
 * Interface DrupalDataCollectorInterface
 */
interface DrupalDataCollectorInterface {

  /**
   * @return mixed
   */
  public function getMenu();

  /**
   * @return mixed
   */
  public function getSummary();

  /**
   * @return mixed
   */
  public function getPanel();

}
