<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Controller\FrontendController.
 */

namespace Drupal\webprofiler\Controller;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Database;
use Drupal\webprofiler\DataCollector\DatabaseDataCollector;
use Drupal\webprofiler\DataCollector\FrontendDataCollector;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class FrontendController
 */
class FrontendController extends ControllerBase {

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

  /**
   * @param Profile $profile
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   */
  public function saveAction(Profile $profile, Request $request) {
    $this->profiler->disable();

    $data = Json::decode($request->getContent());

    /** @var FrontendDataCollector $collector */
    $collector = $profile->getCollector('frontend');
    $collector->setData($data);
    $this->profiler->saveProfile($profile);

    return new JsonResponse(array('success' => TRUE));
  }

}
