<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Controller\DiffController.
 */

namespace Drupal\webprofiler\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\webprofiler\DataCollector\DatabaseDataCollector;
use Drupal\webprofiler\Diff\ProfilerDiffer;
use SebastianBergmann\Diff\Differ;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DiffController
 */
class DiffController extends ControllerBase {

  /**
   * @var \Symfony\Component\HttpKernel\Profiler\Profiler
   */
  private $profiler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('profiler')
    );
  }

  /**
   * Constructs a new WebprofilerController.
   *
   * @param \Symfony\Component\HttpKernel\Profiler\Profiler $profiler
   */
  public function __construct(Profiler $profiler) {
    $this->profiler = $profiler;
  }


  public function diffAction($token1, $token2) {
    if (NULL === $this->profiler) {
      throw new NotFoundHttpException('The profiler must be enabled.');
    }

    $this->profiler->disable();

    $profile1 = $this->profiler->loadProfile($token1);
    $profile2 = $this->profiler->loadProfile($token2);

    if (!$profile1 || !$profile2) {
      throw new NotFoundHttpException($this->t('Token @token1 or token @token2 does not exist.', array(
        '@token1' => $token1,
        '@token2' => $token2
      )));
    }

    kpr($profile1);

    $differ = new ProfilerDiffer($profile1, $profile2);

    $build['diff'] = array(
      '#markup' => $differ->getDiff(),
    );

    return $build;
  }
}
