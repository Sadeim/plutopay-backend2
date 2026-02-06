<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Terminal;
use App\Services\Payment\PaymentProcessorFactory;
use Illuminate\Http\Request;

class TerminalController extends Controller
{
    public function index(Request $request)
    {
        $merchant = $request->attributes->get('merchant');

        $terminals = Terminal::where('merchant_id', $merchant->id)
            ->when($request->input('status'), fn($q, $s) => $q->where('status', $s))
            ->when($request->input('location_name'), fn($q, $l) => $q->where('location_name', 'ilike', "%{$l}%"))
            ->orderByDesc('created_at')
            ->paginate($request->input('per_page', 20));

        return response()->json($terminals);
    }

    public function store(Request $request)
    {
        $merchant = $request->attributes->get('merchant');

        $request->validate([
            'name' => 'required|string|max:255',
            'registration_code' => 'sometimes|string',
            'location_name' => 'sometimes|string|max:255',
            'location_address' => 'sometimes|string|max:500',
            'metadata' => 'sometimes|array',
        ]);

        $processorData = [];
        if ($request->registration_code) {
            $processor = PaymentProcessorFactory::make($merchant);
            try {
                $processorData = $processor->registerTerminal([
                    'registration_code' => $request->registration_code,
                    'label' => $request->name,
                    'location_id' => $merchant->processor_metadata['location_id'] ?? null,
                ]);
            } catch (\Exception $e) {
                return response()->json([
                    'error' => ['type' => 'terminal_error', 'message' => $e->getMessage()]
                ], 400);
            }
        }

        $terminal = Terminal::create([
            'merchant_id' => $merchant->id,
            'name' => $request->name,
            'serial_number' => $processorData['serial_number'] ?? null,
            'status' => 'offline',
            'location_name' => $request->location_name,
            'location_address' => $request->location_address,
            'processor_terminal_id' => $processorData['processor_id'] ?? null,
            'processor_metadata' => $processorData['raw'] ?? null,
            'is_test' => $merchant->test_mode,
            'paired_at' => $processorData ? now() : null,
            'metadata' => $request->metadata,
        ]);

        return response()->json($terminal, 201);
    }

    public function show(Request $request, string $id)
    {
        $merchant = $request->attributes->get('merchant');
        $terminal = Terminal::where('merchant_id', $merchant->id)->findOrFail($id);
        return response()->json($terminal);
    }

    public function update(Request $request, string $id)
    {
        $merchant = $request->attributes->get('merchant');
        $terminal = Terminal::where('merchant_id', $merchant->id)->findOrFail($id);

        $terminal->update($request->only([
            'name', 'location_name', 'location_address', 'metadata',
        ]));

        return response()->json($terminal);
    }

    public function destroy(Request $request, string $id)
    {
        $merchant = $request->attributes->get('merchant');
        $terminal = Terminal::where('merchant_id', $merchant->id)->findOrFail($id);
        $terminal->delete();
        return response()->json(['deleted' => true]);
    }
}
