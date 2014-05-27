<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Form\PurgeForm.
 */

namespace Drupal\webprofiler\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\webprofiler\DataCollector\DatabaseDataCollector;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * Class ManageForm
 */
class QueryFilterForm extends FormBase {

  /**
   * @var \Symfony\Component\HttpKernel\Profiler\Profiler
   */
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
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'webprofiler_query_filter';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $form['type'] = array(
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#options' => array('select', 'update', 'insert', 'delete'),
    );

    $token = \Drupal::request()->attributes->get('token');
    $profile = $this->profiler->loadProfile($token);

    /** @var DatabaseDataCollector $databaseCollector */
    $databaseCollector = $profile->getCollector('database');

    $queries = $databaseCollector->getQueries();

    $callers = array();
    foreach ($queries as $query) {
      if ($query['caller']['class']) {
        $callers[$query['caller']['class']] = $query['caller']['class'];
      }
    }

    $form['caller'] = array(
      '#type' => 'select',
      '#title' => $this->t('Caller'),
      '#options' => $callers,
    );

    $form['purge']['purge'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
  }
}
