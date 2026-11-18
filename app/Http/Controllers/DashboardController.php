<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\ProfitLossService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request, ProfitLossService $svc)
    {
        $user = $request->user();
        $uid = $user->isAdmin() ? null : $user->id;

        $now = now();
        $current = $svc->generate($now->copy()->startOfMonth()->toDateString(), $now->copy()->endOfMonth()->toDateString(), $uid);
        $prevStart = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $previous = $svc->generate($prevStart->toDateString(), $prevStart->copy()->endOfMonth()->toDateString(), $uid);

        $trend = collect(range(5, 0))->map(function ($i) use ($svc, $uid, $now) {
            $m = $now->copy()->subMonthsNoOverflow($i)->startOfMonth();
            $r = $svc->generate($m->toDateString(), $m->copy()->endOfMonth()->toDateString(), $uid);

            return ['label' => $m->format('M Y'), 'income' => $r['total_revenue'], 'expense' => $r['total_expense'], 'profit' => $r['net_profit']];
        })->values();

        $topExpenses = collect($current['cogs'])->merge($current['opex'])->sortByDesc('total')->take(5)->values();

        $recent = Transaction::visibleTo($user)->with(['category', 'user'])
            ->latest('date')->latest('id')->limit(5)->get()
            ->map(fn ($t) => [
                'id' => $t->id, 'date' => $t->date->format('Y-m-d'), 'amount' => $t->amount,
                'description' => $t->description, 'type' => $t->category->type, 'category' => $t->category->name,
            ]);

        return Inertia::render('Dashboard', [
            'month' => $now->format('F Y'),
            'current' => $current,
            'previous' => $previous,
            'trend' => $trend,
            'topExpenses' => $topExpenses,
            'recent' => $recent,
            'target' => $user->profit_target,
        ]);
    }

    public function updateTarget(Request $request)
    {
        $data = $request->validate(['profit_target' => ['nullable', 'integer', 'min:0', 'max:100000000000']]);
        $request->user()->update($data);

        return back()->with('success', 'Target laba bulanan diperbarui.');
    }
}
