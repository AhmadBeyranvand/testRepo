<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Driver;
use App\Models\Factor;
use App\Models\FactorContent;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FactorController extends Controller
{
    public function newShow()
    {
        return view('pages.new');
    }

    public function newSubmit(Request $req)
    {
        $data = [];
        $customer = Customer::where("phone", $req->post('customer_phone'))->first();
        if (!$customer) {

            $customer = new Customer();
            $customer->full_name = $req->post("customer_name");
            $customer->phone = $req->post("customer_phone");
            $customer->address = $req->post("customer_address");
            $customer->save();

            $data['customer']['id'] = $customer->id;
            $data['customer']['name'] = $customer->full_name;
            $data['customer']['phone'] = $customer->phone;
            $data['customer']['address'] = $customer->address;
        } else {
            $customer = Customer::find($customer->id);
            $data['customer']['id'] = $customer->id;
            $data['customer']['name'] = $customer->full_name;
            $data['customer']['phone'] = $customer->phone;
            $data['customer']['address'] = $customer->address;
        }
        $driver = Driver::where("phone", $req->post('driver_phone'))->first();
        if (!$driver) {
            $driver = new Driver();
            $driver->full_name = $req->post('driver_name');
            $driver->phone = $req->post('driver_phone');
            $driver->car_no = $req->post('car_no');
            $driver->save();

            $data['driver']['name'] = $driver->full_name;
            $data['driver']['phone'] = $driver->phone;
            $data['driver']['car_no'] = $driver->car_no;
        } else {
            $driver = Driver::find($driver->id);
            $data['driver']['name'] = $driver->full_name;
            $data['driver']['phone'] = $driver->phone;
            $data['driver']['car_no'] = $driver->car_no;
        }
        $factor = new Factor();
        $factor->customer_id = $customer->id;
        $factor->driver_id = $driver->id;
        $factor->payment = $req->post("payment");
        $factor->save();

        $data['factor']['id'] = $factor->id;
        $data['factor']['date'] = \Morilog\Jalali\CalendarUtils::strftime("Y-m-d",explode(" ", $factor->created_at)[0]);
        $data['factor']['time'] = explode(" ", $factor->created_at)[1];

        $counter = 0;

        $data['totalPrice'] = 0;
        foreach ($req->post('contents') as $content) {
            if ($content['count_of'] >= 0) {
                $factorContent = new FactorContent();
                $factorContent->factor_id = $data['factor']['id'];
                $factorContent->count_of = $content['count_of'];
                $factorContent->price = $content['price'];
                $factorContent->save();

                $data['factor']['content'][$counter]['count_of'] = $factorContent->count_of;
                $data['factor']['content'][$counter]['price'] = $factorContent->price;
                $data['factor']['content'][$counter]['total'] = $factorContent->price * $factorContent->count_of;
                $data['totalPrice'] +=$data['factor']['content'][$counter]['total'];
                $counter++;
            }
        }

        $data['payment'] = $req->post("payment");
        $data['remainPrice'] = $req->post("remainPrice");

        // return $data;
        return view('pages.factor',['data'=>$data]);
    }

    public function listShow()
    {
        $factors = Factor::all();
        foreach ($factors as $factor) {
            $factor->driver = Driver::find($factor->driver_id);
            $factor->customer = Customer::find($factor->customer_id);
            $factor->contents = FactorContent::where('factor_id', $factor->id)->get();
        }
        return view('pages.list');
    }


}
