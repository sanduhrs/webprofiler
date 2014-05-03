<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\webprofiler\Form\FormBuilderWrapper;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Class FormDataCollector
 */
class FormDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  private $form_builder;

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'form';
  }

  /**
   * {@inheritdoc}
   */
  public function getMenu() {
    return $this->t('Forms');
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    return $this->t('Rendered forms: @forms', array('@forms' => count($this->data['forms'])));
  }

  /**
   * @param FormBuilderWrapper $form_builder
   */
  public function __construct(FormBuilderWrapper $form_builder) {
    $this->form_builder = $form_builder;
  }

  /**
   * Collects data for the given Request and Response.
   *
   * @param Request $request A Request instance
   * @param Response $response A Response instance
   * @param \Exception $exception An Exception instance
   *
   * @api
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $this->data['forms'] = $this->form_builder->getBuildForm();
  }

  /**
   * @return array
   */
  public function getForms() {
    return $this->data['forms'];
  }

  /**
   * {@inheritdoc}
   */
  public function getPanel() {
    $build = array();

    if (count($this->getForms()) == 0) {
      $build['no-forms'] = array(
        '#markup' => $this->t('No forms.'),
      );

      return $build;
    }

    foreach ($this->getForms() as $form) {
      $formData = $form['form'];

      $build[$formData['#form_id']]['class'] = array(
        '#markup' => '<h3>#' . $formData['#form_id'] . ': ' . $form['class'] . '</h3>',
      );

      $rows = array();
      foreach ($formData as $key => $value) {
        if (strpos($key, '#') !== 0) {
          $row = array();

          $row[] = $key;
          $row[] = isset($value['#title']) ? $value['#title'] : '-';
          $row[] = isset($value['#access']) ? $value['#access'] : '-';
          $row[] = isset($value['#type']) ? $value['#type'] : '-';

          $rows[] = $row;
        }
      }

      $build[$formData['#form_id']]['fields'] = array(
        '#theme' => 'table',
        '#rows' => $rows,
        '#header' => array(
          $this->t('Name'),
          $this->t('Title'),
          $this->t('Access'),
          $this->t('Type'),
        ),
      );
    }

    return $build;
  }
}
