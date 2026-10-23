<?php

namespace App\Repositories;

use App\Interfaces\DevelopmentApplicantRepositoryInterface;
use App\Models\Development;
use App\Models\DevelopmentApplicant;
use Exception;
use Illuminate\Support\Facades\DB;

class DevelopmentApplicantRepository implements DevelopmentApplicantRepositoryInterface
{
    public function getAll(
        ?string $search, 
        ?int $limit, 
        bool $execute
    ){
        $query = DevelopmentApplicant::where(function ($query) use ($search) {
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
        $query = DevelopmentApplicant::where('id', $id);
        
        return $query->first();   
    }

    public function create(
        array $data
    ){
        DB::beginTransaction();

        try{

            $developmentApplicant = new DevelopmentApplicant();
            $developmentApplicant->development_id = $data['development_id'];
            $developmentApplicant->user_id = $data['user_id'];

            if (isset($data['status'])) {
                $developmentApplicant->status = $data['status'];
            } 

            $developmentApplicant->save();
            DB::commit();

            return $developmentApplicant;
 
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
            $developmentApplicant = DevelopmentApplicant::find($id);
            $developmentApplicant->development_id = $data['development_id'];
            $developmentApplicant->user_id = $data['user_id'];

            if (isset($data['status'])) {
                $developmentApplicant->status = $data['status'];
            }

            $developmentApplicant->save();
            DB::commit();

            return $developmentApplicant;
 
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
            $developmentApplicant = DevelopmentApplicant::find($id);
            $developmentApplicant->delete();

            DB::commit();
            return $developmentApplicant;
        } catch(\Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }
    }
}
     