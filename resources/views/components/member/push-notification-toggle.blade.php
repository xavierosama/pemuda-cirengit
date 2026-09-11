@php
    $vapidPublicKey = config('services.webpush.vapid.public_key');
@endphp

<div
    class="rounded-xl border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950/60"
    x-data="{
        supported: false,
        subscribed: false,
        loading: false,
        permission: 'default',
        message: '',
        vapidPublicKey: @js($vapidPublicKey),
        routes: {
            store: @js(route('member.push-subscriptions.store')),
            destroy: @js(route('member.push-subscriptions.destroy')),
            test: @js(route('member.push-notifications.test')),
        },
        csrf: @js(csrf_token()),
        async init() {
            this.supported = 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window && window.isSecureContext;

            if (! this.supported) {
                this.message = window.isSecureContext
                    ? 'Browser ini belum mendukung push notification.'
                    : 'Push notification membutuhkan HTTPS.';
                return;
            }

            if (! this.vapidPublicKey) {
                this.message = 'VAPID public key belum dikonfigurasi.';
                return;
            }

            this.permission = Notification.permission;

            if (this.permission === 'denied') {
                this.message = 'Notifikasi diblokir di browser.';
                return;
            }

            const registration = await navigator.serviceWorker.ready;
            const subscription = await registration.pushManager.getSubscription();

            this.subscribed = Boolean(subscription);
            this.message = this.subscribed
                ? 'Notifikasi aktif di perangkat ini.'
                : 'Aktifkan notifikasi untuk menerima info penting di HP/browser.';
        },
        async enable() {
            if (! this.supported || ! this.vapidPublicKey || this.loading) {
                return;
            }

            this.loading = true;
            this.message = 'Menyiapkan notifikasi...';

            try {
                if (Notification.permission === 'default') {
                    this.permission = await Notification.requestPermission();
                } else {
                    this.permission = Notification.permission;
                }

                if (this.permission === 'denied') {
                    this.message = 'Notifikasi diblokir. Aktifkan kembali melalui pengaturan browser.';
                    return;
                }

                if (this.permission !== 'granted') {
                    this.message = 'Izin notifikasi belum diberikan.';
                    return;
                }

                const registration = await navigator.serviceWorker.register('/sw.js');
                const existingSubscription = await registration.pushManager.getSubscription();
                const subscription = existingSubscription || await registration.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: this.urlBase64ToUint8Array(this.vapidPublicKey),
                });

                await this.storeSubscription(subscription);

                this.subscribed = true;
                this.message = 'Notifikasi aktif di perangkat ini.';
            } catch (error) {
                console.warn('Push subscription failed:', error);
                this.message = 'Gagal mengaktifkan notifikasi. Coba lagi beberapa saat.';
            } finally {
                this.loading = false;
            }
        },
        async disable() {
            if (this.loading) {
                return;
            }

            this.loading = true;
            this.message = 'Menonaktifkan notifikasi...';

            try {
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.getSubscription();

                if (subscription) {
                    await fetch(this.routes.destroy, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': this.csrf,
                        },
                        body: JSON.stringify({ endpoint: subscription.endpoint }),
                    });

                    await subscription.unsubscribe();
                }

                this.subscribed = false;
                this.message = 'Notifikasi perangkat ini dinonaktifkan.';
            } catch (error) {
                console.warn('Push unsubscribe failed:', error);
                this.message = 'Gagal menonaktifkan notifikasi.';
            } finally {
                this.loading = false;
            }
        },
        async sendTest() {
            if (! this.subscribed || this.loading) {
                return;
            }

            this.loading = true;
            this.message = 'Mengirim tes notifikasi...';

            try {
                const response = await fetch(this.routes.test, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': this.csrf,
                    },
                });

                const payload = await response.json();
                this.message = payload.message || 'Tes notifikasi diproses.';
            } catch (error) {
                console.warn('Push test failed:', error);
                this.message = 'Gagal mengirim tes notifikasi.';
            } finally {
                this.loading = false;
            }
        },
        async storeSubscription(subscription) {
            const serialized = subscription.toJSON();
            const contentEncoding = ('supportedContentEncodings' in PushManager && PushManager.supportedContentEncodings.includes('aes128gcm'))
                ? 'aes128gcm'
                : 'aesgcm';

            const response = await fetch(this.routes.store, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrf,
                },
                body: JSON.stringify({
                    endpoint: serialized.endpoint,
                    keys: serialized.keys,
                    content_encoding: contentEncoding,
                    device_name: navigator.userAgent,
                }),
            });

            if (! response.ok) {
                throw new Error('Subscription could not be saved.');
            }
        },
        urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);

            for (let i = 0; i < rawData.length; ++i) {
                outputArray[i] = rawData.charCodeAt(i);
            }

            return outputArray;
        },
    }"
>
    <div class="flex items-start gap-3">
        <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 ring-1 ring-inset ring-emerald-200">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.85 18.25a2.85 2.85 0 0 1-5.7 0M18.5 9.5a6.5 6.5 0 0 0-13 0c0 6.75-2.5 7.75-2.5 7.75h18s-2.5-1-2.5-7.75Z" />
            </svg>
        </span>
        <div class="min-w-0 flex-1">
            <p class="text-sm font-bold text-slate-950 dark:text-white">Notifikasi HP</p>
            <p class="mt-1 text-xs leading-5 text-slate-600 dark:text-slate-400" x-text="message || 'Cek dukungan notifikasi...'"></p>

            <div class="mt-3 flex flex-wrap gap-2">
                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg bg-emerald-700 px-3 py-2 text-xs font-bold text-white transition hover:bg-emerald-800 disabled:cursor-not-allowed disabled:opacity-60"
                    x-show="supported && ! subscribed && permission !== 'denied' && vapidPublicKey"
                    :disabled="loading"
                    @click="enable"
                >
                    <span x-text="loading ? 'Memproses...' : 'Aktifkan Notifikasi'"></span>
                </button>

                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-xs font-bold text-slate-700 transition hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200"
                    x-show="supported && subscribed"
                    :disabled="loading"
                    @click="sendTest"
                >
                    Kirim Tes
                </button>

                <button
                    type="button"
                    class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-white px-3 py-2 text-xs font-bold text-red-700 transition hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-60 dark:border-red-900/60 dark:bg-slate-900"
                    x-show="supported && subscribed"
                    :disabled="loading"
                    @click="disable"
                >
                    Nonaktifkan
                </button>
            </div>
        </div>
    </div>
</div>
