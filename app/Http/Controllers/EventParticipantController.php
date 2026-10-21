<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\EventParticipantStoreRequest;
use App\Http\Requests\EventParticipantUpdateRequest;
use App\Http\Resources\EventParticipantResource;
use App\Http\Resources\PaginateResource;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

class EventParticipantController extends Controller implements HasMiddleware
{

    private $eventParticipantRepository;

    public function __construct(
        \App\Interfaces\EventParticipantRepositoryInterface $eventParticipantRepository
    ) {
        $this->eventParticipantRepository = $eventParticipantRepository;
    }

    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(['event-participant-list', 'event-participant-create', 'event-participant-edit', 'event-participant-delete']), only : ['index', 'getAllPaginated', 'show']),
            new Middleware(PermissionMiddleware::using(['event-participant-create']), only : ['store']),
            new Middleware(PermissionMiddleware::using(['event-participant-edit']), only : ['update']),
            new Middleware(PermissionMiddleware::using(['event-participant-delete']), only : ['destroy']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try{
            $eventParticipants = $this->eventParticipantRepository->getAll(
                $request->search,
                $request->status,
                $request->limit,
                true
            );

            return ResponseHelper::jsonResponse(true, 'Events Participant Data retrieve successfully!', EventParticipantResource::collection($eventParticipants), 200);
        } catch (\Exception $e) {
            //return ResponseHelper::jsonResponse(true, 'Head Of Family Data not found!', null, 500);
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        };
    }

    public function getAllPaginated(Request $request) 
    {
        $request = $request->validate([
            'search' => 'nullable|string',
            'status' => 'nullable|string',
            'row_per_page' => 'required|integer'
        ]);

        try{
            $eventParticipants = $this->eventParticipantRepository->getAllPaginated(
                $request['search'] ?? null,
                $request['status'] ?? null,
                $request['row_per_page'],
            );

            return ResponseHelper::jsonResponse(true, 'Event Participant Data retrieve successfully!', PaginateResource::make($eventParticipants, EventParticipantResource::class), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        };
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventParticipantStoreRequest $request)
    {
        $request = $request->validated();

        try{
            $eventParticipant = $this->eventParticipantRepository->create($request);

            return ResponseHelper::jsonResponse(true, 'Event Participant data added successfully', new EventParticipantResource($eventParticipant), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $eventParticipant = $this->eventParticipantRepository->getById($id);

            if (!$eventParticipant) {
                return ResponseHelper::jsonResponse(false, 'Event data not found!', null, 404);
            }

            return ResponseHelper::jsonResponse(
                true, 
                'Event data retrieve successfully', 
                new EventParticipantResource($eventParticipant), 
                200
            );
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EventParticipantUpdateRequest $request, string $id)
    {
         $request = $request->validated();

        try{
            $eventParticipant = $this->eventParticipantRepository->getById(
                $id
            );

            if(!$eventParticipant){
                return ResponseHelper::jsonResponse(false, 'Event Participant not found', null, 404);
            }

            $eventParticipant = $this->eventParticipantRepository->update(
                $id,
                $request
            );

            return ResponseHelper::jsonResponse(true, 'Event Participant data updated successfully', new EventParticipantResource($eventParticipant), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try{
            $eventParticipant = $this->eventParticipantRepository->getById(
                $id
            );

            if(!$eventParticipant){
                return ResponseHelper::jsonResponse(false, 'Event Participant not found', null, 404);
            }

            $eventParticipant = $this->eventParticipantRepository->delete(
                $id,
            );

            return ResponseHelper::jsonResponse(true, 'Event Participant data deleted successfully', null, 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }
}
