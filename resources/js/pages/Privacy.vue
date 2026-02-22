<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { home } from '@/routes';
</script>

<template>
    <Head title="Privacy Policy" />

    <div
        class="min-h-screen bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a] dark:text-[#EDEDEC]"
    >
        <header
            class="sticky top-0 z-10 border-b border-[#19140035] bg-[#FDFDFC]/95 py-4 backdrop-blur dark:border-[#3E3E3A] dark:bg-[#0a0a0a]/95"
        >
            <div class="mx-auto flex max-w-3xl items-center justify-between px-4 sm:px-6">
                <Link
                    :href="home()"
                    class="text-sm font-medium text-[#1b1b18] hover:underline dark:text-[#EDEDEC]"
                >
                    ← Back
                </Link>
                <span class="text-sm text-muted-foreground">CashRound</span>
            </div>
        </header>

        <main
            class="prose prose-neutral mx-auto max-w-3xl px-4 py-8 dark:prose-invert sm:px-6"
        >
            <h1 class="mb-2 text-2xl font-semibold sm:text-3xl">
                CashRound Privacy Policy
            </h1>
            <p class="mb-8 text-sm text-muted-foreground">
                <strong>Last updated:</strong> February 22, 2025
            </p>

            <p>
                CashRound ("we", "our", or "the app") is a mobile application
                that lets you save money in groups, manage a wallet, and make
                deposits and transfers using mobile money. This Privacy Policy
                describes how we collect, use, store, and protect your
                information when you use the CashRound app.
            </p>
            <p>
                By using CashRound, you agree to the practices described in this
                policy. If you do not agree, please do not use the app.
            </p>

            <hr class="my-8 border-[#e3e3e0] dark:border-[#3E3E3A]" />

            <h2 class="mt-8 text-xl font-semibold">1. Information We Collect</h2>

            <h3 class="mt-6 text-lg font-medium">
                1.1 Information You Provide
            </h3>
            <ul class="list-disc pl-6 space-y-1">
                <li>
                    <strong>Account registration:</strong> First name, last
                    name, email address, phone number, country, and password.
                </li>
                <li>
                    <strong>Profile:</strong> Updates to your name, email, or
                    phone number in your profile.
                </li>
                <li>
                    <strong>Verification:</strong> Email verification codes and
                    related data used to verify your account.
                </li>
                <li>
                    <strong>Financial actions:</strong> When you deposit,
                    withdraw, or transfer money we process the transaction
                    amount and, where relevant, your mobile money phone number
                    and recipient account details.
                </li>
                <li>
                    <strong>Groups:</strong> Group names, membership, roles, and
                    invitation codes when you create or join groups.
                </li>
                <li>
                    <strong>Support:</strong> Any information you send to us when
                    you contact support or use in-app help.
                </li>
            </ul>

            <h3 class="mt-6 text-lg font-medium">
                1.2 Information Collected Automatically
            </h3>
            <ul class="list-disc pl-6 space-y-1">
                <li>
                    <strong>Device and app:</strong> We use Firebase Cloud
                    Messaging (FCM) to send push notifications. To do this we
                    collect and store a device push token (FCM token) and
                    associate it with your account on our servers.
                </li>
                <li>
                    <strong>Usage:</strong> Our backend may log requests (e.g. IP
                    address, device type, timestamps) for security, debugging,
                    and service improvement.
                </li>
            </ul>

            <h3 class="mt-6 text-lg font-medium">
                1.3 Information Stored on Your Device
            </h3>
            <p>
                The app stores the following on your device (using local
                storage):
            </p>
            <ul class="list-disc pl-6 space-y-1">
                <li>
                    <strong>Session:</strong> Access token and token type so you
                    stay logged in.
                </li>
                <li>
                    <strong>Profile:</strong> Your member profile (e.g. name,
                    email, phone) for display in the app.
                </li>
                <li>
                    <strong>Optional:</strong> If you choose to save your login
                    details, your email and password may be stored locally to
                    prefill the login screen next time. You can avoid this by
                    not using the "remember me" or save-credentials option if
                    offered.
                </li>
            </ul>

            <hr class="my-8 border-[#e3e3e0] dark:border-[#3E3E3A]" />

            <h2 class="mt-8 text-xl font-semibold">
                2. How We Use Your Information
            </h2>
            <p>We use the information we collect to:</p>
            <ul class="list-disc pl-6 space-y-1">
                <li>Create and manage your account and authenticate you.</li>
                <li>
                    Provide wallet and group features: balance, deposits,
                    withdrawals, member-to-member and member-to-group
                    transfers, and transaction history.
                </li>
                <li>
                    Process mobile money (e.g. MoMo) deposits and withdrawals
                    using the phone number and amount you provide.
                </li>
                <li>
                    Send you push notifications about group updates, payments,
                    and other app-related alerts (via Firebase Cloud
                    Messaging).
                </li>
                <li>
                    Display and manage in-app notifications and mark them as
                    read when you use the app.
                </li>
                <li>
                    Improve the app, fix errors, and protect against fraud and
                    abuse.
                </li>
                <li>
                    Comply with applicable laws and respond to lawful requests
                    from authorities.
                </li>
            </ul>
            <p class="mt-4">
                We do not sell your personal information to third parties.
            </p>

            <hr class="my-8 border-[#e3e3e0] dark:border-[#3E3E3A]" />

            <h2 class="mt-8 text-xl font-semibold">3. Sharing of Information</h2>
            <ul class="list-disc pl-6 space-y-1">
                <li>
                    <strong>Backend services:</strong> Your data is sent to our
                    backend API (hosted at
                    <code class="rounded bg-muted px-1.5 py-0.5"
                        >https://payment.theagrotracker.com</code
                    >) to provide account, wallet, group, and notification
                    features.
                </li>
                <li>
                    <strong>Firebase (Google):</strong> We use Firebase Core
                    and Firebase Cloud Messaging for push notifications. FCM
                    tokens and notification-related data are processed by Google
                    in accordance with
                    <a
                        href="https://policies.google.com/privacy"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="font-medium text-[#f53003] underline underline-offset-4 dark:text-[#FF4433]"
                        >Google's Privacy Policy</a
                    >.
                </li>
                <li>
                    <strong>Third-party apps:</strong> When you use in-app
                    sharing (e.g. sharing an invite via WhatsApp), the content
                    you choose to share is sent to that third-party app
                    according to its own privacy practices. We do not control
                    those apps.
                </li>
                <li>
                    <strong>Legal and safety:</strong> We may disclose your
                    information where required by law, or to protect our
                    rights, your safety, or the safety of others.
                </li>
            </ul>

            <hr class="my-8 border-[#e3e3e0] dark:border-[#3E3E3A]" />

            <h2 class="mt-8 text-xl font-semibold">
                4. Data Retention and Security
            </h2>
            <ul class="list-disc pl-6 space-y-1">
                <li>
                    We retain your account and transaction data for as long as
                    your account is active and as needed to provide the service,
                    resolve disputes, and comply with legal obligations.
                </li>
                <li>
                    We use industry-standard measures (including HTTPS and
                    secure authentication) to protect data in transit and on our
                    servers. Data on your device is stored in the app's local
                    storage, which is managed by the operating system.
                </li>
                <li>
                    You are responsible for keeping your password and device
                    secure. If you believe your account has been compromised,
                    contact us and change your password.
                </li>
            </ul>

            <hr class="my-8 border-[#e3e3e0] dark:border-[#3E3E3A]" />

            <h2 class="mt-8 text-xl font-semibold">5. Your Rights and Choices</h2>
            <ul class="list-disc pl-6 space-y-1">
                <li>
                    <strong>Access and correction:</strong> You can view and
                    update your profile information (e.g. name, email, phone) in
                    the app.
                </li>
                <li>
                    <strong>Notifications:</strong> You can disable push
                    notifications in your device settings. The app may still
                    store an FCM token on our servers until you log out or
                    delete your account.
                </li>
                <li>
                    <strong>Saved credentials:</strong> If you saved your login
                    details on the device, you can clear them by logging out
                    and/or clearing the app's data (depending on how the app
                    implements this).
                </li>
                <li>
                    <strong>Account deletion:</strong> To request deletion of
                    your account and associated data, please contact us using
                    the details below. Note that we may need to retain certain
                    data for legal or regulatory reasons.
                </li>
            </ul>
            <p class="mt-4">
                If you are in a jurisdiction that provides additional rights
                (e.g. GDPR, CCPA), you may have the right to access, correct,
                delete, or port your data, or to object to or restrict certain
                processing. Contact us to exercise those rights.
            </p>

            <hr class="my-8 border-[#e3e3e0] dark:border-[#3E3E3A]" />

            <h2 class="mt-8 text-xl font-semibold">6. Children</h2>
            <p>
                CashRound is not intended for users under the age of 18 (or the
                age of majority in your country). We do not knowingly collect
                personal information from children. If you believe we have
                collected such information, please contact us so we can delete
                it.
            </p>

            <hr class="my-8 border-[#e3e3e0] dark:border-[#3E3E3A]" />

            <h2 class="mt-8 text-xl font-semibold">
                7. International Transfers
            </h2>
            <p>
                Your information may be processed and stored on servers
                located outside your country. By using the app, you consent to
                such transfer and processing in accordance with this policy and
                applicable law.
            </p>

            <hr class="my-8 border-[#e3e3e0] dark:border-[#3E3E3A]" />

            <h2 class="mt-8 text-xl font-semibold">8. Changes to This Policy</h2>
            <p>
                We may update this Privacy Policy from time to time. We will
                post the updated policy in the app or on our website and update
                the "Last updated" date. Continued use of the app after changes
                constitutes acceptance of the revised policy. We encourage you
                to review this policy periodically.
            </p>

            <hr class="my-8 border-[#e3e3e0] dark:border-[#3E3E3A]" />

            <h2 class="mt-8 text-xl font-semibold">9. Contact Us</h2>
            <p>
                If you have questions about this Privacy Policy or our data
                practices, or wish to exercise your rights, please contact us:
            </p>
            <ul class="list-disc pl-6 space-y-1">
                <li><strong>App:</strong> CashRound</li>
                <li>
                    <strong>Backend / service:</strong> Via the contact details
                    provided in the app or on the website associated with the
                    CashRound service (e.g. theagrotracker.com or the contact
                    information shown in the app's Help or About section).
                </li>
            </ul>

            <p class="mt-8 border-t border-[#e3e3e0] pt-6 text-sm italic text-muted-foreground dark:border-[#3E3E3A]">
                This privacy policy applies to the CashRound mobile application
                and the services it connects to.
            </p>
        </main>
    </div>
</template>
