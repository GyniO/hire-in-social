<?php

/*
 * This file is part of the Hire in Social project.
 *
 * (c) Norbert Orzechowicz <norbert@orzechowicz.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace HireInSocial\Application\Query\Filter;

class Column
{
    private $column;
    private $direction;

    private function __construct()
    {
    }

    public static function asc(string $column) : self
    {
        $sort = new self();
        $sort->column = $column;
        $sort->direction = 'ASC';

        return $sort;
    }

    public static function desc(string $column) : self
    {
        $sort = new self();
        $sort->column = $column;
        $sort->direction = 'DESC';

        return $sort;
    }

    public function is(string $name) : bool
    {
        return \mb_strtolower($this->column) === \mb_strtolower($name);
    }

    public function column() : string
    {
        return $this->column;
    }

    public function direction() : string
    {
        return $this->direction;
    }
}
