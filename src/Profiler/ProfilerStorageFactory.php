<?php

namespace Drupal\webprofiler\Profiler;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Profiler\ProfilerStorageInterface;

class ProfilerStorageFactory {

  /**
   * @param ConfigFactoryInterface $config
   * @param ContainerInterface $container
   *
   * @return ProfilerStorageInterface
   */
  final public static function getProfilerStorage(ConfigFactoryInterface $config, ContainerInterface $container) {
    $storage = $config->get('webprofiler.config')->get('storage') ? : 'profiler.database_storage';

    return $container->get($storage);
  }

}
