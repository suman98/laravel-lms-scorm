<?php

namespace EscolaLms\Categories\Tests\API;

use EscolaLms\Categories\Database\Seeders\CategoriesPermissionSeeder;
use EscolaLms\Categories\Enums\CategoriesPermissionsEnum;
use EscolaLms\Categories\Enums\ConstantEnum;
use EscolaLms\Categories\Models\Category;
use EscolaLms\Categories\Tests\TestCase;
use EscolaLms\Core\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CategoriesApiTest extends TestCase
{
    use WithoutMiddleware, DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CategoriesPermissionSeeder::class);

        $this->user = $this->createAdmin();
    }

    public function testCategoriesIndex(): void
    {
        $this->response = $this->json('GET', '/api/categories');

        $this->response->assertOk();
    }

    public function testCategoriesIndexTree(): void
    {
        $this->response = $this->json('GET', '/api/categories/tree');

        $this->response->assertOk();
    }

    public function testCategoriesIndexUserAnonymous()
    {
        Category::factory()->count(10)->create(['is_active' => true]);
        Category::factory()->count(5)->create(['is_active' => false]);

        $this->response = $this->json('GET', '/api/categories');

        $this->response->assertOk();
        $this->response->assertJsonCount(10, 'data');
        $this->response->assertJsonStructure([
            'data' => [[
                'id',
                'name',
                'name_with_breadcrumbs',
                'slug',
                'icon',
                'icon_class',
                'is_active',
                'created_at',
                'updated_at',
                'parent_id',
                'count',
            ]]
        ]);
    }

    public function testCategoriesIndexUserAdmin()
    {
        $user = $this->createAdmin();
        $user->givePermissionTo(CategoriesPermissionsEnum::CATEGORY_LIST);

        Category::factory()->count(10)->create(['is_active' => true]);
        Category::factory()->count(5)->create(['is_active' => false]);

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/admin/categories');

        $this->response->assertOk();
        $this->response->assertJsonCount(15, 'data');
        $this->response->assertJsonStructure([
            'data' => [[
                'id',
                'name',
                'name_with_breadcrumbs',
                'slug',
                'icon',
                'icon_class',
                'is_active',
                'created_at',
                'updated_at',
                'parent_id',
                'count',
            ]]
        ]);
    }

    public function testCategoriesTreeUserAnonymous()
    {
        $category_parent_active = Category::factory()->create(['parent_id' => null, 'is_active' => true]);
        $category_parent_inactive = Category::factory()->create(['parent_id' => null, 'is_active' => false]);
        Category::factory()->create(['parent_id' => $category_parent_active->getKey(), 'is_active' => false]);
        Category::factory()->create(['parent_id' => $category_parent_inactive->getKey(), 'is_active' => false]);

        $this->response = $this->json('GET', '/api/categories/tree');

        $this->response->assertOk();
        $this->response->assertJsonCount(1, 'data');
        $this->response->assertJsonCount(0, 'data.0.subcategories');
        $this->response->assertJsonStructure([
            'data' => [[
                'id',
                'name',
                'name_with_breadcrumbs',
                'slug',
                'icon',
                'icon_class',
                'is_active',
                'created_at',
                'updated_at',
                'parent_id',
                'count',
                'subcategories'
            ]]
        ]);
    }

    public function testCategoriesTreeUserAdmin()
    {
        $user = $this->createAdmin();
        $user->givePermissionTo(CategoriesPermissionsEnum::CATEGORY_LIST);

        $category_parent_active = Category::factory()->create(['parent_id' => null, 'is_active' => true]);
        $category_parent_inactive = Category::factory()->create(['parent_id' => null, 'is_active' => false]);
        Category::factory()->create(['parent_id' => $category_parent_active->getKey(), 'is_active' => true]);
        Category::factory()->create(['parent_id' => $category_parent_inactive->getKey(), 'is_active' => false]);

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/categories/tree');

        $this->response->assertOk();
        $this->response->assertJsonCount(2, 'data');
        $this->response->assertJsonCount(1, 'data.0.subcategories');
        $this->response->assertJsonStructure([
            'data' => [[
                'id',
                'name',
                'name_with_breadcrumbs',
                'slug',
                'icon',
                'icon_class',
                'is_active',
                'created_at',
                'updated_at',
                'parent_id',
                'count',
                'subcategories'
            ]]
        ]);
    }

    public function testCategoryCannotShow(): void
    {
        $user = $this->createStudent();
        $category = Category::factory()->create();

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/admin/categories/' . $category->getKey());

        $this->response->assertForbidden();
    }

    public function testCategoryShow(): void
    {
        $user = $this->createAdmin();
        $user->givePermissionTo(CategoriesPermissionsEnum::CATEGORY_READ);
        $category = Category::factory()->create();

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/admin/categories/' . $category->getKey());

        $this->response->assertOk();
    }

    public function testCategoryShowWithParent(): void
    {
        $user = $this->createAdmin();
        $user->givePermissionTo(CategoriesPermissionsEnum::CATEGORY_READ);
        $category_parent = Category::factory()->create();
        $category_child = Category::factory()->create(['parent_id' => $category_parent->getKey()]);

        $this->assertEquals($category_parent->getKey(), $category_child->parent->getKey());

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/admin/categories/' . $category_child->getKey());

        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'name_with_breadcrumbs' => ucfirst($category_parent->name) . '. ' . ucfirst($category_child->name),
        ]);
    }

    public function testCategoryShowWithCycleInAncestors(): void
    {
        $user = $this->createAdmin();
        $user->givePermissionTo(CategoriesPermissionsEnum::CATEGORY_READ);
        $category_parent = Category::factory()->create();
        $category_child = Category::factory()->create(['parent_id' => $category_parent->getKey()]);
        $category_parent->update(['parent_id' => $category_child->getKey()]);

        $this->assertEquals($category_child->getKey(), $category_parent->parent->getKey());
        $this->assertEquals($category_parent->getKey(), $category_child->parent->getKey());

        $this->response = $this->actingAs($user, 'api')->json('GET', '/api/admin/categories/' . $category_child->getKey());

        $this->response->assertOk();
        $this->response->assertJsonFragment([
            'name_with_breadcrumbs' => ucfirst($category_parent->name) . '. ' . ucfirst($category_child->name),
        ]);
    }

    public function testCategoryUpdate(): void
    {
        $user = User::factory(['email' => 'category@email.com'])->make();
        $category = Category::factory()->create();

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/admin/categories/' . $category->getKey(), [
            'name' => 'Category 123',
            'icon_class' => 'fa-business-time',
            'is_active' => true
        ]);

        $this->response->assertForbidden();
    }

    public function testCategoryCreate(): void
    {
        $user = User::factory(['email' => 'category@email.com'])->make();

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/admin/categories', [
            'name' => 'Category 123',
            'icon_class' => 'fa-business-time',
            'is_active' => true
        ]);

        $this->response->assertForbidden();
    }

    public function testCategoryDestroy(): void
    {
        $user = User::factory(['email' => 'category@email.com'])->make();
        $category = Category::factory()->create();

        $this->response = $this->actingAs($user, 'api')->json('DELETE', '/api/admin/categories/' . $category->getKey());

        $this->response->assertForbidden();
    }

    public function testCategoryUpdateUserAdmin(): void
    {
        $user = $this->createAdmin();
        $user->givePermissionTo(CategoriesPermissionsEnum::CATEGORY_UPDATE);

        $category = Category::factory()->create();
        $this->response = $this->actingAs($this->user, 'api')->json('POST', '/api/admin/categories/' . $category->getKey(), [
            'name' => 'Category 123',
            'icon_class' => 'fa-business-time',
            'is_active' => true
        ]);
        $this->response->assertOk();
    }

    public function testCategoryCreateUserAdmin(): void
    {
        $user = $this->createAdmin();
        $user->givePermissionTo(CategoriesPermissionsEnum::CATEGORY_CREATE);

        $this->response = $this->actingAs($this->user, 'api')->json('POST', '/api/admin/categories', [
            'name' => 'Category 123',
            'icon_class' => 'fa-business-time',
            'is_active' => true
        ]);
        $this->response->assertCreated();
    }

    public function testCategoryDestroyUserAdmin(): void
    {
        $user = config('auth.providers.users.model')::factory()->create();
        $user->guard_name = 'api';
        $user->givePermissionTo(CategoriesPermissionsEnum::CATEGORY_DELETE);
        $category = Category::factory()->create();

        $this->response = $this->actingAs($user, 'api')->json('DELETE', '/api/admin/categories/' . $category->getKey());

        $this->response->assertOk();
    }

    public function testCategoryUpdateUserStudent(): void
    {
        $user = $this->createStudent();
        $category = Category::factory()->create();

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/admin/categories/' . $category->getKey(), [
            'name' => 'Category 123',
            'icon_class' => 'fa-business-time',
            'is_active' => true
        ]);

        $this->response->assertForbidden();
    }

    public function testCategoryCreateUserStudent(): void
    {
        $user = $this->createStudent();

        $this->response = $this->actingAs($user, 'api')->json('POST', '/api/admin/categories', [
            'name' => 'Category 123',
            'icon_class' => 'fa-business-time',
            'is_active' => true
        ]);

        $this->response->assertForbidden();
    }

    public function testCategoryDestroyUserStudent(): void
    {
        $user = $this->createStudent();
        $category = Category::factory()->create();

        $this->response = $this->actingAs($user, 'api')->json('DELETE', '/api/admin/categories/' . $category->getKey());

        $this->response->assertForbidden();
    }

    public function testUpdateOnlyCategoryIcon(): void
    {
        Storage::fake();

        $category = Category::factory()->create();
        $image = UploadedFile::fake()->image('icons-test.jpg');

        $this->actingAs($this->user, 'api')->postJson('/api/admin/categories/' . $category->getKey(), [
            'icon' => $image,
        ])->assertOk();

        $category->refresh();
        Storage::assertExists($category->icon);
        $this->assertEquals(ConstantEnum::DIRECTORY . "/{$category->getKey()}/icons/$image->name", $category->icon);

        $response = $this->actingAs($this->user, 'api')->postJson('/api/admin/categories/' . $category->getKey(), [
            'icon_path' => null,
        ])->assertOk();

        $data = $response->getData()->data;
        $this->assertNull($data->icon);
    }

    public function testUpdateCategoryIconFromExistingFile(): void
    {
        Storage::fake();

        $category = Category::factory()->create();
        $directoryPath = ConstantEnum::DIRECTORY . "/{$category->getKey()}/icons";
        $iconPath = UploadedFile::fake()->image('icons-test.jpg')->storeAs($directoryPath, 'icons-test.jpg');

        $this->actingAs($this->user, 'api')->postJson('/api/admin/categories/' . $category->getKey(), [
            'icon' => $iconPath,
        ])->assertOk();

        $category->refresh();
        Storage::assertExists($category->icon);
        $this->assertEquals($category->icon, $iconPath);
    }

    private function createAdmin()
    {
        $user = config('auth.providers.users.model')::factory()->create();
        $user->guard_name = 'api';
        $user->assignRole('admin');

        return $user;
    }

    private function createStudent()
    {
        $user = config('auth.providers.users.model')::factory()->create();
        $user->assignRole('student');

        return $user;
    }
}
