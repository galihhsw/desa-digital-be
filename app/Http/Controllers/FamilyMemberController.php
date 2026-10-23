<?php

namespace App\Http\Controllers;

use App\Interfaces\FamilyMemberRepositoryInterface;
use Illuminate\Http\Request;
use App\Helpers\ResponseHelper;
use App\Http\Requests\FamilyMemberStoreRequest;
use App\Http\Requests\FamilyMemberUpdateRequest;
use App\Http\Resources\FamilyMemberResource;
use App\Http\Resources\PaginateResource;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;

class FamilyMemberController extends Controller implements HasMiddleware
{
    private FamilyMemberRepositoryInterface $familyMemberRepository;

    public function __construct(FamilyMemberRepositoryInterface $familyMemberRepository) {
        $this->familyMemberRepository = $familyMemberRepository;
    }

    public static function middleware()
    {
        return [
            new Middleware(PermissionMiddleware::using(['family-member-list', 'family-member-create', 'family-member-edit', 'family-member-delete']), only : ['index', 'getAllPaginated', 'show']),
            new Middleware(PermissionMiddleware::using(['family-member-create']), only : ['store']),
            new Middleware(PermissionMiddleware::using(['family-member-edit']), only : ['update']),
            new Middleware(PermissionMiddleware::using(['family-member-delete']), only : ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $familyMembers = $this->familyMemberRepository->getAll(
                $request->search,
                $request->limit,
                true
            );

            return ResponseHelper::jsonResponse(true, 'Family Member Data retrieve successfully!', FamilyMemberResource::collection($familyMembers), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    public function getAllPaginated(Request $request) 
    {
        $request = $request->validate([
            'search' => 'nullable|string',
            'row_per_page' => 'required|integer'
        ]);

        try {
            $familyMembers = $this->familyMemberRepository->getAllPaginated(
                $request['search'] ?? null,
                $request['row_per_page'],
            );

            return ResponseHelper::jsonResponse(true, 'Family Member Data retrieve successfully!', PaginateResource::make($familyMembers, FamilyMemberResource::class), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, 'Family Member Data not found!', null, 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(FamilyMemberStoreRequest $request)
    {
        $request = $request->validated();

        try {
            $familyMember = $this->familyMemberRepository->create($request);

            return ResponseHelper::jsonResponse(true, 'Family Member data added successfully', new FamilyMemberResource($familyMember), 201);
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
            $familyMember = $this->familyMemberRepository->getById(
                $id
            );

            if (!$familyMember) {
                return ResponseHelper::jsonResponse(false, 'Family Member data not found', null, 404);
            }

            return ResponseHelper::jsonResponse(true, 'Family Member detail retrieve successfully!', new FamilyMemberResource($familyMember), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(FamilyMemberUpdateRequest $request, string $id)
    {
        $request = $request->validated();

        try {
            $familyMember = $this->familyMemberRepository->getById(
                $id
            );

            if (!$familyMember) {
                return ResponseHelper::jsonResponse(false, 'Family Member not found', null, 404);
            }

            $familyMember = $this->familyMemberRepository->update(
                $id,
                $request
            );

            return ResponseHelper::jsonResponse(true, 'Family Member data updated successfully', new FamilyMemberResource($familyMember), 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $familyMember = $this->familyMemberRepository->getById(
                $id
            );

            if (!$familyMember) {
                return ResponseHelper::jsonResponse(false, 'Family Member not found', null, 404);
            }

            $familyMember = $this->familyMemberRepository->delete(
                $id,
            );

            return ResponseHelper::jsonResponse(true, 'Family Member data deleted successfully', null, 200);
        } catch (\Exception $e) {
            return ResponseHelper::jsonResponse(false, $e->getMessage(), null, 500);
        }
    }
}
