<?php

namespace Statamic\View\Blade;

use Stillat\BladeParser\Compiler\Transformers\NodeTransformer;
use Stillat\BladeParser\Nodes\DirectiveNode;

class AntlersDirectiveTransformer extends NodeTransformer
{
    public function transformNode($node): ?string
    {
        if (! $node instanceof DirectiveNode || $node->content != 'antlers') {
            return null;
        }

        $contentHash = sha1($node->innerDocumentContent);
        $fileName = 'antlers_'.$contentHash;

        file_put_contents(storage_path('framework/views/'.$fileName.'.antlers.html'), $node->innerDocumentContent);

        $this->skipToNode($node->isClosedBy);

        return '@include(\'compiled__views::'.$fileName.'\')';
    }
}
