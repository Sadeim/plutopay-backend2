<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Terminal;
use Illuminate\Http\Request;

class TerminalController extends Controller
{
    public function index()
    {
        $merchant = auth()->user()->merchant;

        // Auto-sync terminal status from Stripe
        $this->syncTerminalStatus($merchant);

        $terminals = Terminal::where('merchant_id', $merchant->id)
            ->orderByDesc('status')
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'total' => $terminals->count(),
            'online' => $terminals->where('status', 'online')->count(),
            'offline' => $terminals->where('status', 'offline')->count(),
            'pairing' => $terminals->where('status', 'pairing')->count(),
        ];

        return view('dashboard.terminals.index', compact('terminals', 'stats'));
    }

    public function fetchReaders(Request $request)
    {
        $merchant = auth()->user()->merchant;

        $request->validate([
            'location_id' => 'required|string|min:5',
        ]);

        try {
            $stripe = new \Stripe\StripeClient(
                $merchant->test_mode
                    ? config('services.stripe.test_secret')
                    : config('services.stripe.secret')
            );

            $readers = $stripe->terminal->readers->all(
                ['location' => $request->location_id, 'limit' => 100]
            );

            $existingIds = Terminal::where('merchant_id', $merchant->id)
                ->pluck('processor_terminal_id')
                ->toArray();

            $results = [];
            foreach ($readers->data as $reader) {
                $results[] = [
                    'id' => $reader->id,
                    'label' => $reader->label,
                    'serial_number' => $reader->serial_number,
                    'device_type' => $reader->device_type,
                    'status' => $reader->status,
                    'ip_address' => $reader->ip_address,
                    'location' => $reader->location,
                    'already_imported' => in_array($reader->id, $existingIds),
                ];
            }

            $location = $stripe->terminal->locations->retrieve(
                $request->location_id
            );

            return response()->json([
                'success' => true,
                'location' => [
                    'id' => $location->id,
                    'display_name' => $location->display_name,
                    'address' => implode(', ', array_filter([
                        $location->address->line1 ?? '',
                        $location->address->city ?? '',
                        $location->address->state ?? '',
                        $location->address->country ?? '',
                    ])),
                ],
                'readers' => $results,
            ]);

        } catch (\Stripe\Exception\InvalidRequestException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Location ID. Please check and try again.',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    public function importReaders(Request $request)
    {
        $merchant = auth()->user()->merchant;

        $request->validate([
            'readers' => 'required|array|min:1',
            'readers.*.id' => 'required|string',
            'readers.*.label' => 'required|string',
            'readers.*.serial_number' => 'required|string',
            'readers.*.device_type' => 'required|string',
            'readers.*.status' => 'required|string',
            'readers.*.location' => 'required|string',
            'location_name' => 'required|string',
            'location_address' => 'nullable|string',
        ]);

        $imported = 0;

        foreach ($request->readers as $reader) {
            $exists = Terminal::where('merchant_id', $merchant->id)
                ->where('processor_terminal_id', $reader['id'])
                ->exists();

            if ($exists) continue;

            Terminal::create([
                'merchant_id' => $merchant->id,
                'name' => $reader['label'],
                'serial_number' => $reader['serial_number'],
                'model' => $reader['device_type'],
                'status' => $reader['status'] === 'online' ? 'online' : 'offline',
                'location_name' => $request->location_name,
                'location_address' => $request->location_address,
                'processor_terminal_id' => $reader['id'],
                'processor_location_id' => $reader['location'],
                'is_test' => $merchant->test_mode,
                'last_seen_at' => now(),
            ]);

            $imported++;
        }

        return redirect()->route('dashboard.terminals.index')
            ->with('success', "{$imported} terminal(s) imported successfully!");
    }

    public function store(Request $request)
    {
        $merchant = auth()->user()->merchant;

        $request->validate([
            'name' => 'required|string|max:255',
            'registration_code' => 'required|string',
            'location_name' => 'nullable|string|max:255',
            'location_address' => 'nullable|string|max:500',
        ]);

        try {
            $stripe = new \Stripe\StripeClient(
                $merchant->test_mode
                    ? config('services.stripe.test_secret')
                    : config('services.stripe.secret')
            );

            $connectOpts = [];
            if ($merchant->processor_account_id) {
                $connectOpts['stripe_account'] = $merchant->processor_account_id;
            }

            $location = $stripe->terminal->locations->create([
                'display_name' => $request->location_name ?? $request->name,
                'address' => [
                    'line1' => $request->location_address ?? '123 Main St',
                    'city' => 'City',
                    'state' => 'IL',
                    'country' => 'US',
                    'postal_code' => '60004',
                ],
            ], $connectOpts);

            $reader = $stripe->terminal->readers->create([
                'registration_code' => $request->registration_code,
                'label' => $request->name,
                'location' => $location->id,
            ], $connectOpts);

            Terminal::create([
                'merchant_id' => $merchant->id,
                'name' => $request->name,
                'serial_number' => $reader->serial_number,
                'model' => $reader->device_type,
                'status' => 'pairing',
                'location_name' => $request->location_name,
                'location_address' => $request->location_address,
                'processor_terminal_id' => $reader->id,
                'processor_location_id' => $location->id,
                'is_test' => $merchant->test_mode,
                'last_seen_at' => now(),
            ]);

            return redirect()->route('dashboard.terminals.index')
                ->with('success', 'Terminal registered successfully!');

        } catch (\Exception $e) {
            return back()->with('error', 'Registration failed: ' . $e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        $merchant = auth()->user()->merchant;
        $terminal = Terminal::where('merchant_id', $merchant->id)->findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'location_name' => 'sometimes|string|max:255',
        ]);

        $terminal->update($request->only(['name', 'location_name']));

        return back()->with('success', 'Terminal updated.');
    }

    public function destroy(string $id)
    {
        $merchant = auth()->user()->merchant;
        $terminal = Terminal::where('merchant_id', $merchant->id)->findOrFail($id);
        $terminal->delete();

        return redirect()->route('dashboard.terminals.index')
            ->with('success', 'Terminal removed.');
    }

    protected function syncTerminalStatus($merchant)
    {
        try {
            $stripe = new \Stripe\StripeClient(
                $merchant->test_mode
                    ? config('services.stripe.test_secret')
                    : config('services.stripe.secret')
            );

            $terminals = Terminal::where('merchant_id', $merchant->id)->get();

            foreach ($terminals as $terminal) {
                if (!$terminal->processor_terminal_id) continue;
                try {
                    $reader = $stripe->terminal->readers->retrieve($terminal->processor_terminal_id);
                    $terminal->update([
                        'status' => $reader->status === 'online' ? 'online' : 'offline',
                        'last_seen_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    $terminal->update(['status' => 'offline']);
                }
            }
        } catch (\Exception $e) {}
    }
}
