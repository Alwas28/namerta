{{-- resources/views/components/diskusi-component.blade.php --}}

<!-- Modal Buat Topik Diskusi (Guru) -->
<div id="createTopikModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xl font-bold text-slate-900">Buat Topik Diskusi Baru</h3>
            <button onclick="closeCreateTopikModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-6 overflow-y-auto" style="max-height: calc(90vh - 140px)">
            <form id="createTopikForm">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Judul Diskusi *</label>
                    <input type="text" name="judul" required
                           class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-purple focus:border-transparent"
                           placeholder="Masukkan judul diskusi">
                </div>
                
                <div class="mb-6">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Deskripsi/Pertanyaan *</label>
                    <textarea name="deskripsi" rows="6" required
                              class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-purple focus:border-transparent"
                              placeholder="Jelaskan topik diskusi atau pertanyaan yang ingin didiskusikan"></textarea>
                </div>
                
                <div class="flex space-x-3">
                    <button type="button" onclick="closeCreateTopikModal()" 
                            class="flex-1 px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2 bg-edu-purple text-white rounded-lg hover:bg-edu-purple/90">
                        Buat Topik
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Komentar -->
<div id="editKomentarModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-lg">
        <div class="px-6 py-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="text-xl font-bold text-slate-900">Edit Komentar</h3>
            <button onclick="closeEditKomentarModal()" class="text-slate-400 hover:text-slate-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <div class="p-6">
            <form id="editKomentarForm">
                <input type="hidden" id="editKomentarId" name="id_komentar">
                <div class="mb-4">
                    <textarea id="editKomentarText" name="konten" rows="4" required
                              class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-purple focus:border-transparent"
                              placeholder="Tulis komentar Anda"></textarea>
                </div>
                <div class="flex space-x-3">
                    <button type="button" onclick="closeEditKomentarModal()" 
                            class="flex-1 px-4 py-2 border border-slate-300 text-slate-700 rounded-lg hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit"
                            class="flex-1 px-4 py-2 bg-edu-purple text-white rounded-lg hover:bg-edu-purple/90">
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.diskusi-card {
    transition: all 0.3s ease;
}

.diskusi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
}

.komentar-item {
    transition: background-color 0.2s ease;
}

.komentar-item:hover {
    background-color: #f8fafc;
}

.reply-item {
    border-left: 3px solid #e2e8f0;
    margin-left: 1rem;
    padding-left: 1rem;
}

.badge-unread {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .5;
    }
}

.status-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
}

.status-aktif {
    background-color: #dcfce7;
    color: #166534;
}

.status-ditutup {
    background-color: #fee2e2;
    color: #991b1b;
}

.like-button {
    transition: all 0.2s ease;
}

.like-button:hover {
    transform: scale(1.1);
}

.like-button.liked {
    color: #ef4444;
}

.comment-actions {
    opacity: 0;
    transition: opacity 0.2s ease;
}

.komentar-item:hover .comment-actions {
    opacity: 1;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>

{{-- Include JavaScript dari view yang sudah dibuat sebelumnya --}}
@push('scripts')
<script src="{{ asset('js/diskusi-manager.js') }}"></script>
@endpush