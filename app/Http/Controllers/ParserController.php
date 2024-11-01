<?php

namespace App\Http\Controllers;

use App\Services\SaveHandler;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Services\ContentParser;
use Illuminate\Support\Facades\Storage;
class ParserController extends Controller
{
    /**
     * @param \App\Services\Helper\DownloadGenerator $downloadGenerator
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application
     */
    public function index(\App\Services\Helper\DownloadGenerator $downloadGenerator)
    {
        $databases = $this->getDatabaseDumps();
        $mergedOptions = ['no', 'yes'];
        $formats = ['csv', 'text', 'xml'];
        $paths = session('last_generated_files', []);
        $links = $downloadGenerator->getLinks($paths);
        return view('parser.index', compact('databases', 'formats', 'mergedOptions', 'links'));
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function downloadFile(Request $request)
    {
        $fileName = $request->query('filePath');
        $filePath = "public/{$fileName}";

        if (Storage::exists($filePath)) {
            return Storage::download($filePath);
        }

        return redirect()->back()->with('error', 'Файл не знайдено');
    }

    /**
     * @param Request $request
     * @param ContentParser $contentParser
     * @param SaveHandler $saveHandler
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process(
        Request $request,
        ContentParser $contentParser,
        SaveHandler $saveHandler,
    ) {
        $selectedDatabases = $request->input('databases');
        $contentParser->parse($selectedDatabases);
        $format = collect($request->input('format'))->first();
        $merged = collect($request->input('merge'))->first();
        $saveHandler->setFormat($format);
        $saveHandler->setMerged($merged);
        $lastGeneratedFiles = $saveHandler->save();
        session(['last_generated_files' => $lastGeneratedFiles]);

        return redirect()->back()->with([
            'success' => 'Парсинг завершено',
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function loaddb(Request $request)
    {
        $request->validate([
            'database_file' => 'required|file|mimetypes:text/plain,application/sql',
        ]);

        if ($request->hasFile('database_file')) {
            $file = $request->file('database_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('dumps', $fileName); // Зберігаємо файл у storage/app/dumps
        }
        return redirect()->back()->with('success', 'Зфвантаження успішне');
    }

    /**
     * @return array|\Symfony\Component\Finder\SplFileInfo[]
     */
    private function getDatabaseDumps()
    {
        try {
            $path = storage_path(\App\Services\SaveConfig::DUMPS_PATH);
            return File::files($path);
        } catch (FileNotFoundException $exception) {
            return [];
        }
    }
}
