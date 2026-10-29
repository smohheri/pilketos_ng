document.addEventListener('alpine:init', () => {
  Alpine.data('countdown', (endAt) => ({
    end: new Date(endAt).getTime(),
    now: Date.now(),
    timer: null,
    init() {
      this.timer = setInterval(() => {
        this.now = Date.now();
        if (this.now >= this.end) {
          clearInterval(this.timer);
        }
      }, 1000);
    },
    get diff() {
      return Math.max(0, this.end - this.now);
    },
    get finished() {
      return this.diff <= 0;
    },
    get days() {
      return String(Math.floor(this.diff / 86400000)).padStart(2, '0');
    },
    get hours() {
      return String(Math.floor((this.diff % 86400000) / 3600000)).padStart(2, '0');
    },
    get minutes() {
      return String(Math.floor((this.diff % 3600000) / 60000)).padStart(2, '0');
    },
    get seconds() {
      return String(Math.floor((this.diff % 60000) / 1000)).padStart(2, '0');
    },
  }));

  Alpine.data('copyText', () => ({
    copied: false,
    copy(text) {
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(text).then(() => {
          this.copied = true;
          setTimeout(() => (this.copied = false), 1800);
        });
        return;
      }

      const area = document.createElement('textarea');
      area.value = text;
      area.style.position = 'fixed';
      area.style.opacity = '0';
      document.body.appendChild(area);
      area.select();
      document.execCommand('copy');
      document.body.removeChild(area);
      this.copied = true;
      setTimeout(() => (this.copied = false), 1800);
    },
  }));

  Alpine.data('otpField', () => ({
    value: '',
    handleInput(event) {
      this.value = event.target.value.replace(/\D/g, '').slice(0, 6);
      event.target.value = this.value;
      if (this.value.length === 6 && this.$refs.submit) {
        this.$refs.submit.focus();
      }
    },
  }));

  Alpine.data('confirmDialog', () => ({
    visible: false,
    title: 'Konfirmasi',
    message: '',
    confirmLabel: 'Lanjutkan',
    cancelLabel: 'Batal',
    tone: 'danger',
    resolver: null,
    lastFocused: null,
    busy: false,
    show(detail) {
      if (this.resolver) {
        const previous = this.resolver;
        this.resolver = null;
        previous(false);
      }

      this.title = detail.title || 'Konfirmasi';
      this.message = detail.message || '';
      this.confirmLabel = detail.confirmLabel || 'Lanjutkan';
      this.cancelLabel = detail.cancelLabel || 'Batal';
      this.tone = detail.tone || 'danger';
      this.resolver = detail.resolve;
      this.lastFocused = document.activeElement;
      this.busy = false;
      this.visible = true;
      this.$nextTick(() => this.$refs.confirmButton?.focus());
    },
    accept() {
      if (this.busy) {
        return;
      }

      this.busy = true;
      this.finish(true);
    },
    cancel() {
      if (this.busy) {
        return;
      }

      this.finish(false);
    },
    finish(value) {
      const resolve = this.resolver;
      const focused = this.lastFocused;
      this.resolver = null;
      this.visible = false;
      this.lastFocused = null;
      this.$nextTick(() => {
        this.busy = false;
        if (!value && focused && typeof focused.focus === 'function') {
          focused.focus();
        }
      });
      if (resolve) {
        resolve(value);
      }
    },
  }));
});

const confirmedForms = new WeakSet();

window.cakraConfirm = function (detail = {}) {
  return new Promise((resolve) => {
    window.dispatchEvent(new CustomEvent('cakra-confirm', {
      detail: {
        title: 'Konfirmasi',
        message: '',
        confirmLabel: 'Lanjutkan',
        cancelLabel: 'Batal',
        tone: 'danger',
        ...detail,
        resolve,
      },
    }));
  });
};

document.addEventListener('submit', (event) => {
  const form = event.target;
  if (!(form instanceof HTMLFormElement) || !form.dataset.confirm) {
    return;
  }

  if (confirmedForms.has(form)) {
    confirmedForms.delete(form);
    return;
  }

  event.preventDefault();
  const submitter = event.submitter instanceof HTMLElement ? event.submitter : null;

  window.cakraConfirm({
    title: form.dataset.confirmTitle || 'Konfirmasi',
    message: form.dataset.confirm,
    confirmLabel: form.dataset.confirmLabel || 'Lanjutkan',
    cancelLabel: form.dataset.confirmCancel || 'Batal',
    tone: form.dataset.confirmTone || 'danger',
  }).then((confirmed) => {
    if (!confirmed) {
      return;
    }

    if (typeof form.requestSubmit === 'function') {
      confirmedForms.add(form);
      form.requestSubmit(submitter);
      confirmedForms.delete(form);
      return;
    }

    form.submit();
  });
}, true);

