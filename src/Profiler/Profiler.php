<?php

namespace Drupal\webprofiler\Profiler;

use Drupal\Core\Config\ConfigFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\DataCollector\DataCollectorInterface;
use Symfony\Component\HttpKernel\Profiler\Profiler as SymfonyProfiler;
use Symfony\Component\HttpKernel\Profiler\ProfilerStorageInterface;

class Profiler extends SymfonyProfiler {

  private $config;

  /**
   * Constructor.
   *
   * @param ProfilerStorageInterface $storage A ProfilerStorageInterface instance
   * @param LoggerInterface $logger A LoggerInterface instance
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
    $active_toolbar_items = $this->config->get('webprofiler.config')->get('active_toolbar_items');

    // drupal collector should not be disabled
    if ($collector->getName() == 'drupal') {
      parent::add($collector);
    }
    else {
      if ($active_toolbar_items[$collector->getName()] !== '0') {
        parent::add($collector);
      }
    }
  }

}
