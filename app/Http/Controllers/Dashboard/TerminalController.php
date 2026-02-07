<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Terminal;
use App\Services\Payment\PaymentProcessorFactory;
use Illuminate\Http\Request;

class TerminalController extends Controller
{
    public function index()
    {
        $merchant = auth()->user()->merchant;

        $terminals = Terminal::where('merchant_id', $merchant->id)
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'total' => $terminals->count(),
            'online' => $terminals->where('status', 'online')->count(),
            'offline' => $terminals->where('status', 'offline')->count(),
            'pairing' => $terminals->where('status', 'pairing')->count(),
        ];

        return view('dashboard.terminals.index', [
            'terminals' => $terminals,
            'stats' => $stats,
        ]);
    }

    /**
     * Register a new terminal.
     */
    public function store(Request $request)
    {
        $merchant = auth()->user()->merchant;

        $request->validate([
            'name' => 'required|string|max:255',
            'registration_code' => 'required|string|max:100',
            'location_name' => 'nullable|string|max:255',
            'location_address' => 'nullable|string|max:500',
        ]);

        try {
            // Register with Stripe Terminal
            $processor = PaymentProcessorFactory::make($merchant);
            $result = $processor->registerTerminal([
                'registration_code' => $request->registration_code,
                'label' => $request->name,
                'stripe_account' => $merchant->processor_account_id,
            ]);

            $terminal = Terminal::create([
                'merchant_id' => $merchant->id,
                'name' => $request->name,
                'serial_number' => $result['serial_number'] ?? null,
                'model' => 'stripe_reader',
                'status' => 'pairing',
                'location_name' => $request->location_name,
                'location_address' => $request->location_address,
                'processor_terminal_id' => $result['processor_id'],
                'is_test' => $merchant->test_mode,
                'paired_at' => now(),
            ]);

            return redirect()->route('dashboard.terminals.index')
                ->with('success', 'Terminal registered successfully!');

        } catch (\Exception $e) {
            return redirect()->route('dashboard.terminals.index')
                ->with('error', 'Failed to register terminal: ' . $e->getMessage());
        }
    }

    /**
     * Update terminal details.
     */
    public function update(Request $request, string $id)
    {
        $merchant = auth()->user()->merchant;
        $terminal = Terminal::where('merchant_id', $merchant->id)->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'location_name' => 'nullable|string|max:255',
            'location_address' => 'nullable|string|max:500',
        ]);

        $terminal->update($request->only(['name', 'location_name', 'location_address']));

        return redirect()->route('dashboard.terminals.index')
            ->with('success', 'Terminal updated.');
    }

    /**
     * Delete a terminal.
     */
    public function destroy(string $id)
    {
        $merchant = auth()->user()->merchant;
        $terminal = Terminal::where('merchant_id', $merchant->id)->findOrFail($id);
        $terminal->delete();

        return redirect()->route('dashboard.terminals.index')
            ->with('success', 'Terminal removed.');
    }
}
