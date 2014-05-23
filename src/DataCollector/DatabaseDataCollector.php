<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Database\Connection;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Class DatabaseDataCollector
 */
class DatabaseDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * @param \Drupal\Core\Database\Connection $database
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $queries = $this->database->getLogger()->get('webprofiler');
    usort($queries, array("Drupal\\webprofiler\\DataCollector\\DatabaseDataCollector", "orderQuery"));

    foreach ($queries as &$query) {
      // remove caller
      unset($query['caller']['args']);
    }

    $this->data['queries'] = $queries;

    $options = $this->database->getConnectionOptions();

    // remove password field for security
    unset($options['password']);

    $this->data['database'] = $options;
  }

  /**
   * @param $a
   * @param $b
   *
   * @return int
   */
  private function orderQuery($a, $b) {
    $at = $a['time'];
    $bt = $b['time'];

    if ($at == $bt) {
      return 0;
    }
    return ($at < $bt) ? 1 : -1;
  }

  /**
   * @return array
   */
  public function getDatabase() {
    return $this->data['database'];
  }

  /**
   * @return int
   */
  public function getQueryCount() {
    return count($this->data['queries']);
  }

  /**
   * @return array
   */
  public function getQueries() {
    return $this->data['queries'];
  }

  /**
   * @return float
   */
  public function getTime() {
    $time = 0;

    foreach ($this->data['queries'] as $query) {
      $time += $query['time'];
    }

    return $time;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'database';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Database');
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return $this->t('Executed queries: @count', array('@count' => $this->getQueryCount()));
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel() {
    $build = array();

    foreach ($this->getQueries() as $query) {
      $table = $this->getTable('Query arguments', $query['args'], array());

      $build[] = array(
        '#theme' => 'webprofiler_db_panel',
        '#query' => $query,
        '#table' => $table,
      );
    }

    return $build;
  }
}
