<?php

namespace Troopers\MangopayBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Troopers\MangopayBundle\DependencyInjection\Compiler\DataTransformerPass;

class TroopersMangopayBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DataTransformerPass());
    }
}
