<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndex()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.index'));

        $response
            ->assertStatus(200)
            ->assertJson([$genre->toArray()]);
    }

    public function testShow()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->get(route('genres.show', ['genre' => $genre->id]));

        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray());
    }

    public function testStoreNameRequired()
    {
        $response = $this->json('POST', route('genres.store', []));

        $this->assertNameRequired($response);
    }

    public function testStoreNameLengthAndIsActive()
    {
        $response = $this->json('POST', route('genres.store', [
            'name' => str_repeat('a', 256),
            'is_active' => 'a'
        ]));

        $this->assertNameMaxLengthAndIsActiveRequired($response);
    }

    public function testUpdateNameRequired()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->json(
            'PUT',
            route('genres.update', ['genre' => $genre->id]),
            []
        );

        $this->assertNameRequired($response);
    }

    public function testUpdateNameLengthAndIsActive()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->json(
            'PUT',
            route('genres.update', ['genre' => $genre->id]),
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
        $response = $this->json('POST', route('genres.store', ['name' => 'test']));

        $id = $response->json('id');
        $genre = Genre::find($id);

        $response
            ->assertStatus(201)
            ->assertJson($genre->toArray());
        $this->assertTrue($response->json('is_active'));
    }

    public function testStoreWithSpecificValues()
    {
        $genre_data = ['name' => 'test', 'is_active' => false];
        $response = $this->json('POST', route('genres.store', $genre_data));

        $id = $response->json('id');
        $genre = Genre::find($id);

        $response
            ->assertStatus(201)
            ->assertJsonFragment($genre_data);
    }

    public function testUpdate()
    {
        $new_genre_data = ['name' => 'a', 'is_active' => true];
        $genre = factory(Genre::class)->create([
            'is_active' => false
        ]);
        $response = $this->json(
            'PUT',
            route('genres.update', ['genre' => $genre->id]),
            $new_genre_data
        );

        $id = $response->json('id');
        $genre = Genre::find($id);

        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray())
            ->assertJsonFragment($new_genre_data);
    }

    public function testUpdateWithEmptyDescription()
    {
        $new_genre_data = ['name' => 'test'];
        $genre = factory(Genre::class)->create([
            'is_active' => false
        ]);
        $response = $this->json(
            'PUT',
            route('genres.update', ['genre' => $genre->id]),
            $new_genre_data
        );

        $id = $response->json('id');
        $genre = Genre::find($id);

        $response
            ->assertStatus(200)
            ->assertJson($genre->toArray())
            ->assertJsonFragment(['name' => 'test']);
    }

    public function testDestroy()
    {
        $genre = factory(Genre::class)->create();
        $response = $this->json(
            'DELETE',
            route('genres.destroy', ['genre' => $genre->id])
        );

        $genre = Genre::find($genre->id);

        $response->assertStatus(204);
        $this->assertNull($genre);
    }
}
