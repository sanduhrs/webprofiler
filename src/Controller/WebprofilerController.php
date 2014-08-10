<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Controller\WebprofilerController.
 */

namespace Drupal\webprofiler\Controller;

use Drupal\Core\Archiver\ArchiveTar;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\system\FileDownloadController;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\webprofiler\Profiler\ProfilerStorageManager;
use Drupal\webprofiler\Profiler\TemplateManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Drupal\webprofiler\Profiler\Profiler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\Routing\RouterInterface;
use Twig_Loader_Filesystem;

/**
 * Class WebprofilerController
 */
class WebprofilerController extends ControllerBase {

  /**
   * @var \Drupal\webprofiler\Profiler\Profiler
   */
  private $profiler;

  /**
   * @var \Symfony\Cmf\Component\Routing\ChainRouter
   */
  private $router;

  /**
   * @var \Drupal\webprofiler\Profiler\TemplateManager
   */
  private $templateManager;

  /**
   * @var \Twig_Loader_Filesystem
   */
  private $twigLoader;

  /**
   * @var \Drupal\Core\Datetime\Date
   */
  private $date;

  /**
   * @var \Drupal\system\FileDownloadController
   */
  private $fileDownloadController;

  /**
   * @var \Drupal\webprofiler\Profiler\ProfilerStorageManager
   */
  private $profilerDownloadManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('profiler'),
      $container->get('router'),
      $container->get('templateManager'),
      $container->get('twig.loader'),
      $container->get('date.formatter'),
      $container->get('profiler.storage_manager'),
      new FileDownloadController()
    );
  }

  /**
   * Constructs a new WebprofilerController.
   *
   * @param \Drupal\webprofiler\Profiler\Profiler $profiler
   * @param \Symfony\Component\Routing\RouterInterface $router
   * @param \Drupal\webprofiler\Profiler\TemplateManager $templateManager
   * @param \Twig_Loader_Filesystem $twigLoader
   * @param \Drupal\Core\Datetime\Date $date
   * @param \Drupal\system\FileDownloadController $fileDownloadController
   * @param \Drupal\webprofiler\Profiler\ProfilerStorageManager $profilerDownloadManager
   */
  public function __construct(Profiler $profiler, RouterInterface $router, TemplateManager $templateManager, Twig_Loader_Filesystem $twigLoader, DateFormatter $date, ProfilerStorageManager $profilerDownloadManager, FileDownloadController $fileDownloadController) {
    $this->profiler = $profiler;
    $this->router = $router;
    $this->templateManager = $templateManager;
    $this->twigLoader = $twigLoader;
    $this->date = $date;
    $this->fileDownloadController = $fileDownloadController;
    $this->profilerDownloadManager = $profilerDownloadManager;
  }

  /**
   * Generates the profile page.
   *
   * @param Profile $profile
   *
   * @return array
   */
  public function profilerAction(Profile $profile) {
    $this->profiler->disable();

    $templateManager = $this->templateManager;
    $templates = $templateManager->getTemplates($profile);

    $childrens = array();
    foreach ($templates as $name => $template) {
      /** @var DrupalDataCollectorInterface $collector */
      $collector = $profile->getCollector($name);

      if ($collector->hasPanel()) {
        $childrens[] = array(
          '#theme' => 'details',
          '#attributes' => array('id' => $name),
          '#title' => $collector->getTitle(),
          '#summary' => 'test',
          '#value' => array(
            '#theme' => 'webprofiler_panel',
            '#name' => $name,
            '#template' => $template,
            '#profile' => $profile,
            '#summary' => $collector->getPanelSummary(),
            '#content' => $collector->getPanel(),
          )
        );
      }
    }

    $build = array();
    $build['resume'] = array(
      '#theme' => 'webprofiler_resume',
      '#profile' => $profile,
    );

    $build['panels'] = array(
      '#theme' => 'vertical_tabs',
      '#children' => $childrens,
      '#attributes' => array('class' => array('webprofiler')),
      '#attached' => array(
        'js' => array(
          array(
            'data' => array('webprofiler' => array('token' => $profile->getToken())),
            'type' => 'setting'
          ),
        ),
        'library' => array(
          'core/drupal.vertical-tabs',
          'webprofiler/webprofiler',
        ),
      ),
    );

    return $build;
  }

  /**
   * Generates the toolbar.
   *
   * @param Profile $profile
   *
   * @return array
   */
  public function toolbarAction(Profile $profile) {
    $this->profiler->disable();

    $url = NULL;
    try {
      $url = $this->router->generate('webprofiler.profiler', array('token' => $profile->getToken()));
    } catch (\Exception $e) {
      // the profiler is not enabled
    }

    $templates = $this->templateManager->getTemplates($profile);

    $toolbar = array(
      '#theme' => 'webprofiler_toolbar',
      '#token' => $profile->getToken(),
      '#templates' => $templates,
      '#profile' => $profile,
      '#profiler_url' => $url,
    );

    return new Response(render($toolbar));
  }

  /**
   * Generates the list page.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   * @return array
   */
  public function listAction(Request $request) {
    $limit = $request->get('limit', 10);
    $this->profiler->disable();

    $ip = $request->query->get('ip');
    $method = $request->query->get('method');
    $url = $request->query->get('url');

    $profiles = $this->profiler->find($ip, $url, $limit, $method, '', '');

    $rows = array();
    if (count($profiles)) {
      foreach ($profiles as $profile) {
        $row = array();
        $row[] = $this->l($profile['token'], 'webprofiler.profiler', array('profile' => $profile['token']));
        $row[] = $profile['ip'];
        $row[] = $profile['method'];
        $row[] = $profile['url'];
        $row[] = $this->date->format($profile['time']);

        $operations = array(
          'export' => array(
            'title' => $this->t('Export'),
            'route_name' => 'webprofiler.single_export',
            'route_parameters' => array('profile' => $profile['token']),
          ),
        );
        $dropbutton = array(
          '#type' => 'operations',
          '#links' => $operations,
        );
        $row[] = render($dropbutton);

        $rows[] = $row;
      }
    }
    else {
      $rows[] = array(
        array(
          'data' => $this->t('No profiles found'),
          'colspan' => 6,
        )
      );
    }

    $build = array();

    $storageId = $this->config('webprofiler.config')->get('storage');
    $storage = $this->profilerDownloadManager->getStorage($storageId);

    $build['resume'] = array(
      '#markup' => '<p>' . t('Profiles stored with %storage service.', array('%storage' => $storage['title'])) . '</p>',
    );

    $build['filters'] = $this->formBuilder()->getForm('Drupal\\webprofiler\\Form\\ProfilesFilterForm');

    $build['table'] = array(
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => array(
        $this->t('Token'),
        array(
          'data' => $this->t('Ip'),
          'class' => array(RESPONSIVE_PRIORITY_LOW),
        ),
        array(
          'data' => $this->t('Method'),
          'class' => array(RESPONSIVE_PRIORITY_LOW),
        ),
        $this->t('Url'),
        array(
          'data' => $this->t('Time'),
          'class' => array(RESPONSIVE_PRIORITY_MEDIUM),
        ),
        $this->t('Actions')
      ),
      '#attached' => array(
        'library' => array(
          'webprofiler/webprofiler',
        ),
      ),
    );

    return $build;
  }

  /**
   * Downloads a single profile.
   *
   * @param Profile $profile
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function singleExportAction(Profile $profile) {
    $this->profiler->disable();
    $token = $profile->getToken();

    if (!$profile = $this->profiler->loadProfile($token)) {
      throw new NotFoundHttpException($this->t('Token @token does not exist.', array('@token' => $token)));
    }

    return new Response($this->profiler->export($profile), 200, array(
      'Content-Type' => 'text/plain',
      'Content-Disposition' => 'attachment; filename= ' . $token . '.txt',
    ));
  }

  /**
   * Downloads a tarball with all stored profiles.
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
   */
  public function allExportAction() {
    $archiver = new ArchiveTar(file_directory_temp() . '/profiles.tar.gz', 'gz');
    $profiles = $this->profiler->find('', '', 100, '', '', '');

    $files = array();
    foreach ($profiles as $profile) {
      $data = $this->profiler->export($this->profiler->loadProfile($profile['token']));
      $filename = file_directory_temp() . "/{$profile['token']}.txt";
      file_put_contents($filename, $data);
      $files[] = $filename;
    }

    $archiver->createModify($files, '', file_directory_temp());

    $request = new Request(array('file' => 'profiles.tar.gz'));
    return $this->fileDownloadController->download($request, 'temporary');
  }
}
