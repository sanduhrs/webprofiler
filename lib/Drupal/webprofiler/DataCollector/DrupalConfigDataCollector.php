<?php

namespace Drupal\webprofiler\DataCollector;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * DrupalConfigDataCollector.
 */
class DrupalConfigDataCollector extends DataCollector {

  private $module_handler;

  /**
   * Constructor.
   *
   * @param ModuleHandlerInterface $module_handler
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->module_handler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = NULL) {
    $this->data = array(
      'token' => $response->headers->get('X-Debug-Token'),
      'php_version' => PHP_VERSION,
      'xdebug_enabled' => extension_loaded('xdebug'),
      'eaccel_enabled' => extension_loaded('eaccelerator') && ini_get('eaccelerator.enable'),
      'apc_enabled' => extension_loaded('apc') && ini_get('apc.enabled'),
      'xcache_enabled' => extension_loaded('xcache') && ini_get('xcache.cacher'),
      'wincache_enabled' => extension_loaded('wincache') && ini_get('wincache.ocenabled'),
      'zend_opcache_enabled' => extension_loaded('Zend OPcache') && ini_get('opcache.enable'),
      'bundles' => array(),
      'sapi_name' => php_sapi_name()
    );

    $this->data['modules'] = $this->module_handler->getModuleList();
  }

  /**
   * Gets the token.
   *
   * @return string The token
   */
  public function getToken() {
    return $this->data['token'];
  }

  /**
   * Gets the PHP version.
   *
   * @return string The PHP version
   */
  public function getPhpVersion() {
    return $this->data['php_version'];
  }

  /**
   * Returns true if the XDebug is enabled.
   *
   * @return Boolean true if XDebug is enabled, false otherwise
   */
  public function hasXDebug() {
    return $this->data['xdebug_enabled'];
  }

  /**
   * Returns true if EAccelerator is enabled.
   *
   * @return Boolean true if EAccelerator is enabled, false otherwise
   */
  public function hasEAccelerator() {
    return $this->data['eaccel_enabled'];
  }

  /**
   * Returns true if APC is enabled.
   *
   * @return Boolean true if APC is enabled, false otherwise
   */
  public function hasApc() {
    return $this->data['apc_enabled'];
  }

  /**
   * Returns true if Zend OPcache is enabled
   *
   * @return Boolean true if Zend OPcache is enabled, false otherwise
   */
  public function hasZendOpcache() {
    return $this->data['zend_opcache_enabled'];
  }

  /**
   * Returns true if XCache is enabled.
   *
   * @return Boolean true if XCache is enabled, false otherwise
   */
  public function hasXCache() {
    return $this->data['xcache_enabled'];
  }

  /**
   * Returns true if WinCache is enabled.
   *
   * @return Boolean true if WinCache is enabled, false otherwise
   */
  public function hasWinCache() {
    return $this->data['wincache_enabled'];
  }

  /**
   * Returns true if any accelerator is enabled.
   *
   * @return Boolean true if any accelerator is enabled, false otherwise
   */
  public function hasAccelerator() {
    return $this->hasApc() || $this->hasZendOpcache() || $this->hasEAccelerator() || $this->hasXCache() || $this->hasWinCache();
  }

  /**
   * @return mixed
   */
  public function getModules() {
    return $this->data['modules'];
  }

  /**
   * Gets the PHP SAPI name.
   *
   * @return string The environment
   */
  public function getSapiName() {
    return $this->data['sapi_name'];
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'config';
  }
}
