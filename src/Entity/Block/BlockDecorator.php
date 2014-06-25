<?php

namespace Drupal\webprofiler\Entity\Block;

use Drupal\webprofiler\Decorator;

class BlockDecorator extends Decorator {

  /**
   * @var array
   */
  protected $blocks;

  /**
   * @return mixed
   */
  public function getBlocks() {
    return $this->blocks;
  }

}
