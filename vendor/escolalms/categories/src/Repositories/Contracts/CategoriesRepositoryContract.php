<?php

namespace EscolaLms\Categories\Repositories\Contracts;

use EscolaLms\Core\Repositories\Contracts\ActivationContract;
use EscolaLms\Core\Repositories\Contracts\BaseRepositoryContract;

interface CategoriesRepositoryContract extends BaseRepositoryContract, ActivationContract
{
    public function allRoots(array $search = [], ?int $skip = null, ?int $limit = null);
}
