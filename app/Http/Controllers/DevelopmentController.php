<?php

namespace App\Http\Controllers;

use App\Helpers\ResponseHelper;
use App\Http\Requests\DevelopmentStoreRequest;
use App\Http\Requests\DevelopmentUpdateRequest;
use App\Http\Resources\DevelopmentResource;
use App\Http\Resources\PaginateResource;
use App\Interfaces\DevelopmentRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

class DevelopmentController extends Controller implements HasMiddleware
{

    private DevelopmentRepositoryInterface $developmentRepository;

    public function __construct(DevelopmentRepositoryInterface $developmentRepository)
    {
        $this->developmentRepository = $developmentRepository;
    }

    public static function middleware(): array
    {
        return [
            new Middleware(PermissionMiddleware::using(['development-list', 'development-create', 'development-edit', 'development-delete']), only : ['index', 'getAllPaginated', 'show']),
            new Middleware(PermissionMiddleware::using(['development-create']), only : ['store']),
            new Middleware(PermissionMiddleware::using(['development-edit']), only : ['update']),
            new Middleware(PermissionMiddleware::using(['development-delete']), only : ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
     public function index(Request $request)
    {
        try{
            $developments = $this->developmentRepository->getAll(
                $request->search,
                $request->limit,
                true
            );

            return ResponseHelper::jsonResponse(true, 'Development Data retrieve successfully!', DevelopmentResource::collection($developments), 200);
        } catch (\Exception $e) {
            //return ResponseHelper::jsonResponse(true, 'Head Of Family Data not found!', null, 500);
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }; 
    }

    public function getAllPaginated(Request $request) 
    {
        $request = $request->validate([
            'search' => 'nullable|string',
            'row_per_page' => 'required|integer'
        ]);

        try{
            $developments = $this->developmentRepository->getAllPaginated(
                $request['search'] ?? null,
                $request['row_per_page'],
            );

            return ResponseHelper::jsonResponse(true, 'Development Data retrieve successfully!', PaginateResource::make($developments, DevelopmentResource::class), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        };
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(DevelopmentStoreRequest $request)
    {
        $request = $request->validated();

        try{
            $development = $this->developmentRepository->create($request);

            return ResponseHelper::jsonResponse(true, 'Development data added successfully', new DevelopmentResource($development), 201);
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
            $development = $this->developmentRepository->getById($id);

            if (!$development) {
                return ResponseHelper::jsonResponse(false, 'Development data not found!', null, 404);
            }

            return ResponseHelper::jsonResponse(
                true, 
                'Development data retrieve successfully', 
                new DevelopmentResource($development), 
                200
            );
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
   public function update(DevelopmentUpdateRequest $request, string $id)
    {
        $request = $request->validated();

        try{
            $development = $this->developmentRepository->getById(
                $id
            );

            if(!$development){
                return ResponseHelper::jsonResponse(false, 'Development not found', null, 404);
            }

            $development = $this->developmentRepository->update(
                $id,
                $request
            );

            return ResponseHelper::jsonResponse(true, 'Development data updated successfully', new DevelopmentResource($development), 200);
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
            $development = $this->developmentRepository->getById(
                $id
            );

            if(!$development){
                return ResponseHelper::jsonResponse(false, 'Development not found', null, 404);
            }

            $development = $this->developmentRepository->delete(
                $id,
            );

            return ResponseHelper::jsonResponse(true, 'Development   data deleted successfully', null, 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }
}
