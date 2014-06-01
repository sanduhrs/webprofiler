<?php

namespace Drupal\webprofiler\Form;

use Drupal\Core\Form\FormBuilder;

/**
 * Class FormBuilderWrapper
 */
class FormBuilderWrapper extends FormBuilder {

  /**
   * @var array
   */
  private $buildForms;

  /**
   * @return array
   */
  public function getBuildForm() {
    return $this->buildForms;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm($form_id, array &$form_state) {
    if (isset($form_state['build_info']) && isset($form_state['build_info']['callback_object'])) {
      $class = get_class($form_state['build_info']['callback_object']);
      $this->buildForms[$form_id] = array(
        'class' => $class,
      );
    }
    return parent::buildForm($form_id, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function retrieveForm($form_id, &$form_state) {
    $form = parent::retrieveForm($form_id, $form_state);

    if ($this->buildForms != NULL) {

      $elements = array();
      foreach ($form as $key => $value) {
        if (strpos($key, '#') !== 0) {
          $elements[$key]['#title'] = isset($value['#title']) ? $value['#title'] : NULL;
          $elements[$key]['#access'] = isset($value['#access']) ? $value['#access'] : NULL;
          $elements[$key]['#type'] = isset($value['#type']) ? $value['#type'] : NULL;
        } else {
          $elements[$key] = $form[$key];
        }
      }

      $this->buildForms[$form_id] += array(
        'form' => $elements,
      );
    }

    return $form;
  }
}
