<?php

namespace App\Http\Controllers;


use Auth;
use File;
use App\Models\Plan;
use App\Models\User;
use App\Models\Invoice;
use App\Models\Utility;
use App\Models\Customer;
use App\Models\CustomField;
use App\Models\DeliveryMan;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\InvoicePayment;
use App\Exports\CustomerExport;
use App\Imports\CustomerImport;
use App\Models\Mail\UserCreate;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;


class DeliveryManController extends Controller
{
    public function dashboard()
    {
        $data['invoiceChartData'] = \Auth::user()->invoiceChartData();

        return view('deliveryman.dashboard', $data);
    }

    public function index()
    {

        if (\Auth::user()->can('manage customer')) {
            $deliverypersons = DeliveryMan::where('created_by', \Auth::user()->creatorId())->get();

            return view('deliveryman.index', compact('deliverypersons'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function create()
    {
        if (\Auth::user()->can('create customer')) {
            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'customer')->get();

            return view('deliveryman.create', compact('customFields'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (\Auth::user()->can('create customer')) {

            // dd($request->all());
            $rules = [
                'name' => 'required',
                'contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                'email' => 'required|email|unique:customers',
                'password' => 'required',

            ];


            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('customer.index')->with('error', $messages->first());
            }

            $objCustomer    = \Auth::user();
            $creator        = User::find($objCustomer->creatorId());
            $total_customer = $objCustomer->countDeliveryPersons();
            $plan           = Plan::find($creator->plan);

            $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->first();
            if ($total_customer < $plan->max_deliverymen || $plan->max_deliverymen == -1) {
                DeliveryMan::create([
                    'deliveryman_id' => $this->customerNumber(),
                    'name' => $request->name,
                    'contact' => $request->contact,
                    'email' => $request->email,
                    'tax_number' => $request->tax_number,
                    'password' => Hash::make($request->password),
                    'created_by' => \Auth::user()->creatorId(),
                    'billing_name' => $request->billing_name,
                    'billing_country' => $request->billing_country,
                    'billing_state' => $request->billing_state,
                    'billing_city' => $request->billing_city,
                    'billing_phone' => $request->billing_phone,
                    'billing_zip' => $request->billing_zip,
                    'billing_address' => $request->billing_address,

                    'shipping_name' => $request->shipping_name,
                    'shipping_country' => $request->shipping_country,
                    'shipping_state' => $request->shipping_state,
                    'shipping_city' => $request->shipping_city,
                    'shipping_phone' => $request->shipping_phone,
                    'shipping_zip' => $request->shipping_zip,
                    'shipping_address' => $request->shipping_address,
                    'lang' => !empty($default_language) ? $default_language->value : '',

                ]);

                // DeliveryMan::create($request->all());
                $customer                  = new DeliveryMan();
                // $customer->deliveryman_id  = $this->customerNumber();
                // $customer->name            = $request->name;
                // $customer->contact         = $request->contact;
                // $customer->email           = $request->email;
                // $customer->tax_number      =$request->tax_number;
                // $customer->password        = Hash::make($request->password);
                // $customer->created_by      = \Auth::user()->creatorId();
                // $customer->billing_name    = $request->billing_name;
                // $customer->billing_country = $request->billing_country;
                // $customer->billing_state   = $request->billing_state;
                // $customer->billing_city    = $request->billing_city;
                // $customer->billing_phone   = $request->billing_phone;
                // $customer->billing_zip     = $request->billing_zip;
                // $customer->billing_address = $request->billing_address;

                // $customer->shipping_name    = $request->shipping_name;
                // $customer->shipping_country = $request->shipping_country;
                // $customer->shipping_state   = $request->shipping_state;
                // $customer->shipping_city    = $request->shipping_city;
                // $customer->shipping_phone   = $request->shipping_phone;
                // $customer->shipping_zip     = $request->shipping_zip;
                // $customer->shipping_address = $request->shipping_address;

                // $customer->lang = !empty($default_language) ? $default_language->value : '';

                // $customer->save();
                
                CustomField::saveData($customer, $request->customField);
            } else {
                return redirect()->back()->with('error', __('Your user limit is over, Please upgrade plan.'));
            }


            $role_r = Role::where('name', '=', 'customer')->firstOrFail();
            $customer->assignRole($role_r);

            $customer->password = $request->password;
            $customer->type     = 'Customer';
            try {
                Mail::to($customer->email)->send(new UserCreate($customer));
            } catch (\Exception $e) {
                $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
            }


            //Twilio Notification
            $setting  = Utility::settings(\Auth::user()->creatorId());
             if(isset($setting['customer_notification']) && $setting['customer_notification'] ==1)
            {
                $msg = __("New Customer created by").' '.\Auth::user()->name.'.';
                Utility::send_twilio_msg($request->contact,$msg);
           }

            return redirect()->route('deliveryman.index')->with('success', __('Personel successfully created.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    function customerNumber()
    {
        $latest = DeliveryMan::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if (!$latest) {
            return 1;
        }

        return $latest->deliveryman_id + 1;
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\DeliveryMan  $deliveryMan
     * @return \Illuminate\Http\Response
     */
    public function show($ids)
    {
        $id       = \Crypt::decrypt($ids);
        $deliverman = DeliveryMan::find($id);
        return view('deliveryman.show', compact('deliverman'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DeliveryMan  $deliveryMan
     * @return \Illuminate\Http\Response
     */
    public function edit(DeliveryMan $deliveryMan)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DeliveryMan  $deliveryMan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DeliveryMan $deliveryMan)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DeliveryMan  $deliveryMan
     * @return \Illuminate\Http\Response
     */
    public function destroy(DeliveryMan $deliveryMan)
    {
        //
    }
}
