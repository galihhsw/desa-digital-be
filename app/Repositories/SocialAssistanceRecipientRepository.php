<?php

namespace App\Repositories;

use App\Interfaces\SocialAssistanceRecipientRepositoryInterface;
use App\Models\SocialAssistanceRecipient;
use Illuminate\Support\Facades\DB;
use Exception;

class SocialAssistanceRecipientRepository implements SocialAssistanceRecipientRepositoryInterface
{
    public function getAll(
        ?string $search, 
        ?int $limit, 
        bool $execute
    ){
        $query = SocialAssistanceRecipient::with(['socialAssistance', 'headOfFamily.user'])->where(function ($query) use ($search) {
            if ($search){
                // if $search --> will search by defined column in  model
                $query->search($search);
            }
        });

        $query->orderBy('created_at', 'desc');

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
        $query = SocialAssistanceRecipient::where('id', $id)->with(['socialAssistance', 'headOfFamily.user']);
        
        return $query->first();   
    }

    public function create(
        array $data
    ) {
        DB::beginTransaction();

        try{

            $socialAssistanceRecipient = new SocialAssistanceRecipient;
            $socialAssistanceRecipient->social_assistance_id = $data['social_assistance_id'];
            $socialAssistanceRecipient->head_of_family_id = $data['head_of_family_id'];
            $socialAssistanceRecipient->amount = $data['amount'];
            $socialAssistanceRecipient->reason = $data['reason'];
            $socialAssistanceRecipient->bank = $data['bank'];
            $socialAssistanceRecipient->account_number = $data['account_number'];

            if(isset($data['proof'])) {
                if ($data['proof'] instanceof \Illuminate\Http\UploadedFile) {
                    $socialAssistanceRecipient->proof = $data['proof']->store('assets/social-assistance-recipient', 'public');
                } else {
                    $socialAssistanceRecipient->proof = $data['proof'];
                }
            }

            if(isset($data['status'])) {
                $socialAssistanceRecipient->status = $data['status'];
            }

            $socialAssistanceRecipient->save();
            DB::commit();

            return $socialAssistanceRecipient;
 
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

            $socialAssistanceRecipient = SocialAssistanceRecipient::find($id);
            $socialAssistanceRecipient->social_assistance_id = $data['social_assistance_id'];
            $socialAssistanceRecipient->head_of_family_id = $data['head_of_family_id'];
            $socialAssistanceRecipient->amount = $data['amount'];
            $socialAssistanceRecipient->reason = $data['reason'];
            $socialAssistanceRecipient->bank = $data['bank'];
            $socialAssistanceRecipient->account_number = $data['account_number'];

            if(isset($data['proof'])) {
                if ($data['proof'] instanceof \Illuminate\Http\UploadedFile) {
                    $socialAssistanceRecipient->proof = $data['proof']->store('assets/social-assistance-recipient', 'public');
                } else {
                    $socialAssistanceRecipient->proof = $data['proof'];
                }
            }

            if(isset($data['status'])) {
                $socialAssistanceRecipient->status = $data['status'];
            }

            $socialAssistanceRecipient->save();
            DB::commit();

            return $socialAssistanceRecipient;
 
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
            $socialAssistanceRecipient = SocialAssistanceRecipient::find($id);
            $socialAssistanceRecipient->delete();

            DB::commit();
            return $socialAssistanceRecipient;
        } catch(\Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }
    }
}