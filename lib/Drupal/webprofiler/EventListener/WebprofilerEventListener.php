<?php

namespace Drupal\webprofiler\EventListener;

use Drupal\Core\Session\AccountInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Twig_Environment;
use Drupal\Core\Database\Database;

class WebprofilerEventListener implements EventSubscriberInterface {

  private $current_user;

  /**
   * @param \Drupal\Core\Session\AccountInterface $current_user
   */
  public function __construct(AccountInterface $current_user) {
    $this->current_user = $current_user;
  }

  /**
   * @param GetResponseEvent $event
   */
  public function onKernelRequest(GetResponseEvent $event) {
    Database::startLog('webprofiler');
  }

  /**
   * @param FilterResponseEvent $event
   */
  public function onKernelResponse(FilterResponseEvent $event) {
    $response = $event->getResponse();
    $request = $event->getRequest();

    // do not capture redirects or modify XML HTTP Requests
    if ($request->isXmlHttpRequest()) {
      return;
    }

    if ($this->current_user->hasPermission('access web profiler')) {
      $this->injectToolbar($response);
    }
  }

  /**
   * @param Response $response
   */
  protected function injectToolbar(Response $response) {
    $content = $response->getContent();
    $pos = mb_strripos($content, '</body>');

    if (FALSE !== $pos) {
      if ($token = $response->headers->get('X-Debug-Token')) {
        $toolbar = array(
          '#theme' => 'webprofiler_loader',
          '#token' => $token,
        );

        $content = mb_substr($content, 0, $pos) . render($toolbar) . mb_substr($content, $pos);
        $response->setContent($content);
      }
    }
  }

  /**
   * @return array
   */
  public static function getSubscribedEvents() {
    return array(
      KernelEvents::RESPONSE => array('onKernelResponse', -128),
      KernelEvents::REQUEST => array('onKernelRequest', -100),
    );
  }
}
