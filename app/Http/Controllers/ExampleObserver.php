<?php

namespace App\Http\Controllers;

use App\Service;
use Spatie\Crawler\CrawlObservers\CrawlObserver;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use DOMDocument;
use Illuminate\Support\Facades\Log;

class ExampleObserver extends CrawlObserver
{
  public $content;
  public $messages;
  private $service;
  private $client;

  public function __construct($client)
  {
    $this->content = NULL;
    $this->messages = [];
    $this->service = new Service();
    $this->client = $client;
  }

  /**
   * Called when the crawler will crawl the url.
   *
   * @param \Psr\Http\Message\UriInterface $url
   */
  public function willCrawl(UriInterface $url): void
  {
    Log::info('willCrawl', ['url' => $url]);
  }

  /**
   * Called when the crawler has crawled the given url successfully.
   *
   * @param \Psr\Http\Message\UriInterface $url
   * @param \Psr\Http\Message\ResponseInterface $response
   * @param \Psr\Http\Message\UriInterface|null $foundOnUrl
   */
  public function crawled(
    UriInterface $url,
    ResponseInterface $response,
    ?UriInterface $foundOnUrl = null
  ): void {
    $doc = new DOMDocument();
    @$doc->loadHTML($response->getBody());

    $content = $doc->saveHTML();
    $name = $url->getHost() . $url->getPath();
    $myfile = fopen(__DIR__ . "/crawled_docs/" . str_replace("/", "", $name) . ".html", "w");
    fwrite($myfile, $content);
    fclose($myfile);
    $myfile = fopen(__DIR__ . "/crawled_docs/" . str_replace("/", "", $name) . ".html", "a");
    fwrite($myfile, '<autoappendedurl>' . $url->getScheme() . '://' . $url->getHost() . $url->getPath() . '</autoappendedurl>');
    fclose($myfile);

    $this->service->extractDocument($this->client);

    $message = $this->service->cleanDocuments();
  }

  /**
   * Called when the crawler had a problem crawling the given url.
   *
   * @param \Psr\Http\Message\UriInterface $url
   * @param \GuzzleHttp\Exception\RequestException $requestException
   * @param \Psr\Http\Message\UriInterface|null $foundOnUrl
   */
  public function crawlFailed(
    UriInterface $url,
    RequestException $requestException,
    ?UriInterface $foundOnUrl = null
  ): void {
    array_push($this->messages, 'crawlFailed' . ': URL: ' . $url . ' : ' . $requestException->getMessage());
  }

  /**
   * Called when the crawl has ended.
   */
  public function finishedCrawling(): void
  {
    array_push($this->messages, "finishedCrawling");
  }
}
