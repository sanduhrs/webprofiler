<?php

namespace Drupal\webprofiler\Profiler;

use Drupal\Core\Database\Connection;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\HttpKernel\Profiler\ProfilerStorageInterface;

class DatabaseProfilerStorage implements ProfilerStorageInterface {

  private $connection;

  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /**
   * Finds profiler tokens for the given criteria.
   *
   * @param string $ip The IP
   * @param string $url The URL
   * @param string $limit The maximum number of tokens to return
   * @param string $method The request method
   * @param int|null $start The start date to search from
   * @param int|null $end The end date to search to
   *
   * @return array An array of tokens
   */
  public function find($ip, $url, $limit, $method, $start = NULL, $end = NULL) {
    if (NULL === $start) {
      $start = 0;
    }

    if (NULL === $end) {
      $end = time();
    }

    $query = $this->connection
      ->select('webprofiler', 'w', array('fetch' => \PDO::FETCH_ASSOC))
      ->fields('w', array(
        'token',
        'ip',
        'method',
        'url',
        'time',
        'parent'
      ));

    if ($ip = preg_replace('/[^\d\.]/', '', $ip)) {
      $query->condition('ip', '%' . $ip . '%'. 'LIKE');
    }

    if ($url) {
      $query->condition('url', '%' . addcslashes($url, '%_\\') . '%', 'LIKE');
    }

    if ($method) {
      $query->condition('method', $method);
    }

    if (!empty($start)) {
      $query->condition('time', $start, '>=');
    }

    if (!empty($end)) {
      $query->condition('time', $end, '<=');
    }

    $tokens = $query->orderBy('time', 'DESC')
      ->range(0, $limit)
      ->execute()
      ->fetchAll();

    return $tokens;
  }

  /**
   * Reads data associated with the given token.
   *
   * The method returns false if the token does not exist in the storage.
   *
   * @param string $token A token
   *
   * @return Profile The profile associated with token
   */
  public function read($token) {
    $record = $this->connection->select('webprofiler', 'w')->fields('w')->condition('token', $token)->execute()
      ->fetch();
    if (isset($record->data)) {
      return $this->createProfileFromData($token, $record);
    }
  }

  /**
   * Saves a Profile.
   *
   * @param Profile $profile A Profile instance
   *
   * @return Boolean Write operation successful
   */
  public function write(Profile $profile) {
    $args = array(
      'token' => $profile->getToken(),
      'parent' => $profile->getParentToken(),
      'data' => base64_encode(serialize($profile->getCollectors())),
      'ip' => $profile->getIp(),
      'method' => $profile->getMethod(),
      'url' => $profile->getUrl(),
      'time' => $profile->getTime(),
      'created_at' => time(),
    );

    try {
      $this->connection->insert('webprofiler')->fields($args)->execute();
      $status = TRUE;
    } catch (\Exception $e) {
      $status = FALSE;
    }

    return $status;
  }

  /**
   * Purges all data from the database.
   */
  public function purge() {
    $this->connection->truncate('webprofiler')->execute();
  }

  /**
   * @param $token
   * @param $data
   *
   * @return Profile
   */
  private function createProfileFromData($token, $data) {
    $profile = new Profile($token);
    $profile->setIp($data->ip);
    $profile->setMethod($data->method);
    $profile->setUrl($data->url);
    $profile->setTime($data->time);
    $profile->setCollectors(unserialize(base64_decode($data->data)));

    return $profile;
  }
}
