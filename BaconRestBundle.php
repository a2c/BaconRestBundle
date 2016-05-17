<?php

namespace Bacon\Bundle\RestBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class BaconRestBundle extends Bundle
{
	public function getParent()
    {
        return 'BaconGeneratorBundle';
    }
}
