<?php

namespace Flowy\DynamicBundle\Service;

use Cycle\Annotated\Embeddings;
use Cycle\Annotated\Entities;
use Cycle\Annotated\Locator\TokenizerEmbeddingLocator;
use Cycle\Annotated\Locator\TokenizerEntityLocator;
use Cycle\Annotated\MergeColumns;
use Cycle\Annotated\MergeIndexes;
use Cycle\Annotated\TableInheritance;
use Cycle\Schema\Generator;
use Cycle\Database;
use Cycle\Database\Config;
use Cycle\Database\Driver\Postgres\PostgresDriver;
use Cycle\ORM\Factory;
use Cycle\ORM\ORM;
use Cycle\ORM\ORMInterface;
use Cycle\ORM\Schema;
use Cycle\Schema\Compiler;
use Cycle\Schema\Registry;
use Flowy\CoreBundle\Entity\Client;
use Flowy\CoreBundle\Entity\ClientDatabase;
use Flowy\CoreBundle\Service\DatabaseManager;
use PDO;
use PDOException;
use Spiral\Tokenizer\Config\TokenizerConfig;
use Spiral\Tokenizer\Tokenizer;

class DynamicDatabaseManager
{

    private ?Client $client = null;

    private ORMInterface $orm;

    public function __construct()
    {
    }


    public function updateSchema(Client $client): void
    {
        DatabaseManager::createDatabase($client->getClientDatabase());

        $generators = [new Generator\ResetTables()];
        $generators = array_merge($generators, $this->getBaseGenerators());
        $generators = array_merge($generators, [
            new TableInheritance(),
            new MergeColumns(),
            new Generator\GenerateRelations(),
            new Generator\GenerateModifiers(),
            new Generator\ValidateEntities(),
            new Generator\RenderTables(),
            new Generator\RenderRelations(),
            new Generator\RenderModifiers(),
            new Generator\ForeignKeys(),
            new MergeIndexes(),
            new Generator\SyncTables(),
            new Generator\GenerateTypecast(),
        ]);

        $dbal = $this->getDBAL($client->getClientDatabase());

        $registry = new Registry($dbal);
        (new Compiler())->compile($registry, $generators);
    }

    public function setClient(Client $client): ORMInterface
    {
        if ($this->client === $client) {
            return $this->orm;
        }

        $generators = $this->getBaseGenerators();
        $dbal = $this->getDBAL($client->getClientDatabase());

        $registry = new Registry($dbal);
        $schemaArray = (new Compiler())->compile($registry, $generators);
        $schema = new Schema($schemaArray);

        $this->orm = new ORM(new Factory($dbal), $schema);
        $this->client = $client;

        return $this->orm;
    }
    private function getDBAL(ClientDatabase $clientDatabase): Database\DatabaseManager
    {
        return new Database\DatabaseManager(
            new Config\DatabaseConfig([
                'default' => 'default',
                'databases' => [
                    'default' => ['connection' => 'postgres']
                ],
                'connections' => [
                    'postgres' => new Config\PostgresDriverConfig(
                        connection: new Config\Postgres\TcpConnectionConfig(
                            database: $clientDatabase->getDatabase(),
                            host: $clientDatabase->getHost(),
                            port: $clientDatabase->getPort(),
                            user: $clientDatabase->getUser(),
                            password: $clientDatabase->getPassword(),
                        ),
                        driver: PostgresDriver::class
                    )
                ]
            ])
        );
    }

    private function getBaseGenerators(): array
    {
        $entityFolder = '/var/www/html/projects/client-project/vendor/flowy/dynamic-bundle/src/Entity';
        $classLocator = (new Tokenizer(new TokenizerConfig(['directories' => [$entityFolder]])))->classLocator();
        $embeddingLocator = new TokenizerEmbeddingLocator($classLocator);
        $entityLocator = new TokenizerEntityLocator($classLocator);

        return [new Embeddings($embeddingLocator), new Entities($entityLocator)];
    }

}