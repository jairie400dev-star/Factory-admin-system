<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected array $admin = ['email' => 'admin@admin.com', 'password' => 'password'];

    protected function setUp(): void
    {
        parent::setUp();

        // Seed roles + admin user as in DatabaseSeeder
        $this->artisan('db:seed')->run();
    }

    public function test_admin_login_and_dashboard_access()
    {
        $resp = $this->postJson('/api/auth/admin/login', $this->admin);

        $resp->assertStatus(200)->assertJsonStructure(['status', 'data' => ['access_token', 'expires_at']])->assertJson(['status' => true]);

        $token = $resp->json('data.access_token');

        $dashboard = $this->withHeader('Authorization', 'Bearer ' . $token)->getJson('/api/dashboard');

        $dashboard->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_logout_revokes_token()
    {
        $resp = $this->postJson('/api/auth/admin/login', $this->admin);
        $token = $resp->json('data.access_token');

        $logout = $this->withHeader('Authorization', 'Bearer ' . $token)->postJson('/api/auth/logout');
        $logout->assertStatus(200)->assertJson(['status' => true, 'data' => ['message' => 'Logged out successfully.']]);

        // Verify the token record was removed from storage
        $user = \App\Models\User::where('email', $this->admin['email'])->first();
        $this->assertNotNull($user);
        $this->assertDatabaseMissing('personal_access_tokens', ['tokenable_id' => $user->id]);
    }

    public function test_factories_crud()
    {
        $resp = $this->postJson('/api/auth/admin/login', $this->admin);
        $token = $resp->json('data.access_token');

        $headers = ['Authorization' => 'Bearer ' . $token];

        $create = $this->withHeaders($headers)->postJson('/api/factories', [
            'factory_name' => 'Acme Plant',
            'location' => 'Springfield',
            'email' => 'info@acme.test',
            'website' => 'https://acme.test',
        ]);

        $create->assertStatus(201)->assertJson(['success' => true]);
        $id = $create->json('data.id');

        $index = $this->withHeaders($headers)->getJson('/api/factories');
        $index->assertStatus(200)->assertJson(['success' => true]);

        $show = $this->withHeaders($headers)->getJson('/api/factories/' . $id);
        $show->assertStatus(200)->assertJson(['success' => true]);

        $update = $this->withHeaders($headers)->putJson('/api/factories/' . $id, [
            'factory_name' => 'Acme Plant Updated',
            'location' => 'Shelbyville',
        ]);
        $update->assertStatus(200)->assertJson(['success' => true, 'data' => ['factory_name' => 'Acme Plant Updated']]);

        $delete = $this->withHeaders($headers)->deleteJson('/api/factories/' . $id);
        $delete->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_employees_crud()
    {
        $resp = $this->postJson('/api/auth/admin/login', $this->admin);
        $token = $resp->json('data.access_token');
        $headers = ['Authorization' => 'Bearer ' . $token];

        // create factory for employee
        $factory = $this->withHeaders($headers)->postJson('/api/factories', [
            'factory_name' => 'Worker Co',
            'location' => 'Metro City',
        ])->json('data');

        $factoryId = $factory['id'];

        $create = $this->withHeaders($headers)->postJson('/api/employees', [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'factory_id' => $factoryId,
            'email' => 'john.doe@test',
        ]);

        $create->assertStatus(201)->assertJson(['success' => true]);
        $empId = $create->json('data.id');

        $index = $this->withHeaders($headers)->getJson('/api/employees');
        $index->assertStatus(200)->assertJson(['success' => true]);

        $show = $this->withHeaders($headers)->getJson('/api/employees/' . $empId);
        $show->assertStatus(200)->assertJson(['success' => true]);

        $update = $this->withHeaders($headers)->putJson('/api/employees/' . $empId, [
            'firstname' => 'Jane',
            'lastname' => 'Doe',
            'factory_id' => $factoryId,
        ]);
        $update->assertStatus(200)->assertJson(['success' => true, 'data' => ['firstname' => 'Jane']]);

        $delete = $this->withHeaders($headers)->deleteJson('/api/employees/' . $empId);
        $delete->assertStatus(200)->assertJson(['success' => true]);
    }

    public function test_logs_endpoint()
    {
        $resp = $this->postJson('/api/auth/admin/login', $this->admin);
        $token = $resp->json('data.access_token');
        $headers = ['Authorization' => 'Bearer ' . $token];

        $logs = $this->withHeaders($headers)->getJson('/api/logs');
        $logs->assertStatus(200)->assertJson(['success' => true]);
    }
}
