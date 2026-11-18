{{--
    Modal konfirmasi hapus. Cukup dipasang SATU kali (sudah ada di layouts/app.blade.php)
    dan dipakai semua halaman lewat tombol <x-delete-button>. Dibangun di atas <x-modal>.

    Alurnya:
    1. Tombol Hapus mengirim event "confirm-delete" berisi action, title, description, name, note.
    2. Modal menampilkan data itu. Belum ada request ke server.
    3. "Batal", klik latar, atau Escape hanya menutup modal.
    4. "Hapus" mengirim form POST + _method=DELETE + token CSRF ke action tersebut.
--}}
<div
    x-data="{
        action: '',
        title: 'Hapus data?',
        description: 'Data yang dihapus tidak dapat dikembalikan.',
        name: '',
        note: '',
        submitting: false,
        open(data) {
            this.action = data.action || '';
            this.title = data.title || 'Hapus data?';
            this.description = data.description || 'Data yang dihapus tidak dapat dikembalikan.';
            this.name = data.name || '';
            this.note = data.note || '';
            this.submitting = false;
            this.$dispatch('open-modal', 'confirm-delete');
        },
    }"
    x-on:confirm-delete.window="open($event.detail)"
>
    <x-modal name="confirm-delete" maxWidth="md" labelledby="confirm-delete-title" focusable>
        <form method="POST" x-bind:action="action" x-on:submit="submitting = true">
            @csrf
            @method('DELETE')

            <div class="flex gap-4 p-6">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 text-red-600">
                    <x-icon name="trash" class="h-5 w-5" />
                </div>

                <div class="min-w-0 flex-1">
                    <h2 id="confirm-delete-title" class="text-lg font-semibold text-slate-900" x-text="title"></h2>
                    <p class="mt-2 text-sm text-slate-600" x-text="description"></p>
                    <p class="mt-2 break-words text-sm font-semibold text-slate-900" x-show="name" x-text="name"></p>
                    <p class="mt-2 text-sm text-slate-500" x-show="note" x-text="note"></p>
                </div>
            </div>

            <div class="flex flex-col-reverse gap-3 bg-slate-50 px-6 py-4 sm:flex-row sm:justify-end">
                <button type="button" x-on:click="$dispatch('close-modal', 'confirm-delete')"
                    class="inline-flex items-center justify-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-700 focus-visible:ring-offset-2">
                    Batal
                </button>
                <button type="submit" x-bind:disabled="submitting"
                    class="inline-flex items-center justify-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-600 focus-visible:ring-offset-2 disabled:opacity-60">
                    Hapus
                </button>
            </div>
        </form>
    </x-modal>
</div>
