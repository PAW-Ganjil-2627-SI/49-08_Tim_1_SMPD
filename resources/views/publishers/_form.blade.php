<div class="space-y-6">
    <!-- Nama & Tipe -->
    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div class="sm:col-span-2">
            <x-input-label for="name" value="Nama Penerbit / Supplier *" />
            <x-text-input
                id="name"
                name="name"
                type="text"
                class="mt-1 block w-full"
                :value="old('name', $publisher->name ?? '')"
                placeholder="Contoh: PT Gramedia Pustaka Utama / CV Mizan Distribusi"
                required
                autofocus
            />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="type" value="Tipe Rekanan *" />
            <select
                id="type"
                name="type"
                class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 text-sm"
                required
            >
                @php($selectedType = old('type', $publisher->type ?? 'publisher'))
                <option value="publisher" @selected($selectedType === 'publisher')>Penerbit (Publisher)</option>
                <option value="distributor" @selected($selectedType === 'distributor')>Distributor / Supplier</option>
            </select>
            <p class="mt-1 text-xs text-slate-500">Pilih apakah instansi adalah penerbit langsung atau distributor/pemasok.</p>
            <x-input-error class="mt-2" :messages="$errors->get('type')" />
        </div>

        <div>
            <x-input-label for="region" value="Wilayah Asal" />
            <x-text-input
                id="region"
                name="region"
                type="text"
                class="mt-1 block w-full"
                :value="old('region', $publisher->region ?? '')"
                placeholder="Contoh: Jakarta Pusat, Bandung, Yogyakarta"
            />
            <p class="mt-1 text-xs text-slate-500">Kota atau provinsi basis operasional penerbit.</p>
            <x-input-error class="mt-2" :messages="$errors->get('region')" />
        </div>
    </div>

    <!-- Kontak Sales & Narahubung -->
    <div class="border-t border-slate-200 pt-6">
        <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-600 mb-4">Informasi Kontak Sales</h3>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <div>
                <x-input-label for="contact_person" value="Kontak Sales / Narahubung" />
                <x-text-input
                    id="contact_person"
                    name="contact_person"
                    type="text"
                    class="mt-1 block w-full"
                    :value="old('contact_person', $publisher->contact_person ?? '')"
                    placeholder="Contoh: Budi Santoso (Account Executive)"
                />
                <x-input-error class="mt-2" :messages="$errors->get('contact_person')" />
            </div>

            <div>
                <x-input-label for="phone" value="Nomor Telepon / WhatsApp" />
                <x-text-input
                    id="phone"
                    name="phone"
                    type="text"
                    class="mt-1 block w-full"
                    :value="old('phone', $publisher->phone ?? '')"
                    placeholder="Contoh: 081234567890 / 021-53650110"
                />
                <x-input-error class="mt-2" :messages="$errors->get('phone')" />
            </div>

            <div>
                <x-input-label for="email" value="Alamat Email" />
                <x-text-input
                    id="email"
                    name="email"
                    type="email"
                    class="mt-1 block w-full"
                    :value="old('email', $publisher->email ?? '')"
                    placeholder="Contoh: sales@penerbit.co.id"
                />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />
            </div>
        </div>
    </div>

    <!-- Alamat & Catatan -->
    <div class="border-t border-slate-200 pt-6">
        <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-600 mb-4">Alamat & Catatan</h3>

        <div class="space-y-6">
            <div>
                <x-input-label for="address" value="Alamat Lengkap" />
                <textarea
                    id="address"
                    name="address"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 text-sm"
                    placeholder="Jalan, nomor gedung, kelurahan, kecamatan, kode pos..."
                >{{ old('address', $publisher->address ?? '') }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('address')" />
            </div>

            <div>
                <x-input-label for="notes" value="Catatan Tambahan (Opsional)" />
                <textarea
                    id="notes"
                    name="notes"
                    rows="2"
                    class="mt-1 block w-full rounded-md border-slate-300 shadow-sm focus:border-blue-900 focus:ring-blue-900 text-sm"
                    placeholder="Contoh: Diskon pengadaan buku 20%, jadwal pengiriman tiap hari Rabu, syarat retur..."
                >{{ old('notes', $publisher->notes ?? '') }}</textarea>
                <x-input-error class="mt-2" :messages="$errors->get('notes')" />
            </div>
        </div>
    </div>
</div>

