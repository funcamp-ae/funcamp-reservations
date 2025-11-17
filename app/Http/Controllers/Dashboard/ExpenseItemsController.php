<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\ExpenseItem;
use App\Models\Expense;
use App\Models\BankAccount;
use Illuminate\Http\Request;

class ExpenseItemsController extends Controller
{
    // List all expense items
    public function index()
    {
        $this->authorize('viewAny', ExpenseItem::class);

        $expenseItems = ExpenseItem::orderBy('ordered_count','desc')->get();

        return view('dashboard.expense_items.index', compact('expenseItems'));
    }



    // Show form to create new expense item
    public function create()
    {
        $bankAccounts = BankAccount::all();
        $expenseItems = ExpenseItem::orderBy('created_at','desc')->get();
        return view('dashboard.expense_items.create', compact('expenseItems', 'bankAccounts'));
    }

    // Store new expense item
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:expense_items,name',
            'description' => 'nullable|string|max:255',
        ]);

        ExpenseItem::create([
            'name' => $request->name,
            'description' => $request->description,
        ]);

       return response()->json();
    }

    public function show($id)
    {
        $request = request();
        $expenseItem = ExpenseItem::findOrFail($id);

        // Calculate total expenses
        $totalExpenses = $expenseItem->expenses->sum(function ($category) {
            return $category->sum('price');
        });

        // Calculate current month's expenses
        $currentMonthExpenses = $expenseItem->expenses->sum(function ($category) {
            return $category->where('created_at', '>=', now()->startOfMonth())->sum('price');
        });


        $query = Expense::query();

        // Filter by date
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $query->where('expense_item_id', $expenseItem->id);

        $expenses = $query->orderBy('created_at','desc')->paginate(100);

        return view('dashboard.expense_items.show', compact('expenseItem', 'totalExpenses', 'currentMonthExpenses','expenses'));
    }


    // Show form to edit expense item
    public function edit( $expenseItem)
    {
        $expenseItem = ExpenseItem::findOrFail($expenseItem);
        $expenseItems = ExpenseItem::orderBy('created_at','desc')->get();

        return view('dashboard.expense_items.create', compact('expenseItem','expenseItems'));
    }

    // Update existing expense item
    public function update(Request $request,  $expenseItem)
    {
        $expenseItem = ExpenseItem::findOrFail($expenseItem);

        $request->validate([
            'name' => 'required|string|max:255|unique:expense_items,name,' . $expenseItem->id,
            'description' => 'nullable|string|max:255',
        ]);

        $expenseItem->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json();
    }

    // Delete expense item
    public function destroy( $expenseItem)
    {
        $expenseItem = ExpenseItem::findOrFail($expenseItem);
        $expenseItem->delete();
       return response()->json();
    }

    public function move(Request $request, ExpenseItem $expenseItem)
    {
        // $this->authorize('update', $expenseItem);

        $data = $request->validate([
            'direction' => 'required|in:up,down',
        ]);

        return \DB::transaction(function () use ($expenseItem, $data) {
            $this->normalizeOrderedCounts();

            $current = $expenseItem ;

            $targetOrder = $data['direction'] === 'up' ? $current->ordered_count + 1 : $current->ordered_count - 1;

            if ($targetOrder < 1) {
                return response()->json(['message' => 'noop', 'moved' => false], 200);
            }

            $max = (int) ExpenseItem::max('ordered_count');
            if ($targetOrder > $max) {
                return response()->json(['message' => 'noop2', 'moved' => false , 'm' => $max , 't' => $targetOrder], 200);
            }

            $neighbor = ExpenseItem::where('ordered_count', $targetOrder)
                ->lockForUpdate()
                ->first();

            if (!$neighbor) {
                $this->normalizeOrderedCounts();
                $neighbor = ExpenseItem::where('ordered_count', $targetOrder)
                    ->lockForUpdate()
                    ->first();

                if (!$neighbor) {
                    return response()->json(['message' => 'noop', 'moved' => false], 200);
                }
            }

            // Swap ordered_count values
            $temp = $current->ordered_count;
            $current->ordered_count = $neighbor->ordered_count;
            $neighbor->ordered_count = $temp;

            $current->save();
            $neighbor->save();

            return response()->json([
                'message' => 'ok',
                'moved'   => true,
                'current' => ['id' => $current->id, 'ordered_count' => $current->ordered_count],
                'neighbor'=> ['id' => $neighbor->id, 'ordered_count' => $neighbor->ordered_count],
            ], 200);
        });
    }

    private function normalizeOrderedCounts(): void
    {
        $rows = ExpenseItem::orderByRaw('CASE WHEN ordered_count IS NULL THEN 0 ELSE 1 END ASC')
            ->orderBy('ordered_count')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        $i = 1;
        foreach ($rows as $r) {
            if ((int)$r->ordered_count !== $i) {
                $r->ordered_count = $i;
                $r->save();
            }
            $i++;
        }
    }
}
