<?php

/**
 * @file
 * Contains Drupal\webprofiler\Command\BenchmarkCommand.
 */

namespace Drupal\webprofiler\Command;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Message\ResponseInterface;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\AppConsole\Command\ContainerAwareCommand;

class BenchmarkCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('webprofiler:benchmark')
      ->setDescription($this->trans('commands.webprofiler.benchmark.description'))
      ->addArgument('url', InputArgument::REQUIRED, $this->trans('commands.webprofiler.benchmark.arguments.url'))
      ->addOption('runs', NULL, InputOption::VALUE_REQUIRED, $this->trans('commands.webprofiler.benchmark.options.runs'), 100);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $runs = $input->getOption('runs');
    $url = $input->getArgument('url');

    /** @var \GuzzleHttp\ClientInterface $http_client */
    $http_client = $this->getContainer()->get('http_client');

    $progress = new ProgressBar($output, $runs + 1);
    $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

    $datas = [];
    for ($i = 0; $i < $runs; $i++) {
      $datas[] = $this->getData($http_client, $url);
      $progress->setMessage($this->trans('commands.webprofiler.benchmark.progress.get'));
      $progress->advance();
    }

    $progress->setMessage($this->trans('commands.webprofiler.benchmark.progress.compute_avg'));
    $avg = $this->computeAvg($datas);
    $progress->advance();

    $progress->setMessage($this->trans('commands.webprofiler.benchmark.progress.done'));
    $progress->finish();
    $output->writeln('');

    $output->writeln('Avg memory: ' . sprintf('%.1f MB', $avg->getMemory() / 1024 / 1024));
    $output->writeln('Avg time: ' . sprintf('%.0f ms', $avg->getTime()));
  }

  /**
   * @param \GuzzleHttp\ClientInterface $http_client
   * @param $url
   *
   * @return array
   */
  private function getData(ClientInterface $http_client, $url) {
    /** @var \GuzzleHttp\Message\ResponseInterface $response */
    $response = $http_client->get($url);

    $token = $response->getHeader('X-Debug-Token');

    /** @var \Drupal\webprofiler\Profiler\Profiler $profiler */
    $profiler = $this->getContainer()->get('profiler');

    /** @var \Symfony\Component\HttpKernel\Profiler\Profile $profile */
    $profile = $profiler->loadProfile($token);

    /** @var \Drupal\webprofiler\DataCollector\TimeDataCollector $timeDataCollector */
    $timeDataCollector = $profile->getCollector('time');

    return new BenchmarkData(
      $token,
      $timeDataCollector->getMemory(),
      $timeDataCollector->getDuration());
  }

  /**
   * @param \Drupal\webprofiler\Command\BenchmarkData[] $datas
   *
   * @return \Drupal\webprofiler\Command\BenchmarkData
   */
  private function computeAvg($datas) {
    $profiles = count($datas);
    $totalTime = 0;
    $totalMemory = 0;

    foreach($datas as $data) {
      $totalTime += $data->getTime();
      $totalMemory += $data->getMemory();
    }

    return new BenchmarkData(NULL, $totalMemory / $profiles, $totalTime / $profiles);
  }
}
