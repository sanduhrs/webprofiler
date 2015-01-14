<?php

/**
 * @file
 * Contains Drupal\webprofiler\Command\ListCommand.
 */

namespace Drupal\webprofiler\Command;

use Drupal\AppConsole\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ListCommand
 */
class ListCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('webprofiler:list')
      ->setDescription($this->trans('command.webprofiler.list.description'))
      ->addOption('ip', NULL, InputOption::VALUE_REQUIRED, $this->trans('command.webprofiler.list.option_ip'), NULL)
      ->addOption('url', NULL, InputOption::VALUE_REQUIRED, $this->trans('command.webprofiler.list.option_url'), NULL)
      ->addOption('method', NULL, InputOption::VALUE_REQUIRED, $this->trans('command.webprofiler.list.option_method'), NULL)
      ->addOption('limit', NULL, InputOption::VALUE_REQUIRED, $this->trans('command.webprofiler.list.option_limit'), 10);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $ip = $input->getOption('ip');
    $url = $input->getOption('url');
    $method = $input->getOption('method');
    $limit = $input->getOption('limit');

    /** @var \Drupal\webprofiler\Profiler\Profiler $profiler */
    $profiler = $this->getContainer()->get('profiler');
    $profiles = $profiler->find($ip, $url, $limit, $method, '', '');

    $rows = [];
    foreach ($profiles as $profile) {
      $row = [];

      $row[] = $profile['token'];
      $row[] = $profile['ip'];
      $row[] = $profile['method'];
      $row[] = $profile['url'];
      $row[] = date($this->trans('command.webprofiler.list.rows.time'), $profile['time']);

      $rows[] = $row;
    }

    $table = new Table($output);
    $table
      ->setHeaders(array(
        $this->trans('command.webprofiler.list.header.token'),
        $this->trans('command.webprofiler.list.header.ip'),
        $this->trans('command.webprofiler.list.header.method'),
        $this->trans('command.webprofiler.list.header.url'),
        $this->trans('command.webprofiler.list.header.time'),
      ))
      ->setRows($rows);
    $table->render();
  }
}
