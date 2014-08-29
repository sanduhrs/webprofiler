<?php

namespace Drupal\webprofiler\Profiler;

use Symfony\Component\HttpKernel\Profiler\ProfilerStorageInterface;

class ProfilerStorageManager {

  /**
   * @var array
   */
  private $storages;

  /**
   * @return array
   */
  public function getStorages() {
    $output = array();

    /** @var \Symfony\Component\HttpKernel\Profiler\ProfilerStorageInterface $storage */
    foreach ($this->storages as $id => $storage) {
      $output[$id] = $storage['title'];
    }

    return $output;
  }

  /**
   * @param $id
   *
   * @return array
   */
  public function getStorage($id) {
    return $this->storages[$id];
  }

  /**
   * @param $id
   * @param $title
   * @param \Symfony\Component\HttpKernel\Profiler\ProfilerStorageInterface $storage
   */
  public function addStorage($id, $title, ProfilerStorageInterface $storage) {
    $this->storages[$id] = array(
      'title' => $title,
      'class' => $storage,
    );
  }

}
