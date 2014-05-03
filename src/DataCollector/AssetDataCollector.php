<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\AssetDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Collects data about the used assets (CSS/JS).
 */
class AssetDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * {@inheritdoc}
   */
  public function getMenu() {
    return $this->t('Assets');
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    return $this->t('Total assets: @count', array('@count' => ($this->getCssCount() + $this->getJsCount())));
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
    return isset($this->data['js']['settings']) ? json_encode($this->data['js']['settings']['data']) : '';
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel() {
    $build = array();

    // CSS
    if (count($this->getCssFiles()) > 0) {
      $build['css'] = $this->getTable('CSS', $this->getCssFiles(), array($this->t('id'), $this->t('file')));
    }

    // Js
    if (count($this->getJsFiles()) > 0) {
      $build['js'] = $this->getTable('JS', $this->getJsFiles(), array($this->t('id'), $this->t('file')));
    }

    // Js settings
    if (isset($this->data['js']['settings'])) {
      $build['js-settings'] = array(
        array(
          '#markup' => '<h3>' . $this->t('JS settings') . '</h3>',
        ),
        array(
          '#markup' => '<textarea style="width:100%; height:400px">' . json_encode($this->data['js']['settings']['data'], JSON_PRETTY_PRINT) . '</textarea>',
        ),
      );
    }

    return $build;
  }
}
