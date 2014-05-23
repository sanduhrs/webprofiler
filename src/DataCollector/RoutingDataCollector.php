<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\RoutingDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Component\Utility\String;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Provides a data collector which shows all available routes.
 */
class RoutingDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * The route profiler.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * Constructs a new RoutingDataCollector.
   *
   * @param \Drupal\Core\Routing\RouteProviderInterface $routeProvider
   *   The route provider.
   */
  public function __construct(RouteProviderInterface $routeProvider) {
    $this->routeProvider = $routeProvider;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $this->data['routing'] = array();
    foreach ($this->routeProvider->getAllRoutes() as $route_name => $route) {
      // @TODO Find a better visual representation.
      $this->data['routing'][String::checkPlain($route_name)] = $route->getPath();
    }
  }

  /**
   * Twig callback for displaying the routes.
   */
  public function routing() {
    return $this->data['routing'];
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Routing');
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'routing';
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return $this->t('Defined routes: @route', array('@route' => count($this->routing())));
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel() {
    $build = array();

    // Routing
    $build['routing'] = $this->getTable($this->t('Available routes'), $this->routing(), array($this->t('Route name'), 'URL'));

    return $build;
  }

} 
