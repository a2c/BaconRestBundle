<?php

namespace Bacon\Bundle\RestBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCrudCommand;
use Sensio\Bundle\GeneratorBundle\Generator\DoctrineCrudGenerator;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Bacon\Bundle\GeneratorBundle\Manipulator\RoutingRestManipulator;
use Bacon\Bundle\GeneratorBundle\Generator\DoctrineRestGenerator as BaconDoctrineRestGenerator;
use Bacon\Bundle\GeneratorBundle\Generator\DoctrineFormGenerator as BaconDoctrineFormGenerator;

class RestDoctrineCommand extends GenerateDoctrineCrudCommand
{
    private $formGenerator;

    /**
     * @var \Sensio\Bundle\GeneratorBundle\Generator\DoctrineRestGenerator
     */
    protected $generator;

    protected function configure()
    {
        parent::configure();

        $this->setName('bacon:generate:rest');
        $this->setDescription('Gerador REST personalizado pela A2C');
        
    }

    protected function updateAnnotationRouting(BundleInterface $bundle, $entity, $prefix)
    {
        $rootDir = $this->getContainer()->getParameter('kernel.root_dir');

        $routing = new RoutingRestManipulator($rootDir.'/config/routing_rest.yml');

        if (!$routing->hasResourceInAnnotation($bundle->getName())) {
            $parts = explode('\\', $entity);
            $controller = array_pop($parts);

            $ret = $routing->addAnnotationController($bundle->getName(), $controller);
        }
    }

    protected function getSkeletonDirs(BundleInterface $bundle = null)
    {
        $skeletonDirs = array();

        if (isset($bundle) && is_dir($dir = $bundle->getPath().'/Resources/SensioGeneratorBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }

        if (is_dir($dir = $this->getContainer()->get('kernel')->getRootdir().'/Resources/SensioGeneratorBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }

        $skeletonDirs[] = realpath(__DIR__.'/../Resources/skeleton');
        $skeletonDirs[] = realpath(__DIR__.'/../Resources');

        return $skeletonDirs;
    }

    protected function createGenerator(BundleInterface $bundle = null)
    {
        return new BaconDoctrineRestGenerator(
            $this->getContainer()->get('filesystem'),
            $this->getContainer()->getParameter('kernel.root_dir')
        );
    }

    protected function getFormGenerator($bundle = null)
    {
        if (null === $this->formGenerator) {
            $this->formGenerator = new BaconDoctrineFormGenerator($this->getContainer()->get('filesystem'));
            $this->formGenerator->setSkeletonDirs($this->getSkeletonDirs($bundle));
        }

        return $this->formGenerator;
    }
}
