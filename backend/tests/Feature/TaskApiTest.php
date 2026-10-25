<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login with valid credentials succeeds and returns a token.
     */
    public function test_login_with_valid_credentials_returns_token(): void
    {
        $user = User::factory()->create([
            'email' => 'student@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'student@example.com',
            'password' => 'secret123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'name', 'email'],
                'token',
            ])
            ->assertJson([
                'message' => 'Login successful',
                'user' => [
                    'email' => 'student@example.com',
                ],
            ]);

        $this->assertNotEmpty($response->json('token'));
    }

    /**
     * Test login with invalid credentials fails with 401.
     */
    public function test_login_with_invalid_credentials_returns_401(): void
    {
        User::factory()->create([
            'email' => 'student@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'student@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'The provided credentials do not match our records.',
            ]);
    }

    /**
     * Test login with missing fields returns 422 validation error.
     */
    public function test_login_validation_failure_returns_422(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    /**
     * Test unauthenticated user is rejected with 401 when accessing tasks.
     */
    public function test_unauthenticated_user_cannot_access_tasks(): void
    {
        $response = $this->getJson('/api/tasks');
        $response->assertStatus(401);

        $postResponse = $this->postJson('/api/tasks', ['title' => 'Sample Task']);
        $postResponse->assertStatus(401);
    }

    /**
     * Test authenticated user can create a task successfully with 201.
     */
    public function test_authenticated_user_can_create_task(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $payload = [
            'title' => 'Selesaikan Tugas UTS',
            'description' => 'Membuat REST API dan Docker',
            'status' => 'pending',
            'due_date' => '2026-11-01',
        ];

        $response = $this->postJson('/api/tasks', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'title', 'description', 'status', 'due_date', 'created_at', 'updated_at'],
            ])
            ->assertJson([
                'message' => 'Task created successfully',
                'data' => [
                    'title' => 'Selesaikan Tugas UTS',
                    'description' => 'Membuat REST API dan Docker',
                    'status' => 'pending',
                    'due_date' => '2026-11-01',
                ],
            ]);

        $this->assertDatabaseHas('tasks', [
            'title' => 'Selesaikan Tugas UTS',
            'status' => 'pending',
        ]);
    }

    /**
     * Test creating a task with invalid input returns 422.
     */
    public function test_create_task_validation_failure_returns_422(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        // Missing title and invalid status
        $response = $this->postJson('/api/tasks', [
            'status' => 'invalid_status',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'status']);
    }

    /**
     * Test authenticated user can read list and detail of tasks.
     */
    public function test_authenticated_user_can_read_tasks(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $task = Task::factory()->create([
            'title' => 'Task Spesifik',
            'status' => 'pending',
        ]);

        // List tasks
        $listResponse = $this->getJson('/api/tasks');
        $listResponse->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'description', 'status', 'due_date', 'created_at', 'updated_at'],
                ],
            ]);

        // Detail task
        $detailResponse = $this->getJson('/api/tasks/' . $task->id);
        $detailResponse->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $task->id,
                    'title' => 'Task Spesifik',
                    'status' => 'pending',
                ],
            ]);

        // Non-existent task returns 404
        $notFoundResponse = $this->getJson('/api/tasks/99999');
        $notFoundResponse->assertStatus(404)
            ->assertJson([
                'message' => 'Task not found',
            ]);
    }

    /**
     * Test authenticated user can update a task.
     */
    public function test_authenticated_user_can_update_task(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $task = Task::factory()->create([
            'title' => 'Judul Awal',
            'status' => 'pending',
        ]);

        $updateResponse = $this->putJson('/api/tasks/' . $task->id, [
            'title' => 'Judul Diperbarui',
            'status' => 'completed',
        ]);

        $updateResponse->assertStatus(200)
            ->assertJson([
                'message' => 'Task updated successfully',
                'data' => [
                    'id' => $task->id,
                    'title' => 'Judul Diperbarui',
                    'status' => 'completed',
                ],
            ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'Judul Diperbarui',
            'status' => 'completed',
        ]);

        // Update non-existent task returns 404
        $notFoundResponse = $this->putJson('/api/tasks/99999', [
            'title' => 'Non Existent',
        ]);
        $notFoundResponse->assertStatus(404);
    }

    /**
     * Test authenticated user can delete a task.
     */
    public function test_authenticated_user_can_delete_task(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $task = Task::factory()->create([
            'title' => 'Task Akan Dihapus',
        ]);

        $deleteResponse = $this->deleteJson('/api/tasks/' . $task->id);

        $deleteResponse->assertStatus(200)
            ->assertJson([
                'message' => 'Task deleted successfully',
            ]);

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ]);

        // Delete non-existent task returns 404
        $notFoundResponse = $this->deleteJson('/api/tasks/99999');
        $notFoundResponse->assertStatus(404);
    }

    /**
     * Test logout revokes active token so it cannot be used again.
     */
    public function test_logout_revokes_token_successfully(): void
    {
        $user = User::factory()->create([
            'email' => 'logout-test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 1. Issue a personal access token for user
        $token = $user->createToken('auth_token')->plainTextToken;
        $this->assertNotEmpty($token);

        // 2. Access protected endpoint with Bearer token -> should succeed 200
        $accessResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/tasks');
        $accessResponse->assertStatus(200);

        // 3. Logout using Bearer token -> should delete active token and succeed 200
        $logoutResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/logout');
        $logoutResponse->assertStatus(200)
            ->assertJson([
                'message' => 'Logout successful',
            ]);

        // 4. Try to access protected endpoint with the revoked token -> must fail 401
        $this->app['auth']->forgetGuards();

        $subsequentResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/tasks');
        $subsequentResponse->assertStatus(401);
    }

    /**
     * Test unauthenticated access to health endpoint is rejected with 401.
     */
    public function test_unauthenticated_user_cannot_access_health_endpoint(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    /**
     * Test authenticated user can access health endpoint with 200.
     */
    public function test_authenticated_user_can_access_health_endpoint(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'database',
            ])
            ->assertJson([
                'status' => 'ok',
            ]);
    }
}
