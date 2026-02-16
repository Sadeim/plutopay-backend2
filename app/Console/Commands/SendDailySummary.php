<?php

namespace App\Console\Commands;

use App\Mail\DailySummaryMail;
use App\Models\Merchant;
use App\Models\MerchantUser;
use App\Models\Transaction;
use App\Models\Payout;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendDailySummary extends Command
{
    protected $signature = 'merchants:daily-summary {--date= : Specific date (Y-m-d), defaults to yesterday}';
    protected $description = 'Send daily summary email to all merchants';

    public function handle(): int
    {
        $date = $this->option('date')
            ? Carbon::parse($this->option('date'))
            : Carbon::yesterday();

        $from = $date->copy()->startOfDay();
        $to = $date->copy()->endOfDay();
        $dateFormatted = $date->format('M d, Y');

        $merchants = Merchant::where('status', 'active')->get();
        $sent = 0;

        foreach ($merchants as $merchant) {
            $txns = Transaction::where('merchant_id', $merchant->id)
                ->whereBetween('created_at', [$from, $to]);

            $succeeded = (clone $txns)->where('status', 'succeeded');
            $failed = (clone $txns)->where('status', 'failed');

            $totalCount = (clone $txns)->count();

            // Skip if no activity
            if ($totalCount === 0) {
                $this->line("{$merchant->business_name}: No activity, skipping.");
                continue;
            }

            $succeededCount = (clone $succeeded)->count();
            $failedCount = (clone $failed)->count();
            $canceledCount = (clone $txns)->where('status', 'canceled')->count();
            $totalVolume = (clone $succeeded)->sum('amount');
            $totalRefunded = (clone $txns)->sum('amount_refunded');

            $payouts = Payout::where('merchant_id', $merchant->id)
                ->whereBetween('created_at', [$from, $to]);
            $payoutCount = (clone $payouts)->count();
            $payoutAmount = (clone $payouts)->where('status', 'paid')->sum('amount');

            $currency = $merchant->default_currency ?? 'USD';
            $currencySymbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'ILS' => '₪', 'AED' => 'د.إ'];
            $symbol = $currencySymbols[$currency] ?? $currency . ' ';

            $summary = [
                'total_transactions' => $totalCount,
                'succeeded_count' => $succeededCount,
                'failed_count' => $failedCount,
                'canceled_count' => $canceledCount,
                'total_volume' => $totalVolume,
                'total_volume_formatted' => $symbol . number_format($totalVolume / 100, 2),
                'average_transaction' => $succeededCount > 0 ? round($totalVolume / $succeededCount) : 0,
                'average_formatted' => $succeededCount > 0 ? $symbol . number_format(($totalVolume / $succeededCount) / 100, 2) : $symbol . '0.00',
                'total_refunded' => $totalRefunded,
                'total_refunded_formatted' => $symbol . number_format($totalRefunded / 100, 2),
                'net_volume' => $totalVolume - $totalRefunded,
                'net_volume_formatted' => $symbol . number_format(($totalVolume - $totalRefunded) / 100, 2),
                'success_rate' => $totalCount > 0 ? round(($succeededCount / $totalCount) * 100, 1) : 0,
                'payout_count' => $payoutCount,
                'payout_amount_formatted' => $symbol . number_format($payoutAmount / 100, 2),
                'symbol' => $symbol,
            ];

            // Send to merchant business email
            $email = $merchant->email;
            if (!$email) {
                $this->warn("{$merchant->business_name}: No email, skipping.");
                continue;
            }

            try {
                Mail::to($email)->send(new DailySummaryMail($merchant, $summary, $dateFormatted));
                $sent++;
                $this->info("Sent to {$email} ({$merchant->business_name})");
            } catch (\Exception $e) {
                $this->error("Failed to send to {$email}: {$e->getMessage()}");
            }
        }

        $this->info("Done! Sent {$sent} emails.");
        return self::SUCCESS;
    }
}
