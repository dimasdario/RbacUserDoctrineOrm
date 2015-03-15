<?php

namespace RbacUserDoctrineOrm;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\ModuleManager\Feature\DependencyIndicatorInterface;
use Zend\ModuleManager\Feature\ServiceProviderInterface;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;

class Module
    implements BootstrapListenerInterface,
               ConfigProviderInterface,
               ServiceProviderInterface,
               DependencyIndicatorInterface
{

    /**
     * Listen to the bootstrap event
     *
     * @param MvcEvent|EventInterface $e
     * @return array
     */
    public function onBootstrap(EventInterface $e)
    {

        $application    = $e->getApplication();
        /* @var $serviceManager ServiceManager */
        $serviceManager = $application->getServiceManager();
        $config = $serviceManager->get('Config');
        unset($config['doctrine']['orm_default']['drivers']['ZfcUser\Entity']);
        $serviceManager->set('Config', $config);
    }


    /**
     * Returns configuration to merge with application configuration
     *
     * @return array|\Traversable
     */
    public function getConfig()
    {
        return include __DIR__ . '/../../config/module.config.php';
    }

    /**
     *
     * Set autoloader config for RbacUserDoctrineOrm module
     *
     * @return array\Traversable
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__,
                ),
            ),
        );
    }
    
    /**
     * Expected to return \Zend\ServiceManager\Config object or array to
     * seed such an object.
     *
     * @return array|\Zend\ServiceManager\Config
     */
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'RbacUserDoctrineOrmRoleMapper' => function ($sm) {
                    return new Mapper\Role(
                        $sm->get('zfcuser_doctrine_em'),
                        $sm->get('RbacUserDoctrineOrmRoleMapperOptions')
                    );
                },
                'RbacUserDoctrineOrmRoleMapperOptions' => function ($sm) {
                    $config = $sm->get('Configuration');
                    return new Options\RoleMapperOptions(
                        (isset($config['rbac-user-doctrine-orm']['mapper']['role'])
                            ? $config['rbac-user-doctrine-orm']['mapper']['role']
                            : array())
                    );
                },
            ),
        );
    }

    /**
     * Expected to return an array of modules on which the current one depends on
     *
     * @return array
     */
    public function getModuleDependencies()
    {
        return array(
            'DoctrineModule', 'DoctrineORMModule', 'ZfcBase',
            'ZfcUser', 'ZfcUserDoctrineORM', 'ZfcRbac'
        );
    }
}
