<?php

/**
 * @file
 * Contains Drupal\webprofiler\Command\CollectorsCommand.
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
 * Class CollectorsCommand
 */
class CollectorsCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('webprofiler:collectors')
      ->setDescription($this->trans('commands.webprofiler.collectors.description'))
      ->addArgument('id', InputArgument::REQUIRED, $this->trans('commands.webprofiler.collectors.arguments.id'));;
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $token = $input->getArgument('token');

    /** @var \Drupal\webprofiler\Profiler\Profiler $profiler */
    $profiler = $this->getContainer()->get('profiler');
    $profile = $profiler->loadProfile($token);

    $collectors = $profile->getCollectors();

    $output->writeln(array_keys($collectors));
  }

  /**
   * {@inheritdoc}
   */
  public function showMessage($output, $message, $type='info') {
  }
}
