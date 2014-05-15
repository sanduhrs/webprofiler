<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\RequestDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\Core\Controller\HtmlFormController;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\RequestDataCollector as BaseRequestDataCollector;

/**
 * Integrate _content into the RequestDataCollector
 */
class RequestDataCollector extends BaseRequestDataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    parent::collect($request, $response, $exception);

    if (isset($this->data['controller']) && $request->attributes->has('_content')) {
      // @todo This would actually have to use the controller resolver.
      $_content = $request->attributes->get('_content');
      if (is_string($_content)) {
        $controller = explode('::', $_content);
      }
      // Forms.
      elseif ($_content[0] instanceof HtmlFormController) {
        $controller = array(
          $request->attributes->get('_form'),
          'buildForm',
        );
      }
      else {
        $controller = $_content;
      }
      try {
        $r = new \ReflectionMethod($controller[0], $controller[1]);
        $this->data['controller'] = array(
          'class' => is_object($controller[0]) ? get_class($controller[0]) : $controller[0],
          'method' => $controller[1],
          'file' => $r->getFilename(),
          'line' => $r->getStartLine(),
        );
      } catch (\ReflectionException $re) {
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    return $this->t('Request');
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel() {
    $build = array();

    $header = array(
      $this->t('Key'),
      $this->t('Value')
    );

    // GET parameters
    if (count($this->getRequestQuery()->all()) > 0) {
      $build['get'] = $this->getTable($this->t('Request GET Parameters'), $this->getRequestQuery(), $header);
    }

    // POST parameters
    if (count($this->getRequestRequest()->all()) > 0) {
      $build['post'] = $this->getTable($this->t('Request POST Parameters'), $this->getRequestRequest(), $header);
    }

    // Attributes
    if (count($this->getRequestAttributes()->all()) > 0) {
      $build['attributes'] = $this->getTable($this->t('Request Attributes'), $this->getRequestAttributes(), $header);
    }

    // Cookies
    if (count($this->getRequestCookies()->all()) > 0) {
      $build['cookies'] = $this->getTable($this->t('Request Cookies'), $this->getRequestCookies(), $header);
    }

    // Headers
    $build['headers'] = $this->getTable($this->t('Request Headers'), $this->getRequestHeaders(), $header);

    // Content
    $build['content'] = array(
      '#markup' => '<h3>' . $this->t('Request Content') . '</h3>',
    );

    if (!$this->getContent()) {
      $build['content']['data'] = array(
        '#markup' => $this->t('No content'),
      );
    }
    else {
      $build['content']['data'] = array(
        '#markup' => $this->getContent(),
      );
    }

    // Server Parameters
    if (count($this->getRequestServer()->all()) > 0) {
      $build['server'] = $this->getTable($this->t('Request Server Parameters'), $this->getRequestServer(), $header);
    }

    // Response Headers
    if (count($this->getResponseHeaders()->all()) > 0) {
      $build['response-headers'] = $this->getTable($this->t('Response Headers'), $this->getResponseHeaders(), $header);
    }

    return $build;
  }
}
