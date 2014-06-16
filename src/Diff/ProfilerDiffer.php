<?php

namespace Drupal\webprofiler\Diff;

use Symfony\Component\HttpKernel\Profiler\Profile;
use SebastianBergmann\Diff\Differ;

/**
 * Class ProfilerDiffer
 */
class ProfilerDiffer extends Differ {

  /**
   * @param Profile $profile1
   * @param Profile $profile2
   */
  public function __construct(Profile $profile1, Profile $profile2) {
    parent::__construct();
  }

  /**
   * @return string
   */
  public function getDiff() {
    return $this->diff("foo \n bar", "foo \n baz");
  }

}
