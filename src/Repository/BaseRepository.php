<?php

/*
 * This file is part of the GraphAware Neo4j PHP OGM package.
 *
 * (c) GraphAware Ltd <info@graphaware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace GraphAware\Neo4j\OGM\Repository;

use GraphAware\Neo4j\OGM\EntityManager;
use GraphAware\Neo4j\OGM\Metadata\NodeEntityMetadata;
use ProxyManager\Configuration;
use ProxyManager\Factory\LazyLoadingGhostFactory;
use ProxyManager\FileLocator\FileLocator;
use ProxyManager\GeneratorStrategy\FileWriterGeneratorStrategy;

class BaseRepository
{
    const FILTER_LIMIT = 'limit';

    const FILTER_ORDER = 'order';

    const ORDER_ASC = 'ASC';

    const ORDER_DESC = 'DESC';

    /**
     * @var \GraphAware\Neo4j\OGM\Metadata\ClassMetadata
     */
    protected $classMetadata;

    /**
     * @var \GraphAware\Neo4j\OGM\EntityManager
     */
    protected $entityManager;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var \ReflectionClass
     */
    protected $reflectionClass;

    /**
     * @var \ReflectionClass[]
     */
    protected $loadedReflClasses = [];

    protected $lazyLoadingFactory;

    private static $PAGINATION_FIRST_RESULT_KEY = 'first';
    private static $PAGINATION_LIMIT_RESULTS_KEY = 'max';

    /**
     * @param \GraphAware\Neo4j\OGM\Metadata\ClassMetadata $classMetadata
     * @param \GraphAware\Neo4j\OGM\EntityManager          $manager
     * @param string                                       $className
     */
    public function __construct(NodeEntityMetadata $classMetadata, EntityManager $manager, $className)
    {
        $this->classMetadata = $classMetadata;
        $this->entityManager = $manager;
        $this->className = $className;
        $config = new Configuration();
        $dir = sys_get_temp_dir();
        $config->setGeneratorStrategy(new FileWriterGeneratorStrategy(new FileLocator($dir)));
        $config->setProxiesTargetDir($dir);
        spl_autoload_register($config->getProxyAutoloader());

        $this->lazyLoadingFactory = new LazyLoadingGhostFactory($config);
    }

    /**
     * @return array
     */
    public function findAll()
    {
        return $this->findBy([]);
    }

    /**
     * @param array      $criteria
     * @param array|null $orderBy
     * @param null|int   $limit
     * @param null|int   $offset
     *
     * @return array
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $persister = $this->entityManager->getEntityPersister($this->className);

        return $persister->loadAll($criteria, $orderBy, $limit, $offset);
    }

    /**
     * @param array      $criteria
     * @param array|null $orderBy
     *
     * @return object|null
     */
    public function findOneBy(array $criteria, array $orderBy = null)
    {
        $persister = $this->entityManager->getEntityPersister($this->className);

        return $persister->load($criteria);
    }

    /**
     * @param int $id
     *
     * @return object|null
     */
    public function findOneById($id)
    {
        $persister = $this->entityManager->getEntityPersister($this->className);

        return $persister->loadOneById($id);
    }

    /**
     * @param $className
     *
     * @return \ReflectionClass
     */
    private function getReflectionClass($className)
    {
        if (!array_key_exists($className, $this->loadedReflClasses)) {
            $this->loadedReflClasses[$className] = new \ReflectionClass($className);
        }

        return $this->loadedReflClasses[$className];
    }
}
