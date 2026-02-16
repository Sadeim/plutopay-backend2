<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Summary</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f6f9; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.06);">

                    {{-- Header with Logo --}}
                    <tr>
                        <td style="background-color: #ffffff; padding: 28px 40px; text-align: center; border-bottom: 1px solid #e2e8f0;">
                            <img src="https://plutopayus.com/images/plutopay.png" alt="PlutoPay" style="height: 40px; width: auto;" />
                            <p style="color: #64748b; margin: 6px 0 0; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Daily Summary Report</p>
                        </td>
                    </tr>

                    {{-- Greeting --}}
                    <tr>
                        <td style="padding: 28px 40px 16px;">
                            <p style="color: #1e293b; font-size: 15px; margin: 0 0 4px;">Hi {{ $merchant->display_name ?: $merchant->business_name }},</p>
                            <p style="color: #64748b; font-size: 14px; margin: 0;">Here's your business summary for <strong style="color: #1e293b;">{{ $date }}</strong></p>
                        </td>
                    </tr>

                    {{-- Main Stats --}}
                    <tr>
                        <td style="padding: 16px 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="48%" style="padding: 16px 20px; background-color: #f0fdf4; border-radius: 10px;">
                                        <p style="color: #16a34a; font-size: 30px; font-weight: 700; margin: 0; letter-spacing: -0.5px;">{{ $summary['total_volume_formatted'] }}</p>
                                        <p style="color: #4ade80; font-size: 11px; margin: 6px 0 0; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 600;">Total Volume</p>
                                    </td>
                                    <td width="4%"></td>
                                    <td width="48%" style="padding: 16px 20px; background-color: #f8fafc; border-radius: 10px; border: 1px solid #e2e8f0;">
                                        <p style="color: #1e293b; font-size: 30px; font-weight: 700; margin: 0;">{{ $summary['total_transactions'] }}</p>
                                        <p style="color: #94a3b8; font-size: 11px; margin: 6px 0 0; text-transform: uppercase; letter-spacing: 0.8px; font-weight: 600;">Transactions</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Breakdown --}}
                    <tr>
                        <td style="padding: 12px 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
                                <tr style="background-color: #f8fafc;">
                                    <td colspan="2" style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0;">
                                        <p style="color: #1e293b; font-size: 13px; font-weight: 700; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">Transaction Breakdown</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #16a34a; font-size: 8px; vertical-align: middle;">&#9679;</span>
                                        <span style="color: #475569; font-size: 13px; margin-left: 6px;">Succeeded</span>
                                    </td>
                                    <td align="right" style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #16a34a; font-size: 14px; font-weight: 700;">{{ $summary['succeeded_count'] }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #ef4444; font-size: 8px; vertical-align: middle;">&#9679;</span>
                                        <span style="color: #475569; font-size: 13px; margin-left: 6px;">Failed</span>
                                    </td>
                                    <td align="right" style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #ef4444; font-size: 14px; font-weight: 700;">{{ $summary['failed_count'] }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 16px;">
                                        <span style="color: #94a3b8; font-size: 8px; vertical-align: middle;">&#9679;</span>
                                        <span style="color: #475569; font-size: 13px; margin-left: 6px;">Canceled</span>
                                    </td>
                                    <td align="right" style="padding: 10px 16px;">
                                        <span style="color: #94a3b8; font-size: 14px; font-weight: 700;">{{ $summary['canceled_count'] }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Revenue --}}
                    <tr>
                        <td style="padding: 12px 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
                                <tr style="background-color: #f8fafc;">
                                    <td colspan="2" style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0;">
                                        <p style="color: #1e293b; font-size: 13px; font-weight: 700; margin: 0; text-transform: uppercase; letter-spacing: 0.5px;">Revenue Summary</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #475569; font-size: 13px;">Gross Volume</span>
                                    </td>
                                    <td align="right" style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #1e293b; font-size: 14px; font-weight: 700;">{{ $summary['total_volume_formatted'] }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #475569; font-size: 13px;">Refunds</span>
                                    </td>
                                    <td align="right" style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #ef4444; font-size: 14px; font-weight: 700;">-{{ $summary['total_refunded_formatted'] }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #1e293b; font-size: 13px; font-weight: 600;">Net Volume</span>
                                    </td>
                                    <td align="right" style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #16a34a; font-size: 15px; font-weight: 700;">{{ $summary['net_volume_formatted'] }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #475569; font-size: 13px;">Average Transaction</span>
                                    </td>
                                    <td align="right" style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #1e293b; font-size: 14px; font-weight: 600;">{{ $summary['average_formatted'] }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 16px;">
                                        <span style="color: #475569; font-size: 13px;">Success Rate</span>
                                    </td>
                                    <td align="right" style="padding: 10px 16px;">
                                        @if($summary['success_rate'] >= 90)
                                            <span style="color: #16a34a; font-size: 14px; font-weight: 700;">{{ $summary['success_rate'] }}%</span>
                                        @elseif($summary['success_rate'] >= 70)
                                            <span style="color: #f59e0b; font-size: 14px; font-weight: 700;">{{ $summary['success_rate'] }}%</span>
                                        @else
                                            <span style="color: #ef4444; font-size: 14px; font-weight: 700;">{{ $summary['success_rate'] }}%</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Payouts --}}
                    @if($summary['payout_count'] > 0)
                    <tr>
                        <td style="padding: 12px 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="background-color: #eff6ff; border-radius: 10px; padding: 14px 16px;">
                                        <p style="color: #1d4ed8; font-size: 13px; font-weight: 600; margin: 0 0 2px;">Payouts</p>
                                        <p style="color: #3b82f6; font-size: 12px; margin: 0;">{{ $summary['payout_count'] }} payout(s) totaling {{ $summary['payout_amount_formatted'] }}</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    @endif

                    {{-- CTA --}}
                    <tr>
                        <td style="padding: 24px 40px;" align="center">
                            <a href="https://plutopayus.com/dashboard" style="display: inline-block; background-color: #1e293b; color: #ffffff; text-decoration: none; padding: 12px 36px; border-radius: 8px; font-size: 14px; font-weight: 600;">View Dashboard</a>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding: 24px 40px; border-top: 1px solid #e2e8f0; text-align: center;">
                            <img src="https://plutopayus.com/images/plutopay.png" alt="PlutoPay" style="height: 22px; width: auto; opacity: 0.4; margin-bottom: 8px;" />
                            <p style="color: #94a3b8; font-size: 11px; margin: 0 0 4px;">&copy; {{ date('Y') }} PlutoPay. All rights reserved.</p>
                            <p style="color: #cbd5e1; font-size: 11px; margin: 0;">You can manage notifications in your <a href="https://plutopayus.com/dashboard/settings" style="color: #3b82f6; text-decoration: none;">settings</a>.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
