<?php

/**
 * @file
 * Contains Drupal\webprofiler\Command\ExportCommand.
 */

namespace Drupal\webprofiler\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ExportCommand extends Command implements ContainerAwareInterface {

  private $container;

  /**
   * {@inheritdoc}
   */
  public function setContainer(ContainerInterface $container = NULL) {
    $this->container = $container;
  }

  /**
   * @return ContainerInterface
   */
  protected function getContainer() {
    if (NULL === $this->container) {
      $this->container = $this->getApplication()->getKernel()->getContainer();
    }

    return $this->container;
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('webprofiler:export')
      ->setDescription('Exports Weprofiler profile/s.')
      ->addArgument('id', InputArgument::OPTIONAL, 'Profile id')
      ->addOption('destination_directory', 'dd', InputOption::VALUE_REQUIRED, 'Destination directory to store exported file/s.');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $id = $input->getArgument('id');
    $profiler = $this->getContainer()->get('profiler');

    if ($id) {
      $text = 'Exporting ' . $id;
      $profile = $profiler->loadProfile($id);
      if($profile) {
        $data = $profiler->export($profile);
      } else {
        $output->writeln('No profile with id ' . $id);
      }
    }
    else {
      $text = 'Exporting all';
    }

    $output->writeln($data);
  }
}
