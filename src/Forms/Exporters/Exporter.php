<?php

namespace Statamic\Forms\Exporters;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Statamic\Contracts\Forms\Form;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

use function Statamic\trans as __;

abstract class Exporter
{
    protected static string $title;
    protected array $config;
    protected string $handle;
    protected Form $form;

    abstract public function export(string $path): void;

    public function setHandle(string $handle)
    {
        $this->handle = $handle;

        return $this;
    }

    public function setConfig(array $config)
    {
        $this->config = $config;

        return $this;
    }

    public function setForm(Form $form)
    {
        $this->form = $form;

        return $this;
    }

    public function contentType(): string
    {
        return 'text/plain';
    }

    public function extension(): string
    {
        return 'txt';
    }

    public function title(): string
    {
        return __($this->config['title'] ?? static::$title);
    }

    public function allowedOnForm(Form $form)
    {
        return ! isset($this->config['forms']) || in_array($form->handle(), $this->config['forms']);
    }

    public function downloadUrl()
    {
        return cp_route('forms.export', [
            'type' => $this->handle,
            'form' => $this->form->handle(),
            'download' => true,
        ]);
    }

    public function response(): Response
    {
        return response($this->export())->header('Content-Type', $this->contentType());
    }

    public function download(): BinaryFileResponse
    {
        $path = storage_path('statamic/tmp/forms/'.$this->form->handle().'-'.time().'.'.$this->extension());
        File::put($path, '');
        $this->export($path);

        return response()->download($path)->deleteFileAfterSend();
    }
}
