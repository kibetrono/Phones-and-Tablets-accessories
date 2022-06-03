<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\Mail\UserCreate;
use App\Models\Plan;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Utility;
use Auth;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CustomerExport;
use App\Imports\CustomerImport;
use App\Models\DeliveryMan;

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


    public function store(Request $request)
    {

        // dd($request->all());

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

                return redirect()->route('deliverman.index')->with('error', $messages->first());
            }

            

            $objDeliveryman    = \Auth::user();
            $creator        = User::find($objDeliveryman->creatorId());
            $total_deliveryman = $objDeliveryman->countDeliveryPersons();
            $plan           = Plan::find($creator->plan);

            $default_language = DB::table('settings')->select('value')->where('name', 'default_language')->first();
            if ($total_deliveryman < $plan->max_deliverymen || $plan->max_deliverymen == -1) {
        // dd($request->all());

                $deliveryman                  = new DeliveryMan();
                $deliveryman->deliveryman_id     = $this->DeliveryManNumber();
                $deliveryman->name            = $request->name;
                $deliveryman->contact         = $request->contact;
                $deliveryman->email           = $request->email;
                $deliveryman->tax_number      =$request->tax_number;
                $deliveryman->password        = Hash::make($request->password);
                $deliveryman->created_by      = \Auth::user()->creatorId();
                $deliveryman->billing_name    = $request->billing_name;
                $deliveryman->billing_country = $request->billing_country;
                $deliveryman->billing_state   = $request->billing_state;
                $deliveryman->billing_city    = $request->billing_city;
                $deliveryman->billing_phone   = $request->billing_phone;
                $deliveryman->billing_zip     = $request->billing_zip;
                $deliveryman->billing_address = $request->billing_address;

                $deliveryman->shipping_name    = $request->shipping_name;
                $deliveryman->shipping_country = $request->shipping_country;
                $deliveryman->shipping_state   = $request->shipping_state;
                $deliveryman->shipping_city    = $request->shipping_city;
                $deliveryman->shipping_phone   = $request->shipping_phone;
                $deliveryman->shipping_zip     = $request->shipping_zip;
                $deliveryman->shipping_address = $request->shipping_address;

                $deliveryman->lang = !empty($default_language) ? $default_language->value : '';

                $deliveryman->save();
                CustomField::saveData($deliveryman, $request->customField);
            } else {
                return redirect()->back()->with('error', __('Your user limit is over, Please upgrade plan.'));
            }


            $role_r = Role::where('name', '=', 'customer')->firstOrFail();
            $deliveryman->assignRole($role_r);

            $deliveryman->password = $request->password;
            $deliveryman->type     = 'DeliveyMan';
            try {
                Mail::to($deliveryman->email)->send(new UserCreate($deliveryman));
            } catch (\Exception $e) {
                $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
            }


            //Twilio Notification
            $setting  = Utility::settings(\Auth::user()->creatorId());
             if(isset($setting['customer_notification']) && $setting['customer_notification'] ==1)
            {
                $msg = __("New Personel created by").' '.\Auth::user()->name.'.';
                Utility::send_twilio_msg($request->contact,$msg);
           }

            return redirect()->route('deliveryman.index')->with('success', __('Delivery Personel successfully created.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function show($ids)
    {

        $id       = \Crypt::decrypt($ids);
        $deliveryman = DeliveryMan::find($id);
        return view('deliveryman.show', compact('deliveryman'));
    }


    public function edit($id)
    {
        if (\Auth::user()->can('edit customer')) {
            $deliveryman              = DeliveryMan::find($id);
            $deliveryman->customField = CustomField::getData($deliveryman, 'deliveryman');

            $customFields = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'deliveryman')->get();

            return view('deliveryman.edit', compact('deliveryman', 'customFields'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function update(Request $request, DeliveryMan $deliveryman)
    {

        if (\Auth::user()->can('edit customer')) {

            $rules = [
                'name' => 'required',
                'contact' => 'required|regex:/^([0-9\s\-\+\(\)]*)$/',
            ];


            $validator = \Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $messages = $validator->getMessageBag();

                return redirect()->route('deliveryman.index')->with('error', $messages->first());
            }

            $deliveryman->name             = $request->name;
            $deliveryman->contact          = $request->contact;
            $deliveryman->email            = $request->email;
            $deliveryman->tax_number        =$request->tax_number;
            $deliveryman->created_by       = \Auth::user()->creatorId();
            $deliveryman->billing_name     = $request->billing_name;
            $deliveryman->billing_country  = $request->billing_country;
            $deliveryman->billing_state    = $request->billing_state;
            $deliveryman->billing_city     = $request->billing_city;
            $deliveryman->billing_phone    = $request->billing_phone;
            $deliveryman->billing_zip      = $request->billing_zip;
            $deliveryman->billing_address  = $request->billing_address;
            $deliveryman->shipping_name    = $request->shipping_name;
            $deliveryman->shipping_country = $request->shipping_country;
            $deliveryman->shipping_state   = $request->shipping_state;
            $deliveryman->shipping_city    = $request->shipping_city;
            $deliveryman->shipping_phone   = $request->shipping_phone;
            $deliveryman->shipping_zip     = $request->shipping_zip;
            $deliveryman->shipping_address = $request->shipping_address;
            $deliveryman->save();

            CustomField::saveData($deliveryman, $request->customField);

            return redirect()->route('deliveryman.index')->with('success', __('Deliver Person successfully updated.'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }


    public function destroy(DeliveryMan $deliveryman)
    {
        if (\Auth::user()->can('delete customer')) {
            if ($deliveryman->created_by == \Auth::user()->creatorId()) {
                $deliveryman->delete();

                return redirect()->route('deliveryman.index')->with('success', __('Delivery Person successfully deleted.'));
            } else {
                return redirect()->back()->with('error', __('Permission denied.'));
            }
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    function DeliveryManNumber()
    {
        $latest = DeliveryMan::where('created_by', '=', \Auth::user()->creatorId())->latest()->first();
        if (!$latest) {
            return 1;
        }

        return $latest->deliveryman_id + 1;
    }

    public function customerLogout(Request $request)
    {
        \Auth::guard('customer')->logout();

        $request->session()->invalidate();

        return redirect()->route('deliveryman.login');
    }

    public function payment(Request $request)
    {

        if (\Auth::user()->can('manage customer payment')) {
            $category = [
                'Invoice' => 'Invoice',
                'Deposit' => 'Deposit',
                'Sales' => 'Sales',
            ];

            $query = Transaction::where('user_id', \Auth::user()->id)->where('user_type', 'Customer')->where('type', 'Payment');
            if (!empty($request->date)) {
                $date_range = explode(' - ', $request->date);
                $query->whereBetween('date', $date_range);
            }

            if (!empty($request->category)) {
                $query->where('category', '=', $request->category);
            }
            $payments = $query->get();

            return view('deliveryman.payment', compact('payments', 'category'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function transaction(Request $request)
    {
        if (\Auth::user()->can('manage customer payment')) {
            $category = [
                'Invoice' => 'Invoice',
                'Deposit' => 'Deposit',
                'Sales' => 'Sales',
            ];

            $query = Transaction::where('user_id', \Auth::user()->id)->where('user_type', 'DeliveryMan');

            if (!empty($request->date)) {
                $date_range = explode(' - ', $request->date);
                $query->whereBetween('date', $date_range);
            }

            if (!empty($request->category)) {
                $query->where('category', '=', $request->category);
            }
            $transactions = $query->get();

            return view('deliveryman.transaction', compact('transactions', 'category'));
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function profile()
    {
        $userDetail              = \Auth::user();
        $userDetail->customField = CustomField::getData($userDetail, 'customer');
        $customFields            = CustomField::where('created_by', '=', \Auth::user()->creatorId())->where('module', '=', 'customer')->get();

        return view('deliveryman.profile', compact('userDetail', 'customFields'));
    }

    public function editprofile(Request $request)
    {
        $userDetail = \Auth::user();
        $user       = DeliveryMan::findOrFail($userDetail['id']);

        $this->validate(
            $request,
            [
                'name' => 'required|max:120',
                'contact' => 'required',
                'email' => 'required|email|unique:users,email,' . $userDetail['id'],
            ]
        );

        if ($request->hasFile('profile')) {
            $filenameWithExt = $request->file('profile')->getClientOriginalName();
            $filename        = pathinfo($filenameWithExt, PATHINFO_FILENAME);
            $extension       = $request->file('profile')->getClientOriginalExtension();
            $fileNameToStore = $filename . '_' . time() . '.' . $extension;

            $dir        = storage_path('uploads/avatar/');
            $image_path = $dir . $userDetail['avatar'];

            if (File::exists($image_path)) {
                File::delete($image_path);
            }

            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            $path = $request->file('profile')->storeAs('uploads/avatar/', $fileNameToStore);
        }

        if (!empty($request->profile)) {
            $user['avatar'] = $fileNameToStore;
        }
        $user['name']    = $request['name'];
        $user['email']   = $request['email'];
        $user['contact'] = $request['contact'];
        $user->save();
        CustomField::saveData($user, $request->customField);

        return redirect()->back()->with(
            'success',
            'Profile successfully updated.'
        );
    }

    public function editBilling(Request $request)
    {
        $userDetail = \Auth::user();
        $user       = DeliveryMan::findOrFail($userDetail['id']);
        $this->validate(
            $request,
            [
                'billing_name' => 'required',
                'billing_country' => 'required',
                'billing_state' => 'required',
                'billing_city' => 'required',
                'billing_phone' => 'required',
                'billing_zip' => 'required',
                'billing_address' => 'required',
            ]
        );
        $input = $request->all();
        $user->fill($input)->save();

        return redirect()->back()->with(
            'success',
            'Profile successfully updated.'
        );
    }

    public function editShipping(Request $request)
    {
        $userDetail = \Auth::user();
        $user       = DeliveryMan::findOrFail($userDetail['id']);
        $this->validate(
            $request,
            [
                'shipping_name' => 'required',
                'shipping_country' => 'required',
                'shipping_state' => 'required',
                'shipping_city' => 'required',
                'shipping_phone' => 'required',
                'shipping_zip' => 'required',
                'shipping_address' => 'required',
            ]
        );
        $input = $request->all();
        $user->fill($input)->save();

        return redirect()->back()->with(
            'success',
            'Profile successfully updated.'
        );
    }

    public function updatePassword(Request $request)
    {
        if (Auth::Check()) {
            $request->validate(
                [
                    'current_password' => 'required',
                    'new_password' => 'required|min:6',
                    'confirm_password' => 'required|same:new_password',
                ]
            );
            $objUser          = Auth::user();
            $request_data     = $request->All();
            $current_password = $objUser->password;
            if (Hash::check($request_data['current_password'], $current_password)) {
                $user_id            = Auth::User()->id;
                $obj_user           = DeliveryMan::find($user_id);
                $obj_user->password = Hash::make($request_data['new_password']);;
                $obj_user->save();

                return redirect()->back()->with('success', __('Password updated successfully.'));
            } else {
                return redirect()->back()->with('error', __('Please enter correct current password.'));
            }
        } else {
            return redirect()->back()->with('error', __('Something is wrong.'));
        }
    }

    public function changeLanquage($lang)
    {

        $user       = Auth::user();
        $user->lang = $lang;
        $user->save();

        return redirect()->back()->with('success', __('Language Change Successfully!'));
    }
    public function export()
    {
        $name = 'customer_' . date('Y-m-d i:h:s');
        $data = Excel::download(new CustomerExport(), $name . '.xlsx');

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

        $deliverymens = (new CustomerImport())->toArray(request()->file('file'))[0];

        $totalCustomer = count($deliverymens) - 1;
        $errorArray    = [];
        $deliveryman_id = $this->DeliveryManNumber();

        for ($i = 1; $i <= count($deliverymens) - 1; $i++) {
            $cust_id = $deliveryman_id++;
            $deliveryman = $deliverymens[$i];
            $deliverymanByEmail = DeliveryMan::where('email', $deliveryman[1])->first();
            if (!empty($deliverymanByEmail)) {
                $deliverymanData = $deliverymanByEmail;
            } else {
                $deliverymanData = new DeliveryMan();
                $deliverymanData->deliveryman_id      = $cust_id;
            }
//            dd(deliveryman);

            $deliverymanData->name             = $deliveryman[0];
            $deliverymanData->email            = $deliveryman[1];
            $deliverymanData->password         = Hash::make($deliveryman[2]);
            $deliverymanData->contact          = $deliveryman[3];
            $deliverymanData->billing_name     = $deliveryman[4];
            $deliverymanData->billing_country  = $deliveryman[5];
            $deliverymanData->billing_state    = $deliveryman[6];
            $deliverymanData->billing_city     = $deliveryman[7];
            $$deliverymanData->billing_phone    = $deliveryman[8];
            $deliverymanData->billing_zip      = $deliveryman[9];
            $deliverymanData->billing_address  = $deliveryman[10];
            $deliverymanData->shipping_name    = $deliveryman[11];
            $deliverymanData->shipping_country = $deliveryman[12];
            $deliverymanData->shipping_state   = $deliveryman[13];
            $deliverymanData->shipping_city    = $deliveryman[14];
            $deliverymanData->shipping_phone   = $deliveryman[15];
            $deliverymanData->shipping_zip     = $deliveryman[16];
            $deliverymanData->shipping_address = $deliveryman[17];
            $deliverymanData->lang             = 'en';
            $deliverymanData->is_active        = 1;
            $deliverymanData->created_by       = \Auth::user()->creatorId();

            if (empty($deliverymanData)) {
                $errorArray[] = $deliverymanData;
            } else {
                $deliverymanData->save();

                $role_r = Role::where('name', '=', 'customer')->firstOrFail();
                $deliverymanData->assignRole($role_r);
            }
        }

        $errorRecord = [];
        if (empty($errorArray)) {
            $data['status'] = 'success';
            $data['msg']    = __('Record successfully imported');
        } else {
            $data['status'] = 'error';
            $data['msg']    = count($errorArray) . ' ' . __('Record imported fail out of' . ' ' . $totalCustomer . ' ' . 'record');


            foreach ($errorArray as $errorData) {

                $errorRecord[] = implode(',', $errorData);
            }

            \Session::put('errorArray', $errorRecord);
        }

        return redirect()->back()->with($data['status'], $data['msg']);
    }
    public function previewInvoice()
    {

        $objUser  = \Auth::user();
        $settings = Utility::settings();

        $invoice  = new Invoice();

        $deliveryman                   = new \stdClass();
        $deliveryman->email            = '<Email>';
        $deliveryman->shipping_name    = '<DeliveryMan Name>';
        $deliveryman->shipping_country = '<Country>';
        $deliveryman->shipping_state   = '<State>';
        $deliveryman->shipping_city    = '<City>';
        $deliveryman->shipping_phone   = '<DeliveryMan Phone Number>';
        $deliveryman->shipping_zip     = '<Zip>';
        $deliveryman->shipping_address = '<Address>';
        $deliveryman->billing_name     = '<DeliveryMan Name>';
        $deliveryman->billing_country  = '<Country>';
        $deliveryman->billing_state    = '<State>';
        $deliveryman->billing_city     = '<City>';
        $deliveryman->billing_phone    = '<DeliveryMan Phone Number>';
        $deliveryman->billing_zip      = '<Zip>';
        $deliveryman->billing_address  = '<Address>';
        $invoice->sku         = 'Test123';

        $totalTaxPrice = 0;
        $taxesData     = [];

        $items = [];
        for($i = 1; $i <= 3; $i++)
        {
            $item           = new \stdClass();
            $item->name     = 'Item ' . $i;
            $item->quantity = 1;
            $item->tax      = 5;
            $item->discount = 50;
            $item->price    = 100;

            $taxes = [
                'Tax 1',
                'Tax 2',
            ];

            $itemTaxes = [];
            foreach($taxes as $k => $tax)
            {
                $taxPrice         = 10;
                $totalTaxPrice    += $taxPrice;
                $itemTax['name']  = 'Tax ' . $k;
                $itemTax['rate']  = '10 %';
                $itemTax['price'] = '$10';
                $itemTaxes[]      = $itemTax;
                if(array_key_exists('Tax ' . $k, $taxesData))
                {
                    $taxesData['Tax ' . $k] = $taxesData['Tax 1'] + $taxPrice;
                }
                else
                {
                    $taxesData['Tax ' . $k] = $taxPrice;
                }
            }
            $item->itemTax = $itemTaxes;
            $items[]       = $item;
        }

        $invoice->invoice_id = 1;
        $invoice->issue_date = date('Y-m-d H:i:s');
        $invoice->due_date   = date('Y-m-d H:i:s');
        $invoice->itemData   = $items;

        $invoice->totalTaxPrice = 60;
        $invoice->totalQuantity = 3;
        $invoice->totalRate     = 300;
        $invoice->totalDiscount = 10;
        $invoice->taxesData     = $taxesData;
        $invoice->customField   = [];
        $customFields           = [];

        $preview    = 1;


        $logo         = asset(Storage::url('uploads/logo/'));
        $company_logo = Utility::getValByName('company_logo_dark');
        $img          = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));

        return view('deliveryman.show', compact('invoice', 'preview', 'img', 'settings', 'customer', 'customFields'));
    }

    public function statement(Request $request, $id)
    {
//       dd($request->all());
        $deliveryman = DeliveryMan::find($id);
        $settings = Utility::settings();
        $deliverymanDetail       = DeliveryMan::findOrFail($deliveryman['id']);
        $invoice   = Invoice::where('created_by', '=', \Auth::user()->creatorId())->where('deliveryman_id', '=', $deliveryman->id)->get()->pluck('id');
        $invoice_payment=InvoicePayment::whereIn('invoice_id',$invoice);
        if(!empty($request->from_date) && !empty($request->until_date))
        {
            $invoice_payment->whereBetween('date',  [$request->from_date, $request->until_date]);

            $data['from_date']  = $request->from_date;
            $data['until_date'] = $request->until_date;
        }
        else
        {
            $data['from_date']  = date('Y-m-1');
            $data['until_date'] = date('Y-m-t');
            $invoice_payment->whereBetween('date',  [$data['from_date'], $data['until_date']]);
        }
        $invoice_payment=$invoice_payment->get();
//        dd($invoice_payment);
        $user = \Auth::user();
        $logo         = asset(Storage::url('uploads/logo/'));
         $company_logo = Utility::getValByName('company_logo_dark');
        $img          = asset($logo . '/' . (isset($company_logo) && !empty($company_logo) ? $company_logo : 'logo-dark.png'));

        return view('deliveryman.statement', compact('customer','img','user','customerDetail','invoice_payment','settings','data'));
    }

    public function customerPassword($id)
    {
        $eId        = \Crypt::decrypt($id);
        $deliveryman = DeliveryMan::find($eId);

        return view('deliveryman.reset', compact('deliveryman'));

    }

    public function customerPasswordReset(Request $request, $id)
    {
        $validator = \Validator::make(
            $request->all(), [
                               'password' => 'required|confirmed|same:password_confirmation',
                           ]
        );

        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }


        $user                 = User::where('id', $id)->first();
        $user->forceFill([
                             'password' => Hash::make($request->password),
                         ])->save();

        return redirect()->route('deliveryman.index')->with(
            'success', 'User Password successfully updated.'
        );


    }




}
