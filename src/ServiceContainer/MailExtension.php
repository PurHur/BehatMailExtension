<?php

namespace BehatMailExtension\ServiceContainer;

use Behat\Behat\Context\ServiceContainer\ContextExtension;
use Behat\Testwork\ServiceContainer\Extension;
use Behat\Testwork\ServiceContainer\ExtensionManager;
use BehatMailExtension\Driver\Driver;
use BehatMailExtension\Driver\IMAPDriver;
use BehatMailExtension\Driver\MailDriverInterface;
use BehatMailExtension\Driver\MailTrapDriver;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use BehatMailExtension\Driver\MailCatcherDriver;

/**
 * Class MailExtension
 *
 * @author Philip Maass <pmaass@databay.de>
 */
class MailExtension implements Extension
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @api
     */
    public function process(ContainerBuilder $container)
    {
    }

    /**
     * Returns the extension config key.
     *
     * @return string
     */
    public function getConfigKey()
    {
        return 'MailExtension';
    }

    /**
     * Initializes other extensions.
     *
     * This method is called immediately after all extensions are activated but
     * before any extension `configure()` method is called. This allows extensions
     * to hook into the configuration of other extensions providing such an
     * extension point.
     *
     * @param ExtensionManager $extensionManager
     */
    public function initialize(ExtensionManager $extensionManager)
    {
    }

    /**
     * Setups configuration for the extension.
     *
     * @param ArrayNodeDefinition $builder
     */
    public function configure(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
            ->scalarNode('driver')
            ->defaultValue('imap')
            ->end()
            ->scalarNode('base_url')
            ->defaultValue('localhost')
            ->end()
            ->scalarNode('http_port')
            ->defaultValue(1080)
            ->end()
            ->scalarNode('api_key')
            ->end()
            ->scalarNode('mailbox_id');
    }

    /**
     * Loads extension services into temporary container.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    public function load(ContainerBuilder $container, array $config)
    {
        $driver = null;

        switch ($config['driver']) {
            case 'mailcatcher':
                $driver = new MailCatcherDriver($config);
                break;
            case 'mailtrap':
                $driver = new MailTrapDriver($config);
                break;
            case 'imap':
                $driver = new IMAPDriver($config);
                break;
        }

        if($driver)
        {
            $this->loadInitializer($container, $driver);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param MailDriverInterface $driver
     */
    private function loadInitializer(ContainerBuilder $container, MailDriverInterface $driver)
    {
        $definition = new Definition('BehatMailExtension\Context\MailAwareInitializer', [$driver]);
        $definition->addTag(ContextExtension::INITIALIZER_TAG, ['priority' => 0]);

        $container->setDefinition('mail.initializer', $definition);
    }
}