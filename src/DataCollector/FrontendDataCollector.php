<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\FrontendDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Collects data about frontend performance.
 */
class FrontendDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {

  }

  /**
   * @param $data
   */
  public function setData($data) {
    $this->data['performance'] = $data;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'frontend';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Frontend');
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel() {
    $build = array();

    $build['title'] = array(
      '#type' => 'inline_template',
      '#template' => '<h3>{{ message }}</h3>',
      '#context' => array(
        'message' => $this->t('Timing API data'),
      )
    );

    $cssHeader = array(
      'metric',
      'value',
    );

    $rows = array();
    $navigationStart = $this->data['performance']['navigationStart'];
    foreach ($this->data['performance'] as $metric => $value) {
      $row = array();

      $row[] = $metric;
      $row[] = $value - $navigationStart . ' ms';

      $rows[] = $row;
    }

    $build['table'] = array(
      '#type' => 'table',
      '#rows' => $rows,
      '#header' => $cssHeader,
      '#sticky' => TRUE,
    );

    return $build;
  }
}
