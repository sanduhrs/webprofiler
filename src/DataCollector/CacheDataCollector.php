<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\CacheDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Collects the used cache bins and cache CIDs.
 */
class CacheDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  const WEBPROFILER_CACHE_HIT = 'bin_cids_hit';
  const WEBPROFILER_CACHE_MISS = 'bin_cids_miss';

  /**
   * Registers a cache get on a specific cache bin.
   */
  public function registerCache($bin, $cid, $type) {
    $this->data[$bin][$type][$cid] = isset($this->data[$bin][$type][$cid]) ? $this->data[$bin][$type][$cid] + 1 : 1;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
  }

  /**
   * Callback to return the total amount of requested cache CIDS.
   *
   * @return int
   */
  public function countCacheCids($type) {
    $total_count = 0;
    foreach ($this->data as $bin) {
      if (array_key_exists($type, $bin)) {
        $total_count += count($bin[$type]);
      }
    }
    return $total_count;
  }

  /**
   * Callback to return the total amount of hit cache CIDS.
   *
   * @return int
   */
  public function countCacheHits() {
    return $this->countCacheCids(CacheDataCollector::WEBPROFILER_CACHE_HIT);
  }

  /**
   * Callback to return the total amount of miss cache CIDS.
   *
   * @return int
   */
  public function countCacheMisses() {
    return $this->countCacheCids(CacheDataCollector::WEBPROFILER_CACHE_MISS);
  }

  /**
   * Callback to return all registered cache CIDs keyed by bin.
   *
   * @return array
   */
  public function cacheCids($type) {
    $cache = array();
    foreach ($this->data as $key => $bin) {
      if (array_key_exists($type, $bin)) {
        $cache[$key] = $bin[$type];
      }
    }

    return $cache;
  }

  /**
   * Callback to return hit registered cache CIDs keyed by bin.
   *
   * @return array
   */
  public function cacheHits() {
    return $this->cacheCids(CacheDataCollector::WEBPROFILER_CACHE_HIT);
  }

  /**
   * Callback to return miss registered cache CIDs keyed by bin.
   *
   * @return array
   */
  public function cacheMisses() {
    return $this->cacheCids(CacheDataCollector::WEBPROFILER_CACHE_MISS);
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'cache';
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Cache');
  }

  /**
   * {@inheritdoc}
   */
  public function getPanelSummary() {
    return $this->t('Total cache hit: @cache_hit, total cache miss: @cache_miss', array(
      '@cache_hit' => $this->countCacheCids(CacheDataCollector::WEBPROFILER_CACHE_HIT),
      '@cache_miss' => $this->countCacheCids(CacheDataCollector::WEBPROFILER_CACHE_MISS)
    ));
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel() {
    $build = array();

    foreach ($this->data as $key => $bin) {
      $rows = array();
      $totalNum = 0;

      if (array_key_exists(CacheDataCollector::WEBPROFILER_CACHE_HIT, $bin)) {
        foreach ($bin[CacheDataCollector::WEBPROFILER_CACHE_HIT] as $cid => $num) {
          $row = array();

          $row[] = $cid;
          $row[] = $num;
          $row[] = '-';

          $rows[] = $row;

          $totalNum += $num;
        }
      }

      if (array_key_exists(CacheDataCollector::WEBPROFILER_CACHE_MISS, $bin)) {
        foreach ($bin[CacheDataCollector::WEBPROFILER_CACHE_MISS] as $cid => $num) {
          $row = array();

          $row[] = $cid;
          $row[] = '-';
          $row[] = $num;

          $rows[] = $row;

          $totalNum += $num;
        }
      }

      $header = array(
        array(
          'data' => $this->t('cid'),
          'class' => array('cache-data-cid'),
        ),
        array(
          'data' => $this->t('hits'),
          'class' => array('cache-data-hit'),
        ),
        array(
          'data' => $this->t('misses'),
          'class' => array('cache-data-miss'),
        ),
      );

      $build[$key . '_title'] = array(
        array(
          '#markup' => '<h3>' . $key . ' (' . $totalNum . ')' . '</h3>',
        ),
      );

      $build[$key] = array(
        '#theme' => 'table',
        '#rows' => $rows,
        '#header' => $header,
        '#attributes' => array('class' => array('cache-data')),
      );
    }

    return $build;
  }
}
