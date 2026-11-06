<?php

namespace Statamic\Search;

use Archetype\Facades\PHPFile;
use PhpParser\BuilderHelpers;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Return_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;

class ConfigUpdater
{
    public function render(string $contents, array $changes, array $removals = []): string
    {
        $file = PHPFile::fromString($contents);
        $nodes = (new NodeTraverser(new CloningVisitor))->traverse($file->ast());
        $config = null;

        foreach ($nodes as $node) {
            if ($node instanceof Return_ && $node->expr instanceof Array_) {
                $config = $node->expr;
                break;
            }
        }

        if (! $config) {
            throw new \RuntimeException('The search config must return an array to configure it automatically.');
        }

        foreach ($removals as $path) {
            $this->remove($config, $path);
        }

        foreach ($changes as [$path, $value]) {
            $this->set($config, $path, $value);
        }

        return $file->ast($nodes)->render();
    }

    private function set(Array_ $array, array $path, mixed $value): void
    {
        $key = array_shift($path);

        foreach ($array->items as $item) {
            if (! $item || ! $item->key instanceof String_ || $item->key->value !== $key) {
                continue;
            }

            if ($path === []) {
                $item->value = BuilderHelpers::normalizeValue($value);

                return;
            }

            if (! $item->value instanceof Array_) {
                throw new \RuntimeException("Search configuration [{$key}] must be an array to configure it automatically.");
            }

            $this->set($item->value, $path, $value);

            return;
        }

        $expression = $path === [] ? BuilderHelpers::normalizeValue($value) : new Array_;

        if ($path !== []) {
            $this->set($expression, $path, $value);
        }

        $array->items[] = new ArrayItem($expression, new String_($key));
    }

    private function remove(Array_ $array, array $path): void
    {
        $key = array_shift($path);

        foreach ($array->items as $position => $item) {
            if (! $item || ! $item->key instanceof String_ || $item->key->value !== $key) {
                continue;
            }

            if ($path === []) {
                array_splice($array->items, $position, 1);
            } elseif ($item->value instanceof Array_) {
                $this->remove($item->value, $path);
            } else {
                throw new \RuntimeException("Search configuration [{$key}] must be an array to configure it automatically.");
            }

            return;
        }
    }
}
