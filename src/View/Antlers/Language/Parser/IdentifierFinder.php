<?php

namespace Statamic\View\Antlers\Language\Parser;

use Illuminate\Support\Str;
use Statamic\View\Antlers\Language\Nodes\AntlersNode;
use Statamic\View\Antlers\Language\Nodes\Paths\PathNode;
use Statamic\View\Antlers\Language\Nodes\Paths\VariableReference;
use Statamic\View\Antlers\Language\Nodes\VariableNode;

class IdentifierFinder
{
    private DocumentParser $documentParser;

    public function __construct()
    {
        $this->documentParser = new DocumentParser;
    }

    public function getIdentifiers(string $content): array
    {
        $this->documentParser->resetState();
        $this->documentParser->parse($content);

        // Get nodes will return a "flat" list of nodes.
        // This will make things a bit easier on us.
        $antlersNodes = collect($this->documentParser->getNodes())->filter(function ($node) {
            return $node instanceof AntlersNode && ! $node->isComment && ! ($node->isClosingTag && ! $node->isSelfClosing);
        })->values()->all();

        $identifiers = [];

        foreach ($antlersNodes as $node) {
            $identifiers = array_merge($this->processNode($node, $identifiers));
        }

        return collect($identifiers)->filter(function ($identifier) {
            // Quick and dirty check to see if we care about this identifier.
            return ! Str::contains($identifier, ['.', ':', '[', ']', '(', ')', '{', '}']);
        })->unique()->values()->all();
    }

    protected function processVarReference(AntlersNode $node, VariableReference $reference, array $identifiers): array
    {
        foreach ($reference->pathParts as $part) {
            if ($part instanceof PathNode) {
                if (! array_key_exists($part->name, $node->interpolationRegions)) {
                    $identifiers[] = $part->name;
                }
            } elseif ($part instanceof VariableReference) {
                $identifiers = array_merge($this->processVarReference($node, $part, $identifiers));
            }
        }

        return $identifiers;
    }

    protected function processNode(AntlersNode $node, array $identifiers): array
    {
        if ($node->name != null) {
            $identifiers[] = $node->name->name;

            if ($node->name->methodPart && ! Str::contains($node->name->methodPart, ':')) {
                $identifiers[] = $node->name->methodPart;
            }

            if ($node->pathReference != null) {
                $identifiers = array_merge($this->processVarReference($node, $node->pathReference, $identifiers));
            }

            if (! empty($node->processedInterpolationRegions)) {
                foreach ($node->processedInterpolationRegions as $region) {
                    if (count($region) == 0 || ! $region[0] instanceof AntlersNode) {
                        continue;
                    }

                    $identifiers = array_merge($this->processNode($region[0], $identifiers));
                }
            }

            if ($node->hasParameters) {
                foreach ($node->parameters as $parameter) {
                    if ($parameter->isVariableReference) {
                        $identifiers[] = $parameter->value;
                    }
                }
            } else {
                if (! empty($node->runtimeNodes)) {
                    foreach ($node->runtimeNodes as $runtimeNode) {
                        if (! $runtimeNode instanceof VariableNode) {
                            continue;
                        }

                        if ($runtimeNode->variableReference == null) {
                            $identifiers[] = $runtimeNode->name;
                        }
                    }
                }
            }
        }

        return $identifiers;
    }
}
