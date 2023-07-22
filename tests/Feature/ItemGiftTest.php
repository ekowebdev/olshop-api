<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Models\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ItemGiftTest extends TestCase
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
            'password' => bcrypt('123456'),
        ];

        $user = User::create($data);
        $this->actingAs($user);

        $this->access_token = $user->createToken('Test Token')->accessToken;
    }

    public function test_create_data()
    {
        $data = [
            'item_gift_name' => 'Testing Item',
            'item_gift_description' => 'Testing Description',
            'item_gift_point' => 25000,
            'item_gift_quantity' => 2,
            'item_gift_status' => 'A',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->access_token,
        ])->postJson('/api/id/gifts', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('item_gifts', [
            'item_gift_name' => 'Testing Item',
        ]);
    }
}
