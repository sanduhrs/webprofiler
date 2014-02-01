<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\StateDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\KeyValueStore\StateInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Provides a data collector to get all requested state values.
 */
class StateDataCollector extends DataCollector implements StateInterface {

  /**
   * The state service.
   *
   * @var \Drupal\Core\KeyValueStore\StateInterface
   */
  protected $state;

  /**
   * Constructs a new StateDataCollector.
   *
   * @param \Drupal\Core\KeyValueStore\StateInterface $state
   *   The state service.
   */
  public function __construct(StateInterface $state) {
    $this->state = $state;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
  }

  /**
   * Twig callback to show all requested state keys.
   */
  public function stateKeys() {
    return $this->data['state_get'];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'state';
  }

  /**
   * {@inheritdoc}
   */
  public function get($key, $default = NULL) {
    $result = $this->state->get($key, $default);
    $this->data['state_get'][] = $key;
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function getMultiple(array $keys) {
    return $this->state->getMultiple($keys);
  }

  /**
   * {@inheritdoc}
   */
  public function set($key, $value) {
    return $this->state->set($key, $value);
  }

  /**
   * {@inheritdoc}
   */
  public function setMultiple(array $data) {
    return $this->state->setMultiple($data);
  }

  /**
   * {@inheritdoc}
   */
  public function delete($key) {
    return $this->state->delete($key);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteMultiple(array $keys) {
    return $this->state->resetCache();
  }

  /**
   * {@inheritdoc}
   */
  public function resetCache() {
    return $this->state->resetCache();
  }

} 
