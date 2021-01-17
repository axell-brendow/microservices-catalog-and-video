<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndex()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$category->toArray()]);
    }

    public function testShow()
    {
        $category = factory(Category::class)->create();
        $response = $this->get(route('categories.show', ['category' => $category->id]));

        $response
            ->assertStatus(200)
            ->assertJson($category->toArray());
    }

    public function testStoreNameRequired()
    {
        $response = $this->json('POST', route('categories.store', []));

        $this->assertNameRequired($response);
    }

    public function testStoreNameLengthAndIsActive()
    {
        $response = $this->json('POST', route('categories.store', [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]));

        $this->assertNameMaxLengthAndIsActiveRequired($response);
    }

    public function testUpdateNameRequired()
    {
        $category = factory(Category::class)->create();
        $response = $this->json(
            'PUT',
            route('categories.update', ['category' => $category->id]),
            []
        );

        $this->assertNameRequired($response);
    }

    public function testUpdateNameLengthAndIsActive()
    {
        $category = factory(Category::class)->create();
        $response = $this->json(
            'PUT',
            route('categories.update', ['category' => $category->id]),
            [
                'name' => str_repeat('a', 256),
                'is_active' => 'a'
            ]
        );

        $this->assertNameMaxLengthAndIsActiveRequired($response);
    }

    protected function assertNameRequired(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                \Lang::get('validation.required', ['attribute' => 'name'])
            ]);
    }

    protected function assertNameMaxLengthAndIsActiveRequired(TestResponse $response)
    {
        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'is_active'])
            ->assertJsonFragment([
                \Lang::get('validation.max.string', ['attribute' => 'name', 'max' => 255])
            ])
            ->assertJsonFragment([
                \Lang::get('validation.boolean', ['attribute' => 'is active'])
            ]);
    }

    public function testStoreWithDefaultValues()
    {
        $response = $this->json('POST', route('categories.store', ['name' => 'test']));

        $id = $response->json('id');
        $category = Category::find($id);

        $response
            ->assertStatus(201)
            ->assertJson($category->toArray());
        $this->assertTrue($response->json('is_active'));
        $this->assertNull($response->json('description'));
    }

    public function testStoreWithSpecificValues()
    {
        $category_data = ['name' => 'test', 'description' => 'description', 'is_active' => false];
        $response = $this->json('POST', route('categories.store', $category_data));

        $id = $response->json('id');
        $category = Category::find($id);

        $response
            ->assertStatus(201)
            ->assertJsonFragment($category_data);
    }

    public function testUpdate()
    {
        $new_category_data = ['name' => 'a', 'description' => 'test', 'is_active' => true];
        $category = factory(Category::class)->create([
            'description' => 'description',
            'is_active' => false
        ]);
        $response = $this->json(
            'PUT',
            route('categories.update', ['category' => $category->id]),
            $new_category_data
        );

        $id = $response->json('id');
        $category = Category::find($id);

        $response
            ->assertStatus(200)
            ->assertJson($category->toArray())
            ->assertJsonFragment($new_category_data);
    }

    public function testUpdateWithEmptyDescription()
    {
        $new_category_data = ['name' => 'test', 'description' => ''];
        $category = factory(Category::class)->create([
            'description' => 'description',
            'is_active' => false
        ]);
        $response = $this->json(
            'PUT',
            route('categories.update', ['category' => $category->id]),
            $new_category_data
        );

        $id = $response->json('id');
        $category = Category::find($id);

        $response
            ->assertStatus(200)
            ->assertJson($category->toArray())
            ->assertJsonFragment(['name' => 'test', 'description' => null]);
    }
}
