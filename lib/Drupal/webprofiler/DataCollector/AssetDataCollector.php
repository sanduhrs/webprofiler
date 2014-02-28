<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\AssetDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\DrupalDataCollectorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Collects data about the used assets (CSS/JS).
 */
class AssetDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  /**
   * {@inheritdoc}
   */
  public function getMenu() {
    return \Drupal::translation()->translate('Assets');
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    return \Drupal::translation()
      ->translate('Total assets: @count', array('@count' => ($this->getCssCount() + $this->getJsCount())));
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $this->data['js'] = _drupal_add_js();
    $this->data['css'] = _drupal_add_css();
  }

  /**
   * Returns the name of the collector.
   *
   * @return string The collector name
   *
   * @api
   */
  public function getName() {
    return 'asset';
  }

  /**
   * Twig callback to return the amount of CSS files.
   */
  public function getCssCount() {
    return count($this->data['css']);
  }

  /**
   * Twig callback to return the CSS files.
   */
  public function getCssFiles() {
    $result = array();
    foreach ($this->data['css'] as $option) {
      $result[] = $option['data'];
    }
    return $result;
  }

  /**
   * Twig callback to return the amount of JS files.
   */
  public function getJsCount() {
    return count($this->data['js']);
  }

  /**
   * Twig callback to return the JS files.
   */
  public function getJsFiles() {
    $result = array();
    foreach ($this->data['js'] as $option) {
      if ($option['type'] != 'setting') {
        $result[] = $option['data'];
      }
    }
    return $result;
  }

  /**
   * Twig callback to return the JS settings.
   */
  public function getJsSettings() {
    return json_encode($this->data['js']['settings']['data']);
  }

}
