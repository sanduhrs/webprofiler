<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Form\ConfigForm.
 */

namespace Drupal\webprofiler\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\webprofiler\Profiler\TemplateManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * Class ConfigForm
 */
class ConfigForm extends ConfigFormBase {

  /**
   * @var \Symfony\Component\HttpKernel\Profiler\Profiler
   */
  private $profiler;

  /**
   * @var array
   */
  private $templates;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('profiler'),
      $container->getParameter('data_collector.templates')
    );
  }

  /**
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   * @param \Symfony\Component\HttpKernel\Profiler\Profiler $profiler
   * @param array $templates
   */
  public function __construct(ConfigFactoryInterface $config_factory, Profiler $profiler, $templates) {
    parent::__construct($config_factory);

    $this->profiler = $profiler;
    $this->templates = $templates;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'webprofiler_config';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $this->profiler->disable();
    $config = $this->config('webprofiler.config');

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
      '#options' => array(
        'profiler.file_storage' => $this->t('File'),
        'profiler.database_storage' => $this->t('Database')
      ),
      '#default_value' => $config->get('storage'),
    );

    $form['exclude'] = array(
      '#type' => 'textarea',
      '#title' => $this->t('Exclude'),
      '#default_value' => $config->get('exclude'),
      '#description' => $this->t('Path to exclude for profiling. One path per line.')
    );

    $form['active_toolbar_items'] = array(
      '#type' => 'checkboxes',
      '#title' => $this->t('Active toolbar items'),
      '#options' => $this->getCollectors(),
      '#description' => $this->t('Choose which items to show into the toolbar.'),
      '#default_value' => $config->get('active_toolbar_items'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $this->config('webprofiler.config')
      ->set('purge_on_cache_clear', $form_state['values']['purge_on_cache_clear'])
      ->set('storage', $form_state['values']['storage'])
      ->set('exclude', $form_state['values']['exclude'])
      ->set('active_toolbar_items', $form_state['values']['active_toolbar_items'])
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * @return array
   */
  private function getCollectors() {
    $options = array();
    foreach ($this->templates as $template) {
      // drupal collector should not be disabled
      if ($template[0] != 'drupal') {
        $options[$template[0]] = $template[2];
      }
    }

    asort($options);

    return $options;
  }
}
