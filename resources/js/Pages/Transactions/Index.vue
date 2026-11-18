<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { reactive, computed } from 'vue';
import { rupiah, tanggal } from '@/utils/format';

const props = defineProps({ transactions: Object, categories: Array, filters: Object });
const isAdmin = computed(() => usePage().props.auth.user.role === 'admin');

const f = reactive({ from: props.filters.from ?? '', to: props.filters.to ?? '', type: props.filters.type ?? '', category_id: props.filters.category_id ?? '' });

const apply = () => router.get(route('transactions.index'), Object.fromEntries(Object.entries(f).filter(([, v]) => v !== '')), { preserveState: true });
const reset = () => { Object.keys(f).forEach((k) => (f[k] = '')); apply(); };
const remove = (t) => confirm('Hapus transaksi ini?') && router.delete(route('transactions.destroy', t.id), { preserveScroll: true });
const cats = computed(() => props.categories.filter((c) => !f.type || c.type === f.type));
</script>

<template>
    <Head title="Transaksi" />
    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Transaksi</h1>
                <Link :href="route('transactions.create')" class="rounded-md bg-emerald-600 px-4 py-2 text-sm text-white">+ Catat Transaksi</Link>
            </div>
        </template>

        <form class="mb-4 grid gap-3 rounded-xl border bg-white p-4 sm:grid-cols-5" @submit.prevent="apply">
            <input v-model="f.from" type="date" class="rounded-md border-slate-300 text-sm" />
            <input v-model="f.to" type="date" class="rounded-md border-slate-300 text-sm" />
            <select v-model="f.type" class="rounded-md border-slate-300 text-sm" @change="f.category_id = ''">
                <option value="">Semua jenis</option><option value="income">Pemasukan</option><option value="expense">Pengeluaran</option>
            </select>
            <select v-model="f.category_id" class="rounded-md border-slate-300 text-sm">
                <option value="">Semua kategori</option><option v-for="c in cats" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
            <div class="flex gap-2">
                <button class="flex-1 rounded-md bg-slate-800 px-3 py-2 text-sm text-white">Filter</button>
                <button type="button" class="rounded-md border px-3 py-2 text-sm" @click="reset">Reset</button>
            </div>
        </form>

        <div class="overflow-x-auto rounded-xl border bg-white">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-500">
                    <tr><th class="px-4 py-2">Tanggal</th><th>Kategori</th><th>Keterangan</th><th v-if="isAdmin">Pemilik</th><th class="text-right">Jumlah</th><th class="px-4"></th></tr>
                </thead>
                <tbody>
                    <tr v-if="!transactions.data.length"><td colspan="6" class="px-4 py-6 text-center text-slate-500">Tidak ada transaksi.</td></tr>
                    <tr v-for="t in transactions.data" :key="t.id" class="border-t">
                        <td class="px-4 py-2 text-slate-500">{{ tanggal(t.date) }}</td>
                        <td>
                            <span class="rounded px-1.5 py-0.5 text-xs" :class="t.type === 'income' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700'">{{ t.category }}</span>
                        </td>
                        <td class="text-slate-600">{{ t.description }}</td>
                        <td v-if="isAdmin" class="text-slate-500">{{ t.owner }}</td>
                        <td class="text-right font-medium" :class="t.type === 'income' ? 'text-emerald-600' : 'text-rose-600'">{{ t.type === 'income' ? '+' : '−' }}{{ rupiah(t.amount) }}</td>
                        <td class="space-x-2 px-4 text-right">
                            <Link :href="route('transactions.edit', t.id)" class="text-emerald-700 hover:underline">Ubah</Link>
                            <button class="text-rose-600 hover:underline" @click="remove(t)">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="transactions.last_page > 1" class="mt-4 flex flex-wrap gap-1">
            <template v-for="l in transactions.links" :key="l.label">
                <Link v-if="l.url" :href="l.url" preserve-scroll class="rounded border px-3 py-1 text-sm"
                    :class="l.active ? 'bg-emerald-600 text-white' : 'bg-white hover:bg-slate-100'" v-html="l.label" />
                <span v-else class="rounded border px-3 py-1 text-sm text-slate-300" v-html="l.label" />
            </template>
        </div>
    </AppLayout>
</template>
