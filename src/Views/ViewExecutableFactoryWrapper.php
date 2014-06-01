<?php

namespace Drupal\webprofiler\Views;

use Drupal\Core\Session\AccountInterface;
use Drupal\views\ViewExecutable;
use Drupal\views\ViewExecutableFactory;
use Drupal\views\ViewStorageInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ViewExecutableFactoryWrapper
 */
class ViewExecutableFactoryWrapper extends ViewExecutableFactory {

  /** @var ViewExecutable $view_executable */
  private $views;

  /**
   * {@inheritdoc}
   */
  public function __construct(AccountInterface $user, RequestStack $request_stack) {
    parent::__construct($user, $request_stack);

    $this->views = array();
  }

  /**
   * {@inheritdoc}
   */
  public function get(ViewStorageInterface $view) {
    $view_executable = new TraceableViewExecutable($view, $this->user);
    $view_executable->setRequest($this->requestStack->getCurrentRequest());
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
