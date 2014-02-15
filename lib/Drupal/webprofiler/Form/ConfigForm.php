<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Form\ConfigForm.
 */

namespace Drupal\webprofiler\Form;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 *
 */
class ConfigForm extends ConfigFormBase {

  private $profiler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('profiler')
    );
  }

  /**
   * @param ConfigFactory $config_factory
   * @param Profiler $profiler
   */
  public function __construct(ConfigFactory $config_factory, Profiler $profiler) {
    parent::__construct($config_factory);

    $this->profiler = $profiler;
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'webprofiler_config';
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
    $config = $this->configFactory->get('webprofiler.config');

    $form['purge_on_cache_clear'] = array(
      '#type' => 'checkbox',
      '#title' => $this->t('Purge on cache clear'),
      '#description' => $this->t('Deletes all profiler files during cache clear.'),
      '#default_value' => $config->get('purge_on_cache_clear'),
    );

    $form['storage'] = array(
      '#type' => 'select',
      '#title' => $this->t('Storage backend'),
      '#description' => $this->t('Choose were to store profiler data.'),
      '#options' => array('profiler.file_storage' => $this->t('File'), 'profiler.database_storage' => $this->t('Database')),
      '#default_value' => $config->get('storage'),
    );

    return parent::buildForm($form, $form_state);
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
    $this->configFactory->get('webprofiler.config')
      ->set('purge_on_cache_clear', $form_state['values']['purge_on_cache_clear'])
      ->set('storage', $form_state['values']['storage'])
      ->save();

    parent::submitForm($form, $form_state);
  }
}
