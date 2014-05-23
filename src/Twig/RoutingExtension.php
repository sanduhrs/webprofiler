<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Twig\RoutingExtension.
 */

namespace Drupal\webprofiler\Twig;

use Drupal\Core\Routing\UrlGeneratorInterface;

/**
 * Class RoutingExtension
 */
class RoutingExtension extends \Twig_Extension {

  /**
   * The URL generator.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Constructs \Drupal\twig_routing\RoutingExtension.
   *
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $urlGenerator
   *   The URL generator.
   */
  public function __construct(UrlGeneratorInterface $urlGenerator) {
    $this->urlGenerator = $urlGenerator;
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions() {
    return array(
      // The url and path function are defined in close parallel to those found
      // in \Symfony\Bridge\Twig\Extension\RoutingExtension
      'url' => new \Twig_SimpleFunction('url', array(
        $this,
        'getUrl'
      ), array('is_safe_callback' => array($this, 'isUrlGenerationSafe'))),
      'path' => new \Twig_SimpleFunction('path', array(
        $this,
        'getPath'
      ), array('is_safe_callback' => array($this, 'isUrlGenerationSafe'))),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'drupal_twig_routing';
  }

  /**
   * Generates a URL path given a route name and parameters.
   *
   * @param $name
   *   The name of the route.
   * @param array $parameters
   *   An associative array of parameter names and values.
   * @param array $options
   *   (optional) An associative array of additional options. The 'absolute'
   *   option is forced to be FALSE.
   * @see \Drupal\Core\Routing\UrlGeneratorInterface::generateFromRoute().
   *
   * @return string
   *   The generated URL path for the given route.
   */
  public function getPath($name, $parameters = array(), $options = array()) {
    $options['absolute'] = FALSE;
    return $this->urlGenerator->generateFromRoute($name, $parameters, $options);
  }

  /**
   * Generates an absolute URL given a route name and parameters.
   * @param $name
   *   The name of the route.
   * @param array $parameters
   *   An associative array of parameter names and values.
   * @param array $options
   *   (optional) An associative array of additional options. The 'absolute'
   *   option is forced to be TRUE.
   *
   * @return string
   *   The generated absolute URL for the given route.
   *
   * @todo - add an option for scheme-relative URLs.
   */
  public function getUrl($name, $parameters = array(), $options = array()) {
    $options['absolute'] = TRUE;
    return $this->urlGenerator->generateFromRoute($name, $parameters, $options);
  }

  /**
   * Determines at compile time whether the generated URL will be safe.
   *
   * Saves the unneeded automatic escaping for performance reasons.
   *
   * The URL generation process percent encodes non-alphanumeric characters.
   * Thus, the only character within an URL that must be escaped in html is the
   * ampersand ("&") which separates query params. Thus we cannot mark
   * the generated URL as always safe, but only when we are sure there won't be
   * multiple query params. This is the case when there are none or only one
   * constant parameter given. E.g. we know beforehand this will not need to
   * be escaped:
   * - path('route')
   * - path('route', {'param': 'value'})
   * But the following may need to be escaped:
   * - path('route', var)
   * - path('route', {'param': ['val1', 'val2'] }) // a sub-array
   * - path('route', {'param1': 'value1', 'param2': 'value2'})
   * If param1 and param2 reference placeholders in the route, it would not
   * need to be escaped, But we don't know that in advance.
   *
   * @param \Twig_Node $argsNode The arguments of the path/url function
   *
   * @return array An array with the contexts the URL is safe
   */
  public function isUrlGenerationSafe(\Twig_Node $argsNode) {
    // Support named arguments.
    $paramsNode = $argsNode->hasNode('parameters') ? $argsNode->getNode('parameters') : ($argsNode->hasNode(1) ? $argsNode->getNode(1) : NULL);

    if (!isset($paramsNode) || $paramsNode instanceof \Twig_Node_Expression_Array && count($paramsNode) <= 2 &&
      (!$paramsNode->hasNode(1) || $paramsNode->getNode(1) instanceof \Twig_Node_Expression_Constant)
    ) {
      return array('html');
    }

    return array();
  }

} 
