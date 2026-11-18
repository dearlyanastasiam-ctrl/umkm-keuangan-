<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ProfitLossService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    private function resolve(Request $request): array
    {
        $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from']]);

        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());
        $userId = $request->user()->isAdmin() ? ($request->integer('user_id') ?: null) : $request->user()->id;

        return [$from, $to, $userId];
    }

    public function profitLoss(Request $request, ProfitLossService $svc)
    {
        [$from, $to, $userId] = $this->resolve($request);

        return Inertia::render('Reports/ProfitLoss', [
            'report' => $svc->generate($from, $to, $userId),
            'filters' => ['from' => $from, 'to' => $to, 'user_id' => $userId],
            'owners' => $request->user()->isAdmin() ? User::where('role', 'owner')->orderBy('name')->get(['id', 'name', 'business_name']) : [],
            'business' => $userId ? optional(User::find($userId))->business_name : 'Semua UMKM',
        ]);
    }

    public function export(Request $request, ProfitLossService $svc)
    {
        [$from, $to, $userId] = $this->resolve($request);
        $r = $svc->generate($from, $to, $userId);

        return response()->streamDownload(function () use ($r) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['LAPORAN LABA RUGI', "{$r['from']} s/d {$r['to']}"]);
            fputcsv($out, ['Pos', 'Jumlah (Rp)']);
            fputcsv($out, ['PENDAPATAN', '']);
            foreach ($r['revenues'] as $x) fputcsv($out, ['  '.$x['name'], $x['total']]);
            fputcsv($out, ['Total Pendapatan', $r['total_revenue']]);
            fputcsv($out, ['HARGA POKOK PENJUALAN', '']);
            foreach ($r['cogs'] as $x) fputcsv($out, ['  '.$x['name'], $x['total']]);
            fputcsv($out, ['Total HPP', $r['total_cogs']]);
            fputcsv($out, ['LABA KOTOR', $r['gross_profit']]);
            fputcsv($out, ['BEBAN OPERASIONAL', '']);
            foreach ($r['opex'] as $x) fputcsv($out, ['  '.$x['name'], $x['total']]);
            fputcsv($out, ['Total Beban Operasional', $r['total_opex']]);
            fputcsv($out, ['LABA BERSIH', $r['net_profit']]);
            fputcsv($out, ['Margin Laba Bersih (%)', $r['net_margin']]);
            fclose($out);
        }, "laba-rugi_{$from}_{$to}.csv", ['Content-Type' => 'text/csv']);
    }
}
