<?php

namespace Drupal\webprofiler\Views;

use Drupal\Core\Session\AccountInterface;
use Drupal\views\ViewEntityInterface;
use Drupal\views\ViewExecutable;
use Drupal\views\ViewExecutableFactory;
use Drupal\views\ViewsData;
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
  public function __construct(AccountInterface $user, RequestStack $request_stack, ViewsData $views_data) {
    parent::__construct($user, $request_stack, $views_data);

    $this->views = array();
  }

  /**
   * {@inheritdoc}
   */
  public function get(ViewEntityInterface $view) {
    $view_executable = new TraceableViewExecutable($view, $this->user, $this->viewsData);
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
