<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase; // For the User model/session

    protected function setUp(): void
    {
        parent::setUp();
        
        // Authenticate as Admin
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user);

        // Clean department DB
        DB::connection('technical_issues_department')->table('tickets')->truncate();
    }

    public function test_admin_can_see_tickets_from_departments()
    {
        // 1. Seed a ticket directly into the Technical Issues DB
        Ticket::on('technical_issues_department')->create([
            'customer_name' => 'Dashboard Test',
            'customer_email' => 'dashboard@test.com',
            'subject' => 'Visible in Dashboard',
            'message' => 'Content',
            'status' => 'new',
        ]);

        // 2. Visit Dashboard
        $response = $this->get(route('admin.tickets.index'));

        // 3. Assert
        $response->assertStatus(200);
        $response->assertSee('Visible in Dashboard');
        $response->assertSee('Dashboard Test');
        $response->assertSee('Technical Issues'); // Check if department name is tagged
    }

    public function test_admin_can_view_single_ticket_details()
    {
        // 1. Create Ticket
        $ticket = Ticket::on('technical_issues_department')->create([
            'customer_name' => 'Detail Test',
            'customer_email' => 'detail@test.com',
            'subject' => 'Detail Subject',
            'message' => 'Detail Content',
            'status' => 'new',
        ]);

        // 2. Visit Show Page
        // URL pattern: /admin/tickets/{department}/{id}
        $response = $this->get(route('admin.tickets.show', [
            'department' => 'Technical Issues', 
            'id' => $ticket->id
        ]));

        // 3. Assert
        $response->assertStatus(200);
        $response->assertSee('Detail Subject');
        $response->assertSee('detail@test.com');
        $response->assertSee('Detail Content');
    }

    public function test_viewing_non_existent_ticket_returns_404()
    {
        $response = $this->get(route('admin.tickets.show', [
            'department' => 'Technical Issues',
            'id' => 99999
        ]));

        $response->assertStatus(404);
    }
}

