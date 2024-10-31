<?php

namespace App\Http\Controllers;

use App\Services\SaveHandler;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Services\ContentParser;
class ParserController extends Controller
{
    public function index()
    {
        $databases = $this->getDatabaseDumps();
        $formats = ['csv', 'text', 'xtml'];
        return view('parser.index', compact('databases', 'formats'));
    }

    public function process(
        Request $request,
        ContentParser $contentParser,
        SaveHandler $saveHandler
    )
    {
        $selectedDatabases = $request->input('databases');
        $saveHandler->setFormat(collect($request->input('format'))->first());

        $parseData = $contentParser->parse($selectedDatabases);
        $saveHandler->save($parseData);

        return redirect()->back()->with('success', 'Парсинг завершено');
    }

    private function getDatabaseDumps()
    {
        try {
            $path = storage_path('dumps');
            return File::files($path);
        } catch (FileNotFoundException $exception) {
            return [];
        }
    }
}
