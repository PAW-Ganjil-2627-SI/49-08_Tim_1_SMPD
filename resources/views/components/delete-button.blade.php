{{--
    Tombol pemicu modal konfirmasi hapus (lihat <x-confirm-delete>). Tombol ini TIDAK
    menghapus apa pun; ia hanya membuka modal.

    Pemakaian:
        <x-delete-button
            :action="route('books.destroy', $book)"
            title="Hapus Buku?"
            :name="$book->title"
            description="Buku berikut akan dihapus:"
            note="Opsional: catatan tambahan."
            class="text-red-600 hover:underline"
        >Hapus</x-delete-button>

    Data dikirim lewat atribut data-* supaya karakter khusus (tanda kutip, dll.) aman.
--}}
@props(['action', 'title' => 'Hapus data?', 'name' => '', 'description' => null, 'note' => null])

<button
    type="button"
    data-action="{{ $action }}"
    data-title="{{ $title }}"
    data-name="{{ $name }}"
    data-description="{{ $description }}"
    data-note="{{ $note }}"
    x-data
    x-on:click="$dispatch('confirm-delete', {
        action: $el.dataset.action,
        title: $el.dataset.title,
        name: $el.dataset.name,
        description: $el.dataset.description,
        note: $el.dataset.note,
    })"
    {{ $attributes }}
>
    {{ $slot }}
</button>
