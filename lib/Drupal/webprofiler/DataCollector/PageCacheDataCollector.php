<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\PageCacheDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;

/**
 * Provides a data collector which collects the used cache tags on the page.
 */
class PageCacheDataCollector implements DataCollectorInterface {

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $this->data['cache_tags'] = array();
    $this->data['cache_keys'] = array();
    $this->data['cache_bin'] = '';
    $this->data['cache_max_page'] = 0;
    if ($cache_tags = $response->headers->get('cache_tags')) {
      $this->data['cache_tags'] = $cache_tags;
    }
    if ($cache_keys = $response->headers->get('cache_keys')) {
      $this->data['cache_keys'] = $cache_keys;
    }
    if ($cache_bin = $response->headers->get('cache_bin')) {
      $this->data['cache_bin'] = $cache_bin;
    }
    if ($cache_max_age = $response->headers->get('cache_max_age')) {
      $this->data['cache_max_age'] = $cache_max_age;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'page_cache';
  }

  /**
   * Twig callback to get all cache tags.
   */
  public function getCacheTags() {
    return $this->data['cache_tags'];
  }

  /**
   * Twig callback to get all cache keys.
   */
  public function getCacheKeys() {
    return $this->data['cache_keys'];
  }

  /**
   * Twig callback to get the cache bin.
   */
  public function getCacheBin() {
    return $this->data['cache_bin'];
  }

  /**
   * Twig callback to get the cache bin.
   */
  public function getCacheMaxAge() {
    return $this->data['cache_max_page'];
  }

} 
