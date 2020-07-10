<?php

/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * @package     CacheLib
 * @copyright   2020 Podvirnyy Nikita (Observer KRypt0n_)
 * @license     GNU GPL-3.0 <https://www.gnu.org/licenses/gpl-3.0.html>
 * @author      Podvirnyy Nikita (Observer KRypt0n_)
 * 
 * Contacts:
 *
 * Email: <suimin.tu.mu.ga.mi@gmail.com>
 * VK:    <https://vk.com/technomindlp>
 *        <https://vk.com/hphp_convertation>
 * 
 */

namespace Cache;

/**
 * Подключение базовых классов
 */
require __DIR__ .'/Providers/Item.php';
require __DIR__ .'/Providers/Provider.php';

/**
 * Подключение провайдеров
 */
foreach (array_slice (scandir (__DIR__ .'/Providers'), 2) as $dir)
    if (is_dir (__DIR__ .'/Providers/'. $dir))
        require __DIR__ .'/Providers/'. $dir .'/Provider.php';
