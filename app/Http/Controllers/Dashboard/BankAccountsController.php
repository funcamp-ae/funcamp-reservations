<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BankAccount;
use App\Models\Transaction;
use App\Models\Expense;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class BankAccountsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('viewAny', BankAccount::class);

        $banks = BankAccount::orderBy('created_at','desc')->get();
        return view('dashboard.banks.index',[
            'banks' => $banks
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('dashboard.banks.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'account_number' => 'nullable|unique:bank_accounts',
            'name' => 'required',
            'balance' => 'required|numeric',
            'notes' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'is_private' => 'nullable|boolean',
        ]);

            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $path = $image->storeAs('payments/images', $imageName , 'public');
                $validatedData['image'] = $path;
            }

            $validatedData['image'] = $validatedData['image'] ?? null;
            $bankAccount = BankAccount::create($validatedData);
            Transaction::create([
                'account_id' => $bankAccount->id,
                'amount' => $validatedData['balance'],
                "type" =>"deposit",
                'source' => 'general_payments_deposit',
                "verified" => 1,
                'date' => now(),
                'description' => 'Initial deposit',
                'image_path' => null,
                'images' => null,
            ]);

        return response()->json(['message' => 'Bank account created successfully', 'bank_account' => $bankAccount]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BankAccount  $bankAccount
     * @return \Illuminate\Http\Response
     */
    public function show( $bankAccount)
    {
        
        $bank = BankAccount::findOrFail($bankAccount);
        $this->authorize('view', $bank);
        $startDate = request('start_date');
        $endDate = request('end_date');

        $perPage = 10; // Set your desired items per page

        $transactions = Transaction::where(function ($query) use ($bank) {
                $query->where('account_id', $bank->id)
                      ->orWhere('receiver_id', $bank->id)
                      ->orWhere('sender_account_id', $bank->id);
                    })
                    ->when($startDate && $endDate, function ($query) use ($startDate, $endDate) {
                        return $query->whereBetween('date', [$startDate, $endDate]);
                    })
                    ->where('verified', "1")
                    ->where("amount" , ">" , 0)
                    ->orderBy('created_at', 'desc')
                    ->paginate(100);

        // Preserve query parameters on pagination links
        $transactions->appends(request()->all());

        return view('dashboard.banks.show',[
            'bank' => $bank,
            'transactions' => $transactions
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BankAccount  $bankAccount
     * @return \Illuminate\Http\Response
     */
    public function edit( $bankAccount)
    {
        $bank = BankAccount::findOrFail($bankAccount);
        return view('dashboard.banks.create',[
            'bank' => $bank
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BankAccount  $bankAccount
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,  $bankAccount)
    {
        $bankAccount = BankAccount::findOrFail($bankAccount);

        $validatedData = $request->validate([
            'account_number' => 'nullable|unique:bank_accounts,account_number,' . $bankAccount->id,
            'name' => 'required',
            'notes' => 'nullable|string',
            'is_private' => 'nullable|boolean',
        ]);

        $bankAccount->update($validatedData);

        return response()->json(['message' => 'Bank account created successfully', 'bank_account' => $bankAccount]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BankAccount  $bankAccount
     * @return \Illuminate\Http\Response
     */
    public function destroy( $bankAccount)
    {
        $bank = BankAccount::findOrFail($bankAccount);
        $bank->delete();
        return response()->json();
    }
}
