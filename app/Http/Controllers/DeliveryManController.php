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
use App\Models\Mail\UserCreate;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DeliveryPersonExport;
use App\Imports\DeliveryPersonImport;
use App\Models\ProductIntake;
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
        // $productService        = new DeliveryMan();

        // $per=$productService->theproductintakes();

        // dd($per);

        //  return theproductintakes();

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
                'first_name' => 'required',
                'last_name' => 'required',
                'contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
                'email' => 'required|email|unique:customers',
                'password' => 'required',

            ];


            $validator = \Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('deliveryman.index')->with('error', $messages->first());
            }

            $objCustomer    = \Auth::user();
            $creator        = User::find($objCustomer->creatorId());
            $total_customer = $objCustomer->countDeliveryPersons();
            $plan           = Plan::find($creator->plan);

            $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->first();
            if ($total_customer < $plan->max_deliverymen || $plan->max_deliverymen == -1) {
                DeliveryMan::create([
                    'deliveryman_id' => $this->deliveryPersonNumber(),
                    'first_name' => $request->first_name,
                    'last_name' => $request->last_name,
                    'contact' => $request->contact,
                    'email' => $request->email,
                    'tax_number' => $request->tax_number,
                    'password' => Hash::make($request->password),
                    'created_by' => \Auth::user()->creatorId(),
                    'lang' => !empty($default_language) ? $default_language->value : '',

                ]);

                // DeliveryMan::create($request->all());
                $customer                  = new DeliveryMan();
               
                
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

            return redirect()->route('deliveryman.index')->with('success', __('Delivery Person successfully created.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    function deliveryPersonNumber()
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
        $deliveryperson = DeliveryMan::find($id);

        $delivered_items = ProductIntake::find($id);
        // dd($delivered_items);
        $returned_items = DeliveryMan::find($id);
        // dd($deliveryperson);
        return view('deliveryman.show', compact('deliveryperson'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DeliveryMan  $deliveryMan
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (\Auth::user()->can('edit customer')) {
            $deliveryperson              = DeliveryMan::find($id);
            $deliveryperson->customField = CustomField::getData($deliveryperson, 'customer');

            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'customer')->get();

            return view('deliveryman.edit', compact('deliveryperson', 'customFields'));
        } else {

            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DeliveryMan  $deliveryMan
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        // dd($request);

        if (\Auth::user()->can('edit customer')) {
            $deliveryMan = DeliveryMan::find($id);

            $rules = [
                'first_name' => 'required',
                'last_name' => 'required',
                'contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
            ];


            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('deliveryman.index')->with('error', $messages->first());
            }

            $deliveryMan->first_name             = $request->first_name;
            $deliveryMan->last_name             = $request->last_name;
            $deliveryMan->contact          = $request->contact;
            $deliveryMan->email            = $request->email;
            $deliveryMan->tax_number        =$request->tax_number;
            $deliveryMan->created_by       = \Auth::user()->creatorId();
           
            $deliveryMan->save();

            CustomField::saveData($deliveryMan, $request->customField);

            return redirect()->route('deliveryman.index')->with('success', __('Delivery person successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DeliveryMan  $deliveryMan
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (\Auth::user()->can('delete customer')) {
            $deliveryMan = DeliveryMan::find($id);

            if ($deliveryMan->created_by == \Auth::user()->creatorId()) {
                
                $deliveryMan->delete();

                return redirect()->route('deliveryman.index')->with('success', __('Delivery person successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function deliveryPersonPassword($id)
    {
        $eId  = \Crypt::decrypt($id);
        $deliveryman = DeliveryMan::find($eId);
        return view('deliveryman.reset', compact('deliveryman'));

    }

    public function deliveryPersonPasswordReset(Request $request, $id)
    {
        $validator = \Validator::make($request->all(), ['password' => 'required|confirmed|same:password_confirmation',]);

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }


        $user= DeliveryMan::where('id', $id)->first();

        $user->forceFill(['password' => Hash::make($request->password),])->save();

        return redirect()->route('deliveryman.index')->with('success', 'User Password successfully updated.');


    }

    public function export()
    {
        $name = 'deliveryPerson_' . date('Y-m-d i:h:s');
        $data = Excel::download(new DeliveryPersonExport(), $name . '.xlsx');

        return $data;
    }

    public function importFile()
    {
        return view('deliveryman.import');
    }

    public function import(Request $request)
    {

        $rules = [
            'file' => 'required|mimes:csv,txt,xls',
        ];

        $validator = \Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $deliverypersons = (new DeliveryPersonImport())->toArray(request()->file('file'))[0];

        $totalDeliveryPersons = count($deliverypersons) - 1;

        $errorArray    = [];
        $deliveryman_id = $this->deliveryPersonNumber();

        for ($i = 1; $i <= count($deliverypersons) - 1; $i++) {
            $cust_id = $deliveryman_id++;
            $deliveryperson = $deliverypersons[$i];
            $deliveryPersonByEmail = DeliveryMan::where('email', $deliveryperson[1])->first();
            if (!empty($deliveryPersonByEmail)) {
                $deliveryPersonData = $deliveryPersonByEmail;
            } else {
                $deliveryPersonData = new DeliveryMan();
                $deliveryPersonData->customer_id      = $cust_id;
            }

            $deliveryPersonData->name             = $deliveryperson[0];
            $deliveryPersonData->email            = $deliveryperson[1];
            $deliveryPersonData->password         = Hash::make($deliveryperson[2]);
            $deliveryPersonData->contact          = $deliveryperson[3];
        
            $deliveryPersonData->lang             = 'en';
            $deliveryPersonData->is_active        = 1;
            $deliveryPersonData->created_by       = \Auth::user()->creatorId();

            if (empty($deliveryPersonData)) {
                $errorArray[] = $deliveryPersonData;
            } else {
                $deliveryPersonData->save();

                $role_r = Role::where('name', '=', 'customer')->firstOrFail();
                $deliveryPersonData->assignRole($role_r);
            }
        }

        $errorRecord = [];
        if (empty($errorArray)) {
            $data['status'] = 'success';
            $data['msg']    = __('Record successfully imported');
        } else {
            $data['status'] = 'error';
            $data['msg']    = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalDeliveryPersons . ' ' . 'record');


            foreach ($errorArray as $errorData) {

                $errorRecord[] = implode(',', $errorData);
            }

            \Session::put('errorArray', $errorRecord);
        }

        return redirect()->back()->with($data['status'], $data['msg']);
    }
}
