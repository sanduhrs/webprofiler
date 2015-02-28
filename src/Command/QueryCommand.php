<?php

/**
 * @file
 * Contains Drupal\webprofiler\Command\QueryCommand.
 */

namespace Drupal\webprofiler\Command;

use Drupal\AppConsole\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ExpressionLanguage;

/**
 * Class QueryCommand
 */
class QueryCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('webprofiler:query')
      ->setDescription($this->trans('commands.webprofiler.list.description'))
      ->addArgument('token', InputArgument::REQUIRED, $this->trans('commands.webprofiler.benchmark.arguments.url'))
      ->addArgument('collector', InputArgument::REQUIRED, $this->trans('commands.webprofiler.benchmark.arguments.url'))
      ->addArgument('query', InputArgument::REQUIRED, $this->trans('commands.webprofiler.benchmark.arguments.url'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $token = $input->getArgument('token');
    $collector = $input->getArgument('collector');
    $query = $input->getArgument('query');

    /** @var \Drupal\webprofiler\Profiler\Profiler $profiler */
    $profiler = $this->getContainer()->get('profiler');
    $profile = $profiler->loadProfile($token);

    $language = new ExpressionLanguage();

    $collectors = $profile->getCollectors();
    $result = $language->evaluate(
      $query,
      $collectors[$collector]->getData()
    );

    $output->writeln($result);
//    print_r($collectors['drupal']);
  }

  /**
   * {@inheritdoc}
   */
  public function showMessage($output, $message, $type='info') {
  }
}
