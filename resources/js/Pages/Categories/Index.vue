<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ categories: Array });
const editing = ref(null);
const form = useForm({ name: '', type: 'income', is_hpp: false });

const income = computed(() => props.categories.filter((c) => c.type === 'income'));
const expense = computed(() => props.categories.filter((c) => c.type === 'expense'));

const edit = (c) => { editing.value = c.id; Object.assign(form, { name: c.name, type: c.type, is_hpp: c.is_hpp }); };
const cancel = () => { editing.value = null; form.reset(); form.clearErrors(); };
const submit = () => {
    const opts = { preserveScroll: true, onSuccess: cancel };
    editing.value ? form.put(route('categories.update', editing.value), opts) : form.post(route('categories.store'), opts);
};
const remove = (c) => confirm(`Hapus kategori "${c.name}"?`) && router.delete(route('categories.destroy', c.id), { preserveScroll: true });
</script>

<template>
    <Head title="Kategori" />
    <AppLayout>
        <template #header><h1 class="text-2xl font-bold">Kategori Transaksi</h1></template>

        <form class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border bg-white p-4" @submit.prevent="submit">
            <div class="min-w-48 flex-1">
                <label class="text-xs text-slate-500">Nama kategori</label>
                <input v-model="form.name" class="w-full rounded-md border-slate-300 text-sm" />
            </div>
            <div>
                <label class="text-xs text-slate-500">Jenis</label>
                <select v-model="form.type" class="block rounded-md border-slate-300 text-sm"><option value="income">Pemasukan</option><option value="expense">Pengeluaran</option></select>
            </div>
            <label v-if="form.type === 'expense'" class="flex items-center gap-2 pb-2 text-sm"><input v-model="form.is_hpp" type="checkbox" class="rounded" /> Termasuk HPP</label>
            <button class="rounded-md bg-emerald-600 px-4 py-2 text-sm text-white">{{ editing ? 'Perbarui' : 'Tambah' }}</button>
            <button v-if="editing" type="button" class="rounded-md border px-4 py-2 text-sm" @click="cancel">Batal</button>
            <p v-if="form.errors.name" class="w-full text-sm text-rose-600">{{ form.errors.name }}</p>
        </form>

        <div class="grid gap-4 md:grid-cols-2">
            <div v-for="group in [{ title: 'Pemasukan', items: income }, { title: 'Pengeluaran', items: expense }]" :key="group.title" class="rounded-xl border bg-white p-4">
                <h2 class="mb-2 font-semibold">{{ group.title }}</h2>
                <p v-if="!group.items.length" class="text-sm text-slate-500">Belum ada kategori.</p>
                <ul class="divide-y">
                    <li v-for="c in group.items" :key="c.id" class="flex items-center justify-between py-2 text-sm">
                        <span>{{ c.name }} <span v-if="c.is_hpp" class="ml-1 rounded bg-amber-100 px-1.5 text-xs text-amber-700">HPP</span>
                            <span class="ml-1 text-xs text-slate-400">({{ c.transactions_count }} transaksi)</span></span>
                        <span class="space-x-2">
                            <button class="text-emerald-700 hover:underline" @click="edit(c)">Ubah</button>
                            <button class="text-rose-600 hover:underline" @click="remove(c)">Hapus</button>
                        </span>
                    </li>
                </ul>
            </div>
        </div>
    </AppLayout>
</template>
