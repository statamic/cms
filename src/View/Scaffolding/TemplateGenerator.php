<?php

namespace Statamic\View\Scaffolding;

use InvalidArgumentException;
use Statamic\Exceptions\FieldtypeNotFoundException;
use Statamic\Fields\Blueprint;
use Statamic\Fields\Field;
use Statamic\Fields\Fieldtype;
use Statamic\View\Scaffolding\Emitters\AntlersSourceEmitter;
use Statamic\View\Scaffolding\Emitters\BladeSourceEmitter;
use Statamic\View\Scaffolding\Fieldtypes\DictionaryFieldtypeGenerator;
use Statamic\View\Scaffolding\Fieldtypes\ViewFieldtypeGenerator;
use Stringable;

class TemplateGenerator
{
    protected array $generators = [];

    protected string $templateLanguage = 'antlers';

    protected ?string $lineEnding = null;

    protected ?string $indentType = null;

    protected ?int $indentSize = null;

    protected ?bool $finalNewline = null;

    protected ?bool $preferComponentSyntax = null;

    public function extension(): string
    {
        if ($this->templateLanguage === 'blade') {
            return '.blade.php';
        }

        return '.antlers.html';
    }

    public static function make(): TemplateGenerator
    {
        /** @var TemplateGenerator $generator */
        $generator = app(TemplateGenerator::class);

        return $generator
            ->withCoreGenerators()
            ->templateLanguage(config('statamic.templates.language', 'antlers'))
            ->indentType(config('statamic.templates.style.indent_type', 'space'))
            ->indentSize(config('statamic.templates.style.indent_size', 4))
            ->finalNewline(config('statamic.templates.style.final_newline', false))
            ->preferComponentSyntax(config('statamic.templates.antlers.use_components', false));
    }

    public function addGenerator(string $handle, callable $generator)
    {
        $this->generators[$handle] = $generator;

        return $this;
    }

    public function withCoreGenerators()
    {
        $generic = new ViewFieldtypeGenerator;

        foreach (app('statamic.extensions')[Fieldtype::class] as $fieldtype) {
            $this->addGenerator($fieldtype::handle(), $generic);
        }

        $this->addGenerator('dictionary', app(DictionaryFieldtypeGenerator::class));

        return $this;
    }

    public function scaffoldBlueprint(Blueprint $blueprint)
    {
        return $this->scaffoldFields($blueprint->fields()->all());
    }

    public function scaffoldFields($fields)
    {
        return collect($fields)
            ->map(function (Field $field) {
                return $this->scaffoldField($field);
            })
            ->filter()
            ->implode("\n");
    }

    public function templateLanguage($language = null): TemplateGenerator|string
    {
        if ($language === null) {
            return $this->templateLanguage;
        }

        $this->templateLanguage = $language;

        return $this;
    }

    public function lineEnding(string $ending): static
    {
        $this->lineEnding = $ending;

        return $this;
    }

    public function indentType(string $type): static
    {
        $this->indentType = $type;

        return $this;
    }

    public function indentSize(int $size): static
    {
        $this->indentSize = $size;

        return $this;
    }

    public function finalNewline(bool $enabled): static
    {
        $this->finalNewline = $enabled;

        return $this;
    }

    public function preferComponentSyntax(bool $enabled): static
    {
        $this->preferComponentSyntax = $enabled;

        return $this;
    }

    public function scaffoldField(Field $field)
    {
        try {
            $fieldtype = $field->fieldtype()?->handle();
        } catch (FieldtypeNotFoundException) {
            return '';
        }

        if (! isset($this->generators[$fieldtype])) {
            return '';
        }

        $result = $this->generators[$fieldtype]($field, $this);

        if ($result instanceof Stringable) {
            $result = (string) $result;
        }

        return trim($result);
    }

    public function getEmitter()
    {
        $emitter = $this->templateLanguage === 'blade'
            ? new BladeSourceEmitter
            : new AntlersSourceEmitter;

        if ($this->lineEnding !== null) {
            $emitter->setNewline($this->lineEnding);
        }

        if ($this->indentType !== null) {
            $emitter->setIndentType($this->indentType);
        }

        if ($this->indentSize !== null) {
            $emitter->setIndentSize($this->indentSize);
        }

        if ($this->finalNewline !== null) {
            $emitter->setFinalNewline($this->finalNewline);
        }

        if ($this->preferComponentSyntax !== null) {
            $emitter->setPreferComponentSyntax($this->preferComponentSyntax);
        }

        return $emitter;
    }

    public function scaffold(string $template, array $context = []): ScaffoldedTemplate
    {
        [$resourceType, $viewName] = $this->parseTemplateName($template);

        $viewPath = sprintf(
            'statamic::scaffolding.%s.%s.%s',
            $this->templateLanguage(),
            $resourceType,
            $viewName
        );

        if (! view()->exists($viewPath)) {
            throw new InvalidArgumentException(
                "Scaffold template [{$viewPath}] not found."
            );
        }

        $content = view($viewPath, array_merge([
            'generator' => $this,
            'emit' => $this->getEmitter(),
        ], $context))->render();

        return new ScaffoldedTemplate(trim($content), $this);
    }

    private function parseTemplateName(string $template): array
    {
        $parts = explode('.', $template);

        if (count($parts) < 2) {
            throw new InvalidArgumentException(
                "Template name must be in format 'resource.view', got: {$template}"
            );
        }

        return [$parts[0], implode('.', array_slice($parts, 1))];
    }

    public function emitView(string $viewName, Field $field, array $context = [])
    {
        $emitter = $this->getEmitter();
        $handle = $field->handle();
        $variable = $emitter->varName($handle);

        $scaffoldingContext = $this->templateLanguage === 'blade'
            ? new BladeScaffoldingContext($emitter, $field, $handle, $variable, $this, $context)
            : new AntlersScaffoldingContext($emitter, $field, $handle, $variable, $this, $context);

        $initialStackSize = count($emitter->getVariableStack());

        try {
            $view = view($viewName, array_merge([
                'context' => $scaffoldingContext,
                'field' => $field,
                'config' => $field->config(),
                'handle' => $handle,
                'variable' => $variable,
                'generator' => $this,
                'emit' => $emitter,
            ], $context));

            $result = $view->render();

            if ($result instanceof Stringable) {
                $result = (string) $result;
            }

            return trim($result);
        } finally {
            $finalStackSize = count($emitter->getVariableStack());

            for ($i = $finalStackSize; $i > $initialStackSize; $i--) {
                $emitter->popContext();
            }
        }
    }

    public function emitScaffoldingView(string $viewName, Field $field, array $context = [])
    {
        $viewName = 'statamic::scaffolding.'.$this->templateLanguage().'.fieldtypes.'.$viewName;

        if (! view()->exists($viewName)) {
            return '';
        }

        return $this->emitView($viewName, $field, $context);
    }

    public function emitFieldtypeView(Field $field, array $context = [])
    {
        return $this->emitScaffoldingView($field->fieldtype()->handle(), $field, $context);
    }
}
