<?php

namespace Drupal\webprofiler\DataCollector;

trait DrupalDataCollectorTrait {

  /**
   * {@inheritdoc}
   */
  public function getMenu() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    return NULL;
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
      $row[] = (is_array($value)) ? implode(', ', $value) : $value;

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
}
