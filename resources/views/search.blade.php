<!DOCTYPE html>
<html lang="pl" data-theme="wireframe">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
    <title>Dyplom</title>
</head>

<body class="bg-base-200 p-4 h-screen">
    <div class="container mx-auto glass rounded-lg p-4 h-full">
        <div class="flex justify-between">
            <form method="POST" action="/search">
                @csrf
                <div class="input-group prose">
                    <input type="text" name="searchContent" placeholder="Szukaj..."
                        class="input input-bordered w-80" />
                    <button class="btn btn-primary btn-square">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>
                </div>
            </form>

            <div class="flex flex-col gap-x-1 gap-y-2 prose">
                <form method="POST" action="/crawl">
                    @csrf
                    <div class="flex gap-1">
                        <input name="crawlUrl" type="text" value="https://thephp.website/"
                            class="input input-bordered input-sm w-full" />
                        <input type="submit" class="btn btn-sm btn-primary" value="Pajączek" />
                    </div>
                </form>
                <div class="flex gap-1">
                    <form method="GET" action="/extract">
                        <input type="submit" class="btn btn-sm btn-primary" value="Indeksuj"></input>
                    </form>
                    <form method="GET" action="/cleanDocuments">
                        <input type="submit" class="btn btn-sm btn-primary" value="Wyczyść pajączka"></input>
                    </form>
                    <form method="GET" action="/cleanDatabase">
                        <input type="submit" class="btn btn-sm btn-primary" value="Wyczyść dokumenty"></input>
                    </form>
                </div>
            </div>
        </div>
        <div class="flex justify-between w-full mt-4">
            <div class="w-2/3 prose prose-sm">
                @if (session('searchResult'))
                    @php
                        $conjugation = '';
                        if (sizeof(session('searchResult')) == 1) {
                            $conjugation = ' wynik';
                        } elseif (sizeof(session('searchResult')) >= 2) {
                            $conjugation = ' wyniki';
                        } elseif (sizeof(session('searchResult')) >= 5 || sizeof(session('searchResult')) == 0) {
                            $conjugation = ' wyników';
                        }
                    @endphp
                    <h4>Znaleziono {{ sizeof(session('searchResult')) . $conjugation }}</h4>
                    @foreach (session('searchResult') as $document)
                        <div class="flex flex-col mt-3">
                            <a class="link link-hover text-lg"
                                href="{{ $document->attr_custom_url[0] }}">{{ $document->id }}</a>
                            <p class="m-0 font-bold">{{ $document->attr_keywords[0] }}</p>
                            @if ($document->attr_description)
                                <p class="m-0">{{ $document->attr_description[0] }}</p>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="w-1/3 prose prose-sm text-right">
                @if (session('logMessages'))
                    <h3>Log</h3>
                    <div class="h-96 overflow-y-scroll">
                        @foreach (session('logMessages') as $message)
                            <span>{{ $message }}</span>
                            <br />
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>

</html>
