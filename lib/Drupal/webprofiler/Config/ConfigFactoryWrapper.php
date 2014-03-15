<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Config\ConfigFactoryWrapper.
 */

namespace Drupal\webprofiler\Config;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageDefault;
use Drupal\webprofiler\DataCollector\ConfigDataCollector;

/**
 * Wraps a config factory to be able to figure out all used config files.
 */
class ConfigFactoryWrapper implements ConfigFactoryInterface {

  /**
   * The wrapped config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The config data collector.
   *
   * @var \Drupal\webprofiler\DataCollector\ConfigDataCollector
   */
  protected $configDataCollector;

  /**
   * Constructs a new ConfigFactoryWrapper.
   *
   * @param \Drupal\webprofiler\DataCollector\ConfigDataCollector $configDataCollector
   *   The config data collector.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory.
   */
  public function __construct(ConfigDataCollector $configDataCollector, ConfigFactoryInterface $configFactory) {
    $this->configFactory = $configFactory;
    $this->configDataCollector = $configDataCollector;
  }

  /**
   * {@inheritdoc}
   */
  public function setOverrideState($state) {
    return $this->configFactory->setOverrideState($state);
  }

  /**
   * {@inheritdoc}
   */
  public function getOverrideState() {
    return $this->configFactory->getOverrideState();
  }

  /**
   * {@inheritdoc}
   */
  public function get($name) {
    $result = $this->configFactory->get($name);
    $this->configDataCollector->addConfigName($name);
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple(array $names) {
    $result = $this->configFactory->loadMultiple($names);
    foreach (array_keys($result) as $name) {
      $this->configDataCollector->addConfigName($name);
    }
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function reset($name = NULL) {
    return $this->configFactory->reset($name);
  }

  /**
   * {@inheritdoc}
   */
  public function rename($old_name, $new_name) {
    return $this->configFactory->rename($old_name, $new_name);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheKey($name) {
    return $this->configFactory->getCacheKey($name);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheKeys($name) {
    return $this->configFactory->getCacheKeys($name);
  }

  /**
   * {@inheritdoc}
   */
  public function clearStaticCache() {
    return $this->configFactory->clearStaticCache();
  }

  /**
   * {@inheritdoc}
   */
  public function setLanguage(Language $language = NULL) {
    return $this->configFactory->setLanguage($language);
  }

  /**
   * {@inheritdoc}
   */
  public function setLanguageFromDefault(LanguageDefault $language_default) {
    return $this->configFactory->setLanguageFromDefault($language_default);
  }

  /**
   * {@inheritdoc}
   */
  public function getLanguage() {
    return $this->configFactory->getLanguage();
  }

  /**
   * {@inheritdoc}
   */
  public function getLanguageConfigNames(array $names) {
    return $this->configFactory->getLanguageConfigNames($names);
  }

  /**
   * {@inheritdoc}
   */
  public function getLanguageConfigName($langcode, $name) {
    return $this->configFactory->getLanguageConfigName($langcode, $name);
  }

  /**
   * {@inheritdoc}
   */
  public function listAll($prefix = '') {
    return $this->configFactory->listAll($prefix);
  }

  /**
   * {@inheritdoc}
   */
  public function addOverride(ConfigFactoryOverrideInterface $config_factory_override) {
    return $this->configFactory->addOverride($config_factory_override);
  }

} 
