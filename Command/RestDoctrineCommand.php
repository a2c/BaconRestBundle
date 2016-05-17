<?php

namespace Bacon\Bundle\RestBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\GenerateDoctrineCrudCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Sensio\Bundle\GeneratorBundle\Command\AutoComplete\EntitiesAutoCompleter;
use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Bacon\Bundle\RestBundle\Manipulator\RoutingRestManipulator;
use Bacon\Bundle\RestBundle\Generator\DoctrineRestGenerator as BaconDoctrineRestGenerator;

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
            $routing->addAnnotationController($bundle->getName(), ucfirst($prefix));
        }
    }


    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getQuestionHelper();
        $questionHelper->writeSection($output, 'Welcome to the Doctrine2 CRUD generator');

        // namespace
        $output->writeln(array(
            '',
            'This command helps you generate CRUD controllers and templates.',
            '',
            'First, give the name of the existing entity for which you want to generate a CRUD',
            '(use the shortcut notation like <comment>AcmeBlogBundle:Post</comment>)',
            '',
        ));

        if ($input->hasArgument('entity') && $input->getArgument('entity') != '') {
            $input->setOption('entity', $input->getArgument('entity'));
        }

        $question = new Question($questionHelper->getQuestion('The Entity shortcut name', $input->getOption('entity')), $input->getOption('entity'));
        $question->setValidator(array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateEntityName'));

        $autocompleter = new EntitiesAutoCompleter($this->getContainer()->get('doctrine')->getManager());
        $autocompleteEntities = $autocompleter->getSuggestions();
        $question->setAutocompleterValues($autocompleteEntities);
        $entity = $questionHelper->ask($input, $output, $question);

        $input->setOption('entity', $entity);
        list($bundle, $entity) = $this->parseShortcutNotation($entity);

        try {
            $entityClass = $this->getContainer()->get('doctrine')->getAliasNamespace($bundle).'\\'.$entity;
            $metadata = $this->getEntityMetadata($entityClass);
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf('Entity "%s" does not exist in the "%s" bundle. You may have mistyped the bundle name or maybe the entity doesn\'t exist yet (create it first with the "doctrine:generate:entity" command).', $entity, $bundle));
        }

        // format
        $format = $input->getOption('format');
        $output->writeln(array(
            '',
            'Determine the format to use for the generated CRUD.',
            '',
        ));
        $question = new Question($questionHelper->getQuestion('Configuration format (yml, xml, php, or annotation)', $format), $format);
        $question->setValidator(array('Sensio\Bundle\GeneratorBundle\Command\Validators', 'validateFormat'));
        $format = $questionHelper->ask($input, $output, $question);
        $input->setOption('format', $format);

        // route prefix
        $prefix = $this->getRoutePrefix($input, $entity);
        $output->writeln(array(
            '',
            'Determine the routes prefix (all the routes will be "mounted" under this',
            'prefix: /prefix/, /prefix/new, ...).',
            '',
        ));
        $prefix = $questionHelper->ask($input, $output, new Question($questionHelper->getQuestion('Routes prefix', '/'.$prefix), '/'.$prefix));
        $input->setOption('route-prefix', $prefix);

        // summary
        $output->writeln(array(
            '',
            $this->getHelper('formatter')->formatBlock('Summary before generation', 'bg=blue;fg=white', true),
            '',
            sprintf('You are going to generate a CRUD controller for "<info>%s:%s</info>"', $bundle, $entity),
            sprintf('using the "<info>%s</info>" format.', $format),
            '',
        ));
    }

    protected function getSkeletonDirs(BundleInterface $bundle = null)
    {
        $skeletonDirs = array();

        if (isset($bundle) && is_dir($dir = $bundle->getPath().'/Resources/SensioGeneratorBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }

        if (is_dir($dir = $this->getContainer()->getParameter('kernel.root_dir').'/Resources/SensioGeneratorBundle/skeleton')) {
            $skeletonDirs[] = $dir;
        }

        $container = $this->getContainer();
        $path = $container->getParameter('kernel.root_dir').'/../vendor/baconmanager/rest-bundle/Resources';
        
        $skeletonDirs[] = realpath($path . '/skeleton');
        $skeletonDirs[] = realpath($path);

        return $skeletonDirs;
    }

    protected function createGenerator(BundleInterface $bundle = null)
    {   
        $dir = $this->getContainer()->getParameter('kernel.root_dir');

        if($bundle) {
            $dir = $bundle->getPath();
        }

        return new BaconDoctrineRestGenerator(
            $this->getContainer()->get('filesystem'),
            $dir
        );
    }
}
