<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\EventDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpKernel\DataCollector\EventDataCollector as BaseEventDataCollector;

/**
 * Class EventDataCollector
 */
class EventDataCollector extends BaseEventDataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Events');
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel() {
    $calledListeners = $this->getCalledListeners();
    $notCalledListeners = $this->getNotCalledListeners();

    $build = array();

    if (empty($calledListeners)) {
      $build['no-events'] = array(
        '#markup' => $this->t('No events have been recorded. Are you sure that debugging is enabled in the kernel?'),
      );

      return $build;
    }

    // Called listeners
    $build['called'] = $this->getTable($this->t('Called listeners'), $calledListeners);

    // Non called listeners
    $build['non-called'] = $this->getTable($this->t('Non called listeners'), $notCalledListeners);

    return $build;
  }

  /**
   * @param $title
   * @param $listeners
   *
   * @return mixed
   */
  private function getTable($title, $listeners) {
    $rows = array();
    foreach ($listeners as $listener) {
      $row = array();
      $row[] = $listener['event'];

      if ($listener['type'] == 'Method') {
        $link = '<a href="' . $this->getFileLink($listener['file'], $listener['line']) . '">' . $listener['method'] . '</a>';
        $row[] = $this->abbrClass($listener['class']) . '::' . $link;
      }
      else {
        $row[] = 'Closure';
      }

      $rows[] = $row;
    }

    return array(
      array(
        '#markup' => '<h3>' . $title . '</h3>',
      ),
      array(
        '#theme' => 'table',
        '#rows' => $rows,
        '#header' => array(
          $this->t('Event name'),
          $this->t('Listener'),
        ),
        //'#sticky' => TRUE,
      )
    );
  }

  /**
   * Returns the link for a given file/line pair.
   *
   * @param string $file
   *   An absolute file path
   * @param integer $line
   *   The line number
   *
   * @return string
   *   A link of false
   */
  private function getFileLink($file, $line) {
    $fileLinkFormat = 'txmt://open?url=file://%f&line=%l';

    if (is_file($file)) {
      return strtr($fileLinkFormat, array('%f' => $file, '%l' => $line));
    }

    return FALSE;
  }
}
