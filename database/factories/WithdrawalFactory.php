<?php

namespace Database\Factories;

use App\Models\Withdrawal;
use App\Models\StoreBalance;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Withdrawal>
 */
class WithdrawalFactory extends Factory
{
    protected $model = Withdrawal::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'store_balance_id' => StoreBalance::factory(),
            'amount' => function (array $attributes) {
                $storeBalance = StoreBalance::find($attributes['store_balance_id']);
                return $this->faker->randomFloat(2, 0, $storeBalance->balance);
            },
            'bank_account_name' => $this->faker->name,
            'bank_account_number' => $this->faker->numerify('###########'),
            'bank_name' => $this->faker->randomElement(['BCA', 'Mandiri', 'BNI', 'BRI']),
            'status' => 'pending'
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Withdrawal $withdrawal) {
            // histori permintaan penarikan (pending)
            $withdrawal->storeBalance->storeBalanceHistories()->create([
                'type' => 'withdraw',
                'reference_id' => $withdrawal->id,
                'reference_type' => Withdrawal::class,
                'amount' => $withdrawal->amount,
                'remarks' => "permintaan penarikan dana {$withdrawal->bank_name} - {$withdrawal->bank_account_number}",
            ]);

            //penarikan dana
            $withdrawal->storeBalance->storeBalanceHistories()->create([
                'type' => 'withdraw',
                'reference_id' => $withdrawal->id,
                'reference_type' => Withdrawal::class,
                'amount' => -$withdrawal->amount,
                'remarks' => "permintaan penarikan dana ke {$withdrawal->bank_name} - {$withdrawal->bank_account_number} telah di proses",
            ]);

            $withdrawal->update(['status' => 'approved']);

            $withdrawal->storeBalance->update([
                'balance' => $withdrawal->storeBalance->balance - $withdrawal->amount
            ]);
        });
    }
}
