<!DOCTYPE html>
<html lang="pl">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wyszukiwarka</title>
</head>

<body>
    <h1>Wyszukiwarka</h1>
    <br />
    <form method="POST" action="/search">
        @csrf
        <label for="searchContent"></label>
        <input type="text" name="searchContent" />
        <input type="submit" value="Szukaj">
    </form>
    <div>
        @foreach ($searchResult as $document)
            <div style="margin-top:10px;">
                {{ $document->directed_by[0] }}
                {{ $document->genre[0] }}
            </div>
        @endforeach
    </div>
</body>

</html>
