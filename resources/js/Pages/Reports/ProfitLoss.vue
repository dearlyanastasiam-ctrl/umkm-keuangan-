<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { reactive, computed } from 'vue';
import { rupiah, tanggal } from '@/utils/format';

const props = defineProps({ report: Object, filters: Object, owners: Array, business: String });
const isAdmin = computed(() => usePage().props.auth.user.role === 'admin');
const f = reactive({ from: props.filters.from, to: props.filters.to, user_id: props.filters.user_id ?? '' });

const query = () => Object.fromEntries(Object.entries(f).filter(([, v]) => v !== ''));
const apply = () => router.get(route('reports.profit-loss'), query(), { preserveState: true });
const exportUrl = computed(() => route('reports.export', query()));
const print = () => window.print();

const r = computed(() => props.report);
const pct = (v) => (r.value.total_revenue > 0 ? ((v / r.value.total_revenue) * 100).toFixed(1) + '%' : '–');
</script>

<template>
    <Head title="Laporan Laba-Rugi" />
    <AppLayout>
        <form class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border bg-white p-4 print:hidden" @submit.prevent="apply">
            <div><label class="block text-xs text-slate-500">Dari</label><input v-model="f.from" type="date" class="rounded-md border-slate-300 text-sm" /></div>
            <div><label class="block text-xs text-slate-500">Sampai</label><input v-model="f.to" type="date" class="rounded-md border-slate-300 text-sm" /></div>
            <div v-if="isAdmin">
                <label class="block text-xs text-slate-500">UMKM</label>
                <select v-model="f.user_id" class="rounded-md border-slate-300 text-sm">
                    <option value="">Semua</option>
                    <option v-for="o in owners" :key="o.id" :value="o.id">{{ o.business_name || o.name }}</option>
                </select>
            </div>
            <button class="rounded-md bg-slate-800 px-4 py-2 text-sm text-white">Tampilkan</button>
            <div class="ml-auto flex gap-2">
                <a :href="exportUrl" class="rounded-md border px-4 py-2 text-sm hover:bg-slate-50">⬇ CSV</a>
                <button type="button" class="rounded-md border px-4 py-2 text-sm hover:bg-slate-50" @click="print">🖨 Cetak</button>
            </div>
        </form>

        <div class="mx-auto max-w-2xl rounded-xl border bg-white p-8 print:border-0">
            <div class="mb-6 text-center">
                <h1 class="text-xl font-bold uppercase">Laporan Laba Rugi</h1>
                <p class="text-sm text-slate-600">{{ business }}</p>
                <p class="text-sm text-slate-500">{{ tanggal(r.from) }} – {{ tanggal(r.to) }}</p>
            </div>

            <table class="w-full text-sm">
                <tbody>
                    <tr class="bg-slate-50 font-semibold"><td colspan="3" class="px-2 py-1.5">PENDAPATAN</td></tr>
                    <tr v-for="x in r.revenues" :key="x.id"><td class="px-2 py-1 pl-6">{{ x.name }}</td><td class="text-right text-slate-400">{{ pct(x.total) }}</td><td class="text-right">{{ rupiah(x.total) }}</td></tr>
                    <tr class="border-t font-semibold"><td class="px-2 py-1.5" colspan="2">Total Pendapatan</td><td class="text-right">{{ rupiah(r.total_revenue) }}</td></tr>

                    <tr class="bg-slate-50 font-semibold"><td colspan="3" class="px-2 py-1.5">HARGA POKOK PENJUALAN</td></tr>
                    <tr v-for="x in r.cogs" :key="x.id"><td class="px-2 py-1 pl-6">{{ x.name }}</td><td class="text-right text-slate-400">{{ pct(x.total) }}</td><td class="text-right">({{ rupiah(x.total) }})</td></tr>
                    <tr class="border-t font-semibold"><td class="px-2 py-1.5" colspan="2">Total HPP</td><td class="text-right">({{ rupiah(r.total_cogs) }})</td></tr>

                    <tr class="border-y-2 border-slate-300 bg-emerald-50 font-bold"><td class="px-2 py-2" colspan="2">LABA KOTOR</td><td class="text-right">{{ rupiah(r.gross_profit) }}</td></tr>

                    <tr class="bg-slate-50 font-semibold"><td colspan="3" class="px-2 py-1.5">BEBAN OPERASIONAL</td></tr>
                    <tr v-for="x in r.opex" :key="x.id"><td class="px-2 py-1 pl-6">{{ x.name }}</td><td class="text-right text-slate-400">{{ pct(x.total) }}</td><td class="text-right">({{ rupiah(x.total) }})</td></tr>
                    <tr class="border-t font-semibold"><td class="px-2 py-1.5" colspan="2">Total Beban Operasional</td><td class="text-right">({{ rupiah(r.total_opex) }})</td></tr>

                    <tr class="border-y-2 border-slate-800 text-base font-bold" :class="r.net_profit >= 0 ? 'bg-emerald-100' : 'bg-rose-100'">
                        <td class="px-2 py-3" colspan="2">{{ r.net_profit >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH' }}</td><td class="text-right">{{ rupiah(r.net_profit) }}</td>
                    </tr>
                </tbody>
            </table>

            <p class="mt-4 text-sm text-slate-600">
                Margin laba bersih: <strong>{{ r.net_margin }}%</strong>
                <span v-if="r.net_profit < 0" class="text-rose-600"> — usaha mengalami kerugian pada periode ini.</span>
            </p>
        </div>
    </AppLayout>
</template>
