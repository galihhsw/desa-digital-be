<?php

namespace App\repositories;

use App\Interfaces\DevelopmentRepositoryInterface;
use App\Models\Development;
use App\Models\FamilyMember;
use Exception;
use Illuminate\Support\Facades\DB;

class DevelopmentRepository implements DevelopmentRepositoryInterface
{
    public function getAll(
        ?string $search,
        ?string $status,
        ?int $limit,
        bool $execute
    ){
        $query = Development::where(function ($query) use ($search) {
            if ($search){
                // if $search --> will search by defined column in  model
                $query->search($search);
            }
        })->with('developmentApplicants');

        if ($status === 'my-applications') {
            $query->whereHas('developmentApplicants', function ($query) {
                $members = FamilyMember::where('head_of_family_id', auth()->user()->headOfFamily->id)->pluck('user_id')->toArray();
                $members[] = auth()->user()->id;

                $query->whereIn('user_id', $members);
            });
        }

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
        ?string $status,
        ?int $rowPerPage
    ) {
        // Implementation of the getAllPaginated method
        $query = $this->getAll(
            $search, 
            $status,
            $rowPerPage, 
            false
        );

        return $query->paginate($rowPerPage);
    }

    public function getById(
        string $id
    ){
        $query = Development::where('id', $id)->with('developmentApplicants');
        
        return $query->first();   
    }

    public function create(
        array $data
    ) {
        DB::beginTransaction();

        try{

            $development = new Development();
            $development->thumbnail = $data['thumbnail']->store('assets/development', 'public');
            $development->name = $data['name'];
            $development->description = $data['description'];
            $development->person_in_charge = $data['person_in_charge'];
            $development->start_date = $data['start_date'];
            $development->end_date = $data['end_date'];
            $development->amount = $data['amount'];
            $development->status = $data['status'];

            $development->save();
            DB::commit();

            return $development;
 
        } catch (\Exception $e) {
            DB::rollBack(); 

            throw new Exception($e->getMessage());
        }
    }

    public function update(
        string $id,
        array $data
    ) {
        DB::beginTransaction();

        try{
            $development = Development::find($id);

            if(isset($data['thumbnail'])) {
                $development->thumbnail = $data['thumbnail']->store('assets/developments', 'public');
            }
        
            $development->name = $data['name'];
            $development->description = $data['description'];
            $development->amount = $data['amount'];
            $development->start_date = $data['start_date'];
            $development->end_date = $data['end_date'];
            $development->status = $data['status'];

            

            $development->save();
            DB::commit();

            return $development;
 
        } catch (\Exception $e) {
            DB::rollBack(); 

            throw new Exception($e->getMessage());
        }
    }

    public function delete(
        string $id
    ){
        DB::beginTransaction();
        
        try{
            $development = Development::find($id);
            $development->delete();

            DB::commit();
            return $development;
        } catch(\Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }
    }
}