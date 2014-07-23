<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class ServiceDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * @var \Symfony\Component\DependencyInjection\ContainerInterface $container
   */
  private $container;

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   */
  public function __construct(ContainerInterface $container) {
    $this->container = $container;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $this->data['initialized_services'] = array();
    if ($this->getServicesCount()) {
      foreach (array_keys($this->getServices()) as $id) {
        if ($this->container->initialized($id)) {
          $this->data['initialized_services'][] = $id;
        }
      }
    }
  }

  /**
   * @param $graph
   */
  public function setServicesGraph($graph) {
    $this->data['graph'] = $graph;
  }

  /**
   * @return int
   */
  public function getServicesCount() {
    return count($this->data['graph']);
  }

  /**
   * @return int
   */
  public function getInitializedServicesCount() {
    return count($this->data['initialized_services']);
  }

  /**
   * @return array
   */
  public function getServices() {
    return $this->data['graph'];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'service';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Service');
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return $this->t('Services: @count', array('@count' => $this->getInitializedServicesCount()));
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel() {
    $build = array();

    if ($this->getServicesCount()) {
      $rows = array();
      $services = $this->getServices();
      ksort($services);

      foreach ($services as $id => $service) {
        $row = array();

        $row[] = $id;
        $row[] = $service['value']['class'];

        $edges = array();
        foreach($service['outEdges'] AS $edge) {
          $edges[] = $edge['id'];
        }

        $row[] = in_array($id, $this->data['initialized_services']) ? $this->t('Yes') : $this->t('No');

        $row[] = implode(', ', $edges);

        $tags = array();
        foreach($service['value']['tags'] AS $tag => $value) {
          $tags[] = $tag;
        }

        $row[] = implode(', ', $tags);

        $rows[] = $row;
      }

      $header = array(
        $this->t('Id'),
        $this->t('Class'),
        $this->t('Initialized'),
        $this->t('Depends on'),
        $this->t('Tags'),
      );

      $build['table'] = array(
        '#theme' => 'table',
        '#rows' => $rows,
        '#header' => $header,
      );
    }

    return $build;
  }

}
