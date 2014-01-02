<?php

namespace Drupal\webprofiler\RequestMatcher;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;

class WebprofilerRequestMatcher implements RequestMatcherInterface {

  /**
   * Decides whether the rule(s) implemented by the strategy matches the supplied request.
   *
   * @param Request $request The request to check for a match
   *
   * @return Boolean true if the request matches, false otherwise
   */
  public function matches(Request $request) {
    $path = $request->getPathInfo();
    $matches = NULL;

    // exclude contextual request
    preg_match('/\\/contextual\\/(.*)/', $path, $matches);
    if ($matches) {
      return FALSE;
    }

    // exclude admin toolbar request
    preg_match('/\\/toolbar\\/(.*)/', $path, $matches);
    if ($matches) {
      return FALSE;
    }

    return TRUE;
  }
}
