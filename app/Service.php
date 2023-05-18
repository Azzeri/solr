<?php

namespace App;

use DirectoryIterator;

class Service
{
    public function extractDocument($client)
    {
        $files = scandir(__DIR__ . "/Http/Controllers/crawled_docs/");

        $query = $client->createExtract();
        $query->addFieldMapping('content', 'text');
        $query->setUprefix('attr_');
        $query->setCommit(true);
        $query->setOmitHeader(false);
        $messages = [];

        foreach ($files as $file) {
            if (in_array($file, array(".", ".."))) {
                continue;
            }
            $myfile = file_get_contents(__DIR__ . "/Http/Controllers/crawled_docs/" . $file);
            $pos = strpos($myfile, "<autoappendedurl>");
            $url = file_get_contents(filename: __DIR__ . "/Http/Controllers/crawled_docs/" . $file, offset: $pos);
            $striped = str_replace("<autoappendedurl>", "", $url);
            $stripedUrl = str_replace("</autoappendedurl>", "", $striped);

            $query->setFile(__DIR__ . "/Http/Controllers/crawled_docs/" . $file);
            $doc = $query->createDocument();
            $doc->id = $file;
            $doc->custom_url = $stripedUrl;
            $query->setDocument($doc);

            $result = $client->extract($query);
            array_push($messages, 'Extract query executed for: ' . $file . ': ' . $result->getQueryTime() . 'ms');
        }
        return redirect()->back()->with('logMessages', $messages);
    }

    public function cleanDocuments()
    {
        foreach (new DirectoryIterator(__DIR__ . "/Http/Controllers/crawled_docs/") as $fileInfo) {
            if (!$fileInfo->isDot()) {
                unlink($fileInfo->getPathname());
            }
        }

        $files = scandir(__DIR__ . "/Http/Controllers/crawled_docs/");
        $message = sizeof($files) == 2 ? 'Pliki usunięte' : 'Nie udało się usunąć wszystkich plików';

        return $message;
    }

    public function getResultsConjugation(int $size): string
    {
        if ($size >= 5 || $size == 0) {
            $conjugation = ' wyników';
        } elseif ($size >= 2) {
            $conjugation = ' wyniki';
        } elseif ($size == 1) {
            $conjugation = ' wynik';
        }

        return $conjugation;
    }
}
