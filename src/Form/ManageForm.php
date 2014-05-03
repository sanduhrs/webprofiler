<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Form\PurgeForm.
 */

namespace Drupal\webprofiler\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 *
 */
class ManageForm extends FormBase {

  /**
   * @var \Symfony\Component\HttpKernel\Profiler\Profiler
   */
  private $profiler;

  /**
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  private $config_factory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('profiler'),
      $container->get('config.factory')
    );
  }

  /**
   * @param Profiler $profiler
   * @param ConfigFactoryInterface $config_factory
   */
  public function __construct(Profiler $profiler, ConfigFactoryInterface $config_factory) {
    $this->profiler = $profiler;
    $this->config_factory = $config_factory;
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

    $storage = $this->config_factory->get('webprofiler.config')->get('storage');

    $form['purge'] = array(
      '#type' => 'details',
      '#title' => $this->t('Purge profiles'),
      '#open' => TRUE,
    );

    $form['purge']['purge'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Purge'),
      '#submit' => array(array($this, 'purge')),
    );

    $form['purge']['purge-help'] = array(
      '#markup' => '<div class="form-item">' . $this->t('Purge %storage profiles.', array('%storage' => $storage)) . '</div>',
    );

    $form['data'] = array(
      '#type' => 'details',
      '#title' => $this->t('Data'),
      '#open' => TRUE,
    );

    $form['data']['export'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Export'),
      '#submit' => array(array($this, 'export')),
    );

    $form['data']['export-help'] = array(
      '#markup' => '<div class="form-item">' . $this->t('Export all %storage profiles.', array('%storage' => $storage)) . '</div>',
    );

    return $form;
  }

  /**
   * Purges profiles.
   */
  public function purge(array &$form, array &$form_state) {
    $this->profiler->purge();
    drupal_set_message($this->t('Profiles purged'));
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
