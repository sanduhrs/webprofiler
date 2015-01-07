<?php

namespace Drupal\webprofiler\Plugin\RulesExpression;

use Drupal\rules\Engine\RulesState;
use Drupal\rules\Plugin\RulesExpression\Rule;
use Drupal\rules\Plugin\RulesExpressionPluginManager;
use Drupal\webprofiler\DataCollector\RulesDataCollector;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TraceableRule
 */
class TraceableRule extends Rule {

  private $collector;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, RulesExpressionPluginManager $expression_manager, RulesDataCollector $collector) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $expression_manager);

    $this->collector = $collector;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('plugin.manager.rules_expression'),
      $container->get('webprofiler.rules')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function executeWithState(RulesState $state) {
    // Evaluate the rule's conditions.
    if (!$this->conditions->executeWithState($state)) {
      $this->collector->registerRuleExecution($this, FALSE);

      // Do not run the actions if the conditions are not met.
      return;
    }
    $this->actions->executeWithState($state);

    $this->collector->registerRuleExecution($this, TRUE);
  }

}
