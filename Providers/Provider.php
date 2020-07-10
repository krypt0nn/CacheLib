<?php

namespace Cache;

/**
 * Интерфейс провайдера кэша
 */
interface Provider
{
    /**
     * Конструктор провайдера
     * 
     * [@param array $params = []] - массив параметров провайдера
     */
    public function __construct (array $params = []);

    /**
     * Получение элемента кэша
     * 
     * @param string $id - индекс элемента
     * 
     * @return Item|null - возвращает объект представления
     * значения кэша либо null в случае отсутствия самого элемента
     */
    public function get (string $id): ?Item;

    /**
     * Установка элемента кэша
     * 
     * @param string $id   - индекс элемента
     * @param mixed $value - значение элемента
     * [@param array $params = []] - массив параметров элемента кэша (объекта Item)
     * 
     * Значение элемента может быть представлено объектом Item
     * 
     * @return bool - возвращает статус установки элемента
     */
    public function set (string $id, $value, array $params = []): bool;

    /**
     * Удаление элемента кэша
     * 
     * @param string $id - индекс элемента
     * 
     * @return bool - возвращает статус удаления элемента
     */
    public function remove (string $id): bool;

    /**
     * Проверка элемента кэша на существование
     * 
     * @param string $id - индекс элемента
     * 
     * @return bool - возвращает true, если элемент существует
     */
    public function exists (string $id): bool;

    /**
     * Удаление устаревших элементов
     * 
     * @return int - возвращает число удалённых устаревших элементов
     */
    public function removeExpired (): int;
}
