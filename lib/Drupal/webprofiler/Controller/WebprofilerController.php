<?php

namespace Drupal\webprofiler\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\webprofiler\Profiler\TemplateManager;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Cmf\Component\Routing\ChainRouter;
use Twig_Loader_Filesystem;

class WebprofilerController implements ContainerInjectionInterface {

  private $profiler;
  private $router;
  private $templateManager;
  private $twig_loader;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('profiler'),
      $container->get('router'),
      $container->get('templateManager'),
      $container->get('twig.loader')
    );
  }

  /**
   * @param Profiler $profiler
   * @param ChainRouter $router
   * @param TemplateManager $templateManager
   * @param Twig_Loader_Filesystem $twig_loader
   */
  public function __construct(Profiler $profiler, ChainRouter $router, TemplateManager $templateManager, Twig_Loader_Filesystem $twig_loader) {
    $this->profiler = $profiler;
    $this->router = $router;
    $this->templateManager = $templateManager;
    $this->twig_loader = $twig_loader;
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

    //kpr($profile->getCollector($panel)->getQueries());

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

}
