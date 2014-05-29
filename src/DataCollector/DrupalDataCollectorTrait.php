<?php

namespace Drupal\webprofiler\DataCollector;

/**
 * Class DrupalDataCollectorTrait
 */
trait DrupalDataCollectorTrait {

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function hasPanel() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel() {
    return NULL;
  }

  /**
   * @param $title
   * @param $values
   * @param $header
   *
   * @return mixed
   */
  private function getTable($title, $values, $header) {
    $rows = array();
    foreach ($values as $key => $value) {
      $row = array();

      $row[] = $key;
      $row[] = print_r($value, TRUE);

      $rows[] = $row;
    }

    if ($title) {
      $build['title'] = array(
        array(
          '#markup' => '<h3>' . $title . '</h3>',
        ),
      );
    }

    $build['table'] = array(
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    );

    return $build;
  }

  /**
   * @param $class
   *
   * @return string
   */
  private function abbrClass($class) {
    $parts = explode('\\', $class);
    $short = array_pop($parts);

    return sprintf("<abbr title=\"%s\">%s</abbr>", $class, $short);
  }
}
