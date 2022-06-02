<?php

namespace App\Http\Controllers;

use App\Models\Mail\EmailTest;
use App\Models\Mail\testMail;
use App\Models\Utility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Artisan;


class SystemController extends Controller
{
    public function index()
    {
        if(\Auth::user()->can('manage system settings'))
        {
            $settings              = Utility::settings();
            $admin_payment_setting = Utility::getAdminPaymentSetting();
            return view('settings.index', compact('settings', 'admin_payment_setting'));
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function store(Request $request)
    {
        if(\Auth::user()->can('manage system settings'))
        {
            if($request->logo_dark)
            {
                $request->validate(
                    [
                        'logo_dark' => 'image|mimes:png|max:20480',
                    ]
                );
                $logoName = 'logo-dark.png';

                $path     = $request->file('logo_dark')->storeAs('uploads/logo/', $logoName);
            }

            if($request->logo_light)
            {
                $request->validate(
                    [
                        'logo_light' => 'image|mimes:png|max:20480',
                    ]
                );
                $lightlogoName = 'logo-light.png';

                $path     = $request->file('logo_light')->storeAs('uploads/logo/', $lightlogoName);
            }

            if($request->favicon)
            {
                $request->validate(
                    [
                        'favicon' => 'image|mimes:png|max:20480',
                    ]
                );
                $favicon = 'favicon.png';
                $path    = $request->file('favicon')->storeAs('uploads/logo/', $favicon);
            }

            if($request->landing_logo)
            {
                $request->validate(
                    [
                        'landing_logo' => 'image|mimes:png|max:20480',
                    ]
                );
                $landingLogoName = 'landing_logo.png';
                $path            = $request->file('landing_logo')->storeAs('uploads/logo/', $landingLogoName);
            }



            $arrEnv = [
                'SITE_RTL' => !isset($request->SITE_RTL) ? 'off' : 'on',
            ];
            Utility::setEnvironmentValue($arrEnv);
//            Artisan::call('config:cache');
//            Artisan::call('config:clear');

            $settings = Utility::settings();
            if(!empty($request->title_text) || !empty($request->footer_text) || !empty($request->default_language) || isset($request->display_landing_page) || isset($request->gdpr_cookie) || isset($request->enable_signup) || isset($request->color) || isset($request->cust_theme_bg) || isset($request->cust_darklayout))
            {
                $post = $request->all();
                if(!isset($request->display_landing_page))
                {
                    $post['display_landing_page'] = 'off';
                }
                if(!isset($request->gdpr_cookie))
                {
                    $post['gdpr_cookie'] = 'off';
                }
                if(!isset($request->enable_signup))
                {
                    $post['enable_signup'] = 'off';
                }
                if(!isset($request->color))
                {
                    $color = $request->has('color') ? $request-> color : 'theme-3';
                    $post['color'] = $color;
                }
                if(!isset($request->cust_theme_bg))
                {
                    $cust_theme_bg         = (isset($request->cust_theme_bg)) ? 'on' : 'off';
                    $post['cust_theme_bg'] = $cust_theme_bg;
                }
                if(!isset($request->cust_darklayout))
                {

                    $cust_darklayout         = isset($request->cust_darklayout) ? 'on' : 'off';
                    $post['cust_darklayout'] = $cust_darklayout;
                }


                unset($post['_token'], $post['logo_dark'], $post['logo_light'], $post['favicon']);
                foreach($post as $key => $data)
                {
                    if(in_array($key, array_keys($settings)))
                    {
                        \DB::insert(
                            'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', [
                                                                                                                                                         $data,
                                                                                                                                                         $key,
                                                                                                                                                         \Auth::user()->creatorId(),
                                                                                                                                                     ]
                        );
                    }
                }
            }

            return redirect()->back()->with('success', 'Setting successfully updated.');
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function saveEmailSettings(Request $request)
    {
        if(\Auth::user()->can('manage system settings'))
        {
            $request->validate(
                [
                    'mail_driver' => 'required|string|max:255',
                    'mail_host' => 'required|string|max:255',
                    'mail_port' => 'required|string|max:255',
                    'mail_username' => 'required|string|max:255',
                    'mail_password' => 'required|string|max:255',
                    'mail_encryption' => 'required|string|max:255',
                    'mail_from_address' => 'required|string|max:255',
                    'mail_from_name' => 'required|string|max:255',
                ]
            );

            $arrEnv = [
                'MAIL_DRIVER' => $request->mail_driver,
                'MAIL_HOST' => $request->mail_host,
                'MAIL_PORT' => $request->mail_port,
                'MAIL_USERNAME' => $request->mail_username,
                'MAIL_PASSWORD' => $request->mail_password,
                'MAIL_ENCRYPTION' => $request->mail_encryption,
                'MAIL_FROM_NAME' => $request->mail_from_name,
                'MAIL_FROM_ADDRESS' => $request->mail_from_address,
            ];
            Utility::setEnvironmentValue($arrEnv);

            return redirect()->back()->with('success', __('Setting successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }

    }

    public function saveCompanySettings(Request $request)
    {

        if(\Auth::user()->can('manage company settings'))
        {
            $user = \Auth::user();
            $request->validate(
                [
                    'company_name' => 'required|string|max:255',
                    'company_email' => 'required',
                    'company_email_from_name' => 'required|string',
                ]
            );
            $post = $request->all();
            unset($post['_token']);
            $settings = Utility::settings();
            foreach($post as $key => $data)
            {
                if(in_array($key, array_keys($settings)))
                {
                    \DB::insert(
                        'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', [
                                                                                                                                                     $data,
                                                                                                                                                     $key,
                                                                                                                                                     \Auth::user()->creatorId(),
                                                                                                                                                 ]
                    );
                }
            }

            return redirect()->back()->with('success', __('Setting successfully updated.'));
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function savePaymentSettings(Request $request)
    {
        if(\Auth::user()->can('manage stripe settings'))
        {

            $validator = \Validator::make(
                $request->all(), [
                                   'currency' => 'required|string',
                                   'currency_symbol' => 'required|string',
                               ]
            );
            if($validator->fails())
            {
                $messages = $validator->getMessageBag();

                return redirect()->back()->with('error', $messages->first());
            }

            $arrEnv = [
                'CURRENCY_SYMBOL' => $request->currency_symbol,
                'CURRENCY' => $request->currency,
            ];
            Utility::setEnvironmentValue($arrEnv);

            self::adminPaymentSettings($request);

            return redirect()->back()->with('success', __('Payment setting successfully saved.'));
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function saveSystemSettings(Request $request)
    {

        if(\Auth::user()->can('manage company settings'))
        {
            $user = \Auth::user();
            $request->validate(
                [
                    'site_currency' => 'required',
                ]
            );
            $post = $request->all();
            unset($post['_token']);

            if(!isset($post['shipping_display']))
            {
                $post['shipping_display'] = 'off';
            }

            $settings = Utility::settings();
            foreach($post as $key => $data)
            {
                if(in_array($key, array_keys($settings)))
                {
                    \DB::insert(
                        'insert into settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', [
                                                                                                                                                                                     $data,
                                                                                                                                                                                     $key,
                                                                                                                                                                                     \Auth::user()->creatorId(),
                                                                                                                                                                                     date('Y-m-d H:i:s'),
                                                                                                                                                                                     date('Y-m-d H:i:s'),
                                                                                                                                                                                 ]
                    );
                }
            }

            return redirect()->back()->with('success', __('Setting successfully updated.'));

        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function saveBusinessSettings(Request $request)
    {

        if(\Auth::user()->can('manage business settings'))
        {

            $user = \Auth::user();

            if($request->company_logo_dark)
            {

                $request->validate(
                    [
                        'company_logo_dark' => 'image|mimes:png|max:20480',
                    ]
                );

                $logoName     = $user->id . '-logo-dark.png';
                $path         = $request->file('company_logo_dark')->storeAs('uploads/logo/', $logoName);
                $company_logo = !empty($request->company_logo_dark) ? $logoName : 'logo-dark.png';

                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', [
                                                                                                                                                 $logoName,
                                                                                                                                                 'company_logo_dark',
                                                                                                                                                 \Auth::user()->creatorId(),
                                                                                                                                             ]
                );
            }

            if($request->company_logo_light)
            {

                $request->validate(
                    [
                        'company_logo_light' => 'image|mimes:png|max:20480',
                    ]
                );

                $logoName     = $user->id . '-logo-light.png';
                $path         = $request->file('company_logo_light')->storeAs('uploads/logo/', $logoName);
                $company_logo = !empty($request->company_logo_light) ? $logoName : 'logo-light.png';

                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', [
                                                                                                                                                 $logoName,
                                                                                                                                                 'company_logo_light',
                                                                                                                                                 \Auth::user()->creatorId(),
                                                                                                                                             ]
                );
            }
            if($request->company_favicon)
            {
                $request->validate(
                    [
                        'company_favicon' => 'image|mimes:png|max:20480',
                    ]
                );
                $favicon = $user->id . '_favicon.png';
                $path    = $request->file('company_favicon')->storeAs('uploads/logo/', $favicon);

                $company_favicon = !empty($request->favicon) ? $favicon : 'favicon.png';

                \DB::insert(
                    'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', [
                                                                                                                                                 $favicon,
                                                                                                                                                 'company_favicon',
                                                                                                                                                 \Auth::user()->creatorId(),
                                                                                                                                             ]
                );
            }

            $settings = Utility::settings();
            if(!empty($request->title_text))
            {
                $post = $request->all();

                unset($post['_token'], $post['company_logo_dark'], $post['company_logo_light'], $post['company_favicon']);
                foreach($post as $key => $data)
                {
                    if(in_array($key, array_keys($settings)))
                    {
                        \DB::insert(
                            'insert into settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', [
                                                                                                                                                         $data,
                                                                                                                                                         $key,
                                                                                                                                                         \Auth::user()->creatorId(),
                                                                                                                                                     ]
                        );
                    }
                }
            }

            return redirect()->back()->with('success', 'Logo successfully updated.');
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function companyIndex()
    {
        if(\Auth::user()->can('manage company settings'))
        {
            $settings                = Utility::settings();
            $company_payment_setting = Utility::getCompanyPaymentSetting(\Auth::user()->id);

            return view('settings.company', compact('settings', 'company_payment_setting'));
        }
        else
        {
            return redirect()->back()->with('error', 'Permission denied.');
        }
    }

    public function saveCompanyPaymentSettings(Request $request)
    {

        if(isset($request->is_stripe_enabled) && $request->is_stripe_enabled == 'on')
        {

            $request->validate(
                [
                    'stripe_key' => 'required|string|max:255',
                    'stripe_secret' => 'required|string|max:255',
                ]
            );

            $post['is_stripe_enabled'] = $request->is_stripe_enabled;
            $post['stripe_secret']     = $request->stripe_secret;
            $post['stripe_key']        = $request->stripe_key;
        }

        else
        {
            $post['is_stripe_enabled'] = 'off';
        }

        if(isset($request->is_paypal_enabled) && $request->is_paypal_enabled == 'on')
        {
            $request->validate(
                [
                    'paypal_mode' => 'required',
                    'paypal_client_id' => 'required',
                    'paypal_secret_key' => 'required',
                ]
            );

            $post['is_paypal_enabled'] = $request->is_paypal_enabled;
            $post['paypal_mode']       = $request->paypal_mode;
            $post['paypal_client_id']  = $request->paypal_client_id;
            $post['paypal_secret_key'] = $request->paypal_secret_key;
        }
        else
        {
            $post['is_paypal_enabled'] = 'off';
        }

        if(isset($request->is_paystack_enabled) && $request->is_paystack_enabled == 'on')
        {
            $request->validate(
                [
                    'paystack_public_key' => 'required|string',
                    'paystack_secret_key' => 'required|string',
                ]
            );
            $post['is_paystack_enabled'] = $request->is_paystack_enabled;
            $post['paystack_public_key'] = $request->paystack_public_key;
            $post['paystack_secret_key'] = $request->paystack_secret_key;
        }
        else
        {
            $post['is_paystack_enabled'] = 'off';
        }

        if(isset($request->is_flutterwave_enabled) && $request->is_flutterwave_enabled == 'on')
        {
            $request->validate(
                [
                    'flutterwave_public_key' => 'required|string',
                    'flutterwave_secret_key' => 'required|string',
                ]
            );
            $post['is_flutterwave_enabled'] = $request->is_flutterwave_enabled;
            $post['flutterwave_public_key'] = $request->flutterwave_public_key;
            $post['flutterwave_secret_key'] = $request->flutterwave_secret_key;
        }
        else
        {
            $post['is_flutterwave_enabled'] = 'off';
        }
        if(isset($request->is_razorpay_enabled) && $request->is_razorpay_enabled == 'on')
        {
            $request->validate(
                [
                    'razorpay_public_key' => 'required|string',
                    'razorpay_secret_key' => 'required|string',
                ]
            );
            $post['is_razorpay_enabled'] = $request->is_razorpay_enabled;
            $post['razorpay_public_key'] = $request->razorpay_public_key;
            $post['razorpay_secret_key'] = $request->razorpay_secret_key;
        }
        else
        {
            $post['is_razorpay_enabled'] = 'off';
        }

        if(isset($request->is_mercado_enabled) && $request->is_mercado_enabled == 'on')
        {
            $request->validate(
                [
                    'mercado_access_token' => 'required|string',
                ]
            );
            $post['is_mercado_enabled'] = $request->is_mercado_enabled;
            $post['mercado_access_token']     = $request->mercado_access_token;
            $post['mercado_mode'] = $request->mercado_mode;
        }
        else
        {
            $post['is_mercado_enabled'] = 'off';
        }

        if(isset($request->is_paytm_enabled) && $request->is_paytm_enabled == 'on')
        {
            $request->validate(
                [
                    'paytm_mode' => 'required',
                    'paytm_merchant_id' => 'required|string',
                    'paytm_merchant_key' => 'required|string',
                    'paytm_industry_type' => 'required|string',
                ]
            );
            $post['is_paytm_enabled']    = $request->is_paytm_enabled;
            $post['paytm_mode']          = $request->paytm_mode;
            $post['paytm_merchant_id']   = $request->paytm_merchant_id;
            $post['paytm_merchant_key']  = $request->paytm_merchant_key;
            $post['paytm_industry_type'] = $request->paytm_industry_type;
        }
        else
        {
            $post['is_paytm_enabled'] = 'off';
        }
        if(isset($request->is_mollie_enabled) && $request->is_mollie_enabled == 'on')
        {
            $request->validate(
                [
                    'mollie_api_key' => 'required|string',
                    'mollie_profile_id' => 'required|string',
                    'mollie_partner_id' => 'required',
                ]
            );
            $post['is_mollie_enabled'] = $request->is_mollie_enabled;
            $post['mollie_api_key']    = $request->mollie_api_key;
            $post['mollie_profile_id'] = $request->mollie_profile_id;
            $post['mollie_partner_id'] = $request->mollie_partner_id;
        }
        else
        {
            $post['is_mollie_enabled'] = 'off';
        }

        if(isset($request->is_skrill_enabled) && $request->is_skrill_enabled == 'on')
        {
            $request->validate(
                [
                    'skrill_email' => 'required|email',
                ]
            );
            $post['is_skrill_enabled'] = $request->is_skrill_enabled;
            $post['skrill_email']      = $request->skrill_email;
        }
        else
        {
            $post['is_skrill_enabled'] = 'off';
        }

        if(isset($request->is_coingate_enabled) && $request->is_coingate_enabled == 'on')
        {
            $request->validate(
                [
                    'coingate_mode' => 'required|string',
                    'coingate_auth_token' => 'required|string',
                ]
            );

            $post['is_coingate_enabled'] = $request->is_coingate_enabled;
            $post['coingate_mode']       = $request->coingate_mode;
            $post['coingate_auth_token'] = $request->coingate_auth_token;
        }
        else
        {
            $post['is_coingate_enabled'] = 'off';
        }

        //save paymentwall Detail
        if(isset($request->is_paymentwall_enabled) && $request->is_paymentwall_enabled == 'on')
        {
//            dd('a');
            $request->validate(
                [
                    'paymentwall_public_key' => 'required|string',
                    'paymentwall_secret_key' => 'required|string',
                ]
            );
            $post['is_paymentwall_enabled'] = $request->is_paymentwall_enabled;
            $post['paymentwall_public_key'] = $request->paymentwall_public_key;
            $post['paymentwall_secret_key'] = $request->paymentwall_secret_key;
        }
        else
        {
            // dd('b');
            $post['is_paymentwall_enabled'] = 'off';
        }
        //  dd($post);

        foreach($post as $key => $data)
        {

            $arr = [
                $data,
                $key,
                \Auth::user()->id,
            ];
            \DB::insert(
                'insert into company_payment_settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', $arr
            );

        }

        return redirect()->back()->with('success', __('Payment setting successfully updated.'));
    }

    public function testMail()
    {
        return view('settings.test_mail');
    }


    public function testSendMail(Request $request)
    {
        $validator = \Validator::make($request->all(), ['email' => 'required|email']);
        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        try
        {
            Mail::to($request->email)->send(new testMail());
        }
        catch(\Exception $e)
        {
            $smtp_error = __('E-Mail has been not sent due to SMTP configuration');
        }

        return redirect()->back()->with('success', __('Email send Successfully.') . ((isset($smtp_error)) ? '<br> <span class="text-danger">' . $smtp_error . '</span>' : ''));

    }

    public function adminPaymentSettings($request)
    {
        if(isset($request->is_stripe_enabled) && $request->is_stripe_enabled == 'on')
        {

            $request->validate(
                [
                    'stripe_key' => 'required|string|max:255',
                    'stripe_secret' => 'required|string|max:255',
                ]
            );

            $post['is_stripe_enabled'] = $request->is_stripe_enabled;
            $post['stripe_secret']     = $request->stripe_secret;
            $post['stripe_key']        = $request->stripe_key;
        }

        else
        {
            $post['is_stripe_enabled'] = 'off';
        }
        if(isset($request->is_paypal_enabled) && $request->is_paypal_enabled == 'on')
        {
            $request->validate(
                [
                    'paypal_mode' => 'required',
                    'paypal_client_id' => 'required',
                    'paypal_secret_key' => 'required',
                ]
            );

            $post['is_paypal_enabled'] = $request->is_paypal_enabled;
            $post['paypal_mode']       = $request->paypal_mode;
            $post['paypal_client_id']  = $request->paypal_client_id;
            $post['paypal_secret_key'] = $request->paypal_secret_key;
        }
        else
        {
            $post['is_paypal_enabled'] = 'off';
        }

        if(isset($request->is_paystack_enabled) && $request->is_paystack_enabled == 'on')
        {
            $request->validate(
                [
                    'paystack_public_key' => 'required|string',
                    'paystack_secret_key' => 'required|string',
                ]
            );
            $post['is_paystack_enabled'] = $request->is_paystack_enabled;
            $post['paystack_public_key'] = $request->paystack_public_key;
            $post['paystack_secret_key'] = $request->paystack_secret_key;
        }
        else
        {
            $post['is_paystack_enabled'] = 'off';
        }

        if(isset($request->is_flutterwave_enabled) && $request->is_flutterwave_enabled == 'on')
        {
            $request->validate(
                [
                    'flutterwave_public_key' => 'required|string',
                    'flutterwave_secret_key' => 'required|string',
                ]
            );
            $post['is_flutterwave_enabled'] = $request->is_flutterwave_enabled;
            $post['flutterwave_public_key'] = $request->flutterwave_public_key;
            $post['flutterwave_secret_key'] = $request->flutterwave_secret_key;
        }
        else
        {
            $post['is_flutterwave_enabled'] = 'off';
        }

        if(isset($request->is_razorpay_enabled) && $request->is_razorpay_enabled == 'on')
        {
            $request->validate(
                [
                    'razorpay_public_key' => 'required|string',
                    'razorpay_secret_key' => 'required|string',
                ]
            );
            $post['is_razorpay_enabled'] = $request->is_razorpay_enabled;
            $post['razorpay_public_key'] = $request->razorpay_public_key;
            $post['razorpay_secret_key'] = $request->razorpay_secret_key;
        }
        else
        {
            $post['is_razorpay_enabled'] = 'off';
        }
        if(isset($request->is_mercado_enabled) && $request->is_mercado_enabled == 'on')
        {
            $request->validate(
                [
                    'mercado_access_token' => 'required|string',
                ]
            );
            $post['is_mercado_enabled'] = $request->is_mercado_enabled;
            $post['mercado_access_token']     = $request->mercado_app_id;
        }
        else
        {
            $post['is_mercado_enabled'] = 'off';
        }

        if(isset($request->is_paytm_enabled) && $request->is_paytm_enabled == 'on')
        {
            $request->validate(
                [
                    'paytm_mode' => 'required',
                    'paytm_merchant_id' => 'required|string',
                    'paytm_merchant_key' => 'required|string',
                    'paytm_industry_type' => 'required|string',
                ]
            );
            $post['is_paytm_enabled']    = $request->is_paytm_enabled;
            $post['paytm_mode']          = $request->paytm_mode;
            $post['paytm_merchant_id']   = $request->paytm_merchant_id;
            $post['paytm_merchant_key']  = $request->paytm_merchant_key;
            $post['paytm_industry_type'] = $request->paytm_industry_type;
        }
        else
        {
            $post['is_paytm_enabled'] = 'off';
        }
        if(isset($request->is_mollie_enabled) && $request->is_mollie_enabled == 'on')
        {
            $request->validate(
                [
                    'mollie_api_key' => 'required|string',
                    'mollie_profile_id' => 'required|string',
                    'mollie_partner_id' => 'required',
                ]
            );
            $post['is_mollie_enabled'] = $request->is_mollie_enabled;
            $post['mollie_api_key']    = $request->mollie_api_key;
            $post['mollie_profile_id'] = $request->mollie_profile_id;
            $post['mollie_partner_id'] = $request->mollie_partner_id;
        }
        else
        {
            $post['is_mollie_enabled'] = 'off';
        }

        if(isset($request->is_skrill_enabled) && $request->is_skrill_enabled == 'on')
        {
            $request->validate(
                [
                    'skrill_email' => 'required|email',
                ]
            );
            $post['is_skrill_enabled'] = $request->is_skrill_enabled;
            $post['skrill_email']      = $request->skrill_email;
        }
        else
        {
            $post['is_skrill_enabled'] = 'off';
        }

        if(isset($request->is_coingate_enabled) && $request->is_coingate_enabled == 'on')
        {
            $request->validate(
                [
                    'coingate_mode' => 'required|string',
                    'coingate_auth_token' => 'required|string',
                ]
            );

            $post['is_coingate_enabled'] = $request->is_coingate_enabled;
            $post['coingate_mode']       = $request->coingate_mode;
            $post['coingate_auth_token'] = $request->coingate_auth_token;
        }
        else
        {
            $post['is_coingate_enabled'] = 'off';
        }

        if(isset($request->is_paymentwall_enabled) && $request->is_paymentwall_enabled == 'on')
        {
            $request->validate(
                [
                    'paymentwall_public_key' => 'required|string',
                    'paymentwall_secret_key' => 'required|string',
                ]
            );
            $post['is_paymentwall_enabled'] = $request->is_paymentwall_enabled;
            $post['paymentwall_public_key'] = $request->paymentwall_public_key;
            $post['paymentwall_secret_key'] = $request->paymentwall_secret_key;
        }
        else
        {
            $post['is_paymentwall_enabled'] = 'off';
        }
        foreach($post as $key => $data)
        {

            $arr = [
                $data,
                $key,
                \Auth::user()->id,
            ];
            \DB::insert(
                'insert into admin_payment_settings (`value`, `name`,`created_by`) values (?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`) ', $arr
            );

        }


    }


    public function saveTwilioSettings(Request $request){
        $post = [];
        $post['twilio_sid'] = $request->input('twilio_sid');
        $post['twilio_token'] = $request->input('twilio_token');
        $post['twilio_from'] = $request->input('twilio_from');
        $post['customer_notification'] = $request->has('customer_notification')?$request->input('customer_notification'):0;
        $post['vender_notification'] = $request->has('vender_notification')?$request->input('vender_notification'):0;
        $post['invoice_notification'] = $request->has('invoice_notification')?$request->input('invoice_notification'):0;
        $post['revenue_notification'] = $request->has('revenue_notification')?$request->input('revenue_notification'):0;
        $post['bill_notification'] = $request->has('bill_notification')?$request->input('bill_notification'):0;
        $post['proposal_notification'] = $request->has('proposal_notification')?$request->input('proposal_notification'):0;
        $post['payment_notification'] = $request->has('payment_notification')?$request->input('payment_notification'):0;
        $post['reminder_notification'] = $request->has('reminder_notification')?$request->input('reminder_notification'):0;



        if(isset($post) && !empty($post) && count($post) > 0)
        {
            $created_at = $updated_at = date('Y-m-d H:i:s');

            foreach($post as $key => $data)
            {
                DB::insert(
                    'INSERT INTO settings (`value`, `name`,`created_by`,`created_at`,`updated_at`) values (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`), `updated_at` = VALUES(`updated_at`) ', [
                                                                                                                                                                                                                      $data,
                                                                                                                                                                                                                      $key,
                                                                                                                                                                                                                      Auth::user()->id,
                                                                                                                                                                                                                      $created_at,
                                                                                                                                                                                                                      $updated_at,
                                                                                                                                                                                                                  ]
                );
            }
        }

        return redirect()->back()->with('success', __('Telegram updated successfully.'));
    }

    public function recaptchaSettingStore(Request $request)
    {
        //return redirect()->back()->with('error', __('This operation is not perform due to demo mode.'));

        $user = \Auth::user();
        $rules = [];

        if($request->recaptcha_module == 'yes')
        {
            $rules['google_recaptcha_key'] = 'required|string|max:50';
            $rules['google_recaptcha_secret'] = 'required|string|max:50';
        }

        $validator = \Validator::make(
            $request->all(), $rules
        );
        if($validator->fails())
        {
            $messages = $validator->getMessageBag();

            return redirect()->back()->with('error', $messages->first());
        }

        $arrEnv = [
            'RECAPTCHA_MODULE' => $request->recaptcha_module ?? 'no',
            'NOCAPTCHA_SITEKEY' => $request->google_recaptcha_key,
            'NOCAPTCHA_SECRET' => $request->google_recaptcha_secret,
        ];

        if(Utility::setEnvironmentValue($arrEnv))
        {
            return redirect()->back()->with('success', __('Recaptcha Settings updated successfully'));
        }
        else
        {
            return redirect()->back()->with('error', __('Something is wrong'));
        }
    }


}
