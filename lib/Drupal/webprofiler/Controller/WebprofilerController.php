<?php

namespace Drupal\webprofiler\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\Date;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\webprofiler\Profiler\TemplateManager;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Cmf\Component\Routing\ChainRouter;
use Twig_Loader_Filesystem;

class WebprofilerController extends ControllerBase implements ContainerInjectionInterface {

  private $profiler;
  private $router;
  private $templateManager;
  private $twig_loader;
  private $date;
  private $form_builder;

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
      $container->get('form_builder')
    );
  }

  /**
   * @param Profiler $profiler
   * @param ChainRouter $router
   * @param TemplateManager $templateManager
   * @param Twig_Loader_Filesystem $twig_loader
   * @param Date $date
   * @param FormBuilderInterface $form_builder
   */
  public function __construct(Profiler $profiler, ChainRouter $router, TemplateManager $templateManager, Twig_Loader_Filesystem $twig_loader, Date $date, FormBuilderInterface $form_builder) {
    $this->profiler = $profiler;
    $this->router = $router;
    $this->templateManager = $templateManager;
    $this->twig_loader = $twig_loader;
    $this->date = $date;
    $this->form_builder = $form_builder;
  }

  /**
   *
   */
  public function profilerAction(Request $request, $token) {
    $this->profiler->disable();
    $profile = $this->profiler->loadProfile($token);

    if (NULL === $profile) {
      return $this->t('No profiler data for @token token.', array('@token' => $token));
    }

    $template_manager = $this->templateManager;
    $panel = $request->query->get('panel', 'request');

    // TODO remove this when https://drupal.org/node/2143557 comes in.
    $this->twig_loader->addPath(drupal_get_path('module', 'webprofiler') . '/templates', 'webprofiler');

    $webprofiler_path = drupal_get_path('module', 'webprofiler');

    $profiler = array(
      '#theme' => 'webprofiler_panel',
      '#token' => $token,
      '#profile' => $profile,
      '#collector' => $profile->getCollector($panel),
      '#panel' => $panel,
      '#page' => '',
      '#request' => $request,
      '#templates' => $template_manager->getTemplates($profile),
      '#attached' => array(
        'css' => array(
          $webprofiler_path . '/css/webprofiler.css' => array(),
        ),
        'js' => array(
          $webprofiler_path . '/js/webprofiler.js' => array(),
        )
      )
    );

    return $profiler;
  }

  /**
   *
   */
  public function toolbarAction(Request $request, $token) {
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
   *
   */
  public function listAction(Request $request) {
    $limit = $request->get('limit', 10);
    $this->profiler->disable();

    $tokens = $this->profiler->find('', '', $limit, '', '', '');

    $rows = array();
    foreach ($tokens as $token) {
      $row = array();
      $row[] = l($token['token'], "admin/config/development/profiler/view/{$token['token']}");
      $row[] = $token['ip'];
      $row[] = $token['method'];
      $row[] = $token['url'];
      $row[] = $this->date->format($token['time']);

      $rows[] = $row;
    }

    return array(
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => array($this->t('Token'), $this->t('Ip'), $this->t('Method'), $this->t('Url'), $this->t('Time')),
    );
  }

}
