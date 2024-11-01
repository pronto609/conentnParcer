<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Парсер контенту</title>
</head>
<body>
<h1>Парсер контенту</h1>

@if (session('success'))
    <p style="color: green;">{{ session('success') }}</p>
@endif

{!! Form::open(['route' => 'parser.process']) !!}
<h3>В один файл</h3>
<select name="merge[]">
    @foreach ($mergedOptions as $value => $name)
        <option value="{{ $value }}">{{ $name }}</option>
    @endforeach
</select><br>

<h3>Формат</h3>
<select name="format[]">
    @foreach ($formats as $format)
        <option value="{{ $format }}">{{ $format }}</option>
    @endforeach
</select><br>

<h3>Вибрати бази для парсингу</h3>
<select name="databases[]" multiple>
    @foreach ($databases as $database)
        <option value="{{ $database }}">{{ $database->getFilename() }}</option>
    @endforeach
</select><br>

<button type="submit">Парсити</button>
{!! Form::close() !!}

@if (!empty($links))
    <h3>Згенеровані файли для завантаження</h3>
    <ul>
        @foreach ($links as $link)
            <li><a href="{{ $link['url'] }}" download>{{ $link['name'] }}</a></li>
        @endforeach
    </ul>
@endif

{!! Form::open(['route' => 'parser.loaddb', 'files' => true]) !!}
<h3>Завантажити нову базу</h3>
<input type="file" name="database_file"><br>

<button type="submit">Завантажити</button>
{!! Form::close() !!}
</body>
</html>
