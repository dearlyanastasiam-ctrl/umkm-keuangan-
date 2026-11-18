<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { rupiah, tanggal } from '@/utils/format';

const props = defineProps({
    month: String, current: Object, previous: Object, trend: Array,
    topExpenses: Array, recent: Array, target: Number,
});

const isOwner = computed(() => usePage().props.auth.user.role === 'owner');
const targetForm = useForm({ profit_target: props.target ?? '' });

const change = (now, before) => {
    if (!before) return null;
    return Math.round(((now - before) / Math.abs(before)) * 100);
};
const profitChange = computed(() => change(props.current.net_profit, props.previous.net_profit));
const maxBar = computed(() => Math.max(1, ...props.trend.flatMap((t) => [t.income, t.expense])));
const targetPct = computed(() => (props.target ? Math.min(100, Math.max(0, Math.round((props.current.net_profit / props.target) * 100))) : 0));
const alerts = computed(() => {
    const a = [];
    if (props.current.net_profit < 0) a.push('Pengeluaran bulan ini melebihi pemasukan. Periksa pos beban terbesar.');
    if (props.current.total_revenue > 0 && props.current.net_margin < 10 && props.current.net_profit >= 0) a.push('Margin laba bersih di bawah 10%. Pertimbangkan efisiensi biaya atau penyesuaian harga.');
    const top = props.topExpenses[0];
    if (top && props.current.total_expense > 0 && top.total / props.current.total_expense > 0.5) a.push(`"${top.name}" menyerap lebih dari 50% total pengeluaran.`);
    return a;
});
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout>
        <template #header>
            <h1 class="text-2xl font-bold">Ringkasan Keuangan</h1>
            <p class="text-sm text-slate-500">Periode {{ month }}</p>
        </template>

        <div v-for="a in alerts" :key="a" class="mb-3 rounded-md border border-amber-200 bg-amber-50 px-4 py-2 text-sm text-amber-800">⚠️ {{ a }}</div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div class="rounded-xl border bg-white p-4">
                <p class="text-sm text-slate-500">Pemasukan</p>
                <p class="mt-1 text-xl font-bold text-emerald-600">{{ rupiah(current.total_revenue) }}</p>
            </div>
            <div class="rounded-xl border bg-white p-4">
                <p class="text-sm text-slate-500">Pengeluaran</p>
                <p class="mt-1 text-xl font-bold text-rose-600">{{ rupiah(current.total_expense) }}</p>
            </div>
            <div class="rounded-xl border bg-white p-4">
                <p class="text-sm text-slate-500">Laba Bersih</p>
                <p class="mt-1 text-xl font-bold" :class="current.net_profit >= 0 ? 'text-slate-900' : 'text-rose-600'">{{ rupiah(current.net_profit) }}</p>
                <p v-if="profitChange !== null" class="text-xs" :class="profitChange >= 0 ? 'text-emerald-600' : 'text-rose-600'">
                    {{ profitChange >= 0 ? '▲' : '▼' }} {{ Math.abs(profitChange) }}% dari bulan lalu
                </p>
            </div>
            <div class="rounded-xl border bg-white p-4">
                <p class="text-sm text-slate-500">Margin Laba Bersih</p>
                <p class="mt-1 text-xl font-bold">{{ current.net_margin }}%</p>
                <p class="text-xs text-slate-500">Laba kotor {{ rupiah(current.gross_profit) }}</p>
            </div>
        </div>

        <div class="mt-6 grid gap-4 lg:grid-cols-3">
            <div class="rounded-xl border bg-white p-4 lg:col-span-2">
                <h2 class="mb-3 font-semibold">Tren 6 Bulan Terakhir</h2>
                <div class="flex h-48 items-end gap-3">
                    <div v-for="t in trend" :key="t.label" class="flex flex-1 flex-col items-center gap-1">
                        <div class="flex h-40 w-full items-end justify-center gap-1">
                            <div class="w-1/2 rounded-t bg-emerald-500" :style="{ height: (t.income / maxBar) * 100 + '%' }" :title="'Pemasukan ' + rupiah(t.income)"></div>
                            <div class="w-1/2 rounded-t bg-rose-400" :style="{ height: (t.expense / maxBar) * 100 + '%' }" :title="'Pengeluaran ' + rupiah(t.expense)"></div>
                        </div>
                        <span class="text-xs text-slate-500">{{ t.label }}</span>
                    </div>
                </div>
                <div class="mt-2 flex gap-4 text-xs text-slate-500">
                    <span><span class="mr-1 inline-block h-2 w-2 rounded bg-emerald-500"></span>Pemasukan</span>
                    <span><span class="mr-1 inline-block h-2 w-2 rounded bg-rose-400"></span>Pengeluaran</span>
                </div>
            </div>

            <div class="space-y-4">
                <div v-if="isOwner" class="rounded-xl border bg-white p-4">
                    <h2 class="mb-2 font-semibold">Target Laba Bulanan</h2>
                    <template v-if="target">
                        <div class="h-2 overflow-hidden rounded bg-slate-100"><div class="h-2 bg-emerald-500" :style="{ width: targetPct + '%' }"></div></div>
                        <p class="mt-1 text-xs text-slate-500">{{ targetPct }}% dari {{ rupiah(target) }}</p>
                    </template>
                    <form class="mt-2 flex gap-2" @submit.prevent="targetForm.patch(route('dashboard.target'), { preserveScroll: true })">
                        <input v-model="targetForm.profit_target" type="number" min="0" placeholder="Rp target" class="w-full rounded-md border-slate-300 text-sm" />
                        <button class="rounded-md bg-emerald-600 px-3 text-sm text-white">Simpan</button>
                    </form>
                </div>

                <div class="rounded-xl border bg-white p-4">
                    <h2 class="mb-2 font-semibold">Pengeluaran Terbesar</h2>
                    <p v-if="!topExpenses.length" class="text-sm text-slate-500">Belum ada data.</p>
                    <ul class="space-y-2">
                        <li v-for="e in topExpenses" :key="e.id">
                            <div class="flex justify-between text-sm"><span>{{ e.name }}</span><span>{{ rupiah(e.total) }}</span></div>
                            <div class="h-1.5 rounded bg-slate-100"><div class="h-1.5 rounded bg-rose-400" :style="{ width: (e.total / current.total_expense) * 100 + '%' }"></div></div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="mt-6 rounded-xl border bg-white p-4">
            <div class="mb-3 flex items-center justify-between">
                <h2 class="font-semibold">Transaksi Terbaru</h2>
                <Link :href="route('transactions.create')" class="rounded-md bg-emerald-600 px-3 py-1.5 text-sm text-white">+ Catat Transaksi</Link>
            </div>
            <p v-if="!recent.length" class="text-sm text-slate-500">Belum ada transaksi.</p>
            <table v-else class="w-full text-sm">
                <tbody>
                    <tr v-for="t in recent" :key="t.id" class="border-t">
                        <td class="py-2 text-slate-500">{{ tanggal(t.date) }}</td>
                        <td>{{ t.category }}<span v-if="t.description" class="text-slate-400"> · {{ t.description }}</span></td>
                        <td class="text-right font-medium" :class="t.type === 'income' ? 'text-emerald-600' : 'text-rose-600'">
                            {{ t.type === 'income' ? '+' : '−' }}{{ rupiah(t.amount) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
