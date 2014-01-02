<?php

namespace Drupal\webprofiler\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WebprofilerController extends ControllerBase {

  /**
   *
   */
  public function profilerAction(Request $request, $token) {
    $profiler = $this->container()->get('profiler');
    $template_manager = $this->container()->get('templateManager');
    $twig_loader = $this->container()->get('twig.loader');
    $panel = $request->query->get('panel', 'request');

    // TODO remove this when https://drupal.org/node/2143557 comes in.
    $twig_loader->addPath(drupal_get_path('module', 'webprofiler') . '/templates', 'webprofiler');

    $profiler->disable();

    $profile = $profiler->loadProfile($token);

    $profiler = array(
      '#theme' => 'webprofiler_panel',
      '#token' => $token,
      '#profile' => $profile,
      '#collector' => $profile->getCollector($panel),
      '#panel' => $panel,
      '#page' => '',
      '#request' => $request,
      '#templates' => $template_manager->getTemplates($profile),
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

    $profiler = $this->container()->get('profiler');
    $profiler->disable();

    if (!$profile = $profiler->loadProfile($token)) {
      return new Response('', 200, array('Content-Type' => 'text/html'));
    }

    $url = NULL;
    try {
      $url = $this->container()->get('router')->generate('webprofiler.profiler', array('token' => $token));
    } catch (\Exception $e) {
      // the profiler is not enabled
    }

    $templates = $this->container()->get('templateManager')->getTemplates($profile);

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
