<?php

/**
 * @file
 * Contains Drupal\webprofiler\Command\BenchmarkCommand.
 */

namespace Drupal\webprofiler\Command;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Message\ResponseInterface;
use GuzzleHttp\Subscriber\Cookie;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\AppConsole\Command\ContainerAwareCommand;
use Symfony\Component\CssSelector\CssSelector;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

class BenchmarkCommand extends ContainerAwareCommand {

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this
      ->setName('webprofiler:benchmark')
      ->setDescription($this->trans('commands.webprofiler.benchmark.description'))
      ->addArgument('url', InputArgument::REQUIRED, $this->trans('commands.webprofiler.benchmark.arguments.url'))
      ->addOption('runs', NULL, InputOption::VALUE_REQUIRED, $this->trans('commands.webprofiler.benchmark.options.runs'), 100)
      ->addOption('file', NULL, InputOption::VALUE_REQUIRED, $this->trans('commands.webprofiler.benchmark.options.file'))
      ->addOption('cache-rebuild', 'cr', InputOption::VALUE_NONE, $this->trans('commands.webprofiler.benchmark.options.cache_rebuild'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $runs = $input->getOption('runs');
    $file = $input->getOption('file');
    $cache_rebuild = $input->getOption('cache-rebuild');
    $url = $input->getArgument('url');
    $steps = 3;

    if ($cache_rebuild) {
      $steps = 4;
    }

    /** @var \Drupal\Core\Http\Client $client */
    $client = $this->getContainer()->get('http_client');

    $progress = new ProgressBar($output, $runs + $steps);
    $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

    if ($cache_rebuild) {
      $progress->setMessage($this->trans('commands.webprofiler.benchmark.progress.cache_rebuild'));
      $this->RebuildCache();
      $progress->advance();
    }

    $datas = [];
    for ($i = 0; $i < $runs; $i++) {
      $progress->setMessage($this->trans('commands.webprofiler.benchmark.progress.get'));
      $datas[] = $this->getData($client, $url);
      $progress->advance();
    }

    $progress->setMessage($this->trans('commands.webprofiler.benchmark.progress.compute_avg'));
    $avg = $this->computeAvg($datas);
    $progress->advance();

    $progress->setMessage($this->trans('commands.webprofiler.benchmark.progress.compute_median'));
    $median = $this->computePercentile($datas, 50);
    $progress->advance();

    $progress->setMessage($this->trans('commands.webprofiler.benchmark.progress.compute_95percentile'));
    $percentile95 = $this->computePercentile($datas, 95);
    $progress->advance();

    $progress->setMessage($this->trans('commands.webprofiler.benchmark.progress.git_hash'));
    $gitHash = $this->getGitHash();
    $progress->advance();

    $progress->setMessage($this->trans('commands.webprofiler.benchmark.progress.yaml'));
    $yaml = $this->generateYaml($gitHash, $runs, $url, $avg, $median, $percentile95);
    $progress->advance();

    $progress->setMessage($this->trans('commands.webprofiler.benchmark.progress.done'));
    $progress->finish();
    $output->writeln('');

    if ($file) {
      file_put_contents($file, $yaml);
    }
    else {
      $output->writeln($yaml);
    }

  }

  /**
   * @param \GuzzleHttp\ClientInterface $client
   * @param $url
   *
   * @return array
   */
  private function getData(ClientInterface $client, $url) {
    /** @var \GuzzleHttp\Message\ResponseInterface $response */
    $response = $client->get($url);

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

    foreach ($datas as $data) {
      $totalTime += $data->getTime();
      $totalMemory += $data->getMemory();
    }

    return new BenchmarkData(NULL, $totalMemory / $profiles, $totalTime / $profiles);
  }

  /**
   * Computes percentile using The Nearest Rank method.
   *
   * @param \Drupal\webprofiler\Command\BenchmarkData[] $datas
   * @param $percentile
   *
   * @return \Drupal\webprofiler\Command\BenchmarkData
   *
   * @throws \Exception
   */
  private function computePercentile($datas, $percentile) {
    if ($percentile < 0 || $percentile > 100) {
      throw new \Exception('Percentile has to be between 0 and 100');
    }

    $profiles = count($datas);

    $n = ceil((($percentile / 100) * $profiles));
    $index = (int) $n - 1;

    $orderedTime = $datas;
    $this->getOrderedDatas($orderedTime, 'Time');

    $orderedMemory = $datas;
    $this->getOrderedDatas($orderedMemory, 'Memory');

    return new BenchmarkData(NULL, $orderedMemory[$index]->getMemory(), $orderedTime[$index]->getTime());
  }

  /**
   * @return string
   */
  private function getGitHash() {
    try {
      $process = new Process('git rev-parse HEAD');
      $process->setTimeout(3600);
      $process->run();
      $git_hash = $process->getOutput();
    } catch (\Exception $e) {
      $git_hash = $this->trans('commands.webprofiler.benchmark.messages.not_git');
    }

    return $git_hash;
  }

  /**
   * @param \Drupal\webprofiler\Command\BenchmarkData[] $datas
   * @param $string
   *
   * @return array
   */
  private function getOrderedDatas(&$datas, $string) {
    usort($datas, function ($a, $b) use ($string) {
      $method = 'get' . $string;
      if ($a->{$method} > $b->{$method}) {
        return 1;
      }
      if ($a->{$method} < $b->{$method}) {
        return -1;
      }
      return 0;
    });
  }

  /**
   * Rebuilds Drupal cache.
   */
  protected function RebuildCache() {
    require_once DRUPAL_ROOT . '/core/includes/utility.inc';
    $kernelHelper = $this->getHelper('kernel');
    $classLoader  = $kernelHelper->getClassLoader();
    $request      = $kernelHelper->getRequest();
    \drupal_rebuild($classLoader, $request);
  }

  /**
   * @param $gitHash
   * @param $runs
   * @param $url
   * @param \Drupal\webprofiler\Command\BenchmarkData $avg
   * @param \Drupal\webprofiler\Command\BenchmarkData $median
   * @param \Drupal\webprofiler\Command\BenchmarkData $percentile95
   *
   * @return string
   */
  private function generateYaml($gitHash, $runs, $url, BenchmarkData $avg, BenchmarkData $median, BenchmarkData $percentile95) {
    $yaml = Yaml::dump([
      'date' => date($this->trans('commands.webprofiler.list.rows.time'), time()),
      'git_commit' => $gitHash,
      'number_of_runs' => $runs,
      'url' => $url,
      'results' => [
        'average' => [
          'time' => sprintf('%.0f ms', $avg->getTime()),
          'memory' => sprintf('%.1f MB', $avg->getMemory() / 1024 / 1024),
        ],
        'median' => [
          'time' => sprintf('%.0f ms', $median->getTime()),
          'memory' => sprintf('%.1f MB', $median->getMemory() / 1024 / 1024),
        ],
        '95_percentile' => [
          'time' => sprintf('%.0f ms', $percentile95->getTime()),
          'memory' => sprintf('%.1f MB', $percentile95->getMemory() / 1024 / 1024),
        ],
      ],
    ]);
    return $yaml;
  }

  /**
   * {@inheritdoc}
   */
  public function showMessage($output, $message, $type='info') {
  }

}
