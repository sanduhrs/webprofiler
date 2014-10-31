<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Form\PurgeForm.
 */

namespace Drupal\webprofiler\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\webprofiler\DataCollector\DatabaseDataCollector;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Profiler\Profiler;

/**
 * Class ProfilesFilterForm
 */
class ProfilesFilterForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'webprofiler_profiles_filter';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['ip'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('IP'),
      '#size' => 30,
      '#default_value' => $this->getRequest()->query->get('ip'),
    );

    $form['url'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Url'),
      '#size' => 30,
      '#default_value' => $this->getRequest()->query->get('url'),
    );

    $form['method'] = array(
      '#type' => 'select',
      '#title' => $this->t('Method'),
      '#options' => array('GET' => 'GET', 'POST' => 'POST'),
      '#default_value' => $this->getRequest()->query->get('method'),
    );

    $limits = array(10, 50, 100);
    $form['limit'] = array(
      '#type' => 'select',
      '#title' => $this->t('Limit'),
      '#options' => array_combine($limits, $limits),
      '#default_value' => $this->getRequest()->query->get('limit'),
    );

    $form['actions'] = array('#type' => 'actions');
    $form['actions']['filter'] = array(
      '#type' => 'submit',
      '#value' => t('Filter'),
      '#attributes' => array('class' => array('button--primary')),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $ip = $form_state->getValue('ip');// ['values']['ip'];
    $url = $form_state->getValue('url');
    $method = $form_state->getValue('method');
    $limit = $form_state->getValue('limit');

    $url = new Url('webprofiler.admin_list', array(), array(
      'query' => array(
        'ip' => $ip,
        'url' => $url,
        'method' => $method,
        'limit' => $limit,
      )
    ));

    $form_state->setRedirectUrl($url);
  }
}
