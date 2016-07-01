<?php

namespace go1\kv;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use InvalidArgumentException;

class KV
{
    private $read;
    private $write;
    private $tableName;

    public function __construct(Connection $read, Connection $write = null, $table = 'kv')
    {
        $this->tableName = $table;
        $this->read = $read;
        $this->write = $write;

        if (!$this->write) {
            $this->write = &$this->read;
        }
    }

    public function install($execute = true)
    {
        static::migrate(
            $schema = $this->write->getSchemaManager()->createSchema(),
            $this->tableName
        );

        if ($execute) {
            foreach ($schema->toSql($this->write->getDatabasePlatform()) as $sql) {
                $this->write->executeQuery($sql);
            }
        }
    }

    public static function migrate(Schema $schema, $tableName)
    {
        $table = $schema->createTable($tableName);
        $table->addColumn('k', 'string');
        $table->addColumn('v', 'blob');
        $table->setPrimaryKey(['k']);
    }

    public function has($key)
    {
        return $this
            ->read
            ->fetchColumn("SELECT 1 FROM {$this->tableName} WHERE k = ?", [$key]) ? true : false;
    }

    public function fetch($key)
    {
        $value = $this
            ->read
            ->fetchColumn("SELECT v FROM {$this->tableName} WHERE k = ?", [$key]);

        if ($value === FALSE || $value === NULL || $value === '') {
            throw new NotFoundException();
        }

        return in_array(substr($value, 0, 2), ['a:', 'O:']) ? unserialize($value) : $value;
    }

    public function save($key, $value)
    {
        if ($value === NULL || $value === '') {
            throw new InvalidArgumentException();
        }

        $update = $this->has($key);
        $value = is_scalar($value) ? $value : serialize($value);

        $update
            ? $this->write->update($this->tableName, ['v' => $value], ['k' => $key])
            : $this->write->insert($this->tableName, ['k' => $key, 'v' => $value]);

        return $update ? 2 : 1;
    }

    public function delete($key)
    {
        $this
            ->write
            ->delete($this->tableName, ['k' => $key]);
    }
}
