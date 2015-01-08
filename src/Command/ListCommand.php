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
      ->setDescription('Lists Webprofiler profiles.')
      ->addOption('ip', NULL, InputOption::VALUE_REQUIRED, 'Filter by IP.', NULL)
      ->addOption('url', NULL, InputOption::VALUE_REQUIRED, 'Filter by URL.', NULL)
      ->addOption('method', NULL, InputOption::VALUE_REQUIRED, 'Filter by HTTP method.', NULL)
      ->addOption('limit', NULL, InputOption::VALUE_REQUIRED, 'Limit printed profiles. Defaults to 10.', 10);
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

    $dateFormatter = $this->getContainer()->get('date.formatter');

    $rows = [];
    foreach ($profiles as $profile) {
      $row = [];

      $row[] = $profile['token'];
      $row[] = $profile['ip'];
      $row[] = $profile['method'];
      $row[] = $profile['url'];
      $row[] = $dateFormatter->format($profile['time']);

      $rows[] = $row;
    }

    $table = new Table($output);
    $table
      ->setHeaders(array('Token', 'Ip', 'Method', 'URL', 'Time'))
      ->setRows($rows);
    $table->render();
  }
}
