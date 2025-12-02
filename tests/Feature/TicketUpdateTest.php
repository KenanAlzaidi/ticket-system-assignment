<?php

namespace Tests\Feature;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class TicketUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        DB::connection('technical_issues_department')->table('tickets')->truncate();
    }

    public function test_admin_can_add_note_and_update_status()
    {
        // 1. Create Ticket
        $ticket = Ticket::on('technical_issues_department')->create([
            'customer_name' => 'Note Test',
            'customer_email' => 'note@test.com',
            'subject' => 'Note Subject',
            'message' => 'Original Message',
            'status' => 'new',
        ]);

        // 2. Submit Update
        $response = $this->put(route('admin.tickets.update', [
            'department' => 'Technical Issues',
            'id' => $ticket->id
        ]), [
            'note' => 'This is an admin response.',
        ]);

        // 3. Assert Redirect & Session
        $response->assertRedirect();
        $response->assertSessionHas('success', 'Ticket updated successfully');

        // 4. Verify Database Update
        $updatedTicket = Ticket::on('technical_issues_department')->find($ticket->id);
        
        $this->assertEquals('noted', $updatedTicket->status);
        $this->assertStringContainsString('Original Message', $updatedTicket->message);
        $this->assertStringContainsString('This is an admin response', $updatedTicket->message);
        $this->assertStringContainsString('Admin Note', $updatedTicket->message);
    }

    public function test_update_fails_validation_if_note_is_missing()
    {
        $ticket = Ticket::on('technical_issues_department')->create([
            'customer_name' => 'Valid Test',
            'customer_email' => 'valid@test.com',
            'subject' => 'Valid Subject',
            'message' => 'Valid Content',
            'status' => 'new',
        ]);

        $response = $this->put(route('admin.tickets.update', [
            'department' => 'Technical Issues',
            'id' => $ticket->id
        ]), [
            'note' => '', // Empty
        ]);

        $response->assertSessionHasErrors(['note']);
    }
}

