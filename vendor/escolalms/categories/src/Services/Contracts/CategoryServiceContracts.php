<?php

namespace EscolaLms\Categories\Services\Contracts;

use EscolaLms\Categories\Dtos\CategoryDto;
use EscolaLms\Categories\Models\Category;

interface CategoryServiceContracts
{
    public function getList(?string $search = null);

    public function find(?int $id = null);

    public function store(CategoryDto $categoryDto): Category;

    public function update(int $id, CategoryDto $categoryDto): Category;

    public function delete(int $id): void;

    public function slugify(string $name): string;
}
