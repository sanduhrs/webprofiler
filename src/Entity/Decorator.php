<?php

namespace Drupal\webprofiler\Entity;

/**
 * Class Decorator
 */
class Decorator {

  /**
   * @var
   */
  protected $object;

  /**
   * @param $object
   */
  function __construct($object) {
    $this->object = $object;
  }

  /**
   * @return mixed
   */
  public function getOriginalObject() {
    $object = $this->object;
    while ($object instanceof Decorator) {
      $object = $object->getOriginalObject();
    }
    return $object;
  }

  /**
   * @param $method
   * @param bool $checkSelf
   *
   * @return bool|BlockStorageDecorator|mixed
   */
  public function isCallable($method, $checkSelf = FALSE) {
    //Check the original object
    $object = $this->getOriginalObject();
    if (is_callable(array($object, $method))) {
      return $object;
    }
    //Check Decorators
    $object = $checkSelf ? $this : $this->object;
    while ($object instanceof Decorator) {
      if (is_callable(array($object, $method))) {
        return $object;
      }
      $object = $this->object;
    }
    return FALSE;
  }

  /**
   * @param $method
   * @param $args
   *
   * @throws \Exception
   */
  public function __call($method, $args) {
    if ($object = $this->isCallable($method)) {
      return call_user_func_array(array($object, $method), $args);
    }
    throw new \Exception(
      'Undefined method - ' . get_class($this->getOriginalObject()) . '::' . $method
    );
  }

  /**
   * @param $property
   *
   * @return null
   */
  public function __get($property) {
    $object = $this->getOriginalObject();
    if (property_exists($object, $property)) {
      return $object->$property;
    }
    return NULL;
  }

}
