<?php

namespace Statamic\Fieldtypes\Bard;

class StatamicImageNode extends ImageNode
{
    protected function getUrl($id)
    {
        return 'statamic://asset::'.$id;
    }
}
