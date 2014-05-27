<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Controller\DatabaseController.
 */

namespace Drupal\webprofiler\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Database\Connection;
use Drupal\webprofiler\DataCollector\DatabaseDataCollector;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class DatabaseController
 */
class DatabaseController extends ControllerBase {

  /**
   * @var \Symfony\Component\HttpKernel\Profiler\Profiler
   */
  private $profiler;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('profiler'),
      $container->get('database')
    );
  }

  /**
   * Constructs a new WebprofilerController.
   *
   * @param \Symfony\Component\HttpKernel\Profiler\Profiler $profiler
   * @param \Drupal\Core\Database\Connection $database
   */
  public function __construct(Profiler $profiler, Connection $database) {
    $this->profiler = $profiler;
    $this->database = $database;
  }

  /**
   * @param string $token
   * @param int $qid
   *
   * @return JsonResponse
   */
  public function explainAction($token, $qid) {
    if (NULL === $this->profiler) {
      throw new NotFoundHttpException('The profiler must be enabled.');
    }

    $this->profiler->disable();

    if (!$profile = $this->profiler->loadProfile($token)) {
      throw new NotFoundHttpException($this->t('Token @token does not exist.', array('@token' => $token)));
    }

    /** @var DatabaseDataCollector $databaseCollector */
    $databaseCollector = $profile->getCollector('database');

    $queries = $databaseCollector->getQueries();
    $query = $queries[$qid];

    $data = array();
    $result = $this->database->query('EXPLAIN ' . $query['query'], (array) $query['args'])->fetchAllAssoc('table');
    $i = 1;
    foreach ($result as $row) {
      foreach($row as $key => $value) {
        $data[$i][$key] = $value;
      }
      $i++;
    }

    return new JsonResponse(array('data' => $data));
  }
}
