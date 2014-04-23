<?php

/**
 * @file
 * Contains \Drupal\webprofiler\DataCollector\RequestDataCollector.
 */

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Controller\HtmlFormController;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\RequestDataCollector as BaseRequestDataCollector;

/**
 * Integrate _content into the RequestDataCollector;
 */
class RequestDataCollector extends BaseRequestDataCollector implements DrupalDataCollectorInterface {

  /**
   * {@inheritdoc}
   */
  public function getMenu() {
    return \Drupal::translation()->translate('Request');
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    return NULL;
  }

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

}
