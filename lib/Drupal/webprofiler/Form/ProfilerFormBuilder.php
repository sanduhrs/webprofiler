<?php

namespace Drupal\webprofiler\Form;

use Drupal\Core\Form\FormBuilder;

/**
 * Class ProfilerFormBuilder
 *
 * @package Drupal\webprofiler\Form
 */
class ProfilerFormBuilder extends FormBuilder {

  private $build_forms;

  /**
   * @return array
   */
  public function getBuildForm() {
    return $this->build_forms;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm($form_id, array &$form_state) {
    if (isset($form_state['build_info'])) {
      $class = get_class($form_state['build_info']['callback_object']);
      $this->build_forms[$form_id] = $class;
    }
    return parent::buildForm($form_id, $form_state);
  }
}
