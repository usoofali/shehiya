<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ArrowLeft, Camera, CheckCircle, Clock, FileCheck, History, ShieldAlert, Upload, User, XCircle } from 'lucide-vue-next';

const props = defineProps<{
    member: {
        id: number;
        membership_number: string;
        first_name: string;
        last_name: string;
        gender: string;
        dob: string;
        phone: string;
        email?: string;
        occupation?: string;
        photo_path?: string;
        status: string;
        registered_at: string;
        state?: { name: string };
        lga?: { name: string };
        ward?: { name: string };
        polling_unit?: { id: number; code: string | null; name: string };
        verifications: Array<{
            id: number;
            previous_status: string;
            new_status: string;
            comments?: string;
            created_at: string;
            verified_by?: { name: string };
        }>;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Members Directory', href: '/members' },
    { title: `${props.member.first_name} ${props.member.last_name}`, href: `/members/${props.member.id}` },
];

const page = usePage();

const verifyForm = useForm({
    new_status: 'verified',
    comments: '',
});

const submitVerification = (status: 'verified' | 'rejected') => {
    verifyForm.new_status = status;
    verifyForm.post(`/members/${props.member.id}/verify`, {
        onSuccess: () => {
            verifyForm.reset('comments');
        },
    });
};

const user = computed(() => (page.props.auth as any)?.user);
const userRoles = computed(() => user.value?.roles || []);
const canEditOrVerify = computed(() => {
    return userRoles.value.some((role: string) => 
        ['Super Administrator', 'National Administrator', 'State Coordinator', 'LGA Coordinator', 'Ward Coordinator', 'Polling Unit Coordinator'].includes(role)
    ) || (user.value?.permissions && (user.value.permissions.includes('edit members') || user.value.permissions.includes('verify members')));
});

const photoForm = useForm({
    photo: null as File | null,
});

const handlePhotoChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        photoForm.photo = target.files[0];
    }
};

const submitPhotoUpdate = () => {
    if (!photoForm.photo) return;
    photoForm.post(`/members/${props.member.id}/photo`, {
        onSuccess: () => {
            photoForm.reset();
            const fileInput = document.querySelector('input[type="file"]') as HTMLInputElement | null;
            if (fileInput) fileInput.value = '';
        },
    });
};
</script>

