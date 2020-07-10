<?php

namespace Cache\File;

use Cache\
{
    Provider as CacheProvider,
    Item
};

/**
 * Провайдер файлового кэша
 */
class Provider implements CacheProvider
{
    /**
     * @var string $dir - директория хранения файлов кэша
     */
    public string $dir;

    /**
     * [@var bool $compress = false] - сжимать ли элементы кэша
     * [@var int $compressionLevel = -1] - уровень сжатия
     */
    public bool $compress = false;
    public int $compressionLevel = -1;

    /**
     * [@var bool $encode = false] - шифровать ли элементы
     * [@var string $encodeKey = '...'] - ключ шифрования
     */
    public bool $encode = false;
    public string $encodeKey = '(*#*(@JDSLKJFIUJ#@H$AW(OJDLOAJO:';

    /**
     * @var callable|string $hash - коллбэк либо название хэш-функции
     */
    public $hash = 'md5';

    public function __construct (array $params = ['dir' => __DIR__ .'/.cache/'])
    {
        foreach ($params as $name => $param)
            $this->$name = $param;

        if (!is_dir ($this->dir))
            mkdir ($this->dir, 0777, true);
    }

    public function get (string $id): ?Item
    {
        if (!$this->exists ($id))
            return null;
        
        $value = $this->getValue (file_get_contents ($this->dir .'/'. $this->hash ($id) .'.cache'));

        if ($this->compress)
            $value = gzinflate ($value);

        return unserialize ($value);
    }

    public function set (string $id, $value, array $params = []): bool
    {
        if (!is_a ($value, 'Cache\Item'))
            $value = new Item ($value, $params);

        $value->createdAt = time ();
        $value = serialize ($value);

        if ($this->compress)
            $value = gzdeflate ($value, $this->compressionLevel);

        return file_put_contents ($this->dir .'/'. $this->hash ($id) .'.cache', $this->getValue ($value)) !== false;
    }

    public function remove (string $id): bool
    {
        return unlink ($this->dir .'/'. $this->hash ($id) .'.cache');
    }

    public function exists (string $id): bool
    {
        return file_exists ($this->dir .'/'. $this->hash ($id) .'.cache');
    }

    public function removeExpired (): int
    {
        $removed = 0;

        foreach (glob ($this->dir .'/*.cache') as $file)
        {
            $item = $this->getValue (file_get_contents ($file));

            if ($this->compress)
                $item = gzinflate ($item);

            $item = unserialize ($item);

            if ($item->expired () && unlink ($file))
                ++$removed;
        }

        return $removed;
    }

    /**
     * Шифрование строки методов Вернама
     * 
     * @param string $text
     * @return string
     */
    protected function encode (string $text): string
    {
        return $text ^ str_repeat ($this->encodeKey, ceil (strlen ($text) / strlen ($this->encodeKey)));
    }

    /**
     * Шифрование строки если это необходимо
     * 
     * @param string $value
     * @return string
     */
    protected function getValue (string $value): string
    {
        return $this->encode ?
            $this->encode ($value) : $value;
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
