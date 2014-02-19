<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Form\PurgeForm.
 */

namespace Drupal\webprofiler\Form;

use Drupal\Core\Form\FormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 *
 */
class ManageForm extends FormBase {

  private $profiler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('profiler')
    );
  }

  /**
   * @param Profiler $profiler
   */
  public function __construct(Profiler $profiler) {
    $this->profiler = $profiler;
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'webprofiler_purge';
  }

  /**
   * Form constructor.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $form_state
   *   An associative array containing the current state of the form.
   *
   * @return array
   *   The form structure.
   */
  public function buildForm(array $form, array &$form_state) {
    $this->profiler->disable();

    $form['purge'] = array(
      '#type' => 'fieldset',
      '#title' => t('Purge profiles'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    );

    $form['purge']['purge'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Purge'),
      '#submit' => array(array($this, 'purge')),
    );

    $form['data'] = array(
      '#type' => 'fieldset',
      '#title' => t('Data'),
      '#collapsible' => FALSE,
      '#collapsed' => FALSE,
    );

    $form['data']['export'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Export'),
      '#submit' => array(array($this, 'export')),
    );

    return $form;
  }

  /**
   * Purges profiles.
   */
  public function purge(array &$form, array &$form_state) {
    $this->profiler->purge();
    drupal_set_message(t('Profiles purged'));
  }

  /**
   * Purges profiles.
   */
  public function export(array &$form, array &$form_state) {
    $form_state['redirect_route']['route_name'] = 'webprofiler.all_export';
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param array $form_state
   *   An associative array containing the current state of the form.
   */
  public function submitForm(array &$form, array &$form_state) {
  }
}
