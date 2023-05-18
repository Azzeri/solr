<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Solarium\Client;
use Spatie\Crawler\Crawler;
use App\Http\Controllers\Controller;
use App\Service;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Auth;
use Spatie\Crawler\CrawlProfiles\CrawlAllUrls;

class SolariumController extends Controller
{
    public function __construct(protected Client $client, public Service $service)
    {
    }

    public function index()
    {
        return view('search');
    }

    public function search(Request $request)
    {
        $request->validate([
            'searchContent' => 'required'
        ]);

        $query = $this->client->createSelect();
        $query->setRows(10000);

        $query->setQuery(
            'attr_keywords:' . $request->searchContent
                . ' OR attr_title:' . $request->searchContent
                . ' OR attr_description:' . $request->searchContent
                . ' OR attr_text:' . $request->searchContent
        );

        $resultset = $this->client->select($query);

        if (Auth::user()) {
            $resultsetWithRecommendation = $this->applyUserInterests($resultset);
        }

        return redirect()
            ->back()
            ->with('searchResult', $resultset)
            ->with(
                'searchResultWithRecommendation',
                isset($resultsetWithRecommendation)
                    ? $resultsetWithRecommendation
                    : null
            )
            ->with('logMessages', ['Przedstawiam wyniki...']);
    }

    private function applyUserInterests($resultset)
    {
        $user = Auth::user();
        $array = [];
        $interests = explode("|", $user->interests);
        foreach ($resultset as $document) {
            $array[] = $document;
        }
        foreach ($resultset as $document) {
            if ($document->attr_keywords) {
                $keywords = explode(",", $document->attr_keywords[0]);
                $haystack = array_map('strtolower', $keywords);
                $needles = array_map('strtolower', $interests);

                if (count(array_intersect($haystack, $needles)) > 0) {
                    $index = array_search($document, $array);
                    array_unshift($array, array_splice($array, $index, 1)[0]);
                }
            }
        }
        return $array;
    }

    public function extract()
    {
        $files = scandir(__DIR__ . "/crawled_docs/");

        $query = $this->client->createExtract();
        $query->addFieldMapping('content', 'text');
        $query->setUprefix('attr_');
        // $query->setFile(__DIR__ . '/index.html');

        // $query->setFile(Storage::url('example.html'));

        $query->setCommit(true);
        $query->setOmitHeader(false);
        $messages = [];
        foreach ($files as $file) {
            if (in_array($file, array(".", ".."))) {
                continue;
            }
            $myfile = file_get_contents(__DIR__ . "/crawled_docs/" . $file);
            $pos = strpos($myfile, "<autoappendedurl>");
            $url = file_get_contents(filename: __DIR__ . "/crawled_docs/" . $file, offset: $pos);
            $striped = str_replace("<autoappendedurl>", "", $url);
            $stripedUrl = str_replace("</autoappendedurl>", "", $striped);

            $query->setFile(__DIR__ . "/crawled_docs/" . $file);
            $doc = $query->createDocument();
            $doc->id = $file;
            $doc->custom_url = $stripedUrl;
            $query->setDocument($doc);
            // // this executes the query and returns the result
            $result = $this->client->extract($query);
            array_push($messages, 'Extract query executed for: ' . $file . ': ' . $result->getQueryTime() . 'ms');
        }
        return redirect()->back()->with('logMessages', $messages);
    }

    public function crawl(Request $request)
    {
        $request->validate([
            'crawlUrl' => 'required'
        ]);

        $observer = new ExampleObserver($this->client);
        Crawler::create([RequestOptions::ALLOW_REDIRECTS => true, RequestOptions::TIMEOUT => 30])
            ->acceptNofollowLinks()
            ->ignoreRobots()
            // ->setParseableMimeTypes(['text/html', 'text/plain'])
            ->setCrawlObserver($observer)
            ->setCrawlProfile(new CrawlAllUrls($request->crawlUrl))
            ->setMaximumResponseSize(1024 * 1024 * 4) // 2 MB maximum
            ->setTotalCrawlLimit(100) // limit defines the maximal count of URLs to crawl
            // ->setConcurrency(1) // all urls will be crawled one by one
            ->setDelayBetweenRequests(150)
            ->startCrawling($request->crawlUrl);

        return redirect()->back()->with('logMessages', $observer->messages);
    }

    public function cleanDocuments()
    {
        $message = $this->service->cleanDocuments();

        return redirect()->back()->with('logMessages', [$message]);
    }

    public function cleanDatabase()
    {
        $update = $this->client->createUpdate();
        $update->addDeleteQuery('*:*');
        $update->addCommit();
        $result = $this->client->update($update);

        $query = $this->client->createQuery($this->client::QUERY_SELECT);
        $resultset = $this->client->execute($query);
        $noResults = $resultset->getNumFound();

        $message = $noResults == 0 ? 'Pliki usunięte' : 'Nie udało się usunąć wszystkich plików';

        return redirect()->back()->with('logMessages', [$message]);
    }
}
