@inject('service', 'App\Service')
<!DOCTYPE html>
<html lang="pl" data-theme="pastel" class="bg-base-200">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/f56eaa1e66.js" crossorigin="anonymous"></script>
    @vite('resources/css/app.css')
    <title>Dyplom</title>
</head>

<body class="p-4 h-screen">
    <div class="container mx-auto glass rounded-lg p-4 min-h-full">
        <div class="flex justify-between">
            <h1 class="text-4xl font-bold font-mono text-primary-content">Semantica</h1>
            @if (Auth::user())
                <div class="flex space-x-2">
                    <a class="font-bold" href="{{ route('profile.edit') }}"
                        class="text-primary-content">{{ Auth::user()->name }}
                    </a>
                    <div class="dropdown dropdown-end">
                        <i tabindex="0" class="fa-solid fa-gear cursor-pointer"></i>
                        <ul tabindex="0" class="dropdown-content menu p-2 shadow bg-base-100 rounded-box w-max">
                            <li>
                                <form action=""></form>
                                <form method="POST" action="/crawl">
                                    @csrf
                                    <div class="flex gap-1">
                                        <input name="crawlUrl" type="text" value="https://thephp.website/"
                                            class="input input-bordered input-sm w-full" />
                                        <input type="submit" class="btn btn-sm btn-primary" value="Pajączek" />
                                    </div>
                                </form>
                            </li>
                            <li>
                                <form method="GET" action="/cleanDatabase">
                                    <input type="submit" class="btn btn-sm btn-error w-full"
                                        value="Wyczyść dokumenty"></input>
                                </form>
                            </li>
                        </ul>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="space-x-1">
                        @csrf
                        <button>
                            <i class="fa-solid fa-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            @else
                <div class="flex space-x-2">
                    <a href="{{ route('login') }}" class="text-primary-content">Logowanie</a>
                    <a href="{{ route('register') }}" class="text-primary-content">Rejestracja</a>
                </div>
            @endif
        </div>
        <div class="flex space-x-3 mt-6">
            <form method="POST" action="/search">
                @csrf
                <div class="input-group prose">
                    <input type="text" value="php" name="searchContent" placeholder="Szukaj..."
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
            @if (Auth::user())
                <div tabindex="0" class="tooltip tooltip-secondary tooltip-right"
                    data-tip={{ Auth::user()->interests }}>
                    <button class="btn btn-secondary">
                        <i class="fa-solid fa-table-tennis-paddle-ball"></i>
                    </button>
                </div>
            @endif
        </div>
        <div class="flex justify-between w-full mt-4">
            @if (session('searchResultWithRecommendation'))
                <div class="prose prose-sm w-1/2">
                    <h4><span class="text-accent-focus italic">Rekomendacja: </span>Znaleziono
                        {{ sizeof(session('searchResultWithRecommendation')) . $service->getResultsConjugation(sizeof(session('searchResultWithRecommendation'))) }}
                    </h4>
                    @foreach (session('searchResultWithRecommendation') as $document)
                        <div class="flex flex-col mt-3">
                            @if ($document->attr_title)
                                <a class="link link-hover text-lg"
                                    href="{{ $document->attr_custom_url[0] }}">{{ $document->attr_title[0] }}
                                </a>
                            @else
                                <a class="link link-hover text-lg"
                                    href="{{ $document->attr_custom_url[0] }}">{{ $document->id }}
                                </a>
                            @endif
                            @if ($document->attr_keywords)
                                <p class="m-0 font-bold">{{ $document->attr_keywords[0] }}</p>
                            @endif
                            @if ($document->attr_description)
                                <p class="m-0">{{ $document->attr_description[0] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
            @if (session('searchResult'))
                <div class="prose prose-sm w-1/2 overflow-auto">
                    <h4>Znaleziono
                        {{ sizeof(session('searchResult')) . $service->getResultsConjugation(sizeof(session('searchResult'))) }}
                    </h4>
                    @foreach (session('searchResult') as $document)
                        <div class="flex flex-col mt-3">
                            @if ($document->attr_title)
                                <a class="link link-hover text-lg"
                                    href="{{ $document->attr_custom_url[0] }}">{{ $document->attr_title[0] }}
                                </a>
                            @else
                                <a class="link link-hover text-lg"
                                    href="{{ $document->attr_custom_url[0] }}">{{ $document->id }}
                                </a>
                            @endif
                            @if ($document->attr_keywords)
                                <p class="m-0 font-bold">{{ $document->attr_keywords[0] }}</p>
                            @endif
                            @if ($document->attr_description)
                                <p class="m-0">{{ $document->attr_description[0] }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</body>

</html>
