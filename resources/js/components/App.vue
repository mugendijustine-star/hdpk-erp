<template>
    <main class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-950 to-slate-900 text-slate-100">
        <header class="border-b border-white/10 bg-white/5 backdrop-blur sticky top-0 z-10">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                <div class="flex items-center gap-2">
                    <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500 text-lg font-black text-slate-900">H</span>
                    <div>
                        <p class="text-sm uppercase tracking-[0.2em] text-emerald-200/80">HDP(K) LTD ERP</p>
                        <p class="text-lg font-semibold">Demo Access Portal</p>
                    </div>
                </div>
                <div class="hidden items-center gap-2 text-sm sm:flex">
                    <span class="rounded-full bg-emerald-500/20 px-3 py-1 text-emerald-200">Vue 3 + Vite</span>
                    <span class="rounded-full bg-sky-500/20 px-3 py-1 text-sky-200">Laravel 10</span>
                    <span class="rounded-full bg-amber-500/20 px-3 py-1 text-amber-200">Demo only</span>
                </div>
            </div>
        </header>

        <section class="mx-auto flex max-w-6xl flex-col gap-10 px-6 py-12 lg:flex-row">
            <div class="flex-1 space-y-6">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-emerald-500/10">
                    <p class="text-sm font-semibold uppercase tracking-[0.35em] text-emerald-200">Secure Access</p>
                    <h1 class="mt-2 text-3xl font-extrabold leading-tight sm:text-4xl">
                        Try the HDP(K) ERP portal with demo sign up and login flows
                    </h1>
                    <p class="mt-3 text-lg text-slate-200/80">
                        This is a front-end only preview. Actions are simulated in-memory so you can explore the
                        onboarding experience without connecting to a backend just yet.
                    </p>
                    <div class="mt-6 grid gap-4 sm:grid-cols-2">
                        <div v-for="feature in features" :key="feature.title" class="flex gap-3 rounded-xl border border-white/10 bg-white/5 p-4">
                            <div :class="['mt-1 h-10 w-10 flex items-center justify-center rounded-lg text-slate-900', feature.tint]">{{ feature.icon }}</div>
                            <div>
                                <p class="font-semibold">{{ feature.title }}</p>
                                <p class="text-sm text-slate-200/70">{{ feature.copy }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-6">
                    <p class="text-sm font-semibold text-emerald-200">Activity log</p>
                    <ul class="mt-3 space-y-2 text-sm text-slate-200/80">
                        <li v-for="(entry, index) in activity" :key="index" class="flex items-start gap-2">
                            <span class="mt-1 h-2 w-2 rounded-full bg-emerald-400" />
                            <span>{{ entry }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="w-full max-w-xl space-y-4 lg:w-96">
                <div class="flex rounded-xl border border-white/10 bg-white/5 p-2 text-sm font-semibold">
                    <button
                        v-for="value in modes"
                        :key="value"
                        class="flex-1 rounded-lg px-4 py-2 transition hover:bg-white/10"
                        :class="{ 'bg-white text-slate-900 shadow': mode === value }"
                        @click="mode = value"
                    >
                        {{ value === 'signup' ? 'Sign up' : 'Log in' }}
                    </button>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-2xl shadow-emerald-500/10">
                    <template v-if="mode === 'signup'">
                        <h2 class="text-xl font-bold">Create demo account</h2>
                        <p class="mb-4 text-sm text-slate-200/80">No backend needed — we keep it in memory.</p>
                        <form class="space-y-4" @submit.prevent="handleSignup">
                            <div class="space-y-1">
                                <label class="text-sm font-semibold">Full name</label>
                                <input
                                    v-model="signupForm.name"
                                    class="w-full rounded-lg border border-white/10 bg-slate-900/40 px-3 py-2 outline-none focus:border-emerald-400"
                                    placeholder="Ada Lovelace"
                                    required
                                />
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm font-semibold">Email</label>
                                <input
                                    v-model="signupForm.email"
                                    type="email"
                                    class="w-full rounded-lg border border-white/10 bg-slate-900/40 px-3 py-2 outline-none focus:border-emerald-400"
                                    placeholder="demo@hdpk.test"
                                    required
                                />
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm font-semibold">Password</label>
                                <input
                                    v-model="signupForm.password"
                                    type="password"
                                    minlength="6"
                                    class="w-full rounded-lg border border-white/10 bg-slate-900/40 px-3 py-2 outline-none focus:border-emerald-400"
                                    placeholder="At least 6 characters"
                                    required
                                />
                            </div>

                            <button
                                type="submit"
                                class="flex w-full items-center justify-center gap-2 rounded-lg bg-emerald-400 px-4 py-3 text-sm font-semibold text-slate-950 transition hover:bg-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-300"
                            >
                                <span>Create demo access</span>
                            </button>
                        </form>

                        <p v-if="signupStatus" class="mt-4 rounded-lg bg-emerald-500/10 px-3 py-2 text-sm text-emerald-100">
                            {{ signupStatus }}
                        </p>
                    </template>

                    <template v-else>
                        <h2 class="text-xl font-bold">Log in to demo</h2>
                        <p class="mb-4 text-sm text-slate-200/80">
                            Use your newly created demo email or try the pre-seeded admin account.
                        </p>
                        <div class="mb-4 rounded-lg border border-amber-400/30 bg-amber-500/10 px-3 py-2 text-xs text-amber-100">
                            Pre-seeded admin: <span class="font-semibold">admin@demo.test</span> / <span class="font-semibold">password</span>
                        </div>
                        <form class="space-y-4" @submit.prevent="handleLogin">
                            <div class="space-y-1">
                                <label class="text-sm font-semibold">Email</label>
                                <input
                                    v-model="loginForm.email"
                                    type="email"
                                    class="w-full rounded-lg border border-white/10 bg-slate-900/40 px-3 py-2 outline-none focus:border-emerald-400"
                                    placeholder="you@hdpk.test"
                                    required
                                />
                            </div>
                            <div class="space-y-1">
                                <label class="text-sm font-semibold">Password</label>
                                <input
                                    v-model="loginForm.password"
                                    type="password"
                                    class="w-full rounded-lg border border-white/10 bg-slate-900/40 px-3 py-2 outline-none focus:border-emerald-400"
                                    placeholder="••••••"
                                    required
                                />
                            </div>

                            <button
                                type="submit"
                                class="flex w-full items-center justify-center gap-2 rounded-lg bg-sky-400 px-4 py-3 text-sm font-semibold text-slate-950 transition hover:bg-sky-300 focus:outline-none focus:ring-2 focus:ring-sky-200"
                            >
                                <span>Log in</span>
                            </button>
                        </form>

                        <p
                            v-if="loginStatus"
                            :class="[
                                'mt-4 rounded-lg px-3 py-2 text-sm',
                                loginSuccess ? 'bg-emerald-500/10 text-emerald-100' : 'bg-rose-500/10 text-rose-100',
                            ]"
                        >
                            {{ loginStatus }}
                        </p>
                    </template>
                </div>

                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 text-xs text-slate-200/60">
                    This interface is frontend-only. We will connect Supabase later to persist accounts and sessions.
                </div>
            </div>
        </section>
    </main>
</template>

<script setup>
import { reactive, ref } from 'vue';

const modes = ['signup', 'login'];
const mode = ref('signup');

const features = [
    {
        title: 'Trusted-device aware',
        copy: 'Preview the UX for device-based access approvals before backend wiring.',
        icon: '🔒',
        tint: 'bg-emerald-300',
    },
    {
        title: 'Role-ready flows',
        copy: 'Simulated Admin/Manager/Clerk states to show how permissions surface.',
        icon: '🗂️',
        tint: 'bg-sky-300',
    },
    {
        title: 'Obfuscated numeric rule',
        copy: 'Highlights storage = (real/3) + 5 during the onboarding walkthrough.',
        icon: '🧮',
        tint: 'bg-amber-300',
    },
    {
        title: 'Supabase handoff',
        copy: 'Clean separation so we can plug in Supabase auth without redesign.',
        icon: '🪁',
        tint: 'bg-fuchsia-300',
    },
];

const activity = ref([
    'Pre-seeded admin account ready for login.',
    'Device trust prompt mocked for this demo.',
    'Numeric values obfuscated with stored = (real/3) + 5.',
    'Upcoming: wire Supabase auth + persistence.',
]);

const signupForm = reactive({ name: '', email: '', password: '' });
const loginForm = reactive({ email: '', password: '' });

const demoAccounts = ref([
    { email: 'admin@demo.test', password: 'password', name: 'Demo Admin', role: 'Admin' },
]);

const signupStatus = ref('');
const loginStatus = ref('');
const loginSuccess = ref(false);

const resetStatuses = () => {
    signupStatus.value = '';
    loginStatus.value = '';
    loginSuccess.value = false;
};

const handleSignup = () => {
    resetStatuses();
    const exists = demoAccounts.value.find((account) => account.email === signupForm.email.trim());

    if (exists) {
        signupStatus.value = 'That email already has demo access. Please log in instead.';
        mode.value = 'login';
        return;
    }

    demoAccounts.value.push({
        email: signupForm.email.trim(),
        password: signupForm.password,
        name: signupForm.name.trim() || 'Demo User',
        role: 'Manager',
    });

    activity.value.unshift(
        `${signupForm.name || 'Demo user'} created a demo account with role Manager at ${new Date().toLocaleTimeString()}.`,
    );

    signupStatus.value = 'Demo account created! You can now log in using the email and password above.';
    mode.value = 'login';
    loginForm.email = signupForm.email;
};

const handleLogin = () => {
    resetStatuses();
    const match = demoAccounts.value.find(
        (account) => account.email === loginForm.email.trim() && account.password === loginForm.password,
    );

    if (!match) {
        loginStatus.value = 'Invalid credentials for this demo. Try the seeded admin or sign up first.';
        loginSuccess.value = false;
        return;
    }

    const greeting = `Welcome back, ${match.name}! Role: ${match.role}. Device trust confirmed (mocked).`;
    loginStatus.value = greeting;
    loginSuccess.value = true;
    activity.value.unshift(`${match.email} logged in as ${match.role} at ${new Date().toLocaleTimeString()}.`);
};
</script>
