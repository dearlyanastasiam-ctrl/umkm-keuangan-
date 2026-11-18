<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ users: Array });
const editing = ref(null);
const form = useForm({ name: '', email: '', role: 'owner', business_name: '', password: '' });

const edit = (u) => { editing.value = u.id; Object.assign(form, { name: u.name, email: u.email, role: u.role, business_name: u.business_name ?? '', password: '' }); };
const cancel = () => { editing.value = null; form.reset(); form.clearErrors(); };
const submit = () => {
    const opts = { preserveScroll: true, onSuccess: cancel };
    editing.value ? form.put(route('users.update', editing.value), opts) : form.post(route('users.store'), opts);
};
const remove = (u) => confirm(`Hapus akun ${u.name}? Semua transaksinya ikut terhapus.`) && router.delete(route('users.destroy', u.id), { preserveScroll: true });
</script>

<template>
    <Head title="Pengguna" />
    <AppLayout>
        <template #header><h1 class="text-2xl font-bold">Kelola Akun</h1></template>

        <form class="mb-6 grid gap-3 rounded-xl border bg-white p-4 sm:grid-cols-3" @submit.prevent="submit">
            <div><label class="text-xs text-slate-500">Nama</label><input v-model="form.name" class="w-full rounded-md border-slate-300 text-sm" /><p class="text-xs text-rose-600">{{ form.errors.name }}</p></div>
            <div><label class="text-xs text-slate-500">Email</label><input v-model="form.email" type="email" class="w-full rounded-md border-slate-300 text-sm" /><p class="text-xs text-rose-600">{{ form.errors.email }}</p></div>
            <div><label class="text-xs text-slate-500">Nama usaha</label><input v-model="form.business_name" class="w-full rounded-md border-slate-300 text-sm" /></div>
            <div><label class="text-xs text-slate-500">Peran</label>
                <select v-model="form.role" class="w-full rounded-md border-slate-300 text-sm"><option value="owner">Pemilik</option><option value="admin">Admin</option></select></div>
            <div><label class="text-xs text-slate-500">Kata sandi {{ editing ? '(kosongkan jika tidak diubah)' : '' }}</label>
                <input v-model="form.password" type="password" class="w-full rounded-md border-slate-300 text-sm" /><p class="text-xs text-rose-600">{{ form.errors.password }}</p></div>
            <div class="flex items-end gap-2">
                <button class="rounded-md bg-emerald-600 px-4 py-2 text-sm text-white">{{ editing ? 'Perbarui' : 'Tambah Akun' }}</button>
                <button v-if="editing" type="button" class="rounded-md border px-4 py-2 text-sm" @click="cancel">Batal</button>
            </div>
        </form>

        <div class="overflow-x-auto rounded-xl border bg-white">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-slate-500"><tr><th class="px-4 py-2">Nama</th><th>Email</th><th>Usaha</th><th>Peran</th><th>Transaksi</th><th></th></tr></thead>
                <tbody>
                    <tr v-for="u in users" :key="u.id" class="border-t">
                        <td class="px-4 py-2 font-medium">{{ u.name }}</td><td>{{ u.email }}</td><td>{{ u.business_name || '–' }}</td>
                        <td><span class="rounded px-1.5 py-0.5 text-xs" :class="u.role === 'admin' ? 'bg-violet-100 text-violet-700' : 'bg-emerald-50 text-emerald-700'">{{ u.role === 'admin' ? 'Admin' : 'Pemilik' }}</span></td>
                        <td>{{ u.transactions_count }}</td>
                        <td class="space-x-2 px-4 text-right">
                            <button class="text-emerald-700 hover:underline" @click="edit(u)">Ubah</button>
                            <button class="text-rose-600 hover:underline" @click="remove(u)">Hapus</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
