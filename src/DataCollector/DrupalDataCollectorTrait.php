<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\Component\Utility\String;

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
   * {@inheritdoc}
   */
  public function getData() {
    return $this->data;
  }

  /**
   * Builds a simple key/value table.
   *
   * @param string $title
   *   The title of the table.
   * @param array $values
   *   The array of values for the table.
   * @param array $header
   *   The array of header values for the table.
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
        '#type' => 'inline_template',
        '#template' => '<h3>{{ title }}</h3>',
        '#context' => array(
          'title' => $title,
        ),
      );
    }

    $build['table'] = array(
      '#type' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#sticky' => TRUE,
    );

    return $build;
  }

}
