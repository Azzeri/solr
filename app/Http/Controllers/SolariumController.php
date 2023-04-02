<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Solarium\Client;
use Spatie\Crawler\Crawler;
use Spatie\Crawler\CrawlProfiles\CrawlInternalUrls;
use App\Http\Controllers\Controller;
use GuzzleHttp\RequestOptions;
class SolariumController extends Controller
{
    public function __construct(protected Client $client)
    {
    }

    public function index()
    {
        return view('search', ['searchResult' => []]);
    }

    public function search(Request $request)
    {
        $query = $this->client->createQuery($this->client::QUERY_SELECT);

        $query = $this->client->createSelect();
        $query->createFilterQuery('genre')->setQuery('genre:' . $request->searchContent . '');
        $resultset = $this->client->select($query);

        return view('search', ['searchResult' => $resultset]);
    }

    public function extract()
    {
        $query = $this->client->createExtract();
        $query->addFieldMapping('content', 'text');
        $query->setUprefix('attr_');
        // $query->setFile(__DIR__ . '/index.html');
        $query->setFile(__DIR__ . '/testfile.html');
        $query->setCommit(true);
        $query->setOmitHeader(false);

        // add document
        $doc = $query->createDocument();
        $doc->id = 'extract-test4';
        $doc->some = 'more fields';
        $query->setDocument($doc);

        // this executes the query and returns the result
        $result = $this->client->extract($query);

        echo '<b>Extract query executed</b><br/>';
        echo 'Query status: ' . $result->getStatus() . '<br/>';
        echo 'Query time: ' . $result->getQueryTime();
    }

    public function crawl()
    {
        $observer = new ExampleObserver();
        //# initiate crawler 
        Crawler::create([RequestOptions::ALLOW_REDIRECTS => true, RequestOptions::TIMEOUT => 30])
            ->acceptNofollowLinks()
            ->ignoreRobots()
            // ->setParseableMimeTypes(['text/html', 'text/plain'])
            ->setCrawlObserver($observer)
            ->setCrawlProfile(new CrawlInternalUrls('https://www.lipsum.com'))
            ->setMaximumResponseSize(1024 * 1024 * 2) // 2 MB maximum
            ->setTotalCrawlLimit(100) // limit defines the maximal count of URLs to crawl
            // ->setConcurrency(1) // all urls will be crawled one by one
            ->setDelayBetweenRequests(100)
            ->startCrawling('https://www.lipsum.com');
        $myfile = fopen("testfile.html", "w");
        fwrite($myfile, $observer->content);
        fclose($myfile);
    }
}
