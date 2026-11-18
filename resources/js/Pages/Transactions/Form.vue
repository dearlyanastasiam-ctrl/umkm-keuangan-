<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { rupiah } from '@/utils/format';

const props = defineProps({ transaction: Object, categories: Array });
const t = props.transaction;

const form = useForm({
    type: t?.type ?? 'income',
    category_id: t?.category_id ?? '',
    date: t?.date ?? new Date().toISOString().slice(0, 10),
    amount: t?.amount ?? '',
    description: t?.description ?? '',
});

const options = computed(() => props.categories.filter((c) => c.type === form.type));
watch(() => form.type, () => { if (!options.value.some((c) => c.id === form.category_id)) form.category_id = ''; });

const submit = () => (t ? form.put(route('transactions.update', t.id)) : form.post(route('transactions.store')));
</script>

<template>
    <Head :title="t ? 'Ubah Transaksi' : 'Catat Transaksi'" />
    <AppLayout>
        <template #header><h1 class="text-2xl font-bold">{{ t ? 'Ubah Transaksi' : 'Catat Transaksi' }}</h1></template>

        <form class="max-w-xl space-y-4 rounded-xl border bg-white p-6" @submit.prevent="submit">
            <div class="grid grid-cols-2 gap-2">
                <button type="button" class="rounded-md border px-3 py-2 text-sm font-medium"
                    :class="form.type === 'income' ? 'border-emerald-500 bg-emerald-50 text-emerald-700' : ''" @click="form.type = 'income'">Pemasukan</button>
                <button type="button" class="rounded-md border px-3 py-2 text-sm font-medium"
                    :class="form.type === 'expense' ? 'border-rose-500 bg-rose-50 text-rose-700' : ''" @click="form.type = 'expense'">Pengeluaran</button>
            </div>

            <div>
                <label class="text-sm font-medium">Kategori</label>
                <select v-model="form.category_id" class="mt-1 w-full rounded-md border-slate-300">
                    <option value="" disabled>Pilih kategori</option>
                    <option v-for="c in options" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
                <p v-if="form.errors.category_id" class="mt-1 text-sm text-rose-600">{{ form.errors.category_id }}</p>
            </div>

            <div>
                <label class="text-sm font-medium">Tanggal</label>
                <input v-model="form.date" type="date" class="mt-1 w-full rounded-md border-slate-300" />
                <p v-if="form.errors.date" class="mt-1 text-sm text-rose-600">{{ form.errors.date }}</p>
            </div>

            <div>
                <label class="text-sm font-medium">Jumlah (Rp)</label>
                <input v-model="form.amount" type="number" min="1" class="mt-1 w-full rounded-md border-slate-300" placeholder="contoh: 250000" />
                <p v-if="form.amount" class="mt-1 text-xs text-slate-500">{{ rupiah(form.amount) }}</p>
                <p v-if="form.errors.amount" class="mt-1 text-sm text-rose-600">{{ form.errors.amount }}</p>
            </div>

            <div>
                <label class="text-sm font-medium">Keterangan</label>
                <input v-model="form.description" type="text" class="mt-1 w-full rounded-md border-slate-300" placeholder="opsional" />
            </div>

            <div class="flex gap-2">
                <button :disabled="form.processing" class="rounded-md bg-emerald-600 px-4 py-2 text-sm text-white disabled:opacity-50">Simpan</button>
                <Link :href="route('transactions.index')" class="rounded-md border px-4 py-2 text-sm">Batal</Link>
            </div>
        </form>
    </AppLayout>
</template>
