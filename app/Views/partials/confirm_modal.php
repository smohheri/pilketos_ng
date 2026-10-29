<div
    x-data="confirmDialog"
    x-on:cakra-confirm.window="show($event.detail)"
    x-show="visible"
    x-cloak
    x-effect="document.body.style.overflow = visible ? 'hidden' : ''"
    class="fixed inset-0 z-[90] flex items-end justify-center p-4 sm:items-center"
    role="dialog"
    aria-modal="true"
    aria-labelledby="cakra-confirm-title"
    @keydown.escape.window="cancel()"
>
    <div class="absolute inset-0 bg-ink/55 backdrop-blur-sm" @click="cancel()" x-transition.opacity></div>

    <div class="relative w-full max-w-md overflow-hidden rounded-5xl border-2 border-ink bg-paper shadow-hard" x-transition>
        <div class="flex items-start gap-3 border-b-2 border-ink/10 px-5 py-5 sm:px-6">
            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-sun text-ink"><?= icon('alert', 'h-5 w-5') ?></span>
            <div class="min-w-0">
                <p id="cakra-confirm-title" class="font-display text-lg font-bold tracking-[-0.02em]" x-text="title"></p>
                <p class="mt-1 text-sm leading-relaxed text-ink/60" x-text="message"></p>
            </div>
        </div>

        <div class="flex flex-col-reverse gap-2 px-5 py-4 sm:flex-row sm:justify-end sm:px-6">
            <button type="button" class="btn btn-light" @click="cancel()" x-text="cancelLabel"></button>
            <button type="button" class="btn" :class="tone === 'danger' ? 'btn-danger' : 'btn-dark'" @click="accept()" x-ref="confirmButton" x-text="confirmLabel"></button>
        </div>
    </div>
</div>

