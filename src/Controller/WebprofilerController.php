<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Controller\WebprofilerController.
 */

namespace Drupal\webprofiler\Controller;

use Drupal\Core\Archiver\ArchiveTar;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\Date;
use Drupal\system\FileDownloadController;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\webprofiler\Profiler\TemplateManager;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;
use Twig_Loader_Filesystem;

/**
 * Class WebprofilerController
 */
class WebprofilerController extends ControllerBase {

  /**
   * @var \Symfony\Component\HttpKernel\Profiler\Profiler
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
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('profiler'),
      $container->get('router'),
      $container->get('templateManager'),
      $container->get('twig.loader'),
      $container->get('date'),
      new FileDownloadController()
    );
  }

  /**
   * Constructs a new WebprofilerController.
   *
   * @param \Symfony\Component\HttpKernel\Profiler\Profiler $profiler
   * @param \Symfony\Component\Routing\RouterInterface $router
   * @param \Drupal\webprofiler\Profiler\TemplateManager $templateManager
   * @param \Twig_Loader_Filesystem $twigLoader
   * @param \Drupal\Core\Datetime\Date $date
   * @param \Drupal\system\FileDownloadController $fileDownloadController
   */
  public function __construct(Profiler $profiler, RouterInterface $router, TemplateManager $templateManager, Twig_Loader_Filesystem $twigLoader, Date $date, FileDownloadController $fileDownloadController) {
    $this->profiler = $profiler;
    $this->router = $router;
    $this->templateManager = $templateManager;
    $this->twigLoader = $twigLoader;
    $this->date = $date;
    $this->fileDownloadController = $fileDownloadController;
  }

  /**
   * Generates the profile page.
   *
   * @param string $token
   * @return array
   */
  public function profilerAction($token) {
    $this->profiler->disable();
    $profile = $this->profiler->loadProfile($token);

    if (NULL === $profile) {
      return $this->t('No profiler data for @token token.', array('@token' => $token));
    }

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
            'data' => array('webprofiler' => array('token' => $token)),
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
   * @param string $token
   * @return array
   */
  public function toolbarAction($token) {
    if (NULL === $token) {
      return new Response('', 200, array('Content-Type' => 'text/html'));
    }

    $this->profiler->disable();

    if (!$profile = $this->profiler->loadProfile($token)) {
      return new Response('', 200, array('Content-Type' => 'text/html'));
    }

    $url = NULL;
    try {
      $url = $this->router->generate('webprofiler.profiler', array('token' => $token));
    } catch (\Exception $e) {
      // the profiler is not enabled
    }

    $templates = $this->templateManager->getTemplates($profile);

    $toolbar = array(
      '#theme' => 'webprofiler_toolbar',
      '#token' => $token,
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

    $tokens = $this->profiler->find('', '', $limit, '', '', '');

    $rows = array();
    if (count($tokens)) {
      foreach ($tokens as $token) {
        $row = array();
        $row[] = $this->l($token['token'], 'webprofiler.profiler', array('token' => $token['token']));
        $row[] = $token['ip'];
        $row[] = $token['method'];
        $row[] = $token['url'];
        $row[] = $this->date->format($token['time']);

        $operations = array(
          'export' => array(
            'title' => $this->t('Export'),
            'route_name' => 'webprofiler.single_export',
            'route_parameters' => array('token' => $token['token']),
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

    $build = array();

    $storage = $this->config('webprofiler.config')->get('storage');

    $build['resume'] = array(
      '#markup' => '<p>' . t('Profiles stored with %storage service.', array('%storage' => $storage)) . '</p>',
    );

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
    );

    return $build;
  }

  /**
   * Downloads a single profile.
   *
   * @param string $token
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function singleExportAction($token) {
    if (NULL === $this->profiler) {
      throw new NotFoundHttpException('The profiler must be enabled.');
    }

    $this->profiler->disable();

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
    $tokens = $this->profiler->find('', '', 100, '', '', '');

    $files = array();
    foreach ($tokens as $token) {
      $data = $this->profiler->export($this->profiler->loadProfile($token));
      $filename = file_directory_temp() . "/{$token['token']}.txt";
      file_put_contents($filename, $data);
      $files[] = $filename;
    }

    $archiver->createModify($files, '', file_directory_temp());

    $request = new Request(array('file' => 'profiles.tar.gz'));
    return $this->fileDownloadController->download($request, 'temporary');
  }
}
