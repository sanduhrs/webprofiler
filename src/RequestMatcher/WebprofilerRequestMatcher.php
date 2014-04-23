<?php

namespace Drupal\webprofiler\RequestMatcher;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

class WebprofilerRequestMatcher implements RequestMatcherInterface {

  private $config_factory;

  /**
   * @param ConfigFactoryInterface $config_factory
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->config_factory = $config_factory;
  }

  /**
   * Decides whether the rule(s) implemented by the strategy matches the supplied request.
   *
   * @param Request $request The request to check for a match
   *
   * @return Boolean true if the request matches, false otherwise
   */
  public function matches(Request $request) {
    $path = $request->getPathInfo();

    return !drupal_match_path($path, $this->config_factory->get('webprofiler.config')->get('exclude'));
  }
}
