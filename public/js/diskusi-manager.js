// public/js/diskusi-manager.js

const DiskusiManager = {
    currentTopikId: null,
    currentView: 'list',
    
    init: function() {
        this.setupEventListeners();
    },
    
    setupEventListeners: function() {
        const createForm = document.getElementById('createTopikForm');
        if (createForm) {
            createForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.createTopik(e.target);
            });
        }
        
        const editForm = document.getElementById('editKomentarForm');
        if (editForm) {
            editForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.updateKomentar();
            });
        }
    },
    
    loadTopikList: function() {
        const contentArea = document.getElementById('dynamicContent');
        contentArea.innerHTML = this.getLoadingHTML();
        
        fetch(`/courses/${courseId}/materials/${materialId}/diskusi`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.displayTopikList(data.topik);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showError('Gagal memuat daftar diskusi');
            });
    },
    
    displayTopikList: function(topikList) {
        const contentArea = document.getElementById('dynamicContent');
        
        let html = `
            <div class="space-y-6">
                <div class="flex items-center justify-between bg-white rounded-lg p-6 shadow-sm">
                    <div>
                        <h3 class="text-2xl font-bold text-slate-900">Ruang Diskusi</h3>
                        <p class="text-sm text-slate-600 mt-1">Berdiskusi dan berbagi pemikiran dengan teman sekelas</p>
                    </div>
                    ${window.canManage ? `
                        <button onclick="DiskusiManager.openCreateTopikModal()" 
                                class="bg-edu-purple text-white px-6 py-2 rounded-lg hover:bg-edu-purple/90 transition-colors flex items-center space-x-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span>Buat Topik Baru</span>
                        </button>
                    ` : ''}
                </div>
                
                <div class="space-y-4">
        `;
        
        if (topikList.length === 0) {
            html += this.getEmptyStateHTML();
        } else {
            topikList.forEach(topik => {
                html += this.renderTopikCard(topik);
            });
        }
        
        html += `</div></div>`;
        contentArea.innerHTML = html;
    },
    
    renderTopikCard: function(topik) {
        return `
            <div class="diskusi-card bg-white rounded-lg p-6 shadow-sm border border-slate-200 cursor-pointer"
                 onclick="DiskusiManager.loadTopikDetail(${topik.id_topik_diskusi})">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex-1">
                        <div class="flex items-center space-x-3 mb-2">
                            <h4 class="text-lg font-bold text-slate-900">${this.escapeHtml(topik.judul)}</h4>
                            <span class="status-badge ${topik.is_aktif ? 'status-aktif' : 'status-ditutup'}">
                                ${topik.is_aktif ? '🟢 Aktif' : '🔴 Ditutup'}
                            </span>
                        </div>
                        <p class="text-sm text-slate-600 line-clamp-2">${this.escapeHtml(topik.deskripsi)}</p>
                    </div>
                    ${topik.unread_count > 0 ? `
                        <span class="badge-unread bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full ml-3">
                            ${topik.unread_count}
                        </span>
                    ` : ''}
                </div>
                
                <div class="flex items-center justify-between text-sm text-slate-500">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>${this.escapeHtml(topik.pembuat)}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                            </svg>
                            <span>${topik.total_komentar} komentar</span>
                        </div>
                    </div>
                    <span>${topik.dibuat_pada}</span>
                </div>
            </div>
        `;
    },
    
    loadTopikDetail: function(topikId) {
        this.currentTopikId = topikId;
        const contentArea = document.getElementById('dynamicContent');
        contentArea.innerHTML = this.getLoadingHTML();
        
        fetch(`/courses/${courseId}/materials/${materialId}/diskusi/${topikId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.displayTopikDetail(data.topik);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                this.showError('Gagal memuat detail diskusi');
            });
    },
    
    displayTopikDetail: function(topik) {
        const contentArea = document.getElementById('dynamicContent');
        contentArea.innerHTML = `
            <div class="space-y-6">
                ${this.renderTopikHeader(topik)}
                ${this.renderTopikDescription(topik)}
                ${this.renderKomentarSection(topik)}
            </div>
        `;
    },
    
    renderTopikHeader: function(topik) {
        return `
            <div class="flex items-center space-x-4 bg-white rounded-lg p-4 shadow-sm">
                <button onclick="DiskusiManager.backToList()" 
                        class="text-edu-purple hover:text-edu-purple/80 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </button>
                <div class="flex-1">
                    <div class="flex items-center space-x-3">
                        <h3 class="text-xl font-bold text-slate-900">${this.escapeHtml(topik.judul)}</h3>
                        <span class="status-badge ${topik.is_aktif ? 'status-aktif' : 'status-ditutup'}">
                            ${topik.is_aktif ? '🟢 Aktif' : '🔴 Ditutup'}
                        </span>
                    </div>
                    <p class="text-sm text-slate-600 mt-1">
                        Dibuat oleh ${this.escapeHtml(topik.pembuat.nama)} • ${topik.dibuat_pada}
                    </p>
                </div>
                ${window.canManage ? `
                    <button onclick="DiskusiManager.toggleTopikStatus(${topik.id_topik_diskusi}, ${topik.is_aktif})" 
                            class="px-4 py-2 ${topik.is_aktif ? 'bg-red-500 hover:bg-red-600' : 'bg-green-500 hover:bg-green-600'} text-white rounded-lg transition-colors text-sm">
                        ${topik.is_aktif ? 'Tutup Diskusi' : 'Buka Kembali'}
                    </button>
                ` : ''}
            </div>
        `;
    },
    
    renderTopikDescription: function(topik) {
        return `
            <div class="bg-purple-50 rounded-lg p-6 border border-purple-200">
                <p class="text-slate-700 whitespace-pre-wrap">${this.escapeHtml(topik.deskripsi)}</p>
            </div>
        `;
    },
    
    renderKomentarSection: function(topik) {
        let html = `
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h4 class="font-bold text-slate-900">Diskusi (${topik.komentar.length})</h4>
                </div>
                
                <div class="p-6">
        `;
        
        if (topik.is_aktif) {
            html += this.renderAddCommentForm();
        } else {
            html += this.renderClosedNotice();
        }
        
        html += '<div class="space-y-4" id="komentarList">';
        
        if (topik.komentar.length === 0) {
            html += this.getEmptyKomentarHTML();
        } else {
            topik.komentar.forEach(komentar => {
                html += this.renderKomentar(komentar, topik.is_aktif);
            });
        }
        
        html += '</div></div></div>';
        return html;
    },
    
    renderAddCommentForm: function() {
        return `
            <div class="mb-6">
                <form onsubmit="DiskusiManager.addComment(event, null)">
                    <textarea name="konten" rows="3" required
                              class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-purple focus:border-transparent"
                              placeholder="Tulis komentar Anda..."></textarea>
                    <div class="mt-2 flex justify-end">
                        <button type="submit"
                                class="px-6 py-2 bg-edu-purple text-white rounded-lg hover:bg-edu-purple/90 transition-colors">
                            Kirim
                        </button>
                    </div>
                </form>
            </div>
        `;
    },
    
    renderClosedNotice: function() {
        return `
            <div class="mb-6 bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <p class="text-yellow-800 text-sm">
                    <svg class="w-5 h-5 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Diskusi ini telah ditutup. Anda tidak dapat menambahkan komentar baru.
                </p>
            </div>
        `;
    },
    
    renderKomentar: function(komentar, isTopikAktif, isReply = false) {
        const userInitial = komentar.user.nama.charAt(0).toUpperCase();
        const isGuru = komentar.user.is_guru;
        const kontenEscaped = this.escapeHtml(komentar.konten).replace(/\n/g, '<br>');
        
        let html = `
            <div class="komentar-item ${isReply ? 'reply-item' : ''} p-4 rounded-lg">
                <div class="flex items-start space-x-3">
                    <div class="w-10 h-10 ${isGuru ? 'bg-gradient-to-br from-purple-500 to-purple-600' : 'bg-gradient-to-br from-blue-500 to-blue-600'} text-white rounded-full flex items-center justify-center text-sm font-bold flex-shrink-0">
                        ${userInitial}
                    </div>
                    
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center space-x-2">
                                <span class="font-medium text-slate-900">${this.escapeHtml(komentar.user.nama)}</span>
                                ${isGuru ? '<span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded">Guru</span>' : ''}
                                <span class="text-xs text-slate-500">${komentar.created_at}</span>
                                ${komentar.diedit ? '<span class="text-xs text-slate-400">(diedit)</span>' : ''}
                            </div>
                            
                            <div class="comment-actions flex items-center space-x-2">
                                ${komentar.can_edit ? `
                                    <button onclick="DiskusiManager.editKomentar(${komentar.id_komentar}, \`${komentar.konten.replace(/`/g, '\\`')}\`)"
                                            class="text-slate-400 hover:text-slate-600 text-xs">
                                        Edit
                                    </button>
                                ` : ''}
                                ${komentar.can_delete ? `
                                    <button onclick="DiskusiManager.deleteKomentar(${komentar.id_komentar})"
                                            class="text-red-400 hover:text-red-600 text-xs">
                                        Hapus
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                        
                        <p class="text-slate-700 mb-2">${kontenEscaped}</p>
                        
                        <div class="flex items-center space-x-4 text-sm">
                            <button onclick="DiskusiManager.toggleLike(${komentar.id_komentar})"
                                    class="like-button flex items-center space-x-1 ${komentar.is_liked ? 'liked' : 'text-slate-500 hover:text-red-500'} transition-colors">
                                <svg class="w-4 h-4" fill="${komentar.is_liked ? 'currentColor' : 'none'}" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                                </svg>
                                <span id="likes-${komentar.id_komentar}">${komentar.likes_count}</span>
                            </button>
                            
                            ${isTopikAktif && !isReply ? `
                                <button onclick="DiskusiManager.showReplyForm(${komentar.id_komentar})"
                                        class="text-slate-500 hover:text-edu-purple transition-colors">
                                    Balas
                                </button>
                            ` : ''}
                        </div>
                        
                        ${isTopikAktif && !isReply ? this.renderReplyForm(komentar.id_komentar) : ''}
                        ${komentar.replies && komentar.replies.length > 0 ? this.renderReplies(komentar.replies, isTopikAktif) : ''}
                    </div>
                </div>
            </div>
        `;
        
        return html;
    },
    
    renderReplyForm: function(komentarId) {
        return `
            <div id="reply-form-${komentarId}" class="hidden mt-3">
                <form onsubmit="DiskusiManager.addComment(event, ${komentarId})">
                    <textarea name="konten" rows="2" required
                              class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-edu-purple focus:border-transparent text-sm"
                              placeholder="Tulis balasan Anda..."></textarea>
                    <div class="mt-2 flex justify-end space-x-2">
                        <button type="button" onclick="DiskusiManager.hideReplyForm(${komentarId})"
                                class="px-3 py-1 text-sm text-slate-600 hover:text-slate-800">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-1 text-sm bg-edu-purple text-white rounded-lg hover:bg-edu-purple/90">
                            Kirim
                        </button>
                    </div>
                </form>
            </div>
        `;
    },
    
    renderReplies: function(replies, isTopikAktif) {
        let html = '<div class="mt-3 space-y-3">';
        replies.forEach(reply => {
            html += this.renderKomentar(reply, isTopikAktif, true);
        });
        html += '</div>';
        return html;
    },
    
    // API Calls
    createTopik: function(form) {
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        
        submitBtn.disabled = true;
        submitBtn.textContent = 'Membuat...';
        
        fetch(`/courses/${courseId}/materials/${materialId}/diskusi`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.closeCreateTopikModal();
                this.showNotification('success', 'Topik diskusi berhasil dibuat');
                this.loadTopikList();
            } else {
                this.showNotification('error', data.message || 'Gagal membuat topik diskusi');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showNotification('error', 'Terjadi kesalahan');
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        });
    },
    
    addComment: function(event, parentId) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        
        submitBtn.disabled = true;
        
        const data = {
            konten: formData.get('konten'),
            id_parent: parentId
        };
        
        fetch(`/courses/${courseId}/materials/${materialId}/diskusi/${this.currentTopikId}/comments`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification('success', 'Komentar berhasil ditambahkan');
                this.loadTopikDetail(this.currentTopikId);
            } else {
                this.showNotification('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showNotification('error', 'Terjadi kesalahan');
        })
        .finally(() => {
            submitBtn.disabled = false;
        });
    },
    
    editKomentar: function(komentarId, konten) {
        document.getElementById('editKomentarId').value = komentarId;
        document.getElementById('editKomentarText').value = konten;
        document.getElementById('editKomentarModal').classList.remove('hidden');
    },
    
    updateKomentar: function() {
        const komentarId = document.getElementById('editKomentarId').value;
        const konten = document.getElementById('editKomentarText').value;
        
        fetch(`/courses/${courseId}/materials/${materialId}/diskusi/${this.currentTopikId}/comments/${komentarId}`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ konten })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.closeEditKomentarModal();
                this.showNotification('success', 'Komentar berhasil diupdate');
                this.loadTopikDetail(this.currentTopikId);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            this.showNotification('error', 'Terjadi kesalahan');
        });
    },
    
    deleteKomentar: function(komentarId) {
        if (!confirm('Apakah Anda yakin ingin menghapus komentar ini?')) return;
        
        fetch(`/courses/${courseId}/materials/${materialId}/diskusi/${this.currentTopikId}/comments/${komentarId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification('success', 'Komentar berhasil dihapus');
                this.loadTopikDetail(this.currentTopikId);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    },
    
    toggleLike: function(komentarId) {
        fetch(`/courses/${courseId}/materials/${materialId}/diskusi/${this.currentTopikId}/comments/${komentarId}/like`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const likesElement = document.getElementById(`likes-${komentarId}`);
                if (likesElement) {
                    likesElement.textContent = data.likes_count;
                }
            }
        })
        .catch(error => console.error('Error:', error));
    },
    
    toggleTopikStatus: function(topikId, isAktif) {
        const action = isAktif ? 'close' : 'reopen';
        const confirmMsg = isAktif ? 
            'Apakah Anda yakin ingin menutup diskusi ini?' :
            'Apakah Anda yakin ingin membuka kembali diskusi ini?';
        
        if (!confirm(confirmMsg)) return;
        
        fetch(`/courses/${courseId}/materials/${materialId}/diskusi/${topikId}/${action}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.showNotification('success', data.message);
                this.loadTopikDetail(topikId);
            }
        })
        .catch(error => console.error('Error:', error));
    },
    
    // UI Helpers
    showReplyForm: function(komentarId) {
        document.getElementById(`reply-form-${komentarId}`).classList.remove('hidden');
    },
    
    hideReplyForm: function(komentarId) {
        document.getElementById(`reply-form-${komentarId}`).classList.add('hidden');
    },
    
    backToList: function() {
        this.loadTopikList();
    },
    
    openCreateTopikModal: function() {
        document.getElementById('createTopikModal').classList.remove('hidden');
    },
    
    closeCreateTopikModal: function() {
        document.getElementById('createTopikModal').classList.add('hidden');
        document.getElementById('createTopikForm').reset();
    },
    
    closeEditKomentarModal: function() {
        document.getElementById('editKomentarModal').classList.add('hidden');
    },
    
    showNotification: function(type, message) {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
            type === 'success' ? 'bg-green-500' : 'bg-red-500'
        } text-white`;
        notification.textContent = message;
        document.body.appendChild(notification);
        
        setTimeout(() => notification.remove(), 3000);
    },
    
    showError: function(message) {
        const contentArea = document.getElementById('dynamicContent');
        contentArea.innerHTML = `
            <div class="bg-red-50 border border-red-200 rounded-lg p-6">
                <p class="text-red-700">${message}</p>
                <button onclick="DiskusiManager.loadTopikList()" 
                        class="mt-4 bg-red-600 text-white px-4 py-2 rounded-lg">
                    Coba Lagi
                </button>
            </div>
        `;
    },
    
    // Template Helpers
    getLoadingHTML: function() {
        return `
            <div class="flex items-center justify-center py-12">
                <svg class="animate-spin h-10 w-10 text-edu-purple" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>
        `;
    },
    
    getEmptyStateHTML: function() {
        return `
            <div class="bg-white rounded-lg p-12 text-center shadow-sm">
                <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <h4 class="text-lg font-medium text-slate-900 mb-2">Belum Ada Topik Diskusi</h4>
                <p class="text-slate-600">Diskusi akan muncul di sini setelah guru membuat topik</p>
            </div>
        `;
    },
    
    getEmptyKomentarHTML: function() {
        return `
            <div class="text-center py-8 text-slate-500">
                <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <p>Belum ada komentar. Jadilah yang pertama berkomentar!</p>
            </div>
        `;
    },
    
    escapeHtml: function(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
};

// Global functions
window.DiskusiManager = DiskusiManager;

function openCreateTopikModal() {
    DiskusiManager.openCreateTopikModal();
}

function closeCreateTopikModal() {
    DiskusiManager.closeCreateTopikModal();
}

function closeEditKomentarModal() {
    DiskusiManager.closeEditKomentarModal();
}

// Modal click outside to close
document.addEventListener('DOMContentLoaded', function() {
    const createModal = document.getElementById('createTopikModal');
    if (createModal) {
        createModal.addEventListener('click', function(e) {
            if (e.target === this) closeCreateTopikModal();
        });
    }
    
    const editModal = document.getElementById('editKomentarModal');
    if (editModal) {
        editModal.addEventListener('click', function(e) {
            if (e.target === this) closeEditKomentarModal();
        });
    }
});