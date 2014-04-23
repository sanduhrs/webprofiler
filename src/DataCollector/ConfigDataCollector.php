<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\ConfigDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\DrupalDataCollectorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Provides a datacollector to show all requested configs.
 */
class ConfigDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  /**
   * {@inheritdoc}
   */
  public function getMenu() {
    return \Drupal::translation()->translate('Config');
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    return \Drupal::translation()
      ->translate('Total config: @count', array('@count' => count($this->configNames())));
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'config';
  }

  /**
   * Registers a new requested config name.
   *
   * @param string $name
   *   The name of the config.
   */
  public function addConfigName($name) {
    $this->data['config_names'][] = $name;
    $this->data['config_names'] = array_unique($this->data['config_names']);
  }

  /**
   * Twig callback to display the config names.
   */
  public function configNames() {
    return $this->data['config_names'];
  }

} 
