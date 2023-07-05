<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Solarium\Client;
use Spatie\Crawler\Crawler;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Service;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Auth;
use Spatie\Crawler\CrawlProfiles\CrawlAllUrls;
use PhpWndb\Dataset\Model\RelationPointerType;
use PhpWndb\Dataset\WordNetProvider;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

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
        $query->setRows(1500);

        $query->setQuery(
            'attr_keywords:' . $request->searchContent
                . ' OR attr_title:' . $request->searchContent
                . ' OR attr_description:' . $request->searchContent
                // . ' OR attr_text:' . $request->searchContent
        );

        $resultset = $this->client->select($query);
        // $resultset = $this->applyBasicSort($resultset);

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

    private function applyBasicSort($resultset)
    {
        $keywordsArray = [];
        $titleArray = [];
        $descriptionArray = [];
        $textArray = [];

        foreach ($resultset as $document) {
            if ($document->attr_keywords) {
                $keywordsArray[] = $document;
                continue;
            }

            if ($document->attr_title) {
                $titleArray[] = $document;
                continue;
            }

            if ($document->attr_description) {
                $descriptionArray[] = $document;
                continue;
            }

            $textArray[] = $document;
            continue;
        }

        return array_merge($keywordsArray, $titleArray, $descriptionArray, $textArray);
    }

    public function preferencesUpdate(Request $request)
    {
        $request->validate([
            'preferences' => 'required'
        ]);

        $user = User::find(Auth::user()->id);

        $user->update([
            'interests' => $request->preferences
        ]);

        return redirect()->back()->with('status', 'success');
    }

    private function applyUserInterests($resultset)
    {
        $preprocessedDocuments = [];
        foreach ($resultset as $document) {
            $articleKeywordsCombined = [];
            if ($document->attr_keywords) {
                $keywordsPrepared = str_replace([', ', ','], '|', $document->attr_keywords[0]);
                $exploded = array_unique(explode('|', $keywordsPrepared));
                $keywords = array_values($exploded);
                $articleKeywordsCombined += $keywords;
            }

            if ($document->attr_title) {
                $titlePrepared = str_replace(str_split('\\/:*?"<>|+-·,.()""\''), '|', $document->attr_title[0]);
                $titlePrepared = str_replace(' ', '|', $titlePrepared);
                $exploded = array_unique(explode('|', $titlePrepared));
                if (($key = array_search('', $exploded)) !== false) {
                    unset($exploded[$key]);
                }
                $title = array_values($exploded);
                $articleKeywordsCombined += $title;
            }

            // if (sizeof($articleKeywordsCombined) > 0) {
            $preprocessedDocuments[$document->id] = $articleKeywordsCombined;
            // }
        }

        $user = Auth::user();
        $process = new Process([
            'python3', '/var/www/html/app/Http/Controllers/test.py',
            json_encode($preprocessedDocuments, JSON_THROW_ON_ERROR),
            $user->interests
        ]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $result = $process->getOutput();
        $decoded = json_decode($result, true);

        uasort($decoded, function ($a, $b) {
            if ($a == $b) {
                return 0;
            }
            return ($a > $b) ? -1 : 1;
        });

        $sortedResult = [];
        foreach ($decoded as $key => $doc) {
            foreach ($resultset as $res) {
                if ($res->id == $key) {
                    $sortedResult[] = ['document' => $res, 'score' => $doc, 'keywords' => $preprocessedDocuments[$key]];
                }
            }
        }
        return $sortedResult;
    }

    private function getSynsets()
    {
        $process = new Process(['python3', '/var/www/html/app/Http/Controllers/test.py']);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $data = $process->getOutput();

        dd($data);

        $wordNet = (new WordNetProvider(cacheDir: \sys_get_temp_dir(), isDebug: true))->getWordNet();
        $synsets = $wordNet->search('die');

        foreach ($synsets as $synset) {
            // dump($synset->getGloss());
            // echo $synset->getType()->name . ': ' . $synset->getGloss() . "\n";
            foreach ($synset as $word) {
                // dump($word->toString());
                foreach ($word->moveTo(RelationPointerType::ANTONYM) as $antonym) {
                    dump(" x {$antonym->toString()}");
                }
            }
            // foreach ($synset as $word) {
            //     echo " - {$word->toString()}";


            //     echo "\n";
            // }
        }

        die();
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
            ->setTotalCrawlLimit(10000) // limit defines the maximal count of URLs to crawl
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
