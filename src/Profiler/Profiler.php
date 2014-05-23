<?php

namespace Drupal\webprofiler\Profiler;

use Drupal\Core\Config\ConfigFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;
use Symfony\Component\HttpKernel\Profiler\Profiler as SymfonyProfiler;
use Symfony\Component\HttpKernel\Profiler\ProfilerStorageInterface;

/**
 * Class Profiler
 */
class Profiler extends SymfonyProfiler {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface $config
   */
  private $config;

  /**
   * Constructor.
   *
   * @param \Symfony\Component\HttpKernel\Profiler\ProfilerStorageInterface $storage
   *   A ProfilerStorageInterface instance
   * @param \Psr\Log\LoggerInterface $logger
   *   A LoggerInterface instance
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   */
  public function __construct(ProfilerStorageInterface $storage, LoggerInterface $logger = NULL, ConfigFactoryInterface $config) {
    parent::__construct($storage, $logger);

    $this->config = $config;
  }

  /**
   * {@inheritdoc}
   */
  public function add(DataCollectorInterface $collector) {
    $activeToolbarItems = $this->config->get('webprofiler.config')->get('active_toolbar_items');

    // drupal collector should not be disabled
    if ($collector->getName() == 'drupal') {
      parent::add($collector);
    }
    else {
      if ($activeToolbarItems[$collector->getName()] !== '0') {
        parent::add($collector);
      }
    }
  }

}
