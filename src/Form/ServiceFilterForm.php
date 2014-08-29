<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Form\ServiceFilterForm.
 */

namespace Drupal\webprofiler\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\webprofiler\DataCollector\DatabaseDataCollector;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Profiler\Profile;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * Class ServiceFilterForm
 */
class ServiceFilterForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'webprofiler_service_filter';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['sid'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Id'),
      '#size' => 30,
      '#default_value' => $this->getRequest()->query->get('sid'),
    );

    $form['class'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Class'),
      '#size' => 30,
      '#default_value' => $this->getRequest()->query->get('class'),
    );

    $form['tags'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Tags'),
      '#size' => 30,
      '#default_value' => $this->getRequest()->query->get('tags'),
    );

    $form['initialized'] = array(
      '#type' => 'select',
      '#title' => $this->t('Initialized'),
      '#options' => array(
        '' => $this->t('Any'),
        0 => $this->t('No'),
        1 => $this->t('Yes'),
      ),
      '#default_value' => $this->getRequest()->query->get('initialized'),
    );

    $form['service-filter'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
      '#prefix' => '<div id="filter-service-wrapper">',
      '#suffix' => '</div>',
      '#attributes' => array('class' => array('button--primary')),
    );

    $form['#attributes'] = array('id' => array('service-filter-form'));

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }
}
