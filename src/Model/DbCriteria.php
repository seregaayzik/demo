<?php

namespace App\Model;

class DbCriteria
{
    public ?int $offset = null;
    public ?int $limit = null;
    public ?string $query = null;

    /**
     * @param int|null $offset
     * @param int|null $limit
     * @param string|null $query
     */
    public function __construct(?int $offset = null, ?int $limit = null, ?string $query = null)
    {
        $this->offset = $offset;
        $this->limit = $limit;
        $this->query = $query;
    }

}