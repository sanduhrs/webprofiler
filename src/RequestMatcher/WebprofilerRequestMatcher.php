<?php

namespace Drupal\webprofiler\RequestMatcher;

use Drupal\Core\Config\ConfigFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

/**
 * Class WebprofilerRequestMatcher
 */
class WebprofilerRequestMatcher implements RequestMatcherInterface {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  private $configFactory;

  /**
   * @param ConfigFactoryInterface $configFactory
   */
  public function __construct(ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public function matches(Request $request) {
    $path = $request->getPathInfo();

    return !drupal_match_path($path, $this->configFactory->get('webprofiler.config')->get('exclude'));
  }
}
