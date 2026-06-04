<?php

namespace Tests\Feature;

use App\Models\Embassy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class EmbassyTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        Role::firstOrCreate(['name' => 'admin']);
        $u = User::factory()->create();
        $u->assignRole('admin');

        return $u;
    }

    private function embassy(array $attrs = []): Embassy
    {
        return Embassy::create(array_merge([
            'name' => 'ЭСЯ', 'kind' => 'embassy', 'country' => 'Герман', 'city' => 'Берлин',
            'is_active' => true,
        ], $attrs));
    }

    public function test_public_sees_active_only(): void
    {
        $this->embassy(['name' => 'ACTIVE-EMB']);
        $this->embassy(['name' => 'HIDDEN-EMB', 'is_active' => false]);

        $res = $this->get('/embassy');
        $res->assertOk();
        $res->assertSee('ACTIVE-EMB');
        $res->assertDontSee('HIDDEN-EMB');
    }

    public function test_admin_can_create_update_delete(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/embassies', [
            'name' => 'Шинэ ЭСЯ', 'kind' => 'embassy', 'country' => 'Франц', 'city' => 'Парис',
        ])->assertRedirect();
        $this->assertDatabaseHas('embassies', ['name' => 'Шинэ ЭСЯ', 'country' => 'Франц']);

        $e = Embassy::first();
        $this->actingAs($admin)->put("/admin/embassies/{$e->id}", [
            'name' => 'Зассан', 'kind' => 'consulate', 'country' => 'Франц',
        ])->assertRedirect();
        $this->assertSame('Зассан', $e->fresh()->name);

        $this->actingAs($admin)->delete("/admin/embassies/{$e->id}")->assertRedirect();
        $this->assertDatabaseMissing('embassies', ['id' => $e->id]);
    }

    public function test_invalid_kind_rejected(): void
    {
        $this->actingAs($this->admin())->post('/admin/embassies', [
            'name' => 'X', 'kind' => 'bogus', 'country' => 'Герман',
        ])->assertSessionHasErrors('kind');
    }

    public function test_non_admin_cannot_manage(): void
    {
        $this->actingAs(User::factory()->create())->get('/admin/embassies')->assertForbidden();
    }
}