<template>
    <Head :title="`${member.first_name} ${member.last_name} - Profile`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="mx-auto flex w-full max-w-5xl flex-1 flex-col gap-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between border-b border-border pb-6">
                <div class="flex items-center gap-4">
                    <div v-if="member.photo_path" class="size-16 shrink-0 overflow-hidden rounded-full border-2 border-emerald-500/30 shadow-sm">
                        <img :src="`/storage/${member.photo_path}`" :alt="`${member.first_name} ${member.last_name}`" class="size-full object-cover" />
                    </div>
                    <div v-else class="flex size-16 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-2xl font-bold text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-200">
                        {{ member.first_name[0] }}{{ member.last_name[0] }}
                    </div>
                    <div>
                        <div class="flex items-center gap-3">
                            <h1 class="text-2xl font-bold tracking-tight text-foreground sm:text-3xl">{{ member.first_name }} {{ member.last_name }}</h1>
                            <span
                                class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold capitalize"
                                :class="{
                                    'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300': member.status === 'verified',
                                    'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300': member.status === 'pending',
                                    'bg-rose-100 text-rose-800 dark:bg-rose-900/30 dark:text-rose-300': member.status === 'rejected',
                                }"
                            >
                                <CheckCircle v-if="member.status === 'verified'" class="size-3.5" />
                                <Clock v-else-if="member.status === 'pending'" class="size-3.5" />
                                <XCircle v-else class="size-3.5" />
                                {{ member.status }}
                            </span>
                        </div>
                        <p class="font-mono text-sm text-muted-foreground">{{ member.membership_number }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a
                        :href="`/badge/${member.membership_number}`"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-500/30 bg-emerald-50 px-3.5 py-2 text-xs font-semibold text-emerald-800 shadow-sm transition hover:bg-emerald-100 dark:border-emerald-500/40 dark:bg-emerald-950/40 dark:text-emerald-200 dark:hover:bg-emerald-900"
                    >
                        View ID Badge
                    </a>
                    <Link href="/members" class="inline-flex items-center gap-1 text-sm font-medium text-muted-foreground hover:text-foreground">
                        <ArrowLeft class="size-4" /> Back to Directory
                    </Link>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Left Details Column -->
                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:bg-sidebar">
                        <h3 class="flex items-center gap-2 font-semibold text-foreground"><User class="size-5 text-emerald-600" /> Member Details</h3>
                        <dl class="mt-4 grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                            <div>
                                <dt class="text-xs font-medium text-muted-foreground">Full Name</dt>
                                <dd class="mt-1 text-sm font-semibold text-foreground">{{ member.first_name }} {{ member.last_name }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-muted-foreground">Gender & DOB</dt>
                                <dd class="mt-1 text-sm text-foreground">{{ member.gender }} • {{ member.dob }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-muted-foreground">Phone Number</dt>
                                <dd class="mt-1 text-sm font-mono text-foreground">{{ member.phone }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-muted-foreground">Email Address</dt>
                                <dd class="mt-1 text-sm text-foreground">{{ member.email || 'Not provided' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-muted-foreground">Occupation</dt>
                                <dd class="mt-1 text-sm text-foreground">{{ member.occupation || 'Not provided' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-muted-foreground">Registration Date</dt>
                                <dd class="mt-1 text-sm text-foreground">{{ new Date(member.registered_at).toLocaleDateString() }}</dd>
                            </div>
                        </dl>
                    </div>

                    <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:bg-sidebar">
                        <h3 class="flex items-center gap-2 font-semibold text-foreground"><ShieldAlert class="size-5 text-indigo-600" /> Geographical Jurisdiction</h3>
                        <dl class="mt-4 grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-3">
                            <div>
                                <dt class="text-xs font-medium text-muted-foreground">State</dt>
                                <dd class="mt-1 text-sm font-semibold text-foreground">{{ member.state?.name }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-muted-foreground">Local Government Area</dt>
                                <dd class="mt-1 text-sm font-semibold text-foreground">{{ member.lga?.name }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-muted-foreground">Registration Ward</dt>
                                <dd class="mt-1 text-sm font-semibold text-foreground">{{ member.ward?.name }}</dd>
                            </div>
                            <div class="sm:col-span-3">
                                <dt class="text-xs font-medium text-muted-foreground">Polling Unit (INEC)</dt>
                                <dd class="mt-1">
                                    <span v-if="member.polling_unit" class="inline-flex items-center gap-1.5 rounded-full bg-amber-100 px-3 py-1 text-sm font-semibold text-amber-800 dark:bg-amber-900/30 dark:text-amber-300">
                                        <svg class="size-3.5 fill-amber-600 dark:fill-amber-400" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                                        {{ member.polling_unit.code ? `${member.polling_unit.code} — ` : '' }}{{ member.polling_unit.name }}
                                    </span>
                                    <span v-else class="text-sm text-muted-foreground italic">Not assigned</span>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Audit Trail Timeline -->
                    <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:bg-sidebar">
                        <h3 class="flex items-center gap-2 font-semibold text-foreground"><History class="size-5 text-amber-600" /> Verification Audit Trail</h3>
                        <div v-if="member.verifications.length === 0" class="mt-4 text-sm text-muted-foreground">
                            No verification actions recorded yet.
                        </div>
                        <div v-else class="mt-6 space-y-6 border-l-2 border-muted pl-4">
                            <div v-for="log in member.verifications" :key="log.id" class="relative">
                                <div class="absolute -left-[21px] top-1 size-2.5 rounded-full bg-emerald-600"></div>
                                <div class="flex items-center justify-between text-xs text-muted-foreground">
                                    <span class="font-semibold text-foreground">{{ log.verified_by?.name || 'System / Admin' }}</span>
                                    <span>{{ new Date(log.created_at).toLocaleString() }}</span>
                                </div>
                                <p class="mt-1 text-sm text-foreground">
                                    Changed status from <span class="font-mono uppercase text-muted-foreground">{{ log.previous_status }}</span> to
                                    <span class="font-semibold uppercase text-emerald-600 dark:text-emerald-400">{{ log.new_status }}</span>
                                </p>
                                <p v-if="log.comments" class="mt-1.5 rounded-lg bg-muted p-2 text-xs italic text-muted-foreground">
                                    "{{ log.comments }}"
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Action Panel -->
                <div class="space-y-6">
                    <!-- Member Photograph Card -->
                    <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:bg-sidebar">
                        <h3 class="flex items-center gap-2 font-semibold text-foreground">
                            <Camera class="size-5 text-emerald-600" /> Passport Photograph
                        </h3>
                        <p class="mt-1 text-xs text-muted-foreground">Official portrait photo used on digital ID cards and verification checks.</p>

                        <div class="mt-5 flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-border bg-muted/30 p-4 text-center">
                            <div v-if="member.photo_path" class="relative group">
                                <img
                                    :src="`/storage/${member.photo_path}`"
                                    :alt="`${member.first_name} ${member.last_name}`"
                                    class="size-36 rounded-2xl object-cover shadow-md border-4 border-emerald-500/20"
                                />
                                <div class="mt-2 text-xs font-semibold text-emerald-600 dark:text-emerald-400">✓ Photo Uploaded</div>
                            </div>
                            <div v-else class="flex flex-col items-center py-4">
                                <div class="flex size-20 items-center justify-center rounded-full bg-muted text-3xl font-bold text-muted-foreground">
                                    {{ member.first_name[0] }}{{ member.last_name[0] }}
                                </div>
                                <p class="mt-2 text-xs font-semibold text-rose-500">No passport photograph attached</p>
                            </div>
                        </div>

                        <!-- Photo Update Form for Admin / Coordinator -->
                        <div v-if="canEditOrVerify" class="mt-5 border-t border-border pt-4">
                            <h4 class="text-xs font-bold uppercase text-foreground">Update / Upload Photo</h4>
                            <p class="text-[11px] text-muted-foreground">Select a high-definition portrait photo (max 2MB).</p>

                            <form @submit.prevent="submitPhotoUpdate" class="mt-3 space-y-3">
                                <div>
                                    <input
                                        type="file"
                                        accept="image/*"
                                        @change="handlePhotoChange"
                                        class="block w-full text-xs text-muted-foreground file:mr-3 file:rounded-xl file:border-0 file:bg-emerald-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-emerald-700 hover:file:bg-emerald-100 dark:file:bg-emerald-950/50 dark:file:text-emerald-300"
                                    />
                                    <p v-if="photoForm.errors.photo" class="mt-1 text-xs text-rose-500">{{ photoForm.errors.photo }}</p>
                                </div>

                                <button
                                    type="submit"
                                    :disabled="photoForm.processing || !photoForm.photo"
                                    class="inline-flex w-full items-center justify-center gap-1.5 rounded-xl bg-secondary px-4 py-2 text-xs font-semibold text-secondary-foreground shadow-sm transition hover:bg-secondary/80 disabled:opacity-50"
                                >
                                    <Upload class="size-3.5" />
                                    {{ photoForm.processing ? 'Uploading...' : 'Save Photograph' }}
                                </button>
                            </form>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-border bg-card p-6 shadow-sm dark:bg-sidebar">
                        <h3 class="flex items-center gap-2 font-semibold text-foreground"><FileCheck class="size-5 text-emerald-600" /> Review Application</h3>
                        <p class="mt-1 text-xs text-muted-foreground">As an authorized coordinator, you can review and change the verification status of this member.</p>

                        <form @submit.prevent class="mt-6 space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-foreground">Audit Comments / Reason</label>
                                <textarea
                                    v-model="verifyForm.comments"
                                    rows="3"
                                    class="mt-1 w-full rounded-xl border border-input bg-background p-2.5 text-sm focus:border-emerald-500 focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                    placeholder="Optional notes for audit trail..."
                                ></textarea>
                            </div>

                            <div class="flex flex-col gap-2.5">
                                <button
                                    type="button"
                                    @click="submitVerification('verified')"
                                    :disabled="verifyForm.processing || member.status === 'verified'"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-emerald-600 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-500 disabled:opacity-50 dark:bg-emerald-500 dark:hover:bg-emerald-400"
                                >
                                    <CheckCircle class="size-4" /> Approve & Verify Member
                                </button>
                                <button
                                    type="button"
                                    @click="submitVerification('rejected')"
                                    :disabled="verifyForm.processing || member.status === 'rejected'"
                                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-rose-600 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-rose-500 disabled:opacity-50"
                                >
                                    <XCircle class="size-4" /> Reject Application
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
