<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\RoutingDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\Component\Utility\String;
use Drupal\Core\Routing\RouteProviderInterface;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Provides a data collector which shows all available routes.
 */
class RoutingDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  /**
   * The route profiler.
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  /**
   * {@inheritdoc}
   */
  public function getMenu() {
    return \Drupal::translation()->translate('Routing');
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    return \Drupal::translation()->translate('Defined routes: @route', array('@route' => count($this->routing())));
  }

  /**
   * Constructs a new RoutingDataCollector.
   *
   * @param \Drupal\Core\Routing\RouteProviderInterface $route_provider
   *   The route provider.
   */
  public function __construct(RouteProviderInterface $route_provider) {
    $this->routeProvider = $route_provider;
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
  public function getName() {
    return 'routing';
  }

} 
