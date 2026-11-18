<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const flash = computed(() => page.props.flash?.success);
const open = ref(false);

const links = computed(() => {
    const items = [
        { label: 'Dashboard', route: 'dashboard' },
        { label: 'Transaksi', route: 'transactions.*', href: route('transactions.index') },
        { label: 'Laba-Rugi', route: 'reports.*', href: route('reports.profit-loss') },
    ];
    if (user.value.role === 'admin') {
        items.push({ label: 'Kategori', route: 'categories.*', href: route('categories.index') });
        items.push({ label: 'Pengguna', route: 'users.*', href: route('users.index') });
    }
    return items.map((i) => ({ ...i, href: i.href ?? route(i.route) }));
});
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-800">
        <nav class="border-b bg-white print:hidden">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
                <div class="flex items-center gap-6">
                    <Link :href="route('dashboard')" class="text-lg font-bold text-emerald-700">💰 KeuanganUMKM</Link>
                    <div class="hidden gap-1 md:flex">
                        <Link v-for="l in links" :key="l.label" :href="l.href"
                            class="rounded-md px-3 py-1.5 text-sm font-medium"
                            :class="route().current(l.route) ? 'bg-emerald-50 text-emerald-700' : 'text-slate-600 hover:bg-slate-100'">
                            {{ l.label }}
                        </Link>
                    </div>
                </div>
                <div class="hidden items-center gap-3 text-sm md:flex">
                    <Link :href="route('profile.edit')" class="text-slate-600 hover:text-slate-900">
                        {{ user.name }} <span class="rounded bg-slate-100 px-1.5 py-0.5 text-xs uppercase">{{ user.role === 'admin' ? 'Admin' : 'Pemilik' }}</span>
                    </Link>
                    <Link :href="route('logout')" method="post" as="button" class="rounded-md border px-3 py-1 hover:bg-slate-100">Keluar</Link>
                </div>
                <button class="md:hidden" @click="open = !open">☰</button>
            </div>
            <div v-if="open" class="space-y-1 border-t px-4 py-2 md:hidden">
                <Link v-for="l in links" :key="l.label" :href="l.href" class="block py-1.5 text-sm">{{ l.label }}</Link>
                <Link :href="route('profile.edit')" class="block py-1.5 text-sm">Profil</Link>
                <Link :href="route('logout')" method="post" as="button" class="block py-1.5 text-sm">Keluar</Link>
            </div>
        </nav>

        <header v-if="$slots.header" class="mx-auto max-w-6xl px-4 pt-6">
            <slot name="header" />
        </header>

        <div v-if="flash" class="mx-auto mt-4 max-w-6xl px-4 print:hidden">
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm text-emerald-800">{{ flash }}</div>
        </div>

        <main class="mx-auto max-w-6xl px-4 py-6">
            <slot />
        </main>
    </div>
</template>
