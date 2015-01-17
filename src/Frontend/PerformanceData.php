<?php

namespace Drupal\webprofiler\Frontend;

/**
 * Class PerformanceData
 */
class PerformanceData {

  /**
   * @var array
   */
  private $data;

  /**
   * @param array $data
   */
  public function __construct($data) {
    $this->data = $data;
  }

  /**
   * @return int
   */
  public function getDNSTiming() {
    return $this->data['domainLookupEnd'] - $this->data['domainLookupStart'];
  }

  /**
   * @return int
   */
  public function getTCPTiming() {
    return $this->data['connectEnd'] - $this->data['connectStart'];
  }

  /**
   * @return int
   */
  public function getTtfbTiming() {
    return $this->data['responseStart'] - $this->data['connectEnd'];
  }

  /**
   * @return int
   */
  public function getDataTiming() {
    return $this->data['responseEnd'] - $this->data['responseStart'];
  }

  /**
   * @return int
   */
  public function getDomTiming() {
    return $this->data['loadEventStart'] - $this->data['responseEnd'];
  }

}
