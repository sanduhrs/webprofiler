<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\Component\Utility\Timer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class TimerDataCollector extends DataCollector {

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
    // Timer::start('page') was called in bootstrap.inc
    $this->data['timer'] = Timer::read('page');
  }

  /**
   * @return float
   */
  public function getTimer() {
    return $this->data['timer'];
  }

  /**
   * Returns the name of the collector.
   *
   * @return string The collector name
   *
   * @api
   */
  public function getName() {
    return 'timer';
  }
}
