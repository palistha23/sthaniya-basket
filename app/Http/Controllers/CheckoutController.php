<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\CollectionTimeSlot;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class CheckoutController extends Controller
{
    public function index(Request $request) {

        // If collections slots already have 20 products, deny the customer's proceeding to checkout.
        $firstCollectionSlot = CollectionTimeSlot::find(1);
        $secondCollectionSlot = CollectionTimeSlot::find(2);
        $thirdCollectionSlot = CollectionTimeSlot::find(3);

        if ($firstCollectionSlot->order_quantity === 20 && $secondCollectionSlot->order_quantity === 20
            && $thirdCollectionSlot->order_quantity === 20) {
            
            // Collection slot full message.

            $request->session()->flash('slotsFull', 'All of the collection slots are full. Please try 
            again this coming Friday from 7pm onwards.');

            return redirect()->route('home');

        }

        $total_price = 0.0;
        $total_items_quantity = 0;

        // Retrive products related to user from cart.
        $cartAndProductRecords = DB::table('products')
        ->join('carts', function ($join) {
            $join->on('carts.product_id', '=', 'products.id')
                ->where('carts.customer_id', '=', auth()->user()->customers->first()->id);
        })
        ->get();

        foreach ($cartAndProductRecords as $cartAndProductRecord) {
            $total_price += ($cartAndProductRecord->price * $cartAndProductRecord->product_quantity);
            $total_items_quantity += $cartAndProductRecord->product_quantity;
        }

        $total_price_string_2dp = number_format($total_price, 2);

        // dd(Carbon::now()->toDateString() get date as string 
        // Carbon::now()->format('l')); get day as a string

        // Current date and time:
        $currentDateTime = Carbon::now();

        $firstTimeSlot = Carbon::parse('10:00:00')->addDays(1);
        $secondTimeSlot = Carbon::parse('13:00:00')->addDays(1);
        $thirdTimeSlot = Carbon::parse('16:00:00')->addDays(1);

        return view('checkout', [
            "cartAndProductRecords" => $cartAndProductRecords,
            'total_price' => $total_price_string_2dp,
            'total_items_quantity' => $total_items_quantity,
            'currentDateTime' => $currentDateTime,
            'firstTimeSlot' => $firstTimeSlot,
            'secondTimeSlot' => $secondTimeSlot,
            'thirdTimeSlot' => $thirdTimeSlot,
        ]);
    }


    public function store(Request $request, float $totalPrice, int $totalQuantity, 
                            int $totalItems, Carbon $currentDateTime) {

        $orderDescription = "A total of ".$totalItems." "
            .Str::plural('item', $totalItems)." with a total quantity of ".$totalQuantity
            ." and a total price of €".number_format((float)$totalPrice, 2, '.', '').".";
        
        // Creating a checkout record through the customer:
        $order = auth()->user()->customers->first()->checkouts()->create([
                    'order_total' => $totalPrice,
                    'order_quantity' => $totalQuantity,
                    'total_items' => $totalItems,
                    'payment_type' => $request->payment,
                    'collection_time' => $request->collectionTime,
                    'collection_day' => $request->collectionDay,
                    'order_description' => $orderDescription,
                ]); 

        if ($request->payment === 'PayPal') {

            return redirect()->route('paypal.checkout', $order->id);

        }

        return redirect()->route('order');

    }

}   
