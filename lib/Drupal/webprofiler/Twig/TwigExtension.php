<?php

/**
 * @file
 * Contains \Drupal\webprofiler\Twig\TwigExtension.
 */

namespace Drupal\webprofiler\Twig;

class TwigExtension extends \Twig_Extension {

  private $fileLinkFormat;
  private $rootDir;
  private $charset;

  /**
   * Constructor.
   *
   * @param string $fileLinkFormat The format for links to source files
   * @param string $rootDir The project root directory
   * @param string $charset The charset
   */
  public function __construct($fileLinkFormat = 'txmt://open?url=file://%%f&line=%%l', $rootDir = DRUPAL_ROOT, $charset = 'UTF-8') {
    $this->fileLinkFormat = empty($fileLinkFormat) ? ini_get('xdebug.file_link_format') : $fileLinkFormat;
    $this->rootDir = str_replace('\\', '/', $rootDir) . '/';
    $this->charset = $charset;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'drupal_webprofiler';
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters() {
    return array(
      new \Twig_SimpleFilter('abbr_class', array(
        $this,
        'abbrClass'
      ), array('is_safe' => array('html'))),
//      new \Twig_SimpleFilter('abbr_method', array(
//        $this,
//        'abbrMethod'
//      ), array('is_safe' => array('html'))),
//      new \Twig_SimpleFilter('format_args', array(
//        $this,
//        'formatArgs'
//      ), array('is_safe' => array('html'))),
//      new \Twig_SimpleFilter('format_args_as_text', array(
//        $this,
//        'formatArgsAsText'
//      )),
//      new \Twig_SimpleFilter('file_excerpt', array(
//        $this,
//        'fileExcerpt'
//      ), array('is_safe' => array('html'))),
//      new \Twig_SimpleFilter('format_file', array(
//        $this,
//        'formatFile'
//      ), array('is_safe' => array('html'))),
//      new \Twig_SimpleFilter('format_file_from_text', array(
//        $this,
//        'formatFileFromText'
//      ), array('is_safe' => array('html'))),
      new \Twig_SimpleFilter('file_link', array(
        $this,
        'getFileLink'
      ), array('is_safe' => array('html'))),
    );
  }

  public function abbrClass($class) {
    $parts = explode('\\', $class);
    $short = array_pop($parts);

    return sprintf("<abbr title=\"%s\">%s</abbr>", $class, $short);
  }

  /**
   * Returns the link for a given file/line pair.
   *
   * @param string $file An absolute file path
   * @param integer $line The line number
   *
   * @return string A link of false
   */
  public function getFileLink($file, $line) {
    if ($this->fileLinkFormat && is_file($file)) {
      return strtr($this->fileLinkFormat, array('%f' => $file, '%l' => $line));
    }

    return FALSE;
  }

}
