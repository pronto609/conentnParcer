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
<select name="databases[]" multiple>
    @foreach ($databases as $database)
        <option value="{{ $database }}">{{ $database->getFilename() }}</option>
    @endforeach
</select>
<button type="submit">Парсити</button>
{!! Form::close() !!}
</body>
</html>
