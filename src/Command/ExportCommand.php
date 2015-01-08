<?php

/**
 * @file
 * Contains Drupal\webprofiler\Command\ExportCommand.
 */

namespace Drupal\webprofiler\Command;

use Drupal\AppConsole\Command\ContainerAwareCommand;
use Drupal\Core\Archiver\ArchiveTar;
use Drupal\webprofiler\Profiler\Profiler;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ExportCommand
 */
class ExportCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('webprofiler:export')
      ->setDescription('Exports Webprofiler profile/s to file.')
      ->addArgument('id', InputArgument::OPTIONAL, 'Profile id')
      ->addOption('directory', 'd', InputOption::VALUE_REQUIRED, 'Destination directory to store exported file/s.', '/tmp');
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $id = $input->getArgument('id');
    $directory = $input->getOption('directory');

    /** @var \Drupal\webprofiler\Profiler\Profiler $profiler */
    $profiler = $this->getContainer()->get('profiler');

    try {
      if ($id) {
        $filename = $this->exportSingle($profiler, $id, $directory);
      }
      else {
        $filename = $this->exportAll($profiler, $directory, $output);
      }

      $output->writeln('<info>Succesfully exported to ' . $filename . '</info>');
    } catch (\Exception $e) {
      $output->writeln('<error>' . $e->getMessage() . '</error>');
    }
  }

  /**
   * Exports a single profile.
   *
   * @param \Drupal\webprofiler\Profiler\Profiler $profiler
   * @param int $id
   * @param string $directory
   *
   * @return string
   *
   * @throws \Exception
   */
  private function exportSingle(Profiler $profiler, $id, $directory) {
    $profile = $profiler->loadProfile($id);
    if ($profile) {
      $data = $profiler->export($profile);

      $filename = $directory . DIRECTORY_SEPARATOR . $id . '.txt';
      if (file_put_contents($filename, $data) === FALSE) {
        throw new \Exception('Error writing file ' . $filename);
      }
    }
    else {
      throw new \Exception('No profile with id ' . $id);
    }

    return $filename;
  }

  /**
   * Exports all stored profiles (cap limit at 1000 items).
   *
   * @param \Drupal\webprofiler\Profiler\Profiler $profiler
   * @param string $directory
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *
   * @return string
   */
  private function exportAll(Profiler $profiler, $directory, $output) {
    $filename = $directory . DIRECTORY_SEPARATOR . 'profiles.tar.gz';
    $archiver = new ArchiveTar($filename, 'gz');
    $profiles = $profiler->find(NULL, NULL, 1000, NULL, '', '');
    $progress = new ProgressBar($output, count($profiles));
    $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

    $files = array();
    $progress->start();
    foreach ($profiles as $profile) {
      $data = $profiler->export($profiler->loadProfile($profile['token']));
      $profileFilename = $directory . "/{$profile['token']}.txt";
      file_put_contents($profileFilename, $data);
      $files[] = $profileFilename;
      $progress->setMessage('Exporting profiles...');
      $progress->advance();
    }

    $progress->setMessage('Create archive...');
    $progress->advance();
    $archiver->createModify($files, '', $directory);

    $progress->setMessage('Delete temp files...');
    $progress->advance();
    foreach ($files as $file) {
      unlink($file);
    }

    $progress->finish();
    $output->writeln('');

    return $filename;
  }
}
