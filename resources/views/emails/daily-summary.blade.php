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

                    {{-- Header --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); padding: 32px 40px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0 0 4px; font-size: 22px; font-weight: 700; letter-spacing: -0.3px;">PlutoPay</h1>
                            <p style="color: #94a3b8; margin: 0; font-size: 13px;">Daily Summary Report</p>
                        </td>
                    </tr>

                    {{-- Greeting --}}
                    <tr>
                        <td style="padding: 32px 40px 16px;">
                            <p style="color: #334155; font-size: 15px; margin: 0 0 4px;">Hi {{ $merchant->business_name }},</p>
                            <p style="color: #64748b; font-size: 14px; margin: 0;">Here's your business summary for <strong style="color: #334155;">{{ $date }}</strong></p>
                        </td>
                    </tr>

                    {{-- Main Stats --}}
                    <tr>
                        <td style="padding: 16px 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td width="50%" style="padding: 12px 16px; background-color: #f0fdf4; border-radius: 10px;">
                                        <p style="color: #16a34a; font-size: 28px; font-weight: 700; margin: 0; letter-spacing: -0.5px;">{{ $summary['total_volume_formatted'] }}</p>
                                        <p style="color: #4ade80; font-size: 12px; margin: 4px 0 0; text-transform: uppercase; letter-spacing: 0.5px;">Total Volume</p>
                                    </td>
                                    <td width="8"></td>
                                    <td width="50%" style="padding: 12px 16px; background-color: #f8fafc; border-radius: 10px;">
                                        <p style="color: #1e293b; font-size: 28px; font-weight: 700; margin: 0;">{{ $summary['total_transactions'] }}</p>
                                        <p style="color: #94a3b8; font-size: 12px; margin: 4px 0 0; text-transform: uppercase; letter-spacing: 0.5px;">Transactions</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Breakdown --}}
                    <tr>
                        <td style="padding: 16px 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
                                <tr style="background-color: #f8fafc;">
                                    <td colspan="2" style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0;">
                                        <p style="color: #334155; font-size: 14px; font-weight: 600; margin: 0;">Transaction Breakdown</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #16a34a; font-size: 8px; vertical-align: middle;">&#9679;</span>
                                        <span style="color: #475569; font-size: 13px; margin-left: 6px;">Succeeded</span>
                                    </td>
                                    <td align="right" style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #16a34a; font-size: 14px; font-weight: 600;">{{ $summary['succeeded_count'] }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #ef4444; font-size: 8px; vertical-align: middle;">&#9679;</span>
                                        <span style="color: #475569; font-size: 13px; margin-left: 6px;">Failed</span>
                                    </td>
                                    <td align="right" style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #ef4444; font-size: 14px; font-weight: 600;">{{ $summary['failed_count'] }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 16px;">
                                        <span style="color: #94a3b8; font-size: 8px; vertical-align: middle;">&#9679;</span>
                                        <span style="color: #475569; font-size: 13px; margin-left: 6px;">Canceled</span>
                                    </td>
                                    <td align="right" style="padding: 10px 16px;">
                                        <span style="color: #94a3b8; font-size: 14px; font-weight: 600;">{{ $summary['canceled_count'] }}</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Revenue --}}
                    <tr>
                        <td style="padding: 16px 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden;">
                                <tr style="background-color: #f8fafc;">
                                    <td colspan="2" style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0;">
                                        <p style="color: #334155; font-size: 14px; font-weight: 600; margin: 0;">Revenue Summary</p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #475569; font-size: 13px;">Gross Volume</span>
                                    </td>
                                    <td align="right" style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #334155; font-size: 14px; font-weight: 600;">{{ $summary['total_volume_formatted'] }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #475569; font-size: 13px;">Refunds</span>
                                    </td>
                                    <td align="right" style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #ef4444; font-size: 14px; font-weight: 600;">-{{ $summary['total_refunded_formatted'] }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #475569; font-size: 13px; font-weight: 600;">Net Volume</span>
                                    </td>
                                    <td align="right" style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #16a34a; font-size: 14px; font-weight: 700;">{{ $summary['net_volume_formatted'] }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #475569; font-size: 13px;">Average Transaction</span>
                                    </td>
                                    <td align="right" style="padding: 10px 16px; border-bottom: 1px solid #f1f5f9;">
                                        <span style="color: #334155; font-size: 14px; font-weight: 600;">{{ $summary['average_formatted'] }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 10px 16px;">
                                        <span style="color: #475569; font-size: 13px;">Success Rate</span>
                                    </td>
                                    <td align="right" style="padding: 10px 16px;">
                                        <span style="color: {{ $summary['success_rate'] >= 90 ? '#16a34a' : ($summary['success_rate'] >= 70 ? '#f59e0b' : '#ef4444') }}; font-size: 14px; font-weight: 600;">{{ $summary['success_rate'] }}%</span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Payouts --}}
                    @if($summary['payout_count'] > 0)
                    <tr>
                        <td style="padding: 16px 40px;">
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #eff6ff; border-radius: 10px; padding: 14px 16px;">
                                <tr>
                                    <td>
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
                            <a href="https://plutopayus.com/dashboard" style="display: inline-block; background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: #ffffff; text-decoration: none; padding: 12px 32px; border-radius: 8px; font-size: 14px; font-weight: 600;">View Dashboard</a>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="padding: 24px 40px; border-top: 1px solid #e2e8f0; text-align: center;">
                            <p style="color: #94a3b8; font-size: 12px; margin: 0 0 4px;">&copy; {{ date('Y') }} PlutoPay. All rights reserved.</p>
                            <p style="color: #cbd5e1; font-size: 11px; margin: 0;">This is an automated daily summary. You can manage notifications in your <a href="https://plutopayus.com/dashboard/settings" style="color: #3b82f6; text-decoration: none;">settings</a>.</p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
