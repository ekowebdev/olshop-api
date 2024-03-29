<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
    use RefreshDatabase;
    
    protected $access_token;

    public function setUp(): void
    {
        parent::setUp();

        $data = [
            'name' => 'Testing',
            'username' => 'testing',
            'email' => 'testing@mail.com',
            'password' => Hash::make('123456'),
        ];

        $user = User::create($data);
        $this->actingAs($user);

        $this->access_token = $user->createToken('Test Token')->accessToken;
    }

    public function test_create_data()
    {
        $data = [
            'name' => 'Testing',
            'username' => 'testing',
            'email' => 'testing@mail.com',
            'password' => bcrypt('123456'),
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->postJson('/api/id/users', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'testing@mail.com',
        ]);
    }
}
