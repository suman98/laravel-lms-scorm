<?php

namespace EscolaLms\Categories\Http\Resources;

use EscolaLms\Categories\Models\Category;
use EscolaLms\Courses\Enum\CourseStatusEnum;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/**
 * @mixin \EscolaLms\Categories\Models\Category
 */
class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_with_breadcrumbs' => $this->name_with_breadcrumbs,
            'slug' => $this->slug,
            'icon' => $this->icon ? Storage::url($this->icon) : null,
            'icon_class' => $this->icon_class,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'parent_id' => $this->parent_id,
            'count' => $this->published_courses ?? 0,
        ];
    }
}
