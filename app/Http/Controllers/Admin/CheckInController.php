<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CheckInController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/CheckIn');
    }

    /**
     * Тасалбарын кодыг шалгаж, орохыг баталгаажуулна (used болгоно).
     */
    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => ['required', 'string']]);

        $ticket = Ticket::with(['order:id,status,reference,buyer_name', 'ticketType:id,name'])
            ->where('code', $data['code'])
            ->first();

        if (! $ticket) {
            return back()->with('checkin', ['ok' => false, 'message' => 'Тасалбар олдсонгүй.']);
        }

        if ($ticket->order->status !== 'paid') {
            return back()->with('checkin', ['ok' => false, 'message' => 'Төлбөр төлөгдөөгүй тасалбар.']);
        }

        if ($ticket->status === 'used') {
            return back()->with('checkin', [
                'ok' => false,
                'message' => 'Энэ тасалбар аль хэдийн ашиглагдсан: '.$ticket->used_at?->format('Y-m-d H:i'),
            ]);
        }

        if ($ticket->status === 'cancelled') {
            return back()->with('checkin', ['ok' => false, 'message' => 'Цуцлагдсан тасалбар.']);
        }

        $ticket->update(['status' => 'used', 'used_at' => now()]);

        return back()->with('checkin', [
            'ok' => true,
            'message' => 'Зөвшөөрөгдлөө ✓',
            'detail' => ($ticket->ticketType?->name ?? 'Тасалбар').' — '.$ticket->order->buyer_name,
        ]);
    }
}
