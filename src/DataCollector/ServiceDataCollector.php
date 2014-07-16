<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class ServiceDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {

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
    return $this->t('Services: @count', array('@count' => $this->getServicesCount()));
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
        $this->t('Depends by'),
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
