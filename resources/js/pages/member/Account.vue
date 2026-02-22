<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref, computed } from 'vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

const accountNumber = ref('');

const canDelete = computed(() => accountNumber.value.trim().length > 0);

function confirmDelete() {
    Swal.fire({
        title: 'Are you sure?',
        text: 'Do you really want to delete your account? This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Yes, delete my account',
        cancelButtonText: 'Cancel',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Account deletion requested',
                text: 'Your account will be processed for deletion.',
                icon: 'success',
                confirmButtonColor: '#16a34a',
            });
            accountNumber.value = '';
        }
    });
}
</script>

<template>
    <Head title="Delete Your Account" />

    <div
        class="flex min-h-svh flex-col items-center justify-center bg-[#FDFDFC] px-4 text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]"
    >
        <div class="w-full max-w-md">
            <div
                class="rounded-xl border border-[#e3e3e0] bg-white p-8 shadow-sm dark:border-[#3E3E3A] dark:bg-[#141414]"
            >
                <div class="mb-2 flex items-center justify-center">
                    <span
                        class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 text-red-600 dark:bg-red-900/30 dark:text-red-400"
                        aria-hidden="true"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="24"
                            height="24"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <path
                                d="M3 6h18"
                            />
                            <path
                                d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"
                            />
                            <path
                                d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"
                            />
                            <line
                                x1="10"
                                x2="10"
                                y1="11"
                                y2="17"
                            />
                            <line
                                x1="14"
                                x2="14"
                                y1="11"
                                y2="17"
                            />
                        </svg>
                    </span>
                </div>
                <h1 class="text-center text-xl font-semibold sm:text-2xl">
                    Delete Your Account
                </h1>
                <p class="mt-2 text-center text-sm text-muted-foreground">
                    Enter your account number below to request account deletion.
                </p>

                <form
                    class="mt-8 flex flex-col gap-6"
                    @submit.prevent="confirmDelete"
                >
                    <div class="grid gap-2">
                        <Label for="account-number">Account number</Label>
                        <Input
                            id="account-number"
                            v-model="accountNumber"
                            type="text"
                            placeholder="e.g. ACC-12345"
                            autocomplete="off"
                            class="w-full"
                        />
                    </div>

                    <Transition
                        enter-active-class="transition ease-out duration-200"
                        enter-from-class="opacity-0 translate-y-1"
                        enter-to-class="opacity-100 translate-y-0"
                    >
                        <Button
                            v-if="canDelete"
                            type="submit"
                            variant="destructive"
                            class="w-full font-medium"
                        >
                            Delete account
                        </Button>
                    </Transition>
                </form>
            </div>

            <p class="mt-6 text-center text-xs text-muted-foreground">
                Deleting your account will permanently remove your data. This
                cannot be undone.
            </p>
        </div>
    </div>
</template>
