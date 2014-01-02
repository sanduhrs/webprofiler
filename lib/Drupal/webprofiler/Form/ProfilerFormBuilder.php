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
  public function buildForm($form_id, &$form_state) {
    $this->build_forms[] = $form_id;
    return parent::buildForm($form_id, &$form_state);
  }
}
