<?php 

namespace App\Repositories;

use App\Interfaces\HeadOfFamilyRepositoryInterface;
use App\Models\HeadOfFamily;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Override;

class HeadOfFamilyRepository implements HeadOfFamilyRepositoryInterface
{
    public function getAll(
        ?string $search, 
        ?int $limit, 
        bool $execute
    ){
        $query = HeadOfFamily::with(['user', 'familyMembers.user'])->where(function ($query) use ($search) {
            if ($search){
                // if $search --> will search by defined column in  model
                $query->search($search);
            }
        });

        $query->orderBy('created_at', 'desc');
        
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
        $query = HeadOfFamily::where('id', $id)->with(['user', 'familyMembers.user']);
        
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

            ])->assignRole('head-of-family');

            $headOfFamily = new HeadOfFamily;
            $headOfFamily->user_id = $user->id;
            $headOfFamily->profile_picture = $data['profile_picture']->store('assets/head-of-families', 'public');
            $headOfFamily->identity_number = $data['identity_number'];
            $headOfFamily->gender = $data['gender'];
            $headOfFamily->date_of_birth = $data['date_of_birth'];
            $headOfFamily->phone_number = $data['phone_number'];
            $headOfFamily->occupation = $data['occupation'];
            $headOfFamily->marital_status = $data['marital_status'];

            $headOfFamily->save();
            DB::commit();

            return $headOfFamily;
 
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
            $headOfFamily = HeadOfFamily::find($id);

            if(isset($data['profile_picture'])){
                $headOfFamily->profile_picture = $data['profile_picture']->store('assets/head-of-families', 'public');   
            }

            $headOfFamily->identity_number = $data['identity_number'];
            $headOfFamily->gender = $data['gender'];
            $headOfFamily->date_of_birth = $data['date_of_birth'];
            $headOfFamily->phone_number = $data['phone_number'];
            $headOfFamily->occupation = $data['occupation'];
            $headOfFamily->marital_status = $data['marital_status'];

            $headOfFamily->save();

            $userRepository = new UserRepository; 

            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            if(!empty($data['password'])) {
                $userData['password'] = $data['password'];
            }

            $userRepository->update($headOfFamily->user_id, $userData);

            DB::commit();
            return $headOfFamily;

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
            $headOfFamily = HeadOfFamily::find($id);
            $headOfFamily->delete();

            DB::commit();
            return $headOfFamily;
        } catch(\Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }
    }
}