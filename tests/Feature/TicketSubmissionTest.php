<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class TicketSubmissionTest extends TestCase
{
    /**
     * We need to use real DB interactions because our logic depends on actual connections.
     * However, we only want to touch the 'technical_issues_department' for testing.
     */
    
    protected function setUp(): void
    {
        parent::setUp();
        // Ensure the table exists in the test target DB
        // Note: This requires the 'technical_issues_department' to exist in MySQL
        if (Schema::connection('technical_issues_department')->hasTable('tickets')) {
            DB::connection('technical_issues_department')->table('tickets')->truncate();
        }
    }

    public function test_user_can_view_ticket_creation_form()
    {
        $response = $this->get(route('tickets.create'));

        $response->assertStatus(200);
        $response->assertSee('Submit a Support Ticket');
        $response->assertSee('Issue Type (Department)');
    }

    public function test_user_can_submit_ticket_successfully()
    {
        $data = [
            'customer_name' => 'John Test',
            'customer_email' => 'john@test.com',
            'customer_phone' => '1234567890',
            'department' => 'Technical Issues', // Maps to 'technical_issues_department'
            'subject' => 'My internet is down',
            'message' => 'Please help me fix it.',
        ];

        $response = $this->post(route('tickets.store'), $data);

        // 1. Assert Redirect
        $response->assertRedirect(route('tickets.create'));
        $response->assertSessionHas('success', 'Ticket submitted successfully!');

        // 2. Assert Database Has Record (in the CORRECT database)
        $this->assertDatabaseHas('tickets', [
            'customer_email' => 'john@test.com',
            'subject' => 'My internet is down',
            'status' => 'new',
        ], 'technical_issues_department');

        // 3. Assert Database Does NOT Have Record (in the DEFAULT or WRONG database)
        // Assuming 'mysql' is default
        $this->assertDatabaseMissing('tickets', [
            'customer_email' => 'john@test.com',
        ], 'mysql'); 
    }

    public function test_submission_fails_validation_with_missing_fields()
    {
        $response = $this->post(route('tickets.store'), []);

        $response->assertSessionHasErrors(['customer_name', 'customer_email', 'department', 'subject', 'message']);
    }

    public function test_submission_fails_with_invalid_email()
    {
        $response = $this->post(route('tickets.store'), [
            'customer_name' => 'Test',
            'customer_email' => 'not-an-email',
            'department' => 'Technical Issues',
            'subject' => 'Test',
            'message' => 'Test',
        ]);

        $response->assertSessionHasErrors(['customer_email']);
    }

    public function test_submission_fails_gracefully_with_invalid_department_hack()
    {
        // User tries to inspect element and change value to something not in config
        $data = [
            'customer_name' => 'Hacker',
            'customer_email' => 'hacker@test.com',
            'department' => 'Secret Department',
            'subject' => 'Hack',
            'message' => 'Hack',
        ];

        $response = $this->post(route('tickets.store'), $data);

        // Controller catches InvalidArgumentException and redirects back with error
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Invalid department selected.');
    }
}

