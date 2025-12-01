<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\OrderItem;
use App\Models\Expense;
use App\Models\GeneralPayment;
use App\Models\BankAccount;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use App\Services\InsuranceVerificationService;

class FinancialDeletionService
{
    public function deletePaymentById(int $id): array
    {
        DB::beginTransaction();
        try {
            $payment = Payment::findOrFail($id);

            if ($payment->verified && $payment->statement !== 'the_insurance') {
                if ($payment->account_id) {
                    $account = BankAccount::find($payment->account_id);
                    if ($account) {
                        $account->decrement('balance', $payment->price);
                    }
                }
            } elseif ($payment->verified && $payment->statement === 'the_insurance') {
                $insuranceService = new InsuranceVerificationService();
                $insuranceService->insurnaceDeleted($payment);
            }

            $deletedAmount = $payment->price;
            $payment->delete();

            DB::commit();
            return ['success' => true, 'deleted_amount' => $deletedAmount];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('deletePaymentById failed: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function deleteOrderItemById(int $id): array
    {
        DB::beginTransaction();
        try {
            $item = OrderItem::findOrFail($id);

            if ($item->verified) {
                // return stock quantity
                if ($item->stock_id) {
                    $stock = \App\Models\Stock::find($item->stock_id);
                    if ($stock) {
                        $stock->increment('quantity', $item->quantity);
                    }
                }
                if ($item->account_id) {
                    $acc = BankAccount::find($item->account_id);
                    if ($acc) {
                        $acc->decrement('balance', $item->total_price);
                    }
                }
            }

            Transaction::where('order_item_id', $item->id)->delete();
            $deletedAmount = $item->total_price ?? 0;
            $item->delete();

            DB::commit();
            return ['success' => true, 'deleted_amount' => $deletedAmount];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('deleteOrderItemById failed: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function deleteExpenseById(int $id): array
    {
        DB::beginTransaction();
        try {
            $expense = Expense::findOrFail($id);
            if ($expense->verified && $expense->account_id) {
                $account = BankAccount::find($expense->account_id);
                if ($account) {
                    $account->increment('balance', $expense->price);
                }
            }

            // delete images
            if ($expense->images && is_array($expense->images)) {
                $expense->deleteMultipleImages($expense->images);
            }
            if ($expense->image && \Storage::disk('public')->exists($expense->image)) {
                \Storage::disk('public')->delete($expense->image);
            }
            if ($expense->image_path && \Storage::disk('public')->exists($expense->image_path)) {
                \Storage::disk('public')->delete($expense->image_path);
            }

            $deletedAmount = $expense->price;
            $expense->delete();

            DB::commit();
            return ['success' => true, 'deleted_expense_amount' => $deletedAmount];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('deleteExpenseById failed: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function deleteGeneralPaymentById(int $id): array
    {
        DB::beginTransaction();
        try {
            $payment = GeneralPayment::find($id);
            if (!$payment) {
                DB::commit();
                return ['success' => false, 'message' => 'not_found'];
            }

            if ($payment->verified && $payment->account_id) {
                $acc = BankAccount::find($payment->account_id);
                if ($acc) {
                    $acc->decrement('balance', $payment->price);
                }
            }

            // delete image
            if ($payment->image_path) {
                \Storage::disk('public')->delete($payment->image_path);
            }

            // delete transaction if present
            if ($payment->transaction) {
                $payment->transaction()->delete();
            }

            $deletedAmount = $payment->price;
            $payment->delete();

            DB::commit();
            return ['success' => true, 'deleted_amount' => $deletedAmount];
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('deleteGeneralPaymentById failed: ' . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
