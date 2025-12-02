<?php

namespace Tests\Unit;

use App\Factories\EloquentConnectionFactory;
use App\Repositories\EloquentTicketRepository;
use App\Models\Ticket;
use Tests\TestCase;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Mockery;

class EloquentConnectionFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock the configuration for departments
        Config::set('departments.connection_map', [
            'Test Dept' => 'test_connection',
            'Another Dept' => 'another_connection',
        ]);
    }

    public function test_it_sets_correct_connection_on_repository_model()
    {
        // 1. Mock the Model
        $mockModel = Mockery::mock(Ticket::class);
        $mockModel->shouldReceive('setDynamicConnection')
            ->once()
            ->with('test_connection')
            ->andReturnSelf();

        // 2. Mock the Repository
        $mockRepo = Mockery::mock(EloquentTicketRepository::class);
        $mockRepo->shouldReceive('getModel')
            ->once()
            ->andReturn($mockModel);

        // 3. Instantiate Factory
        $factory = new EloquentConnectionFactory($mockRepo);

        // 4. Execute
        $resultRepo = $factory->make('Test Dept');

        // 5. Assert
        $this->assertSame($mockRepo, $resultRepo);
    }

    public function test_it_throws_exception_for_invalid_department()
    {
        // 1. Mock Repo (should not be called)
        $mockRepo = Mockery::mock(EloquentTicketRepository::class);

        // 2. Instantiate Factory
        $factory = new EloquentConnectionFactory($mockRepo);

        // 3. Assert Exception
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Invalid ticket type: 'Invalid Dept'. No corresponding database connection found.");

        // 4. Execute
        $factory->make('Invalid Dept');
    }
}

