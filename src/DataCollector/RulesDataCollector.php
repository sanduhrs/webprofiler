<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\RulesDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Collects data about rendered views.
 */
class RulesDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  const EXECUTED = 'executed';
  const NON_EXECUTED = 'non_executed';

  /**
   * Class constructor.
   */
  public function __construct() {
    $this->data['rules'] = array();
    $this->data['rules'][RulesDataCollector::EXECUTED] = array();
    $this->data['rules'][RulesDataCollector::NON_EXECUTED] = array();
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {

  }

  /**
   * @param \Drupal\Component\Plugin\ConfigurablePluginInterface $rule
   * @param boolean $executed
   */
  public function registerRuleExecution($rule, $executed) {
    if ($executed) {
      $this->data['rules'][RulesDataCollector::EXECUTED][] = $rule->getConfiguration();
    }
    else {
      $this->data['rules'][RulesDataCollector::NON_EXECUTED][] = $rule->getConfiguration();
    }
  }

  /**
   * @return int
   */
  public function getRulesExecutedCount() {
    return count($this->data['rules'][RulesDataCollector::EXECUTED]);
  }

  /**
   * @return int
   */
  public function getRulesNonExecutedCount() {
    return count($this->data['rules'][RulesDataCollector::NON_EXECUTED]);
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'rules';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Rules');
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return $this->t('Rules executed: @executed, rules not executed: @not_executed', array(
      '@executed' => $this->getRulesExecutedCount(),
      '@not_executed' => $this->getRulesNonExecutedCount()
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel() {
    $build = array();

    $build['data'] = [
      '#type' => 'inline_template',
      '#template' => '{{ data }}',
      '#context' => [
        'data' => print_r($this->data['rules'], TRUE),
      ],
    ];

    return $build;
  }

}
