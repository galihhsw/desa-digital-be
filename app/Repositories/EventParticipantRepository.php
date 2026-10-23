<?php

namespace App\Repositories;

use App\Interfaces\EventParticipantRepositoryInterface;
use App\Models\Event;
use App\Models\EventParticipant;
use Exception;
use Illuminate\Support\Facades\DB;

class EventParticipantRepository implements EventParticipantRepositoryInterface
{
    public function getAll(
        ?string $search,
        ?string $status,
        ?int $limit, 
        bool $execute
    ){
        $query = EventParticipant::with(['event', 'headOfFamily.user'])->where(function ($query) use ($search) {
            if ($search){
                // if $search --> will search by defined column in  model
                $query->search($search);
            }
        });

        if ($status) {
            $query->where('payment_status', $status);
        }

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
        $query = EventParticipant::where('id', $id)->with(['event', 'headOfFamily.user']);
        
        return $query->first();   
    }


    public function create(
        array $data
    ) {
        DB::beginTransaction();

        try{
            $event = Event::where('id', $data['event_id'])->first();

            $eventParticipant = new EventParticipant();
            $eventParticipant->event_id = $data['event_id'];
            $eventParticipant->head_of_family_id = $data['head_of_family_id'];
            $eventParticipant->quantity = $data['quantity'];
            $eventParticipant->total_price = $event->price * $data['quantity'];
            $eventParticipant->payment_status = 'pending';
            $eventParticipant->save();

            \Midtrans\Config::$serverKey = config('midtrans.serverKey');
            \Midtrans\Config::$isProduction = config('midtrans.isProduction');
            \Midtrans\Config::$isSanitized = config('midtrans.isSanitized');
            \Midtrans\Config::$is3ds = config('midtrans.is3ds');

            $params = array(
                'transaction_details' => array(
                    'order_id' => $eventParticipant->id,
                    'gross_amount' => $eventParticipant->total_price,
                ),
                'customer_details' => array(
                    'first_name' => auth()->user()->name,
                ),
            );

            $snapToken = \Midtrans\Snap::getSnapToken($params);

            $eventParticipant->snap_token = $snapToken;
            $eventParticipant->save();

            DB::commit();

            return $eventParticipant;
 
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
            $event = Event::where('id', $data['event_id'])->first();

            $eventParticipant = EventParticipant::find($id);

            $eventParticipant->event_id = $data['event_id'];
            $eventParticipant->head_of_family_id = $data['head_of_family_id'];
            
            if (isset($data['quantity'])) {
                $eventParticipant->quantity = $data['quantity'];
            } else {
                $data['quantity'] = $eventParticipant->quantity;
            }

            $eventParticipant->total_price = $event->price * $data['quantity'];
            $eventParticipant->payment_status = $data['payment_status'];

            $eventParticipant->save();
            DB::commit();

            return $eventParticipant;
 
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
            $eventParticipant = EventParticipant::find($id);
            $eventParticipant->delete();

            DB::commit();
            return $eventParticipant;
        } catch(\Exception $e) {
            DB::rollBack();

            throw new Exception($e->getMessage());
        }
    }
}