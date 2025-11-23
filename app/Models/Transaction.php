<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $fillable = [
        'account_id',
        'sender_account_id', 
        'receiver_id',
        'amount',
        'type',
        'source', //=>  reservation_payments ,general_expenses , reservation_expenses , insurances , reservation_addon , charge_account , add_funds_page , general_revenue_deposit , warehouse_sales ;
        'statement',
        'date',
        'verified',
        'description',
        'order_id',
        'order_addon_id',
        'customer_id',
        'payment_id',
        'expense_id',
        'order_item_id',
        'general_payment_id',
        'image_path',
        'images',
        'stock_id',
        'handled_by'
        ];
    protected $casts = [
        'images' => 'array',
    ];
    
     // Define the sender relationship
    public function account()
    {
        return $this->belongsTo(BankAccount::class, 'account_id');
    }
    public function handledBy()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
        public function senderAccount()
    {
        return $this->belongsTo(BankAccount::class, 'sender_account_id');
    }
       public function isInsuranceReturned()
    {
        return $this->payment()->where('insurance_status', 'returned')->exists();
    }
    // Define the receiver relationship
    public function receiver()
    {
        return $this->belongsTo(BankAccount::class, 'receiver_id');
    }
        public function generalPayment()
    {
        return $this->belongsTo(GeneralPayment::class , 'general_payment_id');
    }

    // Define the supplier relationship
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    // Define the customer relationship
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    // Define the expense relationship
    public function expense()
    {
        return $this->belongsTo(Expense::class , 'expense_id');
    }

    public function orderAddon()
    {
        return $this->belongsTo(OrderAddon::class, 'order_addon_id');
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }
    public function order()
    {
        return $this->belongsTo(Order::class,'order_id');
    }

    // Accessor for $transaction->editRoute
    public function getEditRouteAttribute(): string
    {
        if ($this->expense_id) {
            return route('expenses.edit', $this->expense_id);
        }
        if ($this->payment_id) {
            return route('payments.edit', $this->payment_id);
        }
        if ($this->account_id) {
            return route('bank-accounts.show', $this->account_id);
        }
        return '#';
    }

    public function payment(){
        return $this->belongsTo(Payment::class);
    }
    // Accessor for $transaction->destroyRoute
    public function getDestroyRouteAttribute(): string
    {
        if ($this->expense_id) {
            return route('expenses.destroy', $this->expense_id);
        }
        if ($this->payment_id) {
            return route('payments.destroy', $this->payment_id);
        }
        return '#';
    }
    public function editRoute($id)
    {
        if($this->type == 'Expense'){
            return route('expenses.edit', $id);
        }elseif($this->type == 'Payment'){
            return route('payments.edit', $id);
        }elseif($this->type == 'Withdrawal'){
          return   route('bank-accounts.show', $this->id);
        }
    }
public function getDetailedSource()
    {
        $accountName = $this->account?->name ?? '';

        if ($this->order_id) {
            if ($this->payment) {
                return __("dashboard.".'Reservation #:order_id — payment (:statement) on :date by :account', [
                    'order_id' => $this->order_id,
                    'statement' => __("dashboard.".$this->payment->statement ?? ''),
                    'date' => $this->payment->created_at ?? $this->created_at,
                    'account' => $this->handledBy->name ?? null,
                ]);
            } elseif ($this->orderItem) {
                return __("dashboard.".'Reservation #:order_id — warehouse sale (:name) on :date by :account', [
                    'order_id' => $this->order_id,
                    'name' => $this->orderItem->name ?? '',
                    'date' => $this->orderItem->created_at ?? $this->created_at,
                    'account' => $this->handledBy->name ?? null,
                ]);
            } elseif ($this->orderAddon) {
                return __("dashboard.".'Reservation #:order_id — addon (:name) on :date by :account', [
                    'order_id' => $this->order_id,
                    'name' => $this->orderAddon->name ?? '',
                    'date' => $this->orderAddon->created_at ?? $this->created_at,
                    'account' => $this->handledBy->name ?? null,
                ]);
            } elseif ($this->expense) {
                return __("dashboard.".'Reservation #:order_id — expense (:statement) on :date by :account', [
                    'order_id' => $this->order_id,
                    'statement' => $this->expense->statement ?? '',
                    'date' => $this->expense->created_at ?? $this->created_at,
                    'account' => $this->handledBy->name ?? null,
                ]);
            }elseif ($this->source == 'general_revenue_deposit') {
                return __("dashboard.".'Reservation #:order_id — payment (:statement) on :date by :account', [
                    'order_id' => $this->order_id,
                    'statement' => $this->statement ?? '',
                    'date' => $this->created_at ?? $this->created_at,
                    'account' => $this->handledBy->name ?? null,
                ]);
            }

            return '';
        }

        // Non-reservation sources
        if (str_contains($this->source ?? '', 'expenses') && $this->expense) {
            return __("dashboard.".'Expenses / general: (:statement) on :date by :account', [
                'statement' => $this->expense->expenseItem->name ?? '',
                'date' => $this->expense->created_at ?? $this->created_at,
                'account' => $this->handledBy->name ?? null,
            ]);
        }

        return __("dashboard.".'Payments / general: (:source) on :date by :account', [
            'source' =>$this->statement ? __("dashboard.".$this->statement) : __("dashboard.".$this->generalPayment()->statement) ?? '',
            'date' => $this->created_at,
            'account' => $this->handledBy->name ?? null,
        ]);
    }
}
