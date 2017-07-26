<?php
/**
 * Created by PhpStorm.
 * User: kifkif
 * Date: 26/07/2017
 * Time: 15:24
 */

namespace Troopers\MangopayBundle\DependencyInjection\Compiler;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class DataTransformerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('troopers_mangopay.handler')) {
            return;
        }

        $definition = $container->findDefinition(
            'troopers_mangopay.handler'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'troopers_mangopay.data_transformer'
        );
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall(
                'addDataTransformer',
                array(new Reference($id))
            );
        }
    }
}
