<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\webprofiler\DrupalDataCollectorInterface;
use Drupal\webprofiler\Form\FormBuilderWrapper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

class FormDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  private $form_builder;

  /**
   * {@inheritdoc}
   */
  public function getMenu() {
    return \Drupal::translation()->translate('Forms');
  }

  /**
   * {@inheritdoc}
   */
  public function getSummary() {
    return \Drupal::translation()->formatPlural(count($this->data['forms']), 'One form', '@count forms');
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
   * Returns the name of the collector.
   *
   * @return string The collector name
   *
   * @api
   */
  public function getName() {
    return 'form';
  }
}
