<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function show(Request $request, Order $order): Response
    {
        $this->authorizeOwner($request, $order);

        $order->load(['event:id,title,slug,starts_at,venue,city', 'tickets.ticketType:id,name']);

        return Inertia::render('Orders/Show', [
            'order' => [
                'id' => $order->id,
                'reference' => $order->reference,
                'total' => $order->total,
                'status' => $order->status,
                'buyer_name' => $order->buyer_name,
                'event' => $order->event,
                'tickets' => $order->status === 'paid'
                    ? $order->tickets->map(fn ($t) => [
                        'id' => $t->id,
                        'code' => $t->code,
                        'type' => $t->ticketType?->name,
                        'status' => $t->status,
                    ])
                    : [],
            ],
        ]);
    }

    /**
     * Mock төлбөр — жинхэнэ карт холбохоос өмнөх түр шийдэл.
     */
    public function pay(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOwner($request, $order);

        if ($order->status === 'pending') {
            $order->update(['status' => 'paid', 'paid_at' => now()]);
            $order->loadMissing('event');
            $request->user()->notify(new \App\Notifications\OrderPaid($order));
        }

        return redirect()->route('orders.show', $order)
            ->with('success', 'Төлбөр амжилттай. Тасалбараа QR кодоор үзүүлнэ үү. (mock)');
    }

    public function myTickets(Request $request): Response
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->where('status', 'paid')
            ->with(['event:id,title,slug,starts_at', 'tickets'])
            ->latest()
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'reference' => $o->reference,
                'event' => $o->event,
                'ticket_count' => $o->tickets->count(),
            ]);

        return Inertia::render('Orders/MyTickets', ['orders' => $orders]);
    }

    private function authorizeOwner(Request $request, Order $order): void
    {
        abort_unless($order->user_id === $request->user()->id, 403);
    }
}
