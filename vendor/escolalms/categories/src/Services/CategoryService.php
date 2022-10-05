<?php

namespace EscolaLms\Categories\Services;

use EscolaLms\Categories\Dtos\CategoryDto;
use EscolaLms\Categories\Enums\ConstantEnum;
use EscolaLms\Categories\Models\Category;
use EscolaLms\Categories\Repositories\Contracts\CategoriesRepositoryContract;
use EscolaLms\Categories\Services\Contracts\CategoryServiceContracts;
use EscolaLms\Core\Dtos\PaginationDto;
use EscolaLms\Core\Dtos\PeriodDto;
use EscolaLms\Files\Helpers\FileHelper;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryService implements CategoryServiceContracts
{
    private CategoriesRepositoryContract $categoryRepository;

    /**
     * CategoryService constructor.
     * @param CategoriesRepositoryContract $categoryRepository
     */
    public function __construct(CategoriesRepositoryContract $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    public function slugify(string $name): string
    {
        $slug = Str::slug($name, '-');

        $total = Category::where('slug', 'like', $slug . '%')->count();
        return ($total > 0) ? "{$slug}-{$total}" : $slug;
    }

    public function getList(?string $search = null)
    {
        $paginate_count = 10;

        if ($search) {
            return Category::where('name', 'LIKE', '%' . $search . '%')->paginate($paginate_count);
        }

        return Category::paginate($paginate_count);
    }

    public function find(?int $id = null)
    {
        if ($id) {
            return Category::find($id);
        }

        return Controller::getColumnTable('categories');
    }

    public function store(CategoryDto $categoryDto): Category
    {
        return DB::transaction(function () use($categoryDto) {
            $category = $this->categoryRepository->create($categoryDto->toArray());
            $category->slug = $this->slugify($category->name);

            if (!is_null($categoryDto->getIcon())) {
                $category->icon = $this->saveIcon($categoryDto->getIcon(), $category->getKey());
            }

            $category->save();

            return $category;
        });
    }

    public function update(int $id, CategoryDto $categoryDto): Category
    {
        return DB::transaction(function () use($categoryDto, $id) {
            $category = $this->categoryRepository->update($categoryDto->toArray(), $id);
            $category->slug = $this->slugify($category->name);

            if (!is_null($categoryDto->getIcon())) {
                $category->icon = $this->saveIcon($categoryDto->getIcon(), $category->getKey());
            }

            if ($categoryDto->getIconPath() !== false) {
                $category->icon = $categoryDto->getIconPath();
            }

            $category->save();

            return $category;
        });
    }

    public function delete(int $id): void
    {
        Category::destroy($id);
    }

    public function getPopular(PaginationDto $pagination, PeriodDto $period): Collection
    {
        return $this->categoryRepository->getByPopularity($pagination, $period->from(), $period->to());
    }

    private function saveIcon($icon, $id): string
    {
        return FileHelper::getFilePath($icon, ConstantEnum::DIRECTORY . "/{$id}/icons");
    }
}
