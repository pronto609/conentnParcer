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
        $mergedOptions = ['no', 'yes'];
        $formats = ['csv', 'text', 'xml'];
        return view('parser.index', compact('databases', 'formats', 'mergedOptions'));
    }

    public function process(
        Request $request,
        ContentParser $contentParser,
        SaveHandler $saveHandler
    )
    {
        $selectedDatabases = $request->input('databases');
        $contentParser->parse($selectedDatabases);
        $format = collect($request->input('format'))->first();
        $merged = collect($request->input('merge'))->first();
        $saveHandler->setFormat($format);
        $saveHandler->setMerged($merged);
        $saveHandler->save();

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
