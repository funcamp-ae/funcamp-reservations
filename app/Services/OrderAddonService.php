<?php

namespace App\Services;

use App\Models\Order;
use App\Models\BankAccount;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Throwable;

class OrderAddonService
{
    /**
     * Remove an order addon pivot row and related transaction/balance adjustments.
     *
     * @param int $pivotId
     * @return array
     * @throws \Exception
     */
    public function removeAddon(int $pivotId): array
    {
        // Fetch the pivot row
        $existingAddon = DB::table('order_addon')->where('id', $pivotId)->first();

        if (!$existingAddon) {
            throw new \Exception('Addon not found');
        }

        // Ensure the related order exists (preserve previous behaviour)
        $order = Order::findOrFail($existingAddon->order_id);

        // If it was verified, decrement the bank account balance
        if ($existingAddon->verified) {
            \Log::info('addon verified', [
                'addon_id' => $existingAddon->id,
                'account_id' => $existingAddon->account_id,
                'price' => $existingAddon->price,
            ]);

            if ($existingAddon->account_id) {
                $bankAccount = BankAccount::findOrFail($existingAddon->account_id);
                $bankAccount->decrement('balance', $existingAddon->price);
                \Log::info('decremented bank account balance for addon', [
                'addon_id' => $existingAddon->id,
                'account_id' => $existingAddon->account_id,
                'bank account balance' => $bankAccount->balance,
            ]);    
            }
        }

        // Delete related transaction if present
        if (!empty($existingAddon->transaction_id)) {
            $transaction = Transaction::find($existingAddon->transaction_id);
            if ($transaction) {
                $transaction->delete();
            }
        }

        // Capture the price difference for callers
        $priceDifference = $existingAddon->price;

        // Delete the pivot record
        DB::table('order_addon')->where('id', $pivotId)->delete();

        return [
            'success' => true,
            'priceDifference' => $priceDifference,
            'order_id' => $order->id,
        ];
    }
}
