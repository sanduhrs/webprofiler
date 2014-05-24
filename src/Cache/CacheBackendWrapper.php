<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Cache\CacheBackendWrapper.
 */

namespace Drupal\webprofiler\Cache;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\webprofiler\DataCollector\CacheDataCollector;

/**
 * Wraps an existing cache backend to track calls to the cache backend.
 */
class CacheBackendWrapper implements CacheBackendInterface {

  /**
   * The data collector to register the calls.
   *
   * @var \Drupal\webprofiler\DataCollector\CacheDataCollector
   */
  protected $cacheDataCollector;

  /**
   * The wrapped cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The name of the wrapped cache bin.
   *
   * @var string
   */
  protected $bin;

  /**
   * Constructs a new CacheBackendWrapper.
   *
   * @param \Drupal\webprofiler\DataCollector\CacheDataCollector $cacheDataCollector
   *   The cache data collector to inform about cache get calls.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   The wrapped cache backend.
   * @param string $bin
   *   The name of the wrapped cache bin.
   */
  public function __construct(CacheDataCollector $cacheDataCollector, CacheBackendInterface $cacheBackend, $bin) {
    $this->cacheDataCollector = $cacheDataCollector;
    $this->cacheBackend = $cacheBackend;
    $this->bin = $bin;
  }

  /**
   * {@inheritdoc}
   */
  public function get($cid, $allow_invalid = FALSE) {
    $cache = $this->cacheBackend->get($cid, $allow_invalid);

    if ($cache) {
      $this->cacheDataCollector->registerCache($this->bin, $cid, CacheDataCollector::WEBPROFILER_CACHE_HIT);
    }
    else {
      $this->cacheDataCollector->registerCache($this->bin, $cid, CacheDataCollector::WEBPROFILER_CACHE_MISS);
    }

    return $cache;
  }

  /**
   * {@inheritdoc}
   */
  public function getMultiple(&$cids, $allow_invalid = FALSE) {
    foreach ($cids as $cid) {
      $this->cacheDataCollector->registerCache($this->bin, $cid, CacheDataCollector::WEBPROFILER_CACHE_HIT);
    }
    return $this->cacheBackend->getMultiple($cids, $allow_invalid);
  }

  /**
   * {@inheritdoc}
   */
  public function set($cid, $data, $expire = Cache::PERMANENT, array $tags = array()) {
    return $this->cacheBackend->set($cid, $data, $expire, $tags);
  }

  /**
   * {@inheritdoc}
   */
  public function setMultiple(array $items) {
    return $this->cacheBackend->setMultiple($items);
  }

  /**
   * {@inheritdoc}
   */
  public function delete($cid) {
    return $this->cacheBackend->delete($cid);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteMultiple(array $cids) {
    return $this->cacheBackend->deleteMultiple($cids);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteTags(array $tags) {
    return $this->cacheBackend->deleteTags($tags);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll() {
    return $this->cacheBackend->deleteAll();
  }

  /**
   * {@inheritdoc}
   */
  public function invalidate($cid) {
    return $this->cacheBackend->invalidate($cid);
  }

  /**
   * {@inheritdoc}
   */
  public function invalidateMultiple(array $cids) {
    return $this->cacheBackend->invalidateMultiple($cids);
  }

  /**
   * {@inheritdoc}
   */
  public function invalidateTags(array $tags) {
    return $this->cacheBackend->invalidateTags($tags);
  }

  /**
   * {@inheritdoc}
   */
  public function invalidateAll() {
    return $this->cacheBackend->invalidateAll();
  }

  /**
   * {@inheritdoc}
   */
  public function garbageCollection() {
    return $this->cacheBackend->garbageCollection();
  }

  /**
   * {@inheritdoc}
   */
  public function removeBin() {
    return $this->cacheBackend->removeBin();
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    return $this->cacheBackend->isEmpty();
  }
}
