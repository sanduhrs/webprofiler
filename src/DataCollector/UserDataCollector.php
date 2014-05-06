<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Authentication\AuthenticationManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Component\Utility\String;
use Drupal\webprofiler\DrupalDataCollectorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Class UserDataCollector
 */
class UserDataCollector extends DataCollector implements DrupalDataCollectorInterface {

  use StringTranslationTrait, DrupalDataCollectorTrait;

  private $currentUser;
  private $authenticationManager;
  private $entityManager;
  private $configFactory;

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'user';
  }

  /**
   * @param AccountInterface $currentUser
   * @param AuthenticationManagerInterface $authenticationManager
   * @param EntityManagerInterface $entityManager
   * @param ConfigFactoryInterface $configFactory
   */
  public function __construct(AccountInterface $currentUser, AuthenticationManagerInterface $authenticationManager, EntityManagerInterface $entityManager, ConfigFactoryInterface $configFactory) {
    $this->currentUser = $currentUser;
    $this->authenticationManager = $authenticationManager;
    $this->entityManager = $entityManager;
    $this->configFactory = $configFactory;
  }

  /**
   * @return AccountInterface
   */
  public function name() {
    return String::checkPlain($this->data['name']);
  }

  /**
   * @return bool
   */
  public function authenticated() {
    return $this->data['authenticated'];
  }

  /**
   * @return array
   */
  public function roles() {
    return $this->data['roles'];
  }

  /**
   * @return string
   */
  public function provider() {
    return $this->data['provider'];
  }

  /**
   * @return string
   */
  public function anonymous() {
    return $this->data['anonymous'];
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
    $this->data['name'] = $this->currentUser->getUsername();
    $this->data['authenticated'] = $this->currentUser->isAuthenticated();

    $this->data['roles'] = array();
    $storage = $this->entityManager->getStorage('user_role');
    foreach ($this->currentUser->getRoles() as $role) {
      $entity = $storage->load($role);
      $this->data['roles'][] = $entity->label();
    }

    $this->data['provider'] = $this->authenticationManager->defaultProviderId();
    $this->data['anonymous'] = $this->configFactory->get('user.settings')->get('anonymous');
  }
}
