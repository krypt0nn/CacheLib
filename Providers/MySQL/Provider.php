<?php

namespace Cache\MySQL;

use Cache\
{
    Provider as CacheProvider,
    Item
};

/**
 * Провайдер MySQL кэша подключется только если доступен класс mysqli
 */
if (!extension_loaded ('mysqli'))
    return;

/**
 * Провайдер кэша на базе MySQL
 */
class Provider implements CacheProvider
{
    /**
     * [@var string $host = 'localhost']    - IP MySQL сервера
     * [@var string $username = 'mysql']    - Логин
     * [@var string $password = 'mysql']    - Пароль
     * [@var int $port = 3306]              - Порт сервера
     * [@var string $database = 'database'] - Название базы данных
     * [@var string $table    = 'cache']    - Название таблицы для хранения кэша
     * 
     * Таблица должна иметь две строковых колонки: id и value
     */
    public string $host     = 'localhost';
    public string $username = 'mysql';
    public string $password = 'mysql';
    public int $port        = 3306;
    public string $database = 'database';
    public string $table    = 'cache';

    /**
     * @var callable|string $hash - коллбэк либо название хэш-функции
     */
    public $hash = 'md5';

    /**
     * Объект базы данных MySQL
     */
    protected \mysqli $mysqli;

    public function __construct (array $params = [])
    {
        foreach ($params as $name => $param)
            $this->$name = $param;

        $this->mysqli = new \mysqli ($this->host, $this->username, $this->password, $this->database, $this->port);

        $this->mysqli->query ('CREATE TABLE IF NOT EXISTS `'. $this->table .'` (
            `id` TINYBLOB,
            `value` MEDIUMBLOB
        )');
    }

    public function get (string $id): ?Item
    {
        $result = $this->mysqli->query ('SELECT * FROM `'. $this->table .'` WHERE id = \''. $this->hash ($id) .'\'');

        return $result->num_rows > 0 ?
            unserialize ($result->fetch_object ()->value) : null;
    }

    public function set (string $id, $value, array $params = []): bool
    {
        if (!is_a ($value, 'Cache\Item'))
            $value = new Item ($value, $params);

        $value->createdAt = time ();
        $value = addslashes (serialize ($value));

        $id = $this->hash ($id);
        
        $this->mysqli->query ('DELETE FROM `'. $this->table .'` WHERE id = \''. $id .'\'');

        return $this->mysqli->query ('INSERT INTO `'. $this->table .'` (id, value) VALUES (\''. $id .'\', \''. $value .'\')');
    }

    public function remove (string $id): bool
    {
        return $this->mysqli->query ('DELETE FROM `'. $this->table .'` WHERE id = \''. $this->hash ($id) .'\'');
    }

    public function exists (string $id): bool
    {
        return $this->mysqli->query ('SELECT * FROM `'. $this->table .'` WHERE id = \''. $this->hash ($id) .'\' LIMIT 1')->num_rows > 0;
    }

    public function removeExpired (): int
    {
        $removed = 0;
        $result  = $this->mysqli->query ('SELECT * FROM `'. $this->table .'`');

        while ($item = $result->fetch_object ())
        {
            $value = unserialize ($item->value);

            if ($value->expired () && $this->mysqli->query ('DELETE FROM `'. $this->table .'` WHERE id = \''. $item->id .'\''))
                ++$removed;
        }

        return $removed;
    }

    /**
     * Получение хеша строки
     * 
     * @param string $value
     * @return string
     */
    protected function hash (string $value): string
    {
        return is_callable ($this->hash) ?
            ($this->hash)($value) : hash ($this->hash, $value);
    }
}
