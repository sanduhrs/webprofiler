<?php

namespace Drupal\webprofiler\Views;

use Drupal\views\ViewExecutable;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TraceableViewExecutable
 */
class TraceableViewExecutable extends ViewExecutable {

  /**
   * Gets the build time.
   *
   * @return float
   */
  public function getBuildTime() {
    return $this->build_time;
  }

  /**
   * Gets the execute time.
   *
   * @return float
   */
  public function getExecuteTime() {
    return $this->execute_time;
  }

  /**
   * Gets the render time.
   *
   * @return float
   */
  public function getRenderTime() {
    return $this->render_time;
  }

  /**
   * {@inheritdoc}
   */
  public function render($display_id = NULL) {
    $this->execute($display_id);

    $start = microtime(TRUE);

    // Check to see if the build failed.
    if (!empty($this->build_info['fail'])) {
      return;
    }
    if (!empty($this->build_info['denied'])) {
      return;
    }

    $exposed_form = $this->display_handler->getPlugin('exposed_form');
    $exposed_form->preRender($this->result);

    $module_handler = \Drupal::moduleHandler();

    // Check for already-cached output.
    if (!empty($this->live_preview)) {
      $cache = FALSE;
    }
    else {
      $cache = $this->display_handler->getPlugin('cache');
    }

    if ($cache && $cache->cacheGet('output')) {
    }
    else {
      if ($cache) {
        $cache->cacheStart();
      }

      // Run preRender for the pager as it might change the result.
      if (!empty($this->pager)) {
        $this->pager->preRender($this->result);
      }

      // Initialize the style plugin.
      $this->initStyle();

      if (!isset($this->response)) {
        // Set the response so other parts can alter it.
        $this->response = new Response('', 200);
      }

      // Give field handlers the opportunity to perform additional queries
      // using the entire resultset prior to rendering.
      if ($this->style_plugin->usesFields()) {
        foreach ($this->field as $id => $handler) {
          if (!empty($this->field[$id])) {
            $this->field[$id]->preRender($this->result);
          }
        }
      }

      $this->style_plugin->preRender($this->result);

      // Let each area handler have access to the result set.
      $areas = array('header', 'footer');
      // Only call preRender() on the empty handlers if the result is empty.
      if (empty($this->result)) {
        $areas[] = 'empty';
      }
      foreach ($areas as $area) {
        foreach ($this->{$area} as $handler) {
          $handler->preRender($this->result);
        }
      }

      // Let modules modify the view just prior to rendering it.
      $module_handler->invokeAll('views_pre_render', array($this));

      // Let the themes play too, because pre render is a very themey thing.
      if (isset($GLOBALS['base_theme_info']) && isset($GLOBALS['theme'])) {
        foreach ($GLOBALS['base_theme_info'] as $base) {
          $module_handler->invoke($base->getName(), 'views_pre_render', array($this));
        }

        $module_handler->invoke($GLOBALS['theme'], 'views_pre_render', array($this));
      }

      $this->display_handler->output = $this->display_handler->render();
      if ($cache) {
        $cache->cacheSet('output');
      }
    }

    $exposed_form->postRender($this->display_handler->output);

    if ($cache) {
      $cache->postRender($this->display_handler->output);
    }

    // Let modules modify the view output after it is rendered.
    $module_handler->invokeAll('views_post_render', array($this, &$this->display_handler->output, $cache));

    // Let the themes play too, because post render is a very themey thing.
    if (isset($GLOBALS['base_theme_info']) && isset($GLOBALS['theme'])) {
      foreach ($GLOBALS['base_theme_info'] as $base) {
        $module_handler->invoke($base->getName(), 'views_post_render', array($this));
      }

      $module_handler->invoke($GLOBALS['theme'], 'views_post_render', array($this));
    }

    $this->render_time = microtime(TRUE) - $start;

    return $this->display_handler->output;
  }
}
