<?php

namespace CultuurNet\ProjectAanvraag\Core;


use CultuurNet\ProjectAanvraag\Core\Schema\DatabaseSchemaInstaller;
use CultuurNet\ProjectAanvraag\Core\Schema\SchemaConfiguratorInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Schema;
use Pimple\Container;

class DatabaseSchemaInstallerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var DatabaseSchemaInstaller|PHPUnit_Framework_MockObject_MockObject
     */
    private $installer;

    /**
     * @var Connection|PHPUnit_Framework_MockObject_MockObject
     */
    private $connection;

    /**
     * @var AbstractSchemaManager|PHPUnit_Framework_MockObject_MockObject
     */
    private $schemaManager;

    public function setUp()
    {

        $this->connection = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->schemaManager = $this->getMock(AbstractSchemaManager::class, [], [$this->connection]);

        $this->connection->method('getSchemaManager')
            ->willReturn($this->schemaManager);

        $container = new Container(
            [
                'db' => $this->connection,
            ]
        );
        $this->installer = new DatabaseSchemaInstaller($container);
    }

    /**
     * Test unknown service handling
     * @expectedException \Symfony\Component\Security\Core\Exception\ProviderNotFoundException
     */
    public function testProviderNotFound() {
        $container = new Container();
        $installer = new DatabaseSchemaInstaller($container);
        $installer->installSchema();
    }

    /**
     * Test if the schema gets installed.
     */
    public function testInstallSchema() {

        $configurator = $this->getMock(SchemaConfiguratorInterface::class);
        $this->installer->addSchemaConfigurator($configurator);

        $configurator->expects($this->once())->method('configure');
        $this->installer->installSchema();
    }

    /**
     * Test if the schema gets uninstalled.
     */
    public function testUninstallSchema() {

        $schema = $this->getMock(Schema::class);
        $schema->expects($this->once())
            ->method('getTableNames')
            ->willReturn(['test', 'test2']);

        $this->schemaManager->method('createSchema')
            ->willReturn($schema);

        $this->schemaManager->expects($this->exactly(2))
            ->method('dropTable')
            ->withConsecutive(
                ['test'],
                ['test2']
            );

        $schema->expects($this->exactly(2))
            ->method('dropTable')
            ->withConsecutive(
                ['test'],
                ['test2']
            );

        $this->installer->uninstallSchema();
    }

}