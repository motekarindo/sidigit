<div x-data="{
    toasts: [],
    addToast(detail) {
        const type = detail?.type ?? 'success';
        const message = detail?.message ?? '';
        const refMatch = String(message).match(/Kode:\\s*([A-Za-z0-9-]+)/i);
        const toast = {
            id: Date.now() + Math.random(),
            message,
            type,
            duration: detail?.duration ?? 12000,
            ref: detail?.ref ?? (refMatch ? refMatch[1] : null),
            copied: false,
        };

        this.toasts.push(toast);

        setTimeout(() => {
            this.toasts = this.toasts.filter(t => t.id !== toast.id);
        }, toast.duration);
    },
    typeClass(type) {
        if (type === 'danger' || type === 'error') return 'bg-red-600 text-white';
        if (type === 'warning') return 'bg-yellow-500 text-gray-900';
        if (type === 'info') return 'bg-gray-800 text-white';
        return 'bg-green-600 text-white';
    },
    copyRef(toast) {
        if (!toast?.ref) return;
        const text = toast.ref;
        if (navigator.clipboard?.writeText) {
            navigator.clipboard.writeText(text).then(() => {
                toast.copied = true;
                setTimeout(() => toast.copied = false, 1500);
            }).catch(() => {});
            return;
        }
        const el = document.createElement('textarea');
        el.value = text;
        el.setAttribute('readonly', 'readonly');
        el.style.position = 'absolute';
        el.style.left = '-9999px';
        document.body.appendChild(el);
        el.select();
        try {
            document.execCommand('copy');
            toast.copied = true;
            setTimeout(() => toast.copied = false, 1500);
        } catch (e) {}
        document.body.removeChild(el);
    },
}" x-on:toast.window="addToast($event.detail)" class="fixed top-4 right-4 z-[100000] space-y-2">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 translate-y-2"
            :class="typeClass(toast.type)"
            class="relative overflow-hidden rounded-lg px-4 py-4 shadow-lg text-sm w-[320px]">
            <button type="button" @click="toasts = toasts.filter(t => t.id !== toast.id)"
                class="absolute right-2 top-2 rounded-md border border-transparent p-1 text-white/80 transition hover:text-white"
                :class="toast.type === 'warning' ? 'text-gray-700 hover:text-gray-900' : ''"
                aria-label="Tutup">
                <x-lucide-x class="h-4 w-4" />
            </button>
            <div class="flex items-start gap-3">
                <span class="mt-0.5">
                    <x-lucide-check x-show="toast.type === 'success'" class="w-5 h-5" />
                    <x-lucide-triangle-alert x-show="toast.type === 'warning'" class="w-5 h-5" />
                    <x-lucide-info x-show="toast.type === 'info'" class="w-5 h-5" />
                    <x-lucide-alert-circle x-show="toast.type === 'danger' || toast.type === 'error'" class="w-5 h-5" />
                </span>
                <div class="flex-1">
                    <span x-text="toast.message" class="block leading-5"></span>
                    <template x-if="toast.ref">
                        <button type="button" @click="copyRef(toast)"
                            class="mt-2 inline-flex items-center gap-1 rounded-md border border-white/30 bg-white/10 px-2 py-1 text-xs font-semibold uppercase tracking-wide text-white/90 hover:bg-white/20"
                            :class="toast.type === 'warning' ? 'border-black/20 bg-black/10 text-gray-900 hover:bg-black/20' : ''">
                            <span x-text="toast.copied ? 'Tersalin' : 'Salin Kode Error'"></span>
                        </button>
                    </template>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 h-1 w-full bg-black/10"></div>
            <div class="absolute bottom-0 left-0 h-1 bg-white/70"
                :style="`animation: toast-progress ${toast.duration}ms linear forwards;`"></div>
        </div>
    </template>
</div>
