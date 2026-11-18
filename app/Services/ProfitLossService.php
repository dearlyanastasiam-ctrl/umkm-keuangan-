<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Menghasilkan laporan laba-rugi otomatis dari transaksi.
 *
 * Pendapatan            = Σ transaksi kategori income
 * HPP                   = Σ transaksi kategori expense dengan is_hpp = true
 * Laba Kotor            = Pendapatan − HPP
 * Beban Operasional     = Σ transaksi kategori expense dengan is_hpp = false
 * Laba Bersih           = Laba Kotor − Beban Operasional
 */
class ProfitLossService
{
    public function generate(string $from, string $to, ?int $userId = null): array
    {
        $rows = DB::table('transactions as t')
            ->join('categories as c', 'c.id', '=', 't.category_id')
            ->when($userId, fn ($q) => $q->where('t.user_id', $userId))
            ->whereBetween('t.date', [$from, $to])
            ->groupBy('c.id', 'c.name', 'c.type', 'c.is_hpp')
            ->select('c.id', 'c.name', 'c.type', 'c.is_hpp', DB::raw('SUM(t.amount) as total'))
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => ['id' => $r->id, 'name' => $r->name, 'type' => $r->type, 'is_hpp' => (bool) $r->is_hpp, 'total' => (int) $r->total]);

        $revenues = $rows->where('type', 'income')->values();
        $cogs = $rows->where('type', 'expense')->where('is_hpp', true)->values();
        $opex = $rows->where('type', 'expense')->where('is_hpp', false)->values();

        $totalRevenue = $revenues->sum('total');
        $totalCogs = $cogs->sum('total');
        $totalOpex = $opex->sum('total');
        $grossProfit = $totalRevenue - $totalCogs;
        $netProfit = $grossProfit - $totalOpex;

        return [
            'from' => $from,
            'to' => $to,
            'revenues' => $revenues,
            'cogs' => $cogs,
            'opex' => $opex,
            'total_revenue' => $totalRevenue,
            'total_cogs' => $totalCogs,
            'gross_profit' => $grossProfit,
            'total_opex' => $totalOpex,
            'total_expense' => $totalCogs + $totalOpex,
            'net_profit' => $netProfit,
            'net_margin' => $totalRevenue > 0 ? round($netProfit / $totalRevenue * 100, 1) : 0,
        ];
    }
}
