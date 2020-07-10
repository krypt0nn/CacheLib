<?php

namespace Cache;

/**
 * Объект представления элемента кэша
 */
class Item
{
    /**
     * @var mixed $value - значение элемента
     */
    public $value;

    /**
     * @var int|null $ttl - Time To Live элемента - время в секундах, после которого ->expired() будет равен true
     * @var int|null $createdAt - временная метка момента создания элемента
     */
    public ?int $ttl;
    public ?int $createdAt;

    /**
     * Конструктор элемента
     * 
     * @param mixed $value - значение элемента
     * [@param array $params = []] - массив параметров элемента
     */
    public function __construct ($value, array $params = [])
    {
        $this->value = $value;

        foreach ($params as $name => $param)
            $this->$name = $param;
    }

    /**
     * Возвращает true если элемент устарел
     * 
     * @return bool
     */
    public function expired (): bool
    {
        return $this->createdAt !== null &&
               $this->ttl !== null &&
               $this->createdAt + $this->ttl < time ();
    }

    /**
     * Возвращает true если элемент не устарел
     * 
     * @return bool
     */
    public function available (): bool
    {
        return !$this->expired ();
    }
}
