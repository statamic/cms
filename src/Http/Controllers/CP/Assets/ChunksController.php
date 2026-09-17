<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use Statamic\Assets\ChunkUploads;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\AssetContainer as AssetContainerFacade;
use Statamic\Http\Controllers\CP\Assets\Concerns\FinalizesAssetUploads;
use Statamic\Http\Controllers\CP\CpController;
use Statamic\Rules\AllowedFile;
use Symfony\Component\Mime\MimeTypes;

use function Statamic\trans as __;

class ChunksController extends CpController
{
    use FinalizesAssetUploads;

    public function store(Request $request)
    {
        $request->validate([
            'container' => 'required',
            'folder' => 'required',
            'uploadId' => ['required', 'string', 'regex:/^[A-Za-z0-9_-]{8,64}$/'],
            'chunkIndex' => 'required|integer|min:0',
            'totalChunks' => 'required|integer|min:1',
            'chunk' => ['required', 'file', new AllowedFile],
        ]);

        $container = AssetContainerFacade::find($request->container);

        throw_unless($container, NotFoundHttpException::class);

        $this->authorize('store', [AssetContract::class, $container]);

        if (! ChunkUploads::enabledForContainer($container)) {
            $this->reject(__('Chunked uploads are not enabled for this container.'));
        }

        $originalName = $request->file('chunk')->getClientOriginalName();
        $disk = ChunkUploads::disk();
        $directory = ChunkUploads::directory($request->uploadId);
        $chunkIndex = (int) $request->chunkIndex;
        $totalChunks = (int) $request->totalChunks;

        $disk->putFileAs($directory, $request->file('chunk'), (string) $chunkIndex);

        if ($limit = ChunkUploads::maxFilesizeBytes($container->validationRules())) {
            $received = collect($disk->files($directory))
                ->sum(fn ($path) => $disk->size($path));

            if ($received > $limit) {
                $disk->deleteDirectory($directory);
                $this->reject(__('The file is larger than is allowed.'));
            }
        }

        if ($chunkIndex < $totalChunks - 1) {
            return ['data' => ['uploadId' => $request->uploadId, 'received' => $chunkIndex]];
        }

        return $this->assemble($disk, $directory, $totalChunks, $container, $request, $originalName);
    }

    private function assemble(Filesystem $disk, string $directory, int $totalChunks, AssetContainer $container, Request $request, string $originalName)
    {
        $assembledPath = $disk->path($directory.'/assembled');
        $handle = fopen($assembledPath, 'wb');

        for ($i = 0; $i < $totalChunks; $i++) {
            $partPath = $disk->path($directory.'/'.$i);

            if (! is_file($partPath)) {
                fclose($handle);
                $disk->deleteDirectory($directory);
                $this->reject(__('A chunk was missing during assembly.'));
            }

            $part = fopen($partPath, 'rb');
            stream_copy_to_stream($part, $handle);
            fclose($part);
        }

        fclose($handle);

        $file = new UploadedFile($assembledPath, $originalName, (new MimeTypes)->guessMimeType($assembledPath), null, test: true);

        // Re-uploading a large file to resolve a name clash is impractical, so default to appending a timestamp.
        if (! $request->filled('option')) {
            $request->merge(['option' => 'timestamp']);
        }

        try {
            return $this->finalizeUpload($file, $container, $request);
        } finally {
            $disk->deleteDirectory($directory);
        }
    }

    private function reject(string $message): void
    {
        throw ValidationException::withMessages(['file' => [$message]])->status(422);
    }
}
