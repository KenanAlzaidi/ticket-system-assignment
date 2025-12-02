<?php

namespace App\Http\Controllers;

use App\Factories\EloquentConnectionFactory;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketRequest;
use App\Http\Requests\ViewTicketsRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

/**
 * Class TicketController
 *
 * Manages the lifecycle of support tickets (creation, listing, viewing, updating).
 * This controller acts as the bridge between the frontend and the multi-database backend,
 * utilizing the EloquentConnectionFactory to resolve the correct data source dynamically.
 *
 * @package App\Http\Controllers
 */
class TicketController extends Controller
{
    /**
     * The factory responsible for creating Eloquent Ticket Repository.
     *
     * @var EloquentConnectionFactory
     */
    protected EloquentConnectionFactory $repositoryFactory;

    /**
     * TicketController constructor.
     *
     * @param EloquentConnectionFactory $repositoryFactory
     */
    public function __construct(EloquentConnectionFactory $repositoryFactory)
    {
        $this->repositoryFactory = $repositoryFactory;
    }

    /**
     * Display a listing of tickets from ALL department databases.
     *
     * Returns a Server-Side DataTables JSON response using an aggregated
     * SQL UNION query for maximum performance.
     *
     * @param ViewTicketsRequest $request
     * @return View|JsonResponse
     */
    public function index(ViewTicketsRequest $request): View|JsonResponse
    {
        if ($request->ajax()) {
            try {
                $departments = config('departments.connection_map');

                if (empty($departments)) {
                    throw new \InvalidArgumentException('No departments configured.');
                }

                // 1. Get an instance of the repository (connection doesn't matter for the Union generator)
                // We use the factory just to get a valid repo instance
                $firstDepartment = array_key_first($departments);
                $repo = $this->repositoryFactory->make((string)$firstDepartment);

                // 2. Get the Optimized Query Builder (either Single DB or Union Subquery)
                // The request is already validated, so 'department' if present is valid.
                $query = $repo->getCrossDatabaseQuery($request->validated('department'));

                // 3. Pass to DataTables
                return DataTables::of($query)
                    ->filter(function ($query) use ($request) {
                        // Status Filter
                        if ($request->filled('status')) {
                            $query->where('status', $request->validated('status'));
                        }

                        // Global Search
                        if ($request->filled('search.value')) {
                            $search = $request->input('search.value'); // DataTables sends this as nested array/string usually, validated as string
                            $query->where(function ($q) use ($search) {
                                $q->where('subject', 'like', "%{$search}%")
                                  ->orWhere('customer_name', 'like', "%{$search}%")
                                  ->orWhere('customer_phone', 'like', "%{$search}%")
                                  ->orWhere('customer_email', 'like', "%{$search}%");
                            });
                        }
                    })
                    ->addColumn('action', function ($row) {
                        // $row is a stdClass object here because we are using DB::query() / toBase()
                        return '<a href="'.route('admin.tickets.show', ['department' => $row->department, 'id' => $row->id]).'" class="text-blue-600 hover:text-blue-900 font-medium text-sm transition-colors hover:underline">View</a>';
                    })
                    ->editColumn('created_at', function ($row) {
                        return $row->created_at ? \Carbon\Carbon::parse($row->created_at)->format('M d, Y H:i') : 'N/A';
                    })
                    ->editColumn('status', function ($row) {
                        $statusClasses = match($row->status) {
                            'new' => 'bg-green-50 text-green-700 ring-green-600/20',
                            'noted' => 'bg-blue-50 text-blue-700 ring-blue-700/10',
                            'closed' => 'bg-gray-50 text-gray-600 ring-gray-500/10',
                            default => 'bg-gray-50 text-gray-600 ring-gray-500/10'
                        };
                        return '<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset '.$statusClasses.'">'.ucfirst($row->status).'</span>';
                    })
                    ->editColumn('department', function ($row) {
                        return '<span class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">'.$row->department.'</span>';
                    })
                    ->rawColumns(['action', 'status', 'department'])
                    ->make(true);
            } catch (\InvalidArgumentException $e) {
                return response()->json(['error' => 'System configuration error: ' . $e->getMessage()], 500);
            } catch (\Exception $e) {
                return response()->json(['error' => 'An error occurred while fetching tickets.'], 500);
            }
        }

        return view('admin.index');
    }

    /**
     * Show the public form for submitting a new support ticket.
     *
     * @return View
     */
    public function create(): View
    {
        $departments = config('departments.connection_map');
        return view('tickets.create', compact('departments'));
    }

    /**
     * Store a newly created ticket in the appropriate department database.
     *
     * @param StoreTicketRequest $request
     * @return RedirectResponse
     */
    public function store(StoreTicketRequest $request): RedirectResponse
    {
        // Validation is handled by StoreTicketRequest
        $validated = $request->validated();

        try {
            // 1. Resolve the correct repository/connection based on user input
            $repository = $this->repositoryFactory->make($validated['department']);

            // 2. Persist the ticket
            $repository->create([
                'customer_name' => $validated['customer_name'],
                'customer_email' => $validated['customer_email'],
                'customer_phone' => $validated['customer_phone'],
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'status' => 'new',
            ]);

            return redirect()->route('tickets.create')->with('success', 'Ticket submitted successfully!');
        } catch (\InvalidArgumentException $e) {
            // Should be caught by validation, but safe fallback
            return back()->withInput()->with('error', 'Invalid department selected.');
        } catch (\Exception $e) {
            // Generic fallback for DB connection errors
            return back()->withInput()->with('error', 'Failed to submit ticket. Please try again later.');
        }
    }

    /**
     * Display a specific ticket.
     *
     * @param string $department The department name (used to route to correct DB)
     * @param int $id The ticket ID
     * @return View
     */
    public function show(string $department, int $id): View
    {
        try {
            $repo = $this->repositoryFactory->make($department);
            $ticket = $repo->findById($id);

            if (!$ticket) {
                abort(404, 'Ticket not found.');
            }

            $ticket->department = $department;
            return view('admin.show', compact('ticket'));
        } catch (\InvalidArgumentException $e) {
            abort(404, 'Invalid department.');
        } catch (\Exception $e) {
            // If department doesn't exist or connection fails
            abort(404, 'Department database unreachable or invalid.');
        }
    }

    /**
     * Update a ticket with an admin note and change status.
     *
     * @param UpdateTicketRequest $request
     * @param string $department
     * @param int $id
     * @return RedirectResponse
     */
    public function update(UpdateTicketRequest $request, string $department, int $id): RedirectResponse
    {
        // Validation handled by UpdateTicketRequest

        try {
            $repo = $this->repositoryFactory->make($department);

            $repo->updateWithNote($id, $request->validated('note'));

            return redirect()->route('admin.tickets.show', ['department' => $department, 'id' => $id])
                             ->with('success', 'Ticket updated successfully');
        } catch (ModelNotFoundException $e) {
            abort(404, 'Ticket not found.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', 'Invalid department.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to update ticket: ' . $e->getMessage());
        }
    }
}
