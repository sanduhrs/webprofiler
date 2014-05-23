<?php

namespace Drupal\webprofiler\Views;

use Drupal\Core\Session\AccountInterface;
use Drupal\views\ViewExecutable;
use Drupal\views\ViewExecutableFactory;
use Drupal\views\ViewStorageInterface;

/**
 * Class ViewExecutableFactoryWrapper
 */
class ViewExecutableFactoryWrapper extends ViewExecutableFactory {

  /** @var ViewExecutable $view_executable */
  private $views;

  /**
   * {@inheritdoc}
   */
  public function __construct(AccountInterface $user) {
    parent::__construct($user);

    $this->views = array();
  }

  /**
   * {@inheritdoc}
   */
  public function get(ViewStorageInterface $view) {
    $view_executable = new TraceableViewExecutable($view, $this->user);
    $this->views[] = $view_executable;

    return $view_executable;
  }

  /**
   * @return TraceableViewExecutable
   */
  public function getViews() {
    return $this->views;
  }
}
