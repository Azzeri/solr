<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
    <title>Wyszukiwarka</title>
    <style>
        .layout {
            display: flex;
            gap: 100px;
        }

        .log {
            border: 1px solid black;
            padding-left: 10px;
            padding-right: 10px;
            inline-size: 1080px;
            overflow-wrap: break-word;
        }

        .button {
            margin-left: 5px;
        }

        .results {
            width: 500px;
        }
    </style>
</head>

<body>
    <div style="display:flex;align-items:center;">
        <h1 style="margin-right: 50px">Wyszukiwarka</h1>
        <br />
        <form method="POST" action="/crawl">
            @csrf
            <input name="crawlUrl" type="text" value="https://thephp.website/" />
            <input type="submit" class="button" value="Crawl" />
        </form>
        <form method="GET" action="/extract">
            <input type="submit" class="button" value="Extract"></input>
        </form>
        <form method="GET" action="/cleanDocuments">
            <input type="submit" class="button" value="Usuń pliki crawlera"></input>
        </form>
        <form method="GET" action="/cleanDatabase">
            <input type="submit" class="button" value="Usuń zaindeksowane dokumenty"></input>
        </form>

    </div>
    <div class="layout">
        <div><button class="btn btn-primary btn-square">asd</button>
            <form method="POST" action="/search">
                @csrf
                <label for="searchContent"></label>
                <input type="text" name="searchContent" />
                <input type="submit" value="Szukaj">
            </form>
            <div class="results">
                @if (session('searchResult'))
                    @foreach (session('searchResult') as $document)
                        <div style="margin-top:10px;">
                            <a href="{{ $document->attr_custom_url[0] }}">{{ $document->id }}</a>
                            <br />
                            {{ $document->attr_keywords[0] }}
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
        <div id="log" class="log">
            <h3>Log</h3>
            @if (session('logMessages'))
                @foreach (session('logMessages') as $message)
                    {{ $message }}
                    <br />
                @endforeach
            @endif
        </div>
    </div>
</body>

</html>
