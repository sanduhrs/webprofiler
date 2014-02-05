<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Profiler\DatabaseProfilerStorage.
 */

namespace Drupal\webprofiler\Profiler;

use Drupal\Core\Database\Connection;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\HttpKernel\Profiler\ProfilerStorageInterface;

/**
 * Implements a profiler storage using the DBTNG query api.
 */
class DatabaseProfilerStorage implements ProfilerStorageInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructs a new DatabaseProfilerStorage instance.
   *
   * @param \Drupal\Core\Database\Connection $database
   *   The database connection.
   */
  function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdodc}
   */
  public function find($ip, $url, $limit, $method, $start = NULL, $end = NULL) {
    $select = $this->database->select('webprofiler_profiler', 'wp');

    if (null === $start) {
      $start = 0;
    }

    if (null === $end) {
      $end = time();
    }

    if ($ip = preg_replace('/[^\d\.]/', '', $ip)) {
      $select->condition('ip', '%' . $this->database->escapeLike($ip) . '%', 'LIKE');
    }

    if ($url) {
      $select->condition('url', '%' . $this->database->escapeLike(addcslashes($url, '%_\\')) . '%', 'LIKE');
    }

    if ($method) {
      $select->condition('method', $method);
    }

    if (!empty($start)) {
      $select->condition('timestamp', $start, '>=');
    }

    if (!empty($send)) {
      $select->condition('timestamp', $end, '<=');
    }

    $select->fields('wp', array('token', 'ip', 'method', 'url', 'timestamp', 'parent'));
    $select->orderBy('time', 'DESC');
    $select->range(0, $limit);
    return $select->execute()
      ->fetchAllAssoc('token');
  }

  /**
   * {@inheritdoc}
   */
  public function read($token) {
    $profile = $this->database->query("SELECT profile from {webprofiler_profiler} WHERE token = :token", array(':token' => $token))->fetchField();
    if ($profile) {
      return unserialize($profile);
    }
    else {
      return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function write(Profile $profile) {
    $string = serialize($profile);
    $values = array();
    $values['ip'] = $profile->getIp();
    $values['token'] = $profile->getToken();
    $values['url'] = $profile->getUrl();
    $values['method'] = $profile->getMethod();
    $values['profile'] = $string;
    $values['parent'] = $profile->getParentToken();
    $values['time'] = time();
    $this->database->insert('webprofiler_profiler')
      ->fields($values)
      ->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function purge() {
    $this->database->truncate('webprofiler_profiler');
  }

} 
