<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Database\Connection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class DatabaseDataCollector extends DataCollector {

  private $database;

  /**
   * @param Connection $database
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * Collects data for the given Request and Response.
   *
   * @param Request $request A Request instance
   * @param Response $response A Response instance
   * @param \Exception $exception An Exception instance
   *
   * @api
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
   * Returns the name of the collector.
   *
   * @return string The collector name
   *
   * @api
   */
  public function getName() {
    return 'database';
  }
}
