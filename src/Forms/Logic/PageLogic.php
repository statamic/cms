<?php

namespace Statamic\Forms\Logic;

use Illuminate\Support\Collection;
use Statamic\Contracts\Forms\Form;

class PageLogic
{
    private ?Collection $pages = null;

    public function __construct(private readonly Form $form)
    {
    }

    public function nextPage(string $currentPageId, array $data): ?string
    {
        $pages = $this->pages();
        $currentIndex = $pages->search(fn (array $page): bool => $page['id'] === $currentPageId);

        if ($currentIndex === false) {
            return null;
        }

        if ($destination = $this->matchingDestination($pages->get($currentIndex), $data)) {
            return $destination;
        }

        $nextPage = $pages->get($currentIndex + 1);

        return $nextPage ? $nextPage['id'] : null;
    }

    public function isFinalPage(string $currentPageId, array $data): bool
    {
        return $this->nextPage($currentPageId, $data) === null;
    }

    private function matchingDestination(array $page, array $data): ?string
    {
        $evaluator = new RuleEvaluator;

        foreach ($page['rules'] ?? [] as $rule) {
            $destination = $rule['destination'] ?? null;

            if (! $destination || ! $this->pageExists($destination)) {
                continue;
            }

            if ($evaluator->passes($rule['conditions'] ?? [], $data)) {
                return $destination;
            }
        }

        return null;
    }

    private function pageExists(string $id): bool
    {
        return $this->pages()->contains(fn (array $page): bool => $page['id'] === $id);
    }

    private function pages(): Collection
    {
        return $this->pages ??= $this->form->formFields()->pages();
    }
}
