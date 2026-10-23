<?php

namespace App\Repositories;

use App\Interfaces\FamilyMemberRepositoryInterface;
use App\Models\FamilyMember;
use App\Models\HeadOfFamily;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FamilyMemberRepository implements FamilyMemberRepositoryInterface
{
    public function getAll(
        ?string $search, 
        ?int $limit, 
        bool $execute
    ){
        $query = FamilyMember::with(['user', 'headOfFamily'])->where(function ($query) use ($search) {
            if ($search){
                // if $search --> will search by defined column in  model
                $query->search($search);
            }
        });

        if(auth()->user()->hasRole('head-of-family')) {
            $query->where('head_of_family_id', auth()->user()->headOfFamily->id);
        }
        
        if ($limit) {
            // take is fetch data by limit
            $query->take($limit);
        }

        if ($execute) {
            // execute the query and return the results
            return $query->get();
        } 

        return $query;
    }

    public function getAllPaginated(
        ?string $search,
        ?int $rowPerPage
    ) {
        // Implementation of the getAllPaginated method
        $query = $this->getAll(
            $search, 
            $rowPerPage, 
            false
        );

        return $query->paginate($rowPerPage);
    }

    public function getById(
        string $id
    ){
        $query = FamilyMember::where('id', $id)->with('headOfFamily');
        
        return $query->first();   
    }

    public function create(
        array $data
    ) {
        DB::beginTransaction();

        try{
            $userRepository = new UserRepository;

            $user = $userRepository->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password']

            ]);

            $familyMember = new FamilyMember;
            $familyMember->user_id = $user->id;
            $familyMember->head_of_family_id = $data['head_of_family_id'];
            $familyMember->profile_picture = $data['profile_picture']->store('assets/family-members', 'public');
            $familyMember->identity_number = $data['identity_number'];
            $familyMember->gender = $data['gender'];
            $familyMember->date_of_birth = $data['date_of_birth'];
            $familyMember->phone_number = $data['phone_number'];
            $familyMember->occupation = $data['occupation'];
            $familyMember->marital_status = $data['marital_status'];
            $familyMember->relation = $data['relation'];

            $familyMember->save();
            DB::commit();

            return $familyMember;
 
        } catch (\Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }
    }

    public function update(
        string $id,
        array $data
    ){
        DB::beginTransaction();

        try{
            $familyMember = FamilyMember::find($id);

            if(isset($data['profile_picture'])){
                if ($familyMember->profile_picture && Storage::disk('public')->exists($familyMember->profile_picture)) {
                    Storage::disk('public')->delete($familyMember->profile_picture);
                }
                $familyMember->profile_picture = $data['profile_picture']->store('assets/family-members', 'public');   
            }

            $familyMember->identity_number = $data['identity_number'];
            $familyMember->gender = $data['gender'];
            $familyMember->date_of_birth = $data['date_of_birth'];
            $familyMember->phone_number = $data['phone_number'];
            $familyMember->occupation = $data['occupation'];
            $familyMember->marital_status = $data['marital_status'];

            if(isset($data['relation'])) {
                $familyMember->relation = $data['relation'];
            }

            $familyMember->save();

            $userRepository = new UserRepository; 

            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            if(!empty($data['password'])) {
                $userData['password'] = $data['password'];
            }

            $userRepository->update($familyMember->user_id, $userData);

            DB::commit();
            return $familyMember;

        } catch(\Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }
    }

    public function delete(
        string $id
    ){
        DB::beginTransaction();
        
        try{
            $familyMember = FamilyMember::find($id);
            if ($familyMember->profile_picture && Storage::disk('public')->exists($familyMember->profile_picture)) {
                Storage::disk('public')->delete($familyMember->profile_picture);
            }
            $familyMember->delete();

            DB::commit();
            return $familyMember;
        } catch(\Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }
    }
}
