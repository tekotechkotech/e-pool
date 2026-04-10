<div
    x-data
    x-show="$store.confirmDialog.open"
    @keydown.escape.window="$store.confirmDialog.cancel()"
    class="relative z-[9999]"
    style="display: none;"
    role="dialog"
    aria-modal="true"
>
    <!-- Backdrop -->
    <div
        x-show="$store.confirmDialog.open"
        x-transition:enter="ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="$store.confirmDialog.cancel()"
        class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
        aria-hidden="true"
    ></div>

    <!-- Modal Box Container -->
    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div
                x-show="$store.confirmDialog.open"
                x-transition:enter="ease-out duration-200"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-150"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                @click.outside="$store.confirmDialog.cancel()"
                class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md border border-slate-100"
            >
                <div class="bg-white p-6">
                    <div class="flex items-start gap-4">
                        <!-- Icon -->
                        <div
                            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl"
                            :class="{
                                'bg-red-50 text-red-600': $store.confirmDialog.isDanger,
                                'bg-emerald-50 text-[#118243]': !$store.confirmDialog.isDanger && $store.confirmDialog.type !== 'info',
                                'bg-blue-50 text-blue-600': $store.confirmDialog.type === 'info'
                            }"
                        >
                            <!-- Danger / Warning Icon -->
                            <template x-if="$store.confirmDialog.isDanger">
                                <x-heroicon-o-exclamation-triangle class="h-6 w-6" />
                            </template>
                            <!-- Alert / Info / Success Icon -->
                            <template x-if="!$store.confirmDialog.isDanger">
                                <x-heroicon-o-information-circle class="h-6 w-6" />
                            </template>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0 pt-0.5">
                            <h3 class="text-base font-bold text-slate-800 leading-snug" x-text="$store.confirmDialog.title"></h3>
                            <div class="mt-2 text-sm text-slate-600 leading-relaxed break-words whitespace-pre-line" x-text="$store.confirmDialog.message"></div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="bg-slate-50/80 px-6 py-4 flex flex-col-reverse sm:flex-row sm:justify-end gap-2 border-t border-slate-100">
                    <template x-if="$store.confirmDialog.cancelText">
                        <button
                            type="button"
                            @click="$store.confirmDialog.cancel()"
                            class="inline-flex w-full sm:w-auto justify-center rounded-xl px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-200 shadow-sm hover:bg-slate-50 transition-colors focus:outline-none focus:ring-2 focus:ring-slate-300"
                            x-text="$store.confirmDialog.cancelText"
                        ></button>
                    </template>
                    <button
                        type="button"
                        @click="$store.confirmDialog.confirm()"
                        class="inline-flex w-full sm:w-auto justify-center rounded-xl px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all focus:outline-none focus:ring-2 focus:ring-offset-2"
                        :class="{
                            'bg-red-600 hover:bg-red-700 focus:ring-red-500': $store.confirmDialog.isDanger,
                            'bg-[#118243] hover:bg-[#0e6b37] focus:ring-[#118243]': !$store.confirmDialog.isDanger
                        }"
                        x-text="$store.confirmDialog.confirmText"
                    ></button>
                </div>
            </div>
        </div>
    </div>
</div>
