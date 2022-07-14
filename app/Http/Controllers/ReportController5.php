<?php

namespace App\Http\Controllers;

use App\Models\Tax;
use App\Models\Bill;
use App\Models\Order;
use App\Models\Vender;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Revenue;
use App\Models\Utility;
use App\Models\Customer;
// use Barryvdh\DomPDF\PDF;
// use Barryvdh\DomPDF\Facade as PDF;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Exports\DataExport;
use App\Models\BankAccount;
use App\Models\BillProduct;
use App\Models\JournalItem;
use App\Models\StockReport;
use App\Exports\DailyReport;
use Illuminate\Http\Request;
use App\Exports\ReportExport;
use App\Models\ProductIntake;
use App\Models\ChartOfAccount;

use App\Models\InvoiceProduct;
use App\Models\ProductService;
use Illuminate\Support\Carbon;
use App\Exports\FilterUserExport;
use App\Exports\DailyReportExport;
use App\Models\ChartOfAccountType;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\ChartOfAccountSubType;
use App\Models\ProductServiceCategory;


class ReportController extends Controller
{
    public function incomeSummary(Request $request)
    {
        if (\Auth::user()->can('income report')) {
            $account = BankAccount::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('holder_name', 'id');
            $account->prepend('All', '');
            $customer = Customer::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $customer->prepend('Select Customer', '');
            $category = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 1)->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');

            $data['monthList']  = $month = $this->yearMonth();
            $data['yearList']   = $this->yearList();
            $filter['category'] = __('All');
            $filter['customer'] = __('All');


            if (isset($request->year)) {
                $year = $request->year;
            } else {
                $year = date('Y');
            }
            $data['currentYear'] = $year;

            // ------------------------------REVENUE INCOME-----------------------------------
            $incomes = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id')->leftjoin('product_service_categories', 'revenues.category_id', '=', 'product_service_categories.id')->where('product_service_categories.type', '=', 1);
            $incomes->where('revenues.created_by', '=', \Auth::user()->creatorId());
            $incomes->whereRAW('YEAR(date) =?', [$year]);

            if (!empty($request->category)) {
                $incomes->where('category_id', '=', $request->category);
                $cat                = ProductServiceCategory::find($request->category);
                $filter['category'] = !empty($cat) ? $cat->name : '';
            }

            if (!empty($request->customer)) {
                $incomes->where('customer_id', '=', $request->customer);
                $cust               = Customer::find($request->customer);
                $filter['customer'] = !empty($cust) ? $cust->name : '';
            }
            $incomes->groupBy('month', 'year', 'category_id');
            $incomes = $incomes->get();

            $tmpArray = [];
            foreach ($incomes as $income) {
                $tmpArray[$income->category_id][$income->month] = $income->amount;
            }
            $array = [];
            foreach ($tmpArray as $cat_id => $record) {
                $tmp             = [];
                $tmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                $tmp['data']     = [];
                for ($i = 1; $i <= 12; $i++) {
                    $tmp['data'][$i] = array_key_exists($i, $record) ? $record[$i] : 0;
                }
                $array[] = $tmp;
            }


            $incomesData = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year');
            $incomesData->where('revenues.created_by', '=', \Auth::user()->creatorId());
            $incomesData->whereRAW('YEAR(date) =?', [$year]);

            if (!empty($request->category)) {
                $incomesData->where('category_id', '=', $request->category);
            }
            if (!empty($request->customer)) {
                $incomesData->where('customer_id', '=', $request->customer);
            }
            $incomesData->groupBy('month', 'year');
            $incomesData = $incomesData->get();
            $incomeArr   = [];
            foreach ($incomesData as $k => $incomeData) {
                $incomeArr[$incomeData->month] = $incomeData->amount;
            }
            for ($i = 1; $i <= 12; $i++) {
                $incomeTotal[] = array_key_exists($i, $incomeArr) ? $incomeArr[$i] : 0;
            }

            //---------------------------INVOICE INCOME-----------------------------------------------

            $invoices = Invoice::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,invoice_id,id')->where('created_by', \Auth::user()->creatorId())->where('status', '!=', 0);

            $invoices->whereRAW('YEAR(send_date) =?', [$year]);

            if (!empty($request->customer)) {
                $invoices->where('customer_id', '=', $request->customer);
            }

            if (!empty($request->category)) {
                $invoices->where('category_id', '=', $request->category);
            }

            $invoices        = $invoices->get();
            $invoiceTmpArray = [];
            foreach ($invoices as $invoice) {
                $invoiceTmpArray[$invoice->category_id][$invoice->month][] = $invoice->getTotal();
            }

            $invoiceArray = [];
            foreach ($invoiceTmpArray as $cat_id => $record) {

                $invoice             = [];
                $invoice['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                $invoice['data']     = [];
                for ($i = 1; $i <= 12; $i++) {

                    $invoice['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                }
                $invoiceArray[] = $invoice;
            }

            $invoiceTotalArray = [];
            foreach ($invoices as $invoice) {
                $invoiceTotalArray[$invoice->month][] = $invoice->getTotal();
            }
            for ($i = 1; $i <= 12; $i++) {
                $invoiceTotal[] = array_key_exists($i, $invoiceTotalArray) ? array_sum($invoiceTotalArray[$i]) : 0;
            }

            $chartIncomeArr = array_map(
                function () {
                    return array_sum(func_get_args());
                },
                $incomeTotal,
                $invoiceTotal
            );

            $data['chartIncomeArr'] = $chartIncomeArr;
            $data['incomeArr']      = $array;
            $data['invoiceArray']   = $invoiceArray;
            $data['account']        = $account;
            $data['customer']       = $customer;
            $data['category']       = $category;

            $filter['startDateRange'] = 'Jan-' . $year;
            $filter['endDateRange']   = 'Dec-' . $year;


            return view('report.income_summary', compact('filter'), $data);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function expenseSummary(Request $request)
    {
        if (\Auth::user()->can('expense report')) {
            $account = BankAccount::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('holder_name', 'id');
            $account->prepend('All', '');
            $vender = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $vender->prepend('Select Vendor', '');
            $category = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->where('type', '=', 2)->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');

            $data['monthList']  = $month = $this->yearMonth();
            $data['yearList']   = $this->yearList();
            $filter['category'] = __('All');
            $filter['vender']   = __('All');

            if (isset($request->year)) {
                $year = $request->year;
            } else {
                $year = date('Y');
            }
            $data['currentYear'] = $year;

            //   -----------------------------------------PAYMENT EXPENSE ------------------------------------------------------------
            $expenses = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id')->leftjoin('product_service_categories', 'payments.category_id', '=', 'product_service_categories.id')->where('product_service_categories.type', '=', 2);
            $expenses->where('payments.created_by', '=', \Auth::user()->creatorId());
            $expenses->whereRAW('YEAR(date) =?', [$year]);

            if (!empty($request->category)) {
                $expenses->where('category_id', '=', $request->category);
                $cat                = ProductServiceCategory::find($request->category);
                $filter['category'] = !empty($cat) ? $cat->name : '';
            }
            if (!empty($request->vender)) {
                $expenses->where('vender_id', '=', $request->vender);

                $vend             = Vender::find($request->vender);
                $filter['vender'] = !empty($vend) ? $vend->name : '';
            }

            $expenses->groupBy('month', 'year', 'category_id');
            $expenses = $expenses->get();
            $tmpArray = [];
            foreach ($expenses as $expense) {
                $tmpArray[$expense->category_id][$expense->month] = $expense->amount;
            }
            $array = [];
            foreach ($tmpArray as $cat_id => $record) {
                $tmp             = [];
                $tmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                $tmp['data']     = [];
                for ($i = 1; $i <= 12; $i++) {
                    $tmp['data'][$i] = array_key_exists($i, $record) ? $record[$i] : 0;
                }
                $array[] = $tmp;
            }
            $expensesData = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year');
            $expensesData->where('payments.created_by', '=', \Auth::user()->creatorId());
            $expensesData->whereRAW('YEAR(date) =?', [$year]);

            if (!empty($request->category)) {
                $expensesData->where('category_id', '=', $request->category);
            }
            if (!empty($request->vender)) {
                $expensesData->where('vender_id', '=', $request->vender);
            }
            $expensesData->groupBy('month', 'year');
            $expensesData = $expensesData->get();

            $expenseArr = [];
            foreach ($expensesData as $k => $expenseData) {
                $expenseArr[$expenseData->month] = $expenseData->amount;
            }
            for ($i = 1; $i <= 12; $i++) {
                $expenseTotal[] = array_key_exists($i, $expenseArr) ? $expenseArr[$i] : 0;
            }

            //     ------------------------------------BILL EXPENSE----------------------------------------------------

            $bills = Bill::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,bill_id,id')->where('created_by', \Auth::user()->creatorId())->where('status', '!=', 0);
            $bills->whereRAW('YEAR(send_date) =?', [$year]);

            if (!empty($request->vender)) {
                $bills->where('vender_id', '=', $request->vender);
            }

            if (!empty($request->category)) {
                $bills->where('category_id', '=', $request->category);
            }
            $bills        = $bills->get();
            $billTmpArray = [];
            foreach ($bills as $bill) {
                $billTmpArray[$bill->category_id][$bill->month][] = $bill->getTotal();
            }

            $billArray = [];
            foreach ($billTmpArray as $cat_id => $record) {

                $bill             = [];
                $bill['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                $bill['data']     = [];
                for ($i = 1; $i <= 12; $i++) {

                    $bill['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                }
                $billArray[] = $bill;
            }

            $billTotalArray = [];
            foreach ($bills as $bill) {
                $billTotalArray[$bill->month][] = $bill->getTotal();
            }
            for ($i = 1; $i <= 12; $i++) {
                $billTotal[] = array_key_exists($i, $billTotalArray) ? array_sum($billTotalArray[$i]) : 0;
            }

            $chartExpenseArr = array_map(
                function () {
                    return array_sum(func_get_args());
                },
                $expenseTotal,
                $billTotal
            );


            $data['chartExpenseArr'] = $chartExpenseArr;
            $data['expenseArr']      = $array;
            $data['billArray']       = $billArray;
            $data['account']         = $account;
            $data['vender']          = $vender;
            $data['category']        = $category;

            $filter['startDateRange'] = 'Jan-' . $year;
            $filter['endDateRange']   = 'Dec-' . $year;

            return view('report.expense_summary', compact('filter'), $data);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function incomeVsExpenseSummary(Request $request)
    {
        if (\Auth::user()->can('income vs expense report')) {
            $account = BankAccount::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('holder_name', 'id');
            $account->prepend('All', '');
            $vender = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $vender->prepend('Select Vendor', '');
            $customer = Customer::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'id');
            $customer->prepend('Select Customer', '');

            $category = ProductServiceCategory::where('created_by', '=', \Auth::user()->creatorId())->whereIn(
                'type',
                [
                    1,
                    2,
                ]
            )->get()->pluck('name', 'id');
            $category->prepend('Select Category', '');

            $data['monthList'] = $month = $this->yearMonth();
            $data['yearList']  = $this->yearList();

            $filter['category'] = __('All');
            $filter['customer'] = __('All');
            $filter['vender']   = __('All');

            if (isset($request->year)) {
                $year = $request->year;
            } else {
                $year = date('Y');
            }
            $data['currentYear'] = $year;

            // ------------------------------TOTAL PAYMENT EXPENSE-----------------------------------------------------------
            $expensesData = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year');
            $expensesData->where('payments.created_by', '=', \Auth::user()->creatorId());
            $expensesData->whereRAW('YEAR(date) =?', [$year]);

            if (!empty($request->category)) {
                $expensesData->where('category_id', '=', $request->category);
                $cat                = ProductServiceCategory::find($request->category);
                $filter['category'] = !empty($cat) ? $cat->name : '';
            }
            if (!empty($request->vender)) {
                $expensesData->where('vender_id', '=', $request->vender);

                $vend             = Vender::find($request->vender);
                $filter['vender'] = !empty($vend) ? $vend->name : '';
            }
            $expensesData->groupBy('month', 'year');
            $expensesData = $expensesData->get();

            $expenseArr = [];
            foreach ($expensesData as $k => $expenseData) {
                $expenseArr[$expenseData->month] = $expenseData->amount;
            }

            // ------------------------------TOTAL BILL EXPENSE-----------------------------------------------------------

            $bills = Bill::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,bill_id,id')->where('created_by', \Auth::user()->creatorId())->where('status', '!=', 0);
            $bills->whereRAW('YEAR(send_date) =?', [$year]);

            if (!empty($request->vender)) {
                $bills->where('vender_id', '=', $request->vender);
            }

            if (!empty($request->category)) {
                $bills->where('category_id', '=', $request->category);
            }

            $bills        = $bills->get();
            $billTmpArray = [];
            foreach ($bills as $bill) {
                $billTmpArray[$bill->category_id][$bill->month][] = $bill->getTotal();
            }
            $billArray = [];
            foreach ($billTmpArray as $cat_id => $record) {
                $bill             = [];
                $bill['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                $bill['data']     = [];
                for ($i = 1; $i <= 12; $i++) {

                    $bill['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                }
                $billArray[] = $bill;
            }

            $billTotalArray = [];
            foreach ($bills as $bill) {
                $billTotalArray[$bill->month][] = $bill->getTotal();
            }


            // ------------------------------TOTAL REVENUE INCOME-----------------------------------------------------------

            $incomesData = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year');
            $incomesData->where('revenues.created_by', '=', \Auth::user()->creatorId());
            $incomesData->whereRAW('YEAR(date) =?', [$year]);

            if (!empty($request->category)) {
                $incomesData->where('category_id', '=', $request->category);
            }
            if (!empty($request->customer)) {
                $incomesData->where('customer_id', '=', $request->customer);
                $cust               = Customer::find($request->customer);
                $filter['customer'] = !empty($cust) ? $cust->name : '';
            }
            $incomesData->groupBy('month', 'year');
            $incomesData = $incomesData->get();
            $incomeArr   = [];
            foreach ($incomesData as $k => $incomeData) {
                $incomeArr[$incomeData->month] = $incomeData->amount;
            }

            // ------------------------------TOTAL INVOICE INCOME-----------------------------------------------------------
            $invoices = Invoice::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,invoice_id,id')->where('created_by', \Auth::user()->creatorId())->where('status', '!=', 0);
            $invoices->whereRAW('YEAR(send_date) =?', [$year]);
            if (!empty($request->customer)) {
                $invoices->where('customer_id', '=', $request->customer);
            }
            if (!empty($request->category)) {
                $invoices->where('category_id', '=', $request->category);
            }
            $invoices        = $invoices->get();
            $invoiceTmpArray = [];
            foreach ($invoices as $invoice) {
                $invoiceTmpArray[$invoice->category_id][$invoice->month][] = $invoice->getTotal();
            }

            $invoiceArray = [];
            foreach ($invoiceTmpArray as $cat_id => $record) {

                $invoice             = [];
                $invoice['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                $invoice['data']     = [];
                for ($i = 1; $i <= 12; $i++) {

                    $invoice['data'][$i] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                }
                $invoiceArray[] = $invoice;
            }

            $invoiceTotalArray = [];
            foreach ($invoices as $invoice) {
                $invoiceTotalArray[$invoice->month][] = $invoice->getTotal();
            }
            //        ----------------------------------------------------------------------------------------------------

            for ($i = 1; $i <= 12; $i++) {
                $paymentExpenseTotal[] = array_key_exists($i, $expenseArr) ? $expenseArr[$i] : 0;
                $billExpenseTotal[]    = array_key_exists($i, $billTotalArray) ? array_sum($billTotalArray[$i]) : 0;

                $RevenueIncomeTotal[] = array_key_exists($i, $incomeArr) ? $incomeArr[$i] : 0;
                $invoiceIncomeTotal[] = array_key_exists($i, $invoiceTotalArray) ? array_sum($invoiceTotalArray[$i]) : 0;
            }

            $totalIncome = array_map(
                function () {
                    return array_sum(func_get_args());
                },
                $RevenueIncomeTotal,
                $invoiceIncomeTotal
            );

            $totalExpense = array_map(
                function () {
                    return array_sum(func_get_args());
                },
                $paymentExpenseTotal,
                $billExpenseTotal
            );

            $profit = [];
            $keys   = array_keys($totalIncome + $totalExpense);
            foreach ($keys as $v) {
                $profit[$v] = (empty($totalIncome[$v]) ? 0 : $totalIncome[$v]) - (empty($totalExpense[$v]) ? 0 : $totalExpense[$v]);
            }


            $data['paymentExpenseTotal'] = $paymentExpenseTotal;
            $data['billExpenseTotal']    = $billExpenseTotal;
            $data['revenueIncomeTotal']  = $RevenueIncomeTotal;
            $data['invoiceIncomeTotal']  = $invoiceIncomeTotal;
            $data['profit']              = $profit;
            $data['account']             = $account;
            $data['vender']              = $vender;
            $data['customer']            = $customer;
            $data['category']            = $category;

            $filter['startDateRange'] = 'Jan-' . $year;
            $filter['endDateRange']   = 'Dec-' . $year;

            return view('report.income_vs_expense_summary', compact('filter'), $data);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function taxSummary(Request $request)
    {

        if (\Auth::user()->can('tax report')) {
            $data['monthList'] = $month = $this->yearMonth();
            $data['yearList']  = $this->yearList();
            $data['taxList']   = $taxList = Tax::where('created_by', \Auth::user()->creatorId())->get();

            if (isset($request->year)) {
                $year = $request->year;
            } else {
                $year = date('Y');
            }

            $data['currentYear'] = $year;

            $invoiceProducts = InvoiceProduct::selectRaw('invoice_products.* ,MONTH(invoice_products.created_at) as month,YEAR(invoice_products.created_at) as year')->leftjoin('product_services', 'invoice_products.product_id', '=', 'product_services.id')->whereRaw('YEAR(invoice_products.created_at) =?', [$year])->where('product_services.created_by', '=', \Auth::user()->creatorId())->get();

            $incomeTaxesData = [];
            foreach ($invoiceProducts as $invoiceProduct) {
                $incomeTax   = [];
                $incomeTaxes = Utility::tax($invoiceProduct->tax);

                foreach ($incomeTaxes as $taxe) {
                    $taxDataPrice           = Utility::taxRate(!empty($taxe) ? ($taxe->rate) : 0, $invoiceProduct->price, $invoiceProduct->quantity);
                    $incomeTax[!empty($taxe) ? ($taxe->name) : ''] = $taxDataPrice;
                }
                $incomeTaxesData[$invoiceProduct->month][] = $incomeTax;
            }

            $income = [];
            foreach ($incomeTaxesData as $month => $incomeTaxx) {
                $incomeTaxRecord = [];
                foreach ($incomeTaxx as $k => $record) {
                    foreach ($record as $incomeTaxName => $incomeTaxAmount) {
                        if (array_key_exists($incomeTaxName, $incomeTaxRecord)) {
                            $incomeTaxRecord[$incomeTaxName] += $incomeTaxAmount;
                        } else {
                            $incomeTaxRecord[$incomeTaxName] = $incomeTaxAmount;
                        }
                    }
                    $income['data'][$month] = $incomeTaxRecord;
                }
            }

            foreach ($income as $incomeMonth => $incomeTaxData) {
                $incomeData = [];
                for ($i = 1; $i <= 12; $i++) {
                    $incomeData[$i] = array_key_exists($i, $incomeTaxData) ? $incomeTaxData[$i] : 0;
                }
            }

            $incomes = [];
            if (isset($incomeData) && !empty($incomeData)) {
                foreach ($taxList as $taxArr) {
                    foreach ($incomeData as $month => $tax) {
                        if ($tax != 0) {
                            if (isset($tax[$taxArr->name])) {
                                $incomes[$taxArr->name][$month] = $tax[$taxArr->name];
                            } else {
                                $incomes[$taxArr->name][$month] = 0;
                            }
                        } else {
                            $incomes[$taxArr->name][$month] = 0;
                        }
                    }
                }
            }


            $billProducts = BillProduct::selectRaw('bill_products.* ,MONTH(bill_products.created_at) as month,YEAR(bill_products.created_at) as year')->leftjoin('product_services', 'bill_products.product_id', '=', 'product_services.id')->whereRaw('YEAR(bill_products.created_at) =?', [$year])->where('product_services.created_by', '=', \Auth::user()->creatorId())->get();

            $expenseTaxesData = [];
            foreach ($billProducts as $billProduct) {
                $billTax   = [];
                $billTaxes = Utility::tax($billProduct->tax);
                foreach ($billTaxes as $taxe) {
                    $taxDataPrice         = Utility::taxRate($taxe->rate, $billProduct->price, $billProduct->quantity);
                    $billTax[$taxe->name] = $taxDataPrice;
                }
                $expenseTaxesData[$billProduct->month][] = $billTax;
            }

            $bill = [];
            foreach ($expenseTaxesData as $month => $billTaxx) {
                $billTaxRecord = [];
                foreach ($billTaxx as $k => $record) {
                    foreach ($record as $billTaxName => $billTaxAmount) {
                        if (array_key_exists($billTaxName, $billTaxRecord)) {
                            $billTaxRecord[$billTaxName] += $billTaxAmount;
                        } else {
                            $billTaxRecord[$billTaxName] = $billTaxAmount;
                        }
                    }
                    $bill['data'][$month] = $billTaxRecord;
                }
            }

            foreach ($bill as $billMonth => $billTaxData) {
                $billData = [];
                for ($i = 1; $i <= 12; $i++) {
                    $billData[$i] = array_key_exists($i, $billTaxData) ? $billTaxData[$i] : 0;
                }
            }
            $expenses = [];
            if (isset($billData) && !empty($billData)) {

                foreach ($taxList as $taxArr) {
                    foreach ($billData as $month => $tax) {
                        if ($tax != 0) {
                            if (isset($tax[$taxArr->name])) {
                                $expenses[$taxArr->name][$month] = $tax[$taxArr->name];
                            } else {
                                $expenses[$taxArr->name][$month] = 0;
                            }
                        } else {
                            $expenses[$taxArr->name][$month] = 0;
                        }
                    }
                }
            }

            $data['expenses'] = $expenses;
            $data['incomes']  = $incomes;

            $filter['startDateRange'] = 'Jan-' . $year;
            $filter['endDateRange']   = 'Dec-' . $year;

            return view('report.tax_summary', compact('filter'), $data);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function profitLossSummary(Request $request)
    {

        if (\Auth::user()->can('loss & profit report')) {
            $data['month']     = [
                'Jan-Mar',
                'Apr-Jun',
                'Jul-Sep',
                'Oct-Dec',
                'Total',
            ];
            $data['monthList'] = $month = $this->yearMonth();
            $data['yearList']  = $this->yearList();

            if (isset($request->year)) {
                $year = $request->year;
            } else {
                $year = date('Y');
            }
            $data['currentYear'] = $year;

            // -------------------------------REVENUE INCOME-------------------------------------------------

            $incomes = Revenue::selectRaw('sum(revenues.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id');
            $incomes->where('created_by', '=', \Auth::user()->creatorId());
            $incomes->whereRAW('YEAR(date) =?', [$year]);
            $incomes->groupBy('month', 'year', 'category_id');
            $incomes        = $incomes->get();
            $tmpIncomeArray = [];
            foreach ($incomes as $income) {
                $tmpIncomeArray[$income->category_id][$income->month] = $income->amount;
            }

            $incomeCatAmount_1  = $incomeCatAmount_2 = $incomeCatAmount_3 = $incomeCatAmount_4 = 0;
            $revenueIncomeArray = array();
            foreach ($tmpIncomeArray as $cat_id => $record) {

                $tmp             = [];
                $tmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                $sumData         = [];
                for ($i = 1; $i <= 12; $i++) {
                    $sumData[] = array_key_exists($i, $record) ? $record[$i] : 0;
                }

                $month_1 = array_slice($sumData, 0, 3);
                $month_2 = array_slice($sumData, 3, 3);
                $month_3 = array_slice($sumData, 6, 3);
                $month_4 = array_slice($sumData, 9, 3);


                $incomeData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                $incomeData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                $incomeData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                $incomeData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                $incomeData[__('Total')]   = array_sum(
                    array(
                        $sum_1,
                        $sum_2,
                        $sum_3,
                        $sum_4,
                    )
                );

                $incomeCatAmount_1 += $sum_1;
                $incomeCatAmount_2 += $sum_2;
                $incomeCatAmount_3 += $sum_3;
                $incomeCatAmount_4 += $sum_4;

                $data['month'] = array_keys($incomeData);
                $tmp['amount'] = array_values($incomeData);

                $revenueIncomeArray[] = $tmp;
            }

            $data['incomeCatAmount'] = $incomeCatAmount = [
                $incomeCatAmount_1,
                $incomeCatAmount_2,
                $incomeCatAmount_3,
                $incomeCatAmount_4,
                array_sum(
                    array(
                        $incomeCatAmount_1,
                        $incomeCatAmount_2,
                        $incomeCatAmount_3,
                        $incomeCatAmount_4,
                    )
                ),
            ];

            $data['revenueIncomeArray'] = $revenueIncomeArray;

            //-----------------------INVOICE INCOME---------------------------------------------

            $invoices = Invoice::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,invoice_id,id')->where('created_by', \Auth::user()->creatorId())->where('status', '!=', 0);
            $invoices->whereRAW('YEAR(send_date) =?', [$year]);
            if (!empty($request->customer)) {
                $invoices->where('customer_id', '=', $request->customer);
            }
            $invoices        = $invoices->get();
            $invoiceTmpArray = [];
            foreach ($invoices as $invoice) {
                $invoiceTmpArray[$invoice->category_id][$invoice->month][] = $invoice->getTotal();
            }

            $invoiceCatAmount_1 = $invoiceCatAmount_2 = $invoiceCatAmount_3 = $invoiceCatAmount_4 = 0;
            $invoiceIncomeArray = array();
            foreach ($invoiceTmpArray as $cat_id => $record) {

                $invoiceTmp             = [];
                $invoiceTmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                $invoiceSumData         = [];
                for ($i = 1; $i <= 12; $i++) {
                    $invoiceSumData[] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                }

                $month_1                          = array_slice($invoiceSumData, 0, 3);
                $month_2                          = array_slice($invoiceSumData, 3, 3);
                $month_3                          = array_slice($invoiceSumData, 6, 3);
                $month_4                          = array_slice($invoiceSumData, 9, 3);
                $invoiceIncomeData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                $invoiceIncomeData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                $invoiceIncomeData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                $invoiceIncomeData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                $invoiceIncomeData[__('Total')]   = array_sum(
                    array(
                        $sum_1,
                        $sum_2,
                        $sum_3,
                        $sum_4,
                    )
                );
                $invoiceCatAmount_1               += $sum_1;
                $invoiceCatAmount_2               += $sum_2;
                $invoiceCatAmount_3               += $sum_3;
                $invoiceCatAmount_4               += $sum_4;

                $invoiceTmp['amount'] = array_values($invoiceIncomeData);

                $invoiceIncomeArray[] = $invoiceTmp;
            }

            $data['invoiceIncomeCatAmount'] = $invoiceIncomeCatAmount = [
                $invoiceCatAmount_1,
                $invoiceCatAmount_2,
                $invoiceCatAmount_3,
                $invoiceCatAmount_4,
                array_sum(
                    array(
                        $invoiceCatAmount_1,
                        $invoiceCatAmount_2,
                        $invoiceCatAmount_3,
                        $invoiceCatAmount_4,
                    )
                ),
            ];


            $data['invoiceIncomeArray'] = $invoiceIncomeArray;

            $data['totalIncome'] = $totalIncome = array_map(
                function () {
                    return array_sum(func_get_args());
                },
                $invoiceIncomeCatAmount,
                $incomeCatAmount
            );

            //---------------------------------PAYMENT EXPENSE-----------------------------------

            $expenses = Payment::selectRaw('sum(payments.amount) as amount,MONTH(date) as month,YEAR(date) as year,category_id');
            $expenses->where('created_by', '=', \Auth::user()->creatorId());
            $expenses->whereRAW('YEAR(date) =?', [$year]);
            $expenses->groupBy('month', 'year', 'category_id');
            $expenses = $expenses->get();

            $tmpExpenseArray = [];
            foreach ($expenses as $expense) {
                $tmpExpenseArray[$expense->category_id][$expense->month] = $expense->amount;
            }

            $expenseArray       = [];
            $expenseCatAmount_1 = $expenseCatAmount_2 = $expenseCatAmount_3 = $expenseCatAmount_4 = 0;
            foreach ($tmpExpenseArray as $cat_id => $record) {
                $tmp             = [];
                $tmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                $expenseSumData  = [];
                for ($i = 1; $i <= 12; $i++) {
                    $expenseSumData[] = array_key_exists($i, $record) ? $record[$i] : 0;
                }

                $month_1 = array_slice($expenseSumData, 0, 3);
                $month_2 = array_slice($expenseSumData, 3, 3);
                $month_3 = array_slice($expenseSumData, 6, 3);
                $month_4 = array_slice($expenseSumData, 9, 3);

                $expenseData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                $expenseData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                $expenseData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                $expenseData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                $expenseData[__('Total')]   = array_sum(
                    array(
                        $sum_1,
                        $sum_2,
                        $sum_3,
                        $sum_4,
                    )
                );

                $expenseCatAmount_1 += $sum_1;
                $expenseCatAmount_2 += $sum_2;
                $expenseCatAmount_3 += $sum_3;
                $expenseCatAmount_4 += $sum_4;

                $data['month'] = array_keys($expenseData);
                $tmp['amount'] = array_values($expenseData);

                $expenseArray[] = $tmp;
            }

            $data['expenseCatAmount'] = $expenseCatAmount = [
                $expenseCatAmount_1,
                $expenseCatAmount_2,
                $expenseCatAmount_3,
                $expenseCatAmount_4,
                array_sum(
                    array(
                        $expenseCatAmount_1,
                        $expenseCatAmount_2,
                        $expenseCatAmount_3,
                        $expenseCatAmount_4,
                    )
                ),
            ];
            $data['expenseArray']     = $expenseArray;

            //    ----------------------------EXPENSE BILL-----------------------------------------------------------------------

            $bills = Bill::selectRaw('MONTH(send_date) as month,YEAR(send_date) as year,category_id,bill_id,id')->where('created_by', \Auth::user()->creatorId())->where('status', '!=', 0);
            $bills->whereRAW('YEAR(send_date) =?', [$year]);
            if (!empty($request->customer)) {
                $bills->where('vender_id', '=', $request->vender);
            }
            $bills        = $bills->get();
            $billTmpArray = [];
            foreach ($bills as $bill) {
                $billTmpArray[$bill->category_id][$bill->month][] = $bill->getTotal();
            }

            $billExpenseArray       = [];
            $billExpenseCatAmount_1 = $billExpenseCatAmount_2 = $billExpenseCatAmount_3 = $billExpenseCatAmount_4 = 0;
            foreach ($billTmpArray as $cat_id => $record) {
                $billTmp             = [];
                $billTmp['category'] = !empty(ProductServiceCategory::where('id', '=', $cat_id)->first()) ? ProductServiceCategory::where('id', '=', $cat_id)->first()->name : '';
                $billExpensSumData   = [];
                for ($i = 1; $i <= 12; $i++) {
                    $billExpensSumData[] = array_key_exists($i, $record) ? array_sum($record[$i]) : 0;
                }

                $month_1 = array_slice($billExpensSumData, 0, 3);
                $month_2 = array_slice($billExpensSumData, 3, 3);
                $month_3 = array_slice($billExpensSumData, 6, 3);
                $month_4 = array_slice($billExpensSumData, 9, 3);

                $billExpenseData[__('Jan-Mar')] = $sum_1 = array_sum($month_1);
                $billExpenseData[__('Apr-Jun')] = $sum_2 = array_sum($month_2);
                $billExpenseData[__('Jul-Sep')] = $sum_3 = array_sum($month_3);
                $billExpenseData[__('Oct-Dec')] = $sum_4 = array_sum($month_4);
                $billExpenseData[__('Total')]   = array_sum(
                    array(
                        $sum_1,
                        $sum_2,
                        $sum_3,
                        $sum_4,
                    )
                );

                $billExpenseCatAmount_1 += $sum_1;
                $billExpenseCatAmount_2 += $sum_2;
                $billExpenseCatAmount_3 += $sum_3;
                $billExpenseCatAmount_4 += $sum_4;

                $data['month']     = array_keys($billExpenseData);
                $billTmp['amount'] = array_values($billExpenseData);

                $billExpenseArray[] = $billTmp;
            }

            $data['billExpenseCatAmount'] = $billExpenseCatAmount = [
                $billExpenseCatAmount_1,
                $billExpenseCatAmount_2,
                $billExpenseCatAmount_3,
                $billExpenseCatAmount_4,
                array_sum(
                    array(
                        $billExpenseCatAmount_1,
                        $billExpenseCatAmount_2,
                        $billExpenseCatAmount_3,
                        $billExpenseCatAmount_4,
                    )
                ),
            ];

            $data['billExpenseArray'] = $billExpenseArray;


            $data['totalExpense'] = $totalExpense = array_map(
                function () {
                    return array_sum(func_get_args());
                },
                $billExpenseCatAmount,
                $expenseCatAmount
            );


            foreach ($totalIncome as $k => $income) {
                $netProfit[] = $income - $totalExpense[$k];
            }
            $data['netProfitArray'] = $netProfit;

            $filter['startDateRange'] = 'Jan-' . $year;
            $filter['endDateRange']   = 'Dec-' . $year;

            return view('report.profit_loss_summary', compact('filter'), $data);
        } else {
            return redirect()->back()->with('error', __('Permission denied.'));
        }
    }

    public function yearMonth()
    {

        $month[] = __('January');
        $month[] = __('February');
        $month[] = __('March');
        $month[] = __('April');
        $month[] = __('May');
        $month[] = __('June');
        $month[] = __('July');
        $month[] = __('August');
        $month[] = __('September');
        $month[] = __('October');
        $month[] = __('November');
        $month[] = __('December');

        return $month;
    }

    public function yearList()
    {
        $starting_year = date('Y', strtotime('-5 year'));
        $ending_year   = date('Y');

        foreach (range($ending_year, $starting_year) as $year) {
            $years[$year] = $year;
        }

        return $years;
    }

    public function invoiceSummary(Request $request)
    {

        if (\Auth::user()->can('invoice report')) {
            $filter['customer'] = __('All');
            $filter['status']   = __('All');


            $customer = Customer::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'customer_id');
            $customer->prepend('Select Customer', '');
            $status = Invoice::$statues;

            $invoices = Invoice::selectRaw('invoices.*,MONTH(send_date) as month,YEAR(send_date) as year');

            if ($request->status != '') {
                $invoices->where('status', $request->status);

                $filter['status'] = Invoice::$statues[$request->status];
            } else {
                $invoices->where('status', '!=', 0);
            }

            $invoices->where('created_by', '=', \Auth::user()->creatorId());

            if (!empty($request->start_month) && !empty($request->end_month)) {
                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);
            } else {
                $start = strtotime(date('Y-01'));
                $end   = strtotime(date('Y-12'));
            }

            $invoices->where('send_date', '>=', date('Y-m-01', $start))->where('send_date', '<=', date('Y-m-t', $end));


            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange']   = date('M-Y', $end);


            if (!empty($request->customer)) {
                $invoices->where('customer_id', $request->customer);
                $cust = Customer::find($request->customer);

                $filter['customer'] = !empty($cust) ? $cust->name : '';
            }


            $invoices = $invoices->get();


            $totalInvoice      = 0;
            $totalDueInvoice   = 0;
            $invoiceTotalArray = [];
            foreach ($invoices as $invoice) {
                $totalInvoice    += $invoice->getTotal();
                $totalDueInvoice += $invoice->getDue();

                $invoiceTotalArray[$invoice->month][] = $invoice->getTotal();
            }
            $totalPaidInvoice = $totalInvoice - $totalDueInvoice;

            for ($i = 1; $i <= 12; $i++) {
                $invoiceTotal[] = array_key_exists($i, $invoiceTotalArray) ? array_sum($invoiceTotalArray[$i]) : 0;
            }

            $monthList = $month = $this->yearMonth();

            return view('report.invoice_report', compact('invoices', 'customer', 'status', 'totalInvoice', 'totalDueInvoice', 'totalPaidInvoice', 'invoiceTotal', 'monthList', 'filter'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function billSummary(Request $request)
    {
        if (\Auth::user()->can('bill report')) {

            $filter['vender'] = __('All');
            $filter['status'] = __('All');


            $vender = Vender::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('name', 'vender_id');
            $vender->prepend('Select Vendor', '');
            $status = Bill::$statues;

            $bills = Bill::selectRaw('bills.*,MONTH(send_date) as month,YEAR(send_date) as year');

            if (!empty($request->start_month) && !empty($request->end_month)) {
                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);
            } else {
                $start = strtotime(date('Y-01'));
                $end   = strtotime(date('Y-12'));
            }

            $bills->where('send_date', '>=', date('Y-m-01', $start))->where('send_date', '<=', date('Y-m-t', $end));

            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange']   = date('M-Y', $end);


            if (!empty($request->vender)) {
                $bills->where('vender_id', $request->vender);
                $vend = Vender::find($request->vender);

                $filter['vender'] = !empty($vend) ? $vend->name : '';
            }

            if ($request->status != '') {
                $bills->where('status', '=', $request->status);

                $filter['status'] = Bill::$statues[$request->status];
            } else {
                $bills->where('status', '!=', 0);
            }

            $bills->where('created_by', '=', \Auth::user()->creatorId());
            $bills = $bills->get();


            $totalBill      = 0;
            $totalDueBill   = 0;
            $billTotalArray = [];
            foreach ($bills as $bill) {
                $totalBill    += $bill->getTotal();
                $totalDueBill += $bill->getDue();

                $billTotalArray[$bill->month][] = $bill->getTotal();
            }
            $totalPaidBill = $totalBill - $totalDueBill;

            for ($i = 1; $i <= 12; $i++) {
                $billTotal[] = array_key_exists($i, $billTotalArray) ? array_sum($billTotalArray[$i]) : 0;
            }

            $monthList = $month = $this->yearMonth();

            return view('report.bill_report', compact('bills', 'vender', 'status', 'totalBill', 'totalDueBill', 'totalPaidBill', 'billTotal', 'monthList', 'filter'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }


    public function accountStatement(Request $request)
    {

        if (\Auth::user()->can('statement report')) {

            $filter['account']             = __('All');
            $filter['type']                = __('Revenue');
            $reportData['revenues']        = '';
            $reportData['payments']        = '';
            $reportData['revenueAccounts'] = '';
            $reportData['paymentAccounts'] = '';

            $account = BankAccount::where('created_by', '=', \Auth::user()->creatorId())->get()->pluck('holder_name', 'id');
            $account->prepend('All', '');

            $types = [
                'revenue' => __('Revenue'),
                'payment' => __('Payment'),
            ];

            if ($request->type == 'revenue' || !isset($request->type)) {

                $revenueAccounts = Revenue::select('bank_accounts.id', 'bank_accounts.holder_name', 'bank_accounts.bank_name')->leftjoin('bank_accounts', 'revenues.account_id', '=', 'bank_accounts.id')->groupBy('revenues.account_id')->selectRaw('sum(amount) as total')->where('revenues.created_by', '=', \Auth::user()->creatorId());

                $revenues = Revenue::where('revenues.created_by', '=', \Auth::user()->creatorId())->orderBy('id', 'desc');
            }

            if ($request->type == 'payment') {
                $paymentAccounts = Payment::select('bank_accounts.id', 'bank_accounts.holder_name', 'bank_accounts.bank_name')->leftjoin('bank_accounts', 'payments.account_id', '=', 'bank_accounts.id')->groupBy('payments.account_id')->selectRaw('sum(amount) as total')->where('payments.created_by', '=', \Auth::user()->creatorId());

                $payments = Payment::where('payments.created_by', '=', \Auth::user()->creatorId())->orderBy('id', 'desc');
            }


            if (!empty($request->start_month) && !empty($request->end_month)) {
                $start = strtotime($request->start_month);
                $end   = strtotime($request->end_month);
            } else {
                $start = strtotime(date('Y-m'));
                $end   = strtotime(date('Y-m', strtotime("-5 month")));
            }


            $currentdate = $start;
            while ($currentdate <= $end) {
                $data['month'] = date('m', $currentdate);
                $data['year']  = date('Y', $currentdate);

                if ($request->type == 'revenue' || !isset($request->type)) {
                    $revenues->Orwhere(
                        function ($query) use ($data) {
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                            $query->where('revenues.created_by', '=', \Auth::user()->creatorId());
                        }
                    );

                    $revenueAccounts->Orwhere(
                        function ($query) use ($data) {
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                            $query->where('revenues.created_by', '=', \Auth::user()->creatorId());
                        }
                    );
                }

                if ($request->type == 'payment') {
                    $paymentAccounts->Orwhere(
                        function ($query) use ($data) {
                            $query->whereMonth('date', $data['month'])->whereYear('date', $data['year']);
                            $query->where('payments.created_by', '=', \Auth::user()->creatorId());
                        }
                    );
                }


                $currentdate = strtotime('+1 month', $currentdate);
            }

            if (!empty($request->account)) {
                if ($request->type == 'revenue' || !isset($request->type)) {
                    $revenues->where('account_id', $request->account);
                    $revenues->where('revenues.created_by', '=', \Auth::user()->creatorId());
                    $revenueAccounts->where('account_id', $request->account);
                    $revenueAccounts->where('revenues.created_by', '=', \Auth::user()->creatorId());
                }

                if ($request->type == 'payment') {
                    $payments->where('account_id', $request->account);
                    $payments->where('payments.created_by', '=', \Auth::user()->creatorId());

                    $paymentAccounts->where('account_id', $request->account);
                    $paymentAccounts->where('payments.created_by', '=', \Auth::user()->creatorId());
                }


                $bankAccount       = BankAccount::find($request->account);
                $filter['account'] = !empty($bankAccount) ? $bankAccount->holder_name . ' - ' . $bankAccount->bank_name : '';
                if ($bankAccount->holder_name == 'Cash') {
                    $filter['account'] = 'Cash';
                }
            }

            if ($request->type == 'revenue' || !isset($request->type)) {
                $reportData['revenues'] = $revenues->get();

                $revenueAccounts->where('revenues.created_by', '=', \Auth::user()->creatorId());
                $reportData['revenueAccounts'] = $revenueAccounts->get();
            }

            if ($request->type == 'payment') {
                $reportData['payments'] = $payments->get();

                $paymentAccounts->where('payments.created_by', '=', \Auth::user()->creatorId());
                $reportData['paymentAccounts'] = $paymentAccounts->get();
                $filter['type']                = __('Payment');
            }


            $filter['startDateRange'] = date('M-Y', $start);
            $filter['endDateRange']   = date('M-Y', $end);


            return view('report.statement_report', compact('reportData', 'account', 'types', 'filter'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function balanceSheet(Request $request)
    {

        if (\Auth::user()->can('bill report')) {

            if (!empty($request->start_date) && !empty($request->end_date)) {
                $start = $request->start_date;
                $end   = $request->end_date;
            } else {
                $start = date('Y-m-01');
                $end   = date('Y-m-t');
            }

            $types = ChartOfAccountType::get();


            $chartAccounts = [];
            foreach ($types as $type) {
                $subTypes = ChartOfAccountSubType::where('type', $type->id)->get();

                $subTypeArray = [];
                foreach ($subTypes as $subType) {
                    $accounts = ChartOfAccount::where('created_by', \Auth::user()->creatorId())->where('type', $type->id)->where('sub_type', $subType->id)->get();

                    $accountArray = [];
                    foreach ($accounts as $account) {

                        $journalItem = JournalItem::select(\DB::raw('sum(credit) as totalCredit'), \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) - sum(debit) as netAmount'))->where('account', $account->id);
                        $journalItem->where('created_at', '>=', $start);
                        $journalItem->where('created_at', '<=', $end);
                        $journalItem          = $journalItem->first();
                        $data['account_name'] = $account->name;
                        $data['totalCredit']  = $journalItem->totalCredit;
                        $data['totalDebit']   = $journalItem->totalDebit;
                        $data['netAmount']    = $journalItem->netAmount;
                        $accountArray[]       = $data;
                    }
                    $subTypeData['subType'] = $subType->name;
                    $subTypeData['account'] = $accountArray;
                    $subTypeArray[]         = $subTypeData;
                }

                $chartAccounts[$type->name] = $subTypeArray;
            }

            $filter['startDateRange'] = $start;
            $filter['endDateRange']   = $end;


            return view('report.balance_sheet', compact('filter', 'chartAccounts'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function ledgerSummary(Request $request)
    {
        if (\Auth::user()->can('ledger report')) {
            $accounts = ChartOfAccount::where('created_by', \Auth::user()->creatorId())->get()->pluck('name', 'id');

            if (!empty($request->start_date) && !empty($request->end_date)) {
                $start = $request->start_date;
                $end   = $request->end_date;
            } else {
                $start = date('Y-m-01');
                $end   = date('Y-m-t');
            }

            if (!empty($request->account)) {
                $account = ChartOfAccount::find($request->account);
            } else {
                $account = ChartOfAccount::where('created_by', \Auth::user()->creatorId())->first();
            }


            $journalItems = JournalItem::select('journal_entries.journal_id', 'journal_entries.date as transaction_date', 'journal_items.*')->leftjoin('journal_entries', 'journal_entries.id', 'journal_items.journal')->where('journal_entries.created_by', '=', \Auth::user()->creatorId())->where('account', !empty($account) ? $account->id : 0);
            //            $journalItems->where('date', '>=', $start);
            //            $journalItems->where('date', '<=', $end);
            $journalItems = $journalItems->get();

            $balance = 0;
            $debit   = 0;
            $credit  = 0;
            foreach ($journalItems as $item) {
                if ($item->debit > 0) {
                    $debit += $item->debit;
                } else {
                    $credit += $item->credit;
                }

                $balance = $credit - $debit;
            }

            $filter['balance']        = $balance;
            $filter['credit']         = $credit;
            $filter['debit']          = $debit;
            $filter['startDateRange'] = $start;
            $filter['endDateRange']   = $end;


            return view('report.ledger_summary', compact('filter', 'journalItems', 'account', 'accounts'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function trialBalanceSummary(Request $request)
    {
        if (\Auth::user()->can('trial balance report')) {


            if (!empty($request->start_date) && !empty($request->end_date)) {
                $start = $request->start_date;
                $end   = $request->end_date;
            } else {
                $start = date('Y-m-01');
                $end   = date('Y-m-t');
            }

            $journalItem = JournalItem::select('chart_of_accounts.name', \DB::raw('sum(credit) as totalCredit'), \DB::raw('sum(debit) as totalDebit'), \DB::raw('sum(credit) - sum(debit) as netAmount'));
            $journalItem->leftjoin('journal_entries', 'journal_entries.id', 'journal_items.journal');
            $journalItem->leftjoin('chart_of_accounts', 'journal_items.account', 'chart_of_accounts.id');
            $journalItem->where('journal_items.created_at', '>=', $start);
            $journalItem->where('journal_items.created_at', '<=', $end);
            $journalItem->groupBy('account');
            $journalItem = $journalItem->get()->toArray();

            $filter['startDateRange'] = $start;
            $filter['endDateRange']   = $end;

            return view('report.trial_balance', compact('filter', 'journalItem'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function productStock(Request $request)
    {
        if (\Auth::user()->can('stock report')) {
            $stocks = StockReport::where('created_by', '=', \Auth::user()->creatorId())->get();
            return view('report.product_stock_report', compact('stocks'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function dailyReport(Request $request)
    {
        
        if (\Auth::user()->can('stock report')) {
            $venders = Vender::where('created_by', \Auth::user()->creatorId())->latest()->get();
            // dd($venders);
            $time = \Carbon\Carbon::now();

            $dateonly = date("Y-m-d", strtotime($time));

            $products = ProductIntake::where('status', '=', 'sold')->whereDate('updated_at', Carbon::today())->latest()->get();
            $allproducts = ProductIntake::all();
            $total_price = $products->sum('sale_price');
            $total_phones = count($products);
            // $today=Carbon::today();
            $yesterday = Carbon::yesterday()->format('Y-m-d');
            $today = Carbon::now()->format('Y-m-d'); //yyyy-mm-dd etc
            $tomorrow = Carbon::tomorrow()->format('Y-m-d'); //yyyy-mm-dd etc

            // $data=ProductIntake::where('updated_at','like',"%$tomorrow%")->orwhere('created_at', 'like', "%$tomorrow%")->latest()->get();
            // $data2 = ProductIntake::where('status','=' ,'custreturn')
            //     ->where(function ($query) use ($today) {
            //         return $query->whereDate('created_at', 'like', "%$today%")
            //             ->orWhere('updated_at', 'like', "%$today%");
            //     })->latest()
            //     ->get();
            // dd($data2);
            // whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
    
// $received_end_date = $request->end_date_name;
//                     $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
//                     // $data = ProductIntake::select('*')->whereBetween('created_at', [$request->start_date_name, $end_date]);

//             $data = ProductIntake::where(function ($query) {
//                     return $query->whereBetween('created_at', [$request->start_date_name, $end_date])
//                     ->orWhereBetween('updated_at', [$request->start_date_name, $end_date]);
//                 })->latest()
//                 ->get();



            // $startDay = Carbon::now()->startOfDay();
            // $endDay   = Carbon::now()->endOfDay();

            // dd($endDay);

            $date_range = [
                'All Time',
                $today => 'Today',
                $yesterday => 'Yesterday',
                'Last 7 Days',
                'This Month',
                'Last Month',
                'Custom Range',
            ];

            // $date_range = array("a", "b", "c");
            array_unshift($date_range, "");
            unset($date_range[0]);

            $productselect = ProductIntake::all()->pluck('model_name', 'model_name');
            $supplierselect = Vender::all()->pluck('name', 'name');
            // dd($supplierselect);
            $the_status = ProductIntake::$the_status;

            return view('report.daily-report', compact('venders', 'products', 'allproducts', 'total_phones', 'total_price', 'date_range', 'productselect', 'supplierselect', 'the_status'));
        } else {
            return redirect()->back()->with('error', __('Permission Denied.'));
        }
    }

    public function dailyReportAjaxResults(Request $request)
    {
        // start of all five options
        if ($request->date_range_name && $request->product_name && $request->the_supplier && $request->the_status && $request->the_color) {
            if ($request->date_range_name == 1) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::select('*')->where('model_name', $request->product_name)
                        ->where('supplier_person', $request->the_supplier)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
                 else {
                    $data = ProductIntake::select('*')->where('model_name', $request->product_name)
                        ->where('supplier_person', $request->the_supplier)
                        ->where('status', $request->the_status)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 2) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::where('model_name', $request->product_name)
                        ->where('supplier_person', $request->the_supplier)->where('color', $request->the_color)
                    ->where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                        ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                    })->latest()->get();                   
                   
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } 
                else {
                    $data = ProductIntake::where('model_name', $request->product_name)
                    ->where('supplier_person', $request->the_supplier)->where('status', $request->the_status)->where('color', $request->the_color)
                    ->where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                        ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                    })->latest()
                    ->get();
                    
                    
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            }
             elseif ($request->date_range_name == 3) {
                if ($request->the_status == 'All Products') {

                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                            ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                    })->where('model_name', $request->product_name)
                        ->where('supplier_person', $request->the_supplier)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } 
                else {

                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                            ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                    })->where('model_name', $request->product_name)
                        ->where('supplier_person', $request->the_supplier)->where('status', $request->the_status)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            }
             elseif ($request->date_range_name == 4) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                    })
                        ->where('model_name', $request->product_name)->where('supplier_person', $request->the_supplier)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }

                    return response()->json($data);
                }
                 else {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                    })
                        ->where('model_name', $request->product_name)->where('supplier_person', $request->the_supplier)
                        ->where('status', $request->the_status)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }

                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 5) {
                if ($request->the_status == 'All Products') {
                    $received_end_date = $request->end_date_name;
                    $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                    $start_date= $request->start_date_name;
                    $data = ProductIntake::where(function ($query) use($start_date, $end_date) {
                        return $query->whereBetween('created_at', [$start_date, $end_date])
                            ->orWhereBetween('updated_at', [$start_date, $end_date]);
                    })->where('model_name', $request->product_name)->where('supplier_person', $request->the_supplier)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }

                    return response()->json($data);
                } 
                else {
                    $received_end_date = $request->end_date_name;
                    $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                    $start_date = $request->start_date_name;
                    $data = ProductIntake::where(function ($query) use ($start_date, $end_date) {
                        return $query->whereBetween('created_at', [$start_date, $end_date])
                            ->orWhereBetween('updated_at', [$start_date, $end_date]);
                    })
                        ->where('model_name', $request->product_name)->where('supplier_person', $request->the_supplier)
                        ->where('status', $request->the_status)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }

                    return response()->json($data);
                }
            } 
            else {
                if ($request->the_status == 'All Products') {
                    $mydate = $request->date_range_name;
                    $data = ProductIntake::where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })->where('model_name', $request->product_name)
                        ->where('supplier_person', $request->the_supplier)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }

                    return response()->json($data);
                } 
                else {
                    $mydate = $request->date_range_name;
                    $data = ProductIntake::where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })->where('model_name', $request->product_name)
                        ->where('supplier_person', $request->the_supplier)->where('status', $request->the_status)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }

                    return response()->json($data);
                }
            }
        }
        // end of all five options

        // start of four options
        if ($request->date_range_name && $request->product_name && $request->the_supplier && $request->the_status) {

            if ($request->date_range_name == 1) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::select('*')->where('model_name', $request->product_name)->where('supplier_person', $request->the_supplier)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } 
                else {
                    $data = ProductIntake::select('*')->where('model_name', $request->product_name)->where('supplier_person', $request->the_supplier)
                    ->where('status', $request->the_status)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 2) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::where('supplier_person', $request->the_supplier)
                    ->where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                        ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                    })
                    ->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } 
                else {
                    $data = ProductIntake::where('supplier_person', $request->the_supplier)->where('status', $request->the_status)
                    ->where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                        ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                    })->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 3) {
                if ($request->the_status == 'All Products') {

                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                            ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                    })
                    ->where('supplier_person', $request->the_supplier)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } 
                else {

                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                            ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                    })
                    ->where('supplier_person', $request->the_supplier)
                        ->where('status', $request->the_status)->where('model_name', $request->product_name)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 4) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                    })
                    ->where('supplier_person', $request->the_supplier)->where('model_name', $request->product_name)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
                 else {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                    })
                    ->where('supplier_person', $request->the_supplier)->where('model_name', $request->product_name)->where('status', $request->the_status)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            }
             elseif ($request->date_range_name == 5) {
                if ($request->the_status == 'All Products') {
                    $received_end_date = $request->end_date_name;
                    $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                    $start_date = $request->start_date_name;
                    $data = ProductIntake::where(function ($query) use ($start_date, $end_date) {
                        return $query->whereBetween('created_at', [$start_date, $end_date])
                            ->orWhereBetween('updated_at', [$start_date, $end_date]);
                    })
                    ->where('supplier_person', $request->the_supplier)->where('model_name', $request->product_name)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
                 else {
                    $received_end_date = $request->end_date_name;
                    $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                    $start_date = $request->start_date_name;
                    $data = ProductIntake::where(function ($query) use ($start_date, $end_date) {
                        return $query->whereBetween('created_at', [$start_date, $end_date])
                            ->orWhereBetween('updated_at', [$start_date, $end_date]);
                    })                
                      ->where('supplier_person', $request->the_supplier)
                    ->where('status', $request->the_status)->where('model_name', $request->product_name)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            else {
                if ($request->the_status == 'All Products') {
                    $mydate = $request->date_range_name;
                    $data = ProductIntake::where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })
                    ->where('supplier_person', $request->the_supplier)->where('model_name', $request->product_name)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
                 else {
                    $mydate = $request->date_range_name;
                    $data = ProductIntake::where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })
                    ->where('supplier_person', $request->the_supplier)->where('model_name', $request->product_name)->where('status', $request->the_status)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            }
        }

        if ($request->date_range_name && $request->product_name && $request->the_supplier && $request->the_color) {
            if ($request->date_range_name == 1) {
                $data = ProductIntake::select('*')->where('model_name', $request->product_name)->where('supplier_person', $request->the_supplier)->where('color', $request->the_color)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            } 
            elseif ($request->date_range_name == 2) {
                $data = ProductIntake::where('model_name', $request->product_name)->where('supplier_person', $request->the_supplier)->where('color', $request->the_color)
                ->where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                        ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                })->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            } 
            elseif ($request->date_range_name == 3) {

                $data = ProductIntake::where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                })->where('model_name', $request->product_name)
                ->where('supplier_person', $request->the_supplier)->where('color', $request->the_color)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            } 
            elseif ($request->date_range_name == 4) {
                $data = ProductIntake::where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                })
                ->where('model_name', $request->product_name)->where('supplier_person', $request->the_supplier)->where('color', $request->the_color)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            } 
            elseif ($request->date_range_name == 5) {
                $received_end_date = $request->end_date_name;
                $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                $start_date = $request->start_date_name;
                $data = ProductIntake::where(function ($query) use ($start_date, $end_date) {
                    return $query->whereBetween('created_at', [$start_date, $end_date])
                        ->orWhereBetween('updated_at', [$start_date, $end_date]);
                })->where('model_name', $request->product_name)
                ->where('supplier_person', $request->the_supplier)->where('color', $request->the_color)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            } 
            else {
                $mydate = $request->date_range_name;
                    $data = ProductIntake::where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })->where('model_name', $request->product_name)
                ->where('supplier_person', $request->the_supplier)->where('color', $request->the_color)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            }
        }
      
        if ($request->date_range_name && $request->the_supplier && $request->the_status && $request->the_color) {
            if ($request->date_range_name == 1) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::select('*')->where('supplier_person', $request->the_supplier)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
                 else {
                    $data = ProductIntake::select('*')->where('supplier_person', $request->the_supplier)
                    ->where('status', $request->the_status)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 2) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::where('supplier_person', $request->the_supplier)->where('color', $request->the_color)
                    ->where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                            ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                    })->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } 
                else {
                    $data = ProductIntake::where('supplier_person', $request->the_supplier)->where('status', $request->the_status)->where('color', $request->the_color)
                    ->where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                            ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                    })->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            }
             elseif ($request->date_range_name == 3) {
                if ($request->the_status == 'All Products') {
                     $data = ProductIntake::where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                    ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                })->where('supplier_person', $request->the_supplier)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
                 else {

                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                    })                        ->where('supplier_person', $request->the_supplier)
                        ->where('status', $request->the_status)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 4) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                    })
                        ->where('supplier_person', $request->the_supplier)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
                 else {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                    })
                        ->where('supplier_person', $request->the_supplier)->where('color', $request->the_color)->where('status', $request->the_status)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 5) {
                if ($request->the_status == 'All Products') {
                    $received_end_date = $request->end_date_name;
                    $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                    $start_date = $request->start_date_name;
                    $data = ProductIntake::where(function ($query) use ($start_date, $end_date) {
                        return $query->whereBetween('created_at', [$start_date, $end_date])
                            ->orWhereBetween('updated_at', [$start_date, $end_date]);
                    })
                        ->where('supplier_person', $request->the_supplier)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } 
                else {
                    $received_end_date = $request->end_date_name;
                    $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                    $start_date = $request->start_date_name;
                    $data = ProductIntake::where(function ($query) use ($start_date, $end_date) {
                        return $query->whereBetween('created_at', [$start_date, $end_date])
                            ->orWhereBetween('updated_at', [$start_date, $end_date]);
                    })
                        ->where('supplier_person', $request->the_supplier)
                        ->where('status', $request->the_status)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            }
             else {
                if ($request->the_status == 'All Products') {
                    $mydate = $request->date_range_name;
                    $data = ProductIntake::where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })
                        ->where('supplier_person', $request->the_supplier)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
                 else {
                    $mydate = $request->date_range_name;
                    $data = ProductIntake::where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })->where('supplier_person', $request->the_supplier)
                        ->where('color', $request->the_color)->where('status', $request->the_status)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            }
        }
       
        if ($request->date_range_name && $request->product_name && $request->the_status && $request->the_color) {
            if ($request->date_range_name == 1) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::select('*')->where('color', $request->the_color)->where('model_name', $request->product_name)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
                 else {
                    $data = ProductIntake::select('*')->where('model_name', $request->product_name)
                    ->where('status', $request->the_status)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 2) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                        ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                    })->where('model_name', $request->product_name)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
                 else {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                        ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                    })->where('model_name', $request->product_name)->where('status', $request->the_status)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            }
             elseif ($request->date_range_name == 3) {
                if ($request->the_status == 'All Products') {

                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                    })
                    ->where('model_name', $request->product_name)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } 
                else {

                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                    })
                    ->where('model_name', $request->product_name)
                    ->where('status', $request->the_status)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            }
             elseif ($request->date_range_name == 4) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                    })
                        ->where('model_name', $request->product_name)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } 
                else {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                    })
                        ->where('model_name', $request->product_name)->where('status', $request->the_status)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 5) {
                if ($request->the_status == 'All Products') {
                    $received_end_date = $request->end_date_name;
                    $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                    $start_date = $request->start_date_name;
                    $data = ProductIntake::where(function ($query) use ($start_date, $end_date) {
                        return $query->whereBetween('created_at', [$start_date, $end_date])
                            ->orWhereBetween('updated_at', [$start_date, $end_date]);
                    })
                        ->where('model_name', $request->product_name)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } 
                else {
                    $received_end_date = $request->end_date_name;
                    $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                    $start_date = $request->start_date_name;
                    $data = ProductIntake::where(function ($query) use ($start_date, $end_date) {
                        return $query->whereBetween('created_at', [$start_date, $end_date])
                            ->orWhereBetween('updated_at', [$start_date, $end_date]);
                    })
                        ->where('model_name', $request->product_name)->where('status', $request->the_status)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            }
             else {
                if ($request->the_status == 'All Products') {
                    $mydate = $request->date_range_name;
                    $data = ProductIntake::where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })->where('model_name', $request->product_name)
                        ->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } 
                else {
                    $mydate = $request->date_range_name;
                    $data = ProductIntake::where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })->where('model_name', $request->product_name)
                        ->where('status', $request->the_status)->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            }
        }
    
        if ($request->product_name && $request->the_supplier && $request->the_status && $request->the_color) {
            if ($request->the_status == 'All Products') {
                $data = ProductIntake::select('*')->where('model_name', $request->product_name)
                    ->where('supplier_person', $request->the_supplier)->where('color', $request->the_color)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            } 
            else {
                $data = ProductIntake::select('*')->where('model_name', $request->product_name)
                    ->where('supplier_person', $request->the_supplier)
                    ->where('status', $request->the_status)->where('color', $request->the_color)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            }
        }

        // end of four options

        // start of three options
        if ($request->date_range_name && $request->product_name && $request->the_supplier ) {
            if ($request->date_range_name == 1) {
                $data = ProductIntake::select('*')->where('model_name', $request->product_name)->where('supplier_person', $request->the_supplier)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            }
             elseif ($request->date_range_name == 2) {
                $data = ProductIntake::where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                        ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                })->where('model_name', $request->product_name)->where('supplier_person', $request->the_supplier)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            } 
            elseif ($request->date_range_name == 3) {

                $data = ProductIntake::where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                    ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                })->where('model_name', $request->product_name)
                ->where('supplier_person', $request->the_supplier)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            }
            elseif ($request->date_range_name == 4) {
                $data = ProductIntake::where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                })
                ->where('model_name', $request->product_name)->where('supplier_person', $request->the_supplier)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            } 
            elseif ($request->date_range_name == 5) {
                $received_end_date = $request->end_date_name;
                $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                $start_date = $request->start_date_name;
                $data = ProductIntake::where(function ($query) use ($start_date, $end_date) {
                    return $query->whereBetween('created_at', [$start_date, $end_date])
                        ->orWhereBetween('updated_at', [$start_date, $end_date]);
                })
                ->where('model_name', $request->product_name)->where('supplier_person', $request->the_supplier)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            }
            else {
                $mydate = $request->date_range_name;
                    $data = ProductIntake::where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })
                ->where('model_name', $request->product_name)->where('supplier_person', $request->the_supplier)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            }
        }     

        if ($request->date_range_name && $request->product_name && $request->the_color) {
            if ($request->date_range_name == 1) {
                $data = ProductIntake::select('*')->where('model_name', $request->product_name)->where('color', $request->the_color)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            } 
            elseif ($request->date_range_name == 2) {
                $data = ProductIntake::where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                        ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                })->where('model_name', $request->product_name)->where('color', $request->the_color)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            } 
            elseif ($request->date_range_name == 3) {

                $data = ProductIntake::where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                    ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                })->where('model_name', $request->product_name)
                ->where('color', $request->the_color)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            } 
            elseif ($request->date_range_name == 4) {
                $data = ProductIntake::where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                })
                ->where('model_name', $request->product_name)->where('color', $request->the_color)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            } 
            elseif ($request->date_range_name == 5) {
                $received_end_date = $request->end_date_name;
                $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                $start_date = $request->start_date_name;
                $data = ProductIntake::where(function ($query) use ($start_date, $end_date) {
                    return $query->whereBetween('created_at', [$start_date, $end_date])
                        ->orWhereBetween('updated_at', [$start_date, $end_date]);
                })->where('model_name', $request->product_name)
                ->where('color', $request->the_color)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            } 
            else {
                $mydate = $request->date_range_name;
                    $data = ProductIntake::where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })->where('model_name', $request->product_name)
                ->where('color', $request->the_color)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            }
        }

        if ($request->date_range_name && $request->the_supplier && $request->the_status) {
            if ($request->date_range_name == 1) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::select('*')->where('supplier_person', $request->the_supplier)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } 
                else {
                    $data = ProductIntake::select('*')->where('supplier_person', $request->the_supplier)
                    ->where('status', $request->the_status)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 2) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                            ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                    })->where('supplier_person', $request->the_supplier)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } 
                else {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                        ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                    })->where('supplier_person', $request->the_supplier)->where('status', $request->the_status)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 3) {
                if ($request->the_status == 'All Products') {

                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                    })
                    ->where('supplier_person', $request->the_supplier)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } 
                else {

                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                    })
                    ->where('supplier_person', $request->the_supplier)->where('status', $request->the_status)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 4) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                    })
                    ->where('supplier_person', $request->the_supplier)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } 
                else {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                    })
                    ->where('supplier_person', $request->the_supplier)->where('status', $request->the_status)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 5) {
                if ($request->the_status == 'All Products') {
                    $received_end_date = $request->end_date_name;
                    $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                    $start_date = $request->start_date_name;
                    $data = ProductIntake::where(function ($query) use ($start_date, $end_date) {
                        return $query->whereBetween('created_at', [$start_date, $end_date])
                            ->orWhereBetween('updated_at', [$start_date, $end_date]);
                    })
                    ->where('supplier_person', $request->the_supplier)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } 
                else {
                    $received_end_date = $request->end_date_name;
                    $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                    $start_date = $request->start_date_name;
                    $data = ProductIntake::where(function ($query) use ($start_date, $end_date) {
                        return $query->whereBetween('created_at', [$start_date, $end_date])
                            ->orWhereBetween('updated_at', [$start_date, $end_date]);
                    })
                    ->where('supplier_person', $request->the_supplier)->where('status', $request->the_status)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            else {
                if ($request->the_status == 'All Products') {
                   $mydate = $request->date_range_name;
                    $data = ProductIntake::where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })
                    ->where('supplier_person', $request->the_supplier)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } 
                else {
                    $mydate = $request->date_range_name;
                    $data = ProductIntake::where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })
                    ->where('supplier_person', $request->the_supplier)->where('status', $request->the_status)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            }
        }

        if ($request->date_range_name && $request->the_status && $request->the_color) {
            if ($request->date_range_name == 1) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::select('*')->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } else {
                    $data = ProductIntake::select('*')->where('color', $request->the_color)
                        ->where('status', $request->the_status)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 2) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                        ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                    })->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } else {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                        ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                    })->where('color', $request->the_color)->where('status', $request->the_status)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 3) {
                if ($request->the_status == 'All Products') {

                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                    })
                        ->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } else {

                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                    })
                        ->where('color', $request->the_color)->where('status', $request->the_status)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 4) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                    })
                        ->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } else {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                    })
                        ->where('color', $request->the_color)->where('status', $request->the_status)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 5) {
                if ($request->the_status == 'All Products') {
                    $received_end_date = $request->end_date_name;
                    $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                     $start_date= $request->start_date_name;
                    $data = ProductIntake::where(function ($query) use($start_date, $end_date) {
                        return $query->whereBetween('created_at', [$start_date, $end_date])
                            ->orWhereBetween('updated_at', [$start_date, $end_date]);
                    })
                        ->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } else {
                    $received_end_date = $request->end_date_name;
                    $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                    $start_date= $request->start_date_name;
                    $data = ProductIntake::where(function ($query) use($start_date, $end_date) {
                        return $query->whereBetween('created_at', [$start_date, $end_date])
                            ->orWhereBetween('updated_at', [$start_date, $end_date]);
                    })
                        ->where('color', $request->the_color)->where('status', $request->the_status)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            else {
                if ($request->the_status == 'All Products') {
                    $mydate = $request->date_range_name;
                    $data = ProductIntake::where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })
                        ->where('color', $request->the_color)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } else {
                    $mydate = $request->date_range_name;
                    $data = ProductIntake::where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })
                        ->where('color', $request->the_color)->where('status', $request->the_status)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            }
        }

        if ($request->date_range_name && $request->the_color && $request->the_supplier) {
            if ($request->date_range_name == 1) {
                $data = ProductIntake::select('*')->where('color', $request->the_color)->where('supplier_person', $request->the_supplier)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            } 
            elseif ($request->date_range_name == 2) {
                $data = ProductIntake::where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                        ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                })->where('color', $request->the_color)
                ->where('supplier_person', $request->the_supplier)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            } 
            elseif ($request->date_range_name == 3) {

                $data = ProductIntake::where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                    ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                })->where('color', $request->the_color)
                ->where('supplier_person', $request->the_supplier)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            } 
            elseif ($request->date_range_name == 4) {
                $data = ProductIntake::where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                })
                ->where('color', $request->the_color)->where('supplier_person', $request->the_supplier)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            } 
            elseif ($request->date_range_name == 5) {
                $received_end_date = $request->end_date_name;
                $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                $start_date= $request->start_date_name;
                    $data = ProductIntake::where(function ($query) use($start_date, $end_date) {
                        return $query->whereBetween('created_at', [$start_date, $end_date])
                            ->orWhereBetween('updated_at', [$start_date, $end_date]);
                    })
                ->where('color', $request->the_color)->where('supplier_person', $request->the_supplier)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            } 
            else {
                $mydate = $request->date_range_name;
                    $data = ProductIntake::where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })
                ->where('color', $request->the_color)->where('supplier_person', $request->the_supplier)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            }
        }

        if ($request->date_range_name  && $request->product_name && $request->the_status) {
            if ($request->date_range_name == 1) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::select('*')->where('model_name', $request->product_name)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } else {
                    $data = ProductIntake::select('*')
                        ->where('status', $request->the_status)->where('model_name', $request->product_name)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 2) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                        ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                    })->where('model_name', $request->product_name)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } else {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                        ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                    })->where('status', $request->the_status)->where('model_name', $request->product_name)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 3) {
                if ($request->the_status == 'All Products') {

                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                    })
                        ->where('model_name', $request->product_name)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } else {

                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                    })
                        ->where('status', $request->the_status)->where('model_name', $request->product_name)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 4) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                    })
                        ->where('model_name', $request->product_name)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } else {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                    })
                        ->where('status', $request->the_status)->where('model_name', $request->product_name)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 5) {
                if ($request->the_status == 'All Products') {
                    $received_end_date = $request->end_date_name;
                    $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                    $start_date= $request->start_date_name;
                    $data = ProductIntake::where(function ($query) use($start_date, $end_date) {
                        return $query->whereBetween('created_at', [$start_date, $end_date])
                            ->orWhereBetween('updated_at', [$start_date, $end_date]);
                    })
                        ->where('model_name', $request->product_name)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } else {
                    $received_end_date = $request->end_date_name;
                    $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                    $start_date= $request->start_date_name;
                    $data = ProductIntake::where(function ($query) use($start_date, $end_date) {
                        return $query->whereBetween('created_at', [$start_date, $end_date])
                            ->orWhereBetween('updated_at', [$start_date, $end_date]);
                    })
                        ->where('status', $request->the_status)->where('model_name', $request->product_name)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            else {
                if ($request->the_status == 'All Products') {
                    $mydate = $request->date_range_name;
                    $data = ProductIntake::where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })
                        ->where('model_name', $request->product_name)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } else {
                    $mydate = $request->date_range_name;
                    $data = ProductIntake::where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })
                        ->where('status', $request->the_status)->where('model_name', $request->product_name)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            }
        }

        if ($request->product_name && $request->the_supplier && $request->the_status) {
            if ($request->the_status == 'All Products') {
                $data = ProductIntake::select('*')->where('model_name', $request->product_name)
                ->where('supplier_person', $request->the_supplier)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            } 
            else {
                $data = ProductIntake::select('*')->where('model_name', $request->product_name)
                ->where('supplier_person', $request->the_supplier)->where('status', $request->the_status)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            }
        }

        if ($request->the_supplier && $request->the_status && $request->the_color) {
            if ($request->the_status == 'All Products') {
                $data = ProductIntake::select('*')->where('color', $request->the_color)
                    ->where('supplier_person', $request->the_supplier)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            } 
            else {
                $data = ProductIntake::select('*')->where('color', $request->the_color)
                    ->where('supplier_person', $request->the_supplier)->where('status', $request->the_status)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            }
        }

        if ($request->product_name && $request->the_supplier && $request->the_color) {
            $data = ProductIntake::select('*')->where('model_name', $request->product_name)
            ->where('supplier_person', $request->the_supplier)->where('color', $request->the_color)->latest()->get();
            foreach ($data as $the_data) {
                $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
            }
            return response()->json($data);
        }

        if ($request->product_name && $request->the_status && $request->the_color) {
            if ($request->the_status == 'All Products') {
                $data = ProductIntake::select('*')->where('model_name', $request->product_name)
                    ->where('color', $request->the_color)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            } else {
                $data = ProductIntake::select('*')->where('model_name', $request->product_name)
                    ->where('color', $request->the_color)->where('status', $request->the_status)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            }
        }
       
        // end of three options

      
        // start of two options
    
        if ($request->date_range_name && $request->product_name) {
            if ($request->date_range_name == 1) {
                $data = ProductIntake::select('*')->where('model_name', $request->product_name)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            } elseif ($request->date_range_name == 2) {
                $data = ProductIntake::where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                        ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                })->where('model_name', $request->product_name)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            } elseif ($request->date_range_name == 3) {

                $data = ProductIntake::where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                    ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                })->where('model_name', $request->product_name)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            } elseif ($request->date_range_name == 4) {
                $data = ProductIntake::where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                })->where('model_name', $request->product_name)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            } elseif ($request->date_range_name == 5) {
                $received_end_date = $request->end_date_name;
                $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                $start_date= $request->start_date_name;
                    $data = ProductIntake::where(function ($query) use($start_date, $end_date) {
                        return $query->whereBetween('created_at', [$start_date, $end_date])
                            ->orWhereBetween('updated_at', [$start_date, $end_date]);
                    })->where('model_name', $request->product_name)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            } else {
                $mydate = $request->date_range_name;
                    $data = ProductIntake::where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })->where('model_name', $request->product_name)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }

                return response()->json($data);
            }
        }
        
        if ($request->date_range_name && $request->the_supplier) {
            if ($request->date_range_name == 1) {
                $data = ProductIntake::select('*')->where('supplier_person', $request->the_supplier)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            } elseif ($request->date_range_name == 2) {
                $data = ProductIntake::where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                        ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                })->where('supplier_person', $request->the_supplier)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            } elseif ($request->date_range_name == 3) {

                $data = ProductIntake::where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                    ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                })
                    ->where('supplier_person', $request->the_supplier)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            } elseif ($request->date_range_name == 4) {
                $data = ProductIntake::where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                })
                    ->where('supplier_person', $request->the_supplier)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            } elseif ($request->date_range_name == 5) {

                $received_end_date = $request->end_date_name;
                $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                $start_date= $request->start_date_name;
                    $data = ProductIntake::where(function ($query) use($start_date, $end_date) {
                        return $query->whereBetween('created_at', [$start_date, $end_date])
                            ->orWhereBetween('updated_at', [$start_date, $end_date]);
                    })
                    ->where('supplier_person', $request->the_supplier)->latest()->get();

                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            } else {
                $mydate = $request->date_range_name;
                    $data = ProductIntake::where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })
                    ->where('supplier_person', $request->the_supplier)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            }
        }
        
        if ($request->date_range_name && $request->the_status) {
            if ($request->date_range_name == 1) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::all();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } 
                else {
                    $data = ProductIntake::select('*')->where('status', $request->the_status)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 2) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                            ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                    })->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } else {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                        ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                    })->where('status', $request->the_status)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 3) {
                if ($request->the_status == 'All Products') {

                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                    })->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } else {

                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                    })
                        ->where('status', $request->the_status)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 4) {
                if ($request->the_status == 'All Products') {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                    })->latest()->get();
                    // dd($data);
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } else {
                    $data = ProductIntake::where(function ($query) {
                        return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                    })
                        ->where('status', $request->the_status)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            elseif ($request->date_range_name == 5) {
                if ($request->the_status == 'All Products') {
                    $received_end_date= $request->end_date_name;
                    $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                    $start_date= $request->start_date_name;
                    $data = ProductIntake::where(function ($query) use($start_date, $end_date) {
                        return $query->whereBetween('created_at', [$start_date, $end_date])
                            ->orWhereBetween('updated_at', [$start_date, $end_date]);
                    })->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }

                    
                    return response()->json($data);
                } else {
                    $received_end_date = $request->end_date_name;
                    $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                    $start_date= $request->start_date_name;
                    $data = ProductIntake::where(function ($query) use($start_date, $end_date) {
                        return $query->whereBetween('created_at', [$start_date, $end_date])
                            ->orWhereBetween('updated_at', [$start_date, $end_date]);
                    })
                        ->where('status', $request->the_status)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            } 
            else {
                if ($request->the_status == 'All Products') {
                    $mydate = $request->date_range_name;
                    $data = ProductIntake::where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                } else {
                    $mydate = $request->date_range_name;
                    $data = ProductIntake::where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })->where('status', $request->the_status)->latest()->get();
                    foreach ($data as $the_data) {
                        $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                    }
                    return response()->json($data);
                }
            }
        }

        if ($request->date_range_name && $request->the_color) {
            if ($request->date_range_name == 1) {
                $data = ProductIntake::select('*')->where('color', $request->the_color)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            } elseif ($request->date_range_name == 2) {
                $data = ProductIntake::where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                        ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                })->where('color', $request->the_color)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            } elseif ($request->date_range_name == 3) {

                $data = ProductIntake::where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                    ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                })
                    ->where('color', $request->the_color)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            } elseif ($request->date_range_name == 4) {
                $data = ProductIntake::where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                })
                    ->where('color', $request->the_color)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            } elseif ($request->date_range_name == 5) {

                $received_end_date = $request->end_date_name;
                $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                $start_date= $request->start_date_name;
                    $data = ProductIntake::where(function ($query) use($start_date, $end_date) {
                        return $query->whereBetween('created_at', [$start_date, $end_date])
                            ->orWhereBetween('updated_at', [$start_date, $end_date]);
                    })
                    ->where('color', $request->the_color)->latest()->get();

                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            } else {
                $mydate = $request->date_range_name;
                    $data = ProductIntake::where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })
                    ->where('color', $request->the_color)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            }
        }
        
        if ($request->product_name && $request->the_supplier) {
            $data = ProductIntake::select('*')->where('model_name', $request->product_name)
                ->where('supplier_person', $request->the_supplier)->latest()->get();
            foreach ($data as $the_data) {
                $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
            }
            return response()->json($data);
        }
        
        if ($request->product_name && $request->the_status) {
            if ($request->the_status == 'All Products') {
                $data = ProductIntake::select('*')->where('model_name', $request->product_name)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            } else {
                $data = ProductIntake::select('*')->where('model_name', $request->product_name)->where('status', $request->the_status)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            }
        }

        if ($request->product_name && $request->the_color) {
            $data = ProductIntake::select('*')->where('model_name', $request->product_name)
                ->where('color', $request->the_color)->latest()->get();
            foreach ($data as $the_data) {
                $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
            }
            return response()->json($data);
        }
        
        if ($request->the_supplier && $request->the_status) {
            if ($request->the_status == 'All Products') {
                $data = ProductIntake::select('*')->where('supplier_person', $request->the_supplier)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            } else {
                $data = ProductIntake::select('*')->where('supplier_person', $request->the_supplier)->where('status', $request->the_status)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            }
        }

        if ($request->the_supplier && $request->the_color) {
            $data = ProductIntake::select('*')->where('supplier_person', $request->the_supplier)
                ->where('color', $request->the_color)->latest()->get();
            foreach ($data as $the_data) {
                $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
            }
            return response()->json($data);
        }

        if ($request->the_status && $request->the_color) {
            if ($request->the_status == 'All Products') {
                $data = ProductIntake::select('*')->where('color', $request->the_color)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            } else {
                $data = ProductIntake::select('*')->where('color', $request->the_color)->where('status', $request->the_status)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            }
        }
       
        // end of two options

        // start of single option

        if ($request->date_range_name) {
            if ($request->date_range_name == 1) {
                $data = ProductIntake::all();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            } elseif ($request->date_range_name == 2) {
                $data = ProductIntake::where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')])
                        ->orWhereBetween('updated_at', [Carbon::now()->subDays(7)->format('Y-m-d'), Carbon::tomorrow()->format('Y-m-d')]);
                })->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            } elseif ($request->date_range_name == 3) {
                $data = ProductIntake::with('productservice')->where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                })->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            } elseif ($request->date_range_name == 4) {
                $data = ProductIntake::with('productservice')->where(function ($query) {
                    return $query->whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
                        ->orWhereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                })->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            } elseif ($request->date_range_name == 5) {
                $received_end_date = $request->end_date_name;
                $end_date = date('Y-m-d', strtotime($received_end_date . " +1 days"));
                $start_date= $request->start_date_name;
                    $data = ProductIntake::where(function ($query) use($start_date, $end_date) {
                        return $query->whereBetween('created_at', [$start_date, $end_date])
                            ->orWhereBetween('updated_at', [$start_date, $end_date]);
                    })->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            } else {
                $mydate = $request->date_range_name;
                    $data = ProductIntake::with('productservice')->where(function ($query) use ($mydate) {
                            return $query->whereDate('created_at', 'like', "%$mydate%")
                                ->orWhere('updated_at', 'like', "%$mydate%");
                        })->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            }
        }
        
        if ($request->product_name) {
            $data = ProductIntake::select('*')->where('model_name', $request->product_name)->latest()->get();
            foreach ($data as $the_data) {
                $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
            }
            return response()->json($data);
        }
       
        if ($request->the_supplier) {
            $data = ProductIntake::select('*')->where('supplier_person', $request->the_supplier)->latest()->get();
            foreach ($data as $the_data) {
                $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
            }
            return response()->json($data);
        }
        
        if ($request->the_status) {
            if ($request->the_status == 'All Products') {
                $data = ProductIntake::all();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            } else {
                $data = ProductIntake::select('*')->where('status', $request->the_status)->latest()->get();
                foreach ($data as $the_data) {
                    $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
                }
                return response()->json($data);
            }
        }

        if ($request->the_color) {
            $data = ProductIntake::select('*')->where('color', $request->the_color)->latest()->get();
            foreach ($data as $the_data) {
                $the_data->vender_id = \Crypt::encrypt($the_data->vender_id);
            }
            return response()->json($data);
        }
        
        // end of single option
    }

    public function exportdata(Request $request)
    {
        $data = DB::table('product_intakes')

            ->when(request()->input('date_range_name'), function ($query) {

                if (request()->input('date_range_name') == 1) {
                    $query->select('*');
                } elseif (request()->input('date_range_name') == 2) {
                    $thedate = Carbon::now()->subDays(7)->format('Y-m-d');
                    $query->where('updated_at', '>=', $thedate);
                } elseif (request()->input('date_range_name') == 3) {
                    $thedate = Carbon::now()->subDays(30)->format('Y-m-d');
                    $query->where('updated_at', '>=', $thedate);
                } elseif (request()->input('date_range_name') == 4) {
                    $query->whereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                } elseif (request()->input('date_range_name') == 5) {
                    $query->whereBetween('updated_at', [request()->input('start_date_name'), request()->input('end_date_name')]);
                } else {
                    $query->where('updated_at', request()->input('date_range_name'));
                }
            })

            ->when(request()->input('product_name'), function ($query) {
                $query->where('model_name', request()->input('product_name'));
            })

            ->when(request()->input('the_supplier'), function ($query) {
                $query->where('supplier_person', request()->input('the_supplier'));
            })

            ->when(request()->input('the_status'), function ($query) {
                if (request()->input('the_status') == 'All Products') {
                    $query->select('*');
                } else {
                    $query->where('status', request()->input('the_status'));
                }
            })
            ->latest()->get();

        $name = 'daily_report_' . date('Y-m-d i:h:s');


        return Excel::download(new ReportExport($data), $name . '.xlsx');
    }


    public function dailyReportPDF()
    {

        $data = DB::table('product_intakes')


            ->when(request()->input('date_range_name'), function ($query) {

                if (request()->input('date_range_name') == 1) {
                    $query->select('*');
                } elseif (request()->input('date_range_name') == 2) {
                    $thedate = Carbon::now()->subDays(7)->format('Y-m-d');
                    $query->where('updated_at', '>=', $thedate);
                } elseif (request()->input('date_range_name') == 3) {
                    $thedate = Carbon::now()->subDays(30)->format('Y-m-d');
                    $query->where('updated_at', '>=', $thedate);
                } elseif (request()->input('date_range_name') == 4) {
                    $query->whereBetween('updated_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()]);
                } elseif (request()->input('date_range_name') == 5) {
                    $query->whereBetween('updated_at', [request()->input('start_date_name'), request()->input('end_date_name')]);
                } else {
                    $query->where('updated_at', request()->input('date_range_name'));
                }
            })

            ->when(request()->input('product_name'), function ($query) {
                $query->where('model_name', request()->input('product_name'));
            })

            ->when(request()->input('the_supplier'), function ($query) {
                $query->where('supplier_person', request()->input('the_supplier'));
            })

            ->when(request()->input('the_status'), function ($query) {
                if (request()->input('the_status') == 'All Products') {
                    $query->select('*');
                } else {
                    $query->where('status', request()->input('the_status'));
                }
            })

            ->latest()->get();

        $allproducts = [
            'title' => $data,
            'date' => date('m/d/Y')
        ];

        // dd($allproducts);

        $pdf = PDF::loadView('myPDF', compact('allproducts'));

        // dd($pdf);

        return $pdf->stream('daily_report.pdf');

        // return $pdf->download('itsolutionstuff.pdf');

    }
}
