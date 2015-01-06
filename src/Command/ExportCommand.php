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

class ExportCommand extends Command {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('webprofiler:export')
      ->setDescription('Exports Weprofiler profile/s.')
      ->addArgument('id', InputArgument::OPTIONAL, 'Profile id');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $id = $input->getArgument('id');
    if ($id) {
      $text = 'Exporting ' . $id;
    }
    else {
      $text = 'Exporting all';
    }

    $output->writeln($text);
  }
}
