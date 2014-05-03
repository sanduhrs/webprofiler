<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\TimeDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Component\Utility\String;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpKernel\DataCollector\TimeDataCollector as BaseTimeDataCollector;
use Symfony\Component\Stopwatch\StopwatchEvent;

/**
 * Class TimeDataCollector
 */
class TimeDataCollector extends BaseTimeDataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * {@inheritdoc}
   */
  public function getMenu() {
    return $this->t('Timeline');
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel() {
    $rows = array(
      array(
        $this->t('Total time'),
        String::format('!duration ms', array('!duration' => sprintf('%.0f', $this->getDuration()))),
      ),
      array(
        $this->t('Initialization time'),
        String::format('!duration ms', array('!duration' => sprintf('%.0f', $this->getInitTime()))),
      ),
    );

    return array(
      '#theme' => 'table',
      '#rows' => $rows,
      '#attached' => array(
        'js' => array(
          array(
            'data' => array('webprofiler' => $this->getAttachedJs()),
            'type' => 'setting'
          ),
        ),
        'library' => array(
          'webprofiler/d3',
        ),
      ),
    );
  }

  /**
   * {@inheritdoc}
   */
  private function getAttachedJs() {
    /** @var StopwatchEvent[] $collectedEvents */
    $collectedEvents = $this->getEvents();
    $section_periods = $collectedEvents['__section__']->getPeriods();
    $endTime = end($section_periods)->getEndTime();
    $events = array();

    foreach ($collectedEvents as $key => $collectedEvent) {
      if ('__section__' != $key) {
        $periods = array();
        foreach ($collectedEvent->getPeriods() as $period) {
          $periods[] = array(
            'start' => sprintf("%F", $period->getStartTime()),
            'end' => sprintf("%F", $period->getEndTime()),
          );
        }

        $events[] = array(
          "name" => $key,
          "category" => $collectedEvent->getCategory(),
          "origin" => sprintf("%F", $collectedEvent->getOrigin()),
          "starttime" => sprintf("%F", $collectedEvent->getStartTime()),
          "endtime" => sprintf("%F", $collectedEvent->getEndTime()),
          "duration" => sprintf("%F", $collectedEvent->getDuration()),
          "memory" => sprintf("%.1F", $collectedEvent->getMemory() / 1024 / 1024),
          "periods" => $periods,
        );
      }
    }

    return array('time' => array('events' => $events, 'endtime' => $endTime));
  }

}
