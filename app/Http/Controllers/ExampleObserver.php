<?php

namespace App\Http\Controllers;

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

  public function __construct()
  {
    $this->content = NULL;
    $this->messages = [];
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
    //# save HTML 
    $content = $doc->saveHTML();

    //# convert encoding
    // $content1 = mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
    // //# strip all javascript
    // $content2 = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $content1);
    // //# strip all style
    // $content3 = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $content2);
    // //# strip tags
    // $content4 = str_replace('<', ' <', $content3);
    // $content5 = strip_tags($content4);
    // $content6 = str_replace('  ', ' ', $content5);
    // //# strip white spaces and line breaks
    // $content7 = preg_replace('/\s+/S', " ", $content6);
    // //# html entity decode - ö was shown as &ouml;
    // $html = html_entity_decode($content7);

    //# append
    // $this->content .= $content;
    $name = $url->getHost() . $url->getPath();

    $myfile = fopen(__DIR__ . "/crawled_docs/" . str_replace("/", "", $name) . ".html", "w");
    fwrite($myfile, $content);
    fclose($myfile);
    $myfile = fopen(__DIR__ . "/crawled_docs/" . str_replace("/", "", $name) . ".html", "a");
    fwrite($myfile, '<autoappendedurl>' . $url->getScheme() . '://' . $url->getHost() . $url->getPath() . '</autoappendedurl>');
    fclose($myfile);
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
