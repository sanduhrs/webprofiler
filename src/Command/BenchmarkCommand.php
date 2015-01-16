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
      ->setDescription($this->trans('command.webprofiler.benchmark.description'))
      ->addArgument('url', InputArgument::REQUIRED, $this->trans('command.webprofiler.benchmark.arguments.url'))
      ->addOption('iterations', NULL, InputOption::VALUE_REQUIRED, $this->trans('command.webprofiler.benchmark.options.iterations'), 100);
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $iterations = $input->getOption('iterations');
    $url = $input->getArgument('url');

    /** @var \GuzzleHttp\ClientInterface $http_client */
    $http_client = $this->getContainer()->get('http_client');

    $progress = new ProgressBar($output, $iterations);
    $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

    $datas = [];
    for ($i = 0; $i < $iterations; $i++) {
      $datas[] = $this->getData($http_client, $url);
      $progress->setMessage($this->trans('command.webprofiler.benchmark.progress.get'));
      $progress->advance();
    }

    $progress->finish();
    $output->writeln('');
  }

  /**
   * @param \GuzzleHttp\ClientInterface $http_client
   * @param $url
   *
   * @return array
   */
  private function getData(ClientInterface $http_client, $url) {
    /** @var ResponseInterface $response */
    $response = $http_client->get($url);

    $token = $response->getHeader('X-Debug-Token');

    /** @var \Drupal\webprofiler\Profiler\Profiler $profiler */
    $profiler = $this->getContainer()->get('profiler');

    /** @var \Symfony\Component\HttpKernel\Profiler\Profile $profile */
    $profile = $profiler->loadProfile($token);

    /** @var \Drupal\webprofiler\DataCollector\TimeDataCollector $timeDataCollector */
    $timeDataCollector = $profile->getCollector('time');

    $memory = $timeDataCollector->getMemory();
    $duration = $timeDataCollector->getDuration();

    return new BenchmarkData($token, $memory, $duration);
  }
}
