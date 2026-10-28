document.addEventListener('alpine:init', () => {
    Alpine.data('kanban', (initialBoard, initialLabels, base) => ({
        base,
        columns: initialBoard,
        labels: initialLabels,
        search: '',
        quickAddTitles: {},
        currentTask: null,
        showDetail: false,
        showColumnManager: false,
        showLabelManager: false,
        newColumnName: '',
        editingColumnId: null,
        editingColumnName: '',
        labelForm: { id: null, name: '', color: '#3b82f6' },
        newChecklistText: '',
        commentText: '',
        uploading: false,
        selectedFile: null,
        toast: null,
        toastTimer: null,
        saving: false,

        init() {
            this.$nextTick(() => this.initSortables());
        },

        initSortables() {
            if (!window.Sortable) return;
            document.querySelectorAll('.kanban-sortable:not([data-sortable-init])').forEach((el) => {
                el.setAttribute('data-sortable-init', '1');
                window.Sortable.create(el, {
                    group: 'todos',
                    animation: 150,
                    draggable: '.kanban-card',
                    ghostClass: 'opacity-40',
                    onEnd: (evt) => this.onDrop(evt),
                });
            });
        },

        async api(url, options = {}) {
            const res = await fetch(url, {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    Accept: 'application/json',
                    ...(options.body instanceof FormData ? {} : { 'Content-Type': 'application/json' }),
                    ...(options.headers || {}),
                },
                ...options,
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok) {
                const message =
                    data.message ||
                    Object.values(data.errors || {}).flat().join(', ') ||
                    'Terjadi kesalahan';
                throw new Error(message);
            }
            return data;
        },

        showToast(message, type = 'success') {
            this.toast = { message, type };
            clearTimeout(this.toastTimer);
            this.toastTimer = setTimeout(() => (this.toast = null), 3500);
        },

        columnTasks(column) {
            if (!this.search) return column.tasks;
            const q = this.search.toLowerCase();
            return column.tasks.filter(
                (t) =>
                    t.title.toLowerCase().includes(q) ||
                    (t.labels || []).some((l) => l.name.toLowerCase().includes(q))
            );
        },

        isOverdue(task) {
            return !!task.due_date && new Date(task.due_date + 'T23:59:59') < new Date();
        },

        formatDate(value) {
            if (!value) return '';
            return new Date(value).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
        },

        priorityColor(priority) {
            return { high: 'bg-red-500', medium: 'bg-amber-500', low: 'bg-sky-400' }[priority] || '';
        },

        priorityLabel(priority) {
            return { high: 'Tinggi', medium: 'Sedang', low: 'Rendah' }[priority] || '';
        },

        hasBadges(task) {
            return (
                !!task.due_date ||
                !!task.priority ||
                (task.labels || []).length > 0 ||
                task.checklist_total > 0 ||
                task.attachment_count > 0 ||
                task.comment_count > 0
            );
        },

        patchCard(taskId, patch) {
            for (const col of this.columns) {
                const task = col.tasks.find((t) => t.id === taskId);
                if (task) {
                    Object.assign(task, patch);
                    return;
                }
            }
        },

        placeCard(card) {
            for (const col of this.columns) {
                const index = col.tasks.findIndex((t) => t.id === card.id);
                if (index !== -1) {
                    col.tasks.splice(index, 1);
                    break;
                }
            }
            const target = this.columns.find((c) => c.id === card.column_id);
            if (target) target.tasks.push(card);
        },

        onDrop(evt) {
            const taskId = evt.item.dataset.taskId;
            const toColumnId = evt.to.dataset.columnId;
            const position = evt.newIndex;

            let task = null;
            for (const col of this.columns) {
                const index = col.tasks.findIndex((t) => t.id === taskId);
                if (index !== -1) {
                    task = col.tasks.splice(index, 1)[0];
                    break;
                }
            }
            if (!task) return;

            task.column_id = toColumnId;
            const target = this.columns.find((c) => c.id === toColumnId);
            if (target) target.tasks.splice(position, 0, task);

            this.api(`${this.base}/tasks/${taskId}/move`, {
                method: 'PATCH',
                body: JSON.stringify({ column_id: toColumnId, position }),
            }).catch((e) => {
                this.showToast(e.message, 'error');
            });
        },

        async quickAddTask(column) {
            const title = (this.quickAddTitles[column.id] || '').trim();
            if (!title) return;
            try {
                const data = await this.api(`${this.base}/quick-add`, {
                    method: 'POST',
                    body: JSON.stringify({ column_id: column.id, title }),
                });
                column.tasks.push(data.task);
                this.quickAddTitles[column.id] = '';
            } catch (e) {
                this.showToast(e.message, 'error');
            }
        },

        async openTask(taskId) {
            try {
                const data = await this.api(`${this.base}/tasks/${taskId}/detail`);
                this.currentTask = data;
                this.newChecklistText = '';
                this.commentText = '';
                this.selectedFile = null;
                this.showDetail = true;
            } catch (e) {
                this.showToast(e.message, 'error');
            }
        },

        closeDetail() {
            this.showDetail = false;
            this.currentTask = null;
        },

        async saveTask() {
            if (!this.currentTask || this.saving) return;
            this.saving = true;
            try {
                const data = await this.api(`${this.base}/tasks/${this.currentTask.id}`, {
                    method: 'PUT',
                    body: JSON.stringify({
                        title: this.currentTask.title,
                        description: this.currentTask.description,
                        due_date: this.currentTask.due_date || null,
                        priority: this.currentTask.priority || null,
                        column_id: this.currentTask.column_id,
                    }),
                });
                this.placeCard(data.task);
            } catch (e) {
                this.showToast(e.message, 'error');
            } finally {
                this.saving = false;
            }
        },

        async deleteTask() {
            if (!this.currentTask) return;
            if (!confirm('Hapus kartu ini?')) return;
            try {
                await this.api(`${this.base}/tasks/${this.currentTask.id}`, { method: 'DELETE' });
                for (const col of this.columns) {
                    const index = col.tasks.findIndex((t) => t.id === this.currentTask.id);
                    if (index !== -1) {
                        col.tasks.splice(index, 1);
                        break;
                    }
                }
                this.closeDetail();
                this.showToast('Kartu berhasil dihapus.');
            } catch (e) {
                this.showToast(e.message, 'error');
            }
        },

        taskHasLabel(labelId) {
            return (this.currentTask?.labels || []).some((l) => l.id === labelId);
        },

        async toggleLabel(label) {
            if (!this.currentTask) return;
            const current = this.currentTask.labels;
            const index = current.findIndex((l) => l.id === label.id);
            if (index !== -1) current.splice(index, 1);
            else current.push({ id: label.id, name: label.name, color: label.color });

            try {
                const data = await this.api(`${this.base}/tasks/${this.currentTask.id}/labels`, {
                    method: 'PATCH',
                    body: JSON.stringify({ label_ids: current.map((l) => l.id) }),
                });
                this.placeCard(data.task);
            } catch (e) {
                this.showToast(e.message, 'error');
            }
        },

        async addChecklistItem() {
            if (!this.currentTask) return;
            const text = this.newChecklistText.trim();
            if (!text) return;
            try {
                const data = await this.api(`${this.base}/tasks/${this.currentTask.id}/checklist`, {
                    method: 'POST',
                    body: JSON.stringify({ text }),
                });
                this.currentTask.checklist.push(data.item);
                this.currentTask.checklist_total = data.checklist_total;
                this.currentTask.checklist_done = data.checklist_done;
                this.patchCard(this.currentTask.id, {
                    checklist_total: data.checklist_total,
                    checklist_done: data.checklist_done,
                });
                this.newChecklistText = '';
            } catch (e) {
                this.showToast(e.message, 'error');
            }
        },

        async toggleChecklistItem(item, checked) {
            if (!this.currentTask) return;
            try {
                const data = await this.api(`${this.base}/checklist-items/${item.id}`, {
                    method: 'PATCH',
                    body: JSON.stringify({ is_done: checked }),
                });
                item.is_done = data.item.is_done;
                this.currentTask.checklist_total = data.checklist_total;
                this.currentTask.checklist_done = data.checklist_done;
                this.patchCard(this.currentTask.id, {
                    checklist_total: data.checklist_total,
                    checklist_done: data.checklist_done,
                });
            } catch (e) {
                item.is_done = !checked;
                this.showToast(e.message, 'error');
            }
        },

        async deleteChecklistItem(item) {
            if (!this.currentTask) return;
            try {
                const data = await this.api(`${this.base}/checklist-items/${item.id}`, {
                    method: 'DELETE',
                });
                this.currentTask.checklist = this.currentTask.checklist.filter((i) => i.id !== item.id);
                this.currentTask.checklist_total = data.checklist_total;
                this.currentTask.checklist_done = data.checklist_done;
                this.patchCard(this.currentTask.id, {
                    checklist_total: data.checklist_total,
                    checklist_done: data.checklist_done,
                });
            } catch (e) {
                this.showToast(e.message, 'error');
            }
        },

        onFileSelected(event) {
            this.selectedFile = event.target.files[0] || null;
        },

        async uploadFile() {
            if (!this.currentTask || !this.selectedFile) return;
            this.uploading = true;
            const fd = new FormData();
            fd.append('file', this.selectedFile);
            try {
                const data = await this.api(`${this.base}/tasks/${this.currentTask.id}/attachments`, {
                    method: 'POST',
                    body: fd,
                });
                this.currentTask.attachments.push(data.attachment);
                this.currentTask.attachment_count = data.attachment_count;
                this.patchCard(this.currentTask.id, { attachment_count: data.attachment_count });
                this.selectedFile = null;
                if (this.$refs.fileInput) this.$refs.fileInput.value = '';
                this.showToast('Lampiran berhasil diunggah.');
            } catch (e) {
                this.showToast(e.message, 'error');
            } finally {
                this.uploading = false;
            }
        },

        async deleteAttachment(attachment) {
            if (!this.currentTask) return;
            if (!confirm(`Hapus lampiran "${attachment.original_name}"?`)) return;
            try {
                const data = await this.api(`${this.base}/attachments/${attachment.id}`, {
                    method: 'DELETE',
                });
                this.currentTask.attachments = this.currentTask.attachments.filter((a) => a.id !== attachment.id);
                this.currentTask.attachment_count = data.attachment_count;
                this.patchCard(this.currentTask.id, { attachment_count: data.attachment_count });
            } catch (e) {
                this.showToast(e.message, 'error');
            }
        },

        attachmentUrl(attachmentId) {
            return `${this.base}/attachments/${attachmentId}/download`;
        },

        async addComment() {
            if (!this.currentTask) return;
            const body = this.commentText.trim();
            if (!body) return;
            try {
                const data = await this.api(`${this.base}/tasks/${this.currentTask.id}/comments`, {
                    method: 'POST',
                    body: JSON.stringify({ body }),
                });
                this.currentTask.comments.unshift(data.comment);
                this.currentTask.comment_count = data.comment_count;
                this.patchCard(this.currentTask.id, { comment_count: data.comment_count });
                this.commentText = '';
            } catch (e) {
                this.showToast(e.message, 'error');
            }
        },

        async deleteComment(comment) {
            if (!this.currentTask) return;
            if (!confirm('Hapus komentar ini?')) return;
            try {
                const data = await this.api(`${this.base}/comments/${comment.id}`, {
                    method: 'DELETE',
                });
                this.currentTask.comments = this.currentTask.comments.filter((c) => c.id !== comment.id);
                this.currentTask.comment_count = data.comment_count;
                this.patchCard(this.currentTask.id, { comment_count: data.comment_count });
            } catch (e) {
                this.showToast(e.message, 'error');
            }
        },

        async addColumn() {
            const name = prompt('Nama kolom baru:');
            if (!name || !name.trim()) return;
            try {
                const data = await this.api(`${this.base}/columns`, {
                    method: 'POST',
                    body: JSON.stringify({ name: name.trim() }),
                });
                this.columns.push(data.column);
                this.$nextTick(() => this.initSortables());
            } catch (e) {
                this.showToast(e.message, 'error');
            }
        },

        startRenameColumn(column) {
            this.editingColumnId = column.id;
            this.editingColumnName = column.name;
        },

        async saveRenameColumn() {
            const name = this.editingColumnName.trim();
            if (!name || !this.editingColumnId) return;
            try {
                const data = await this.api(`${this.base}/columns/${this.editingColumnId}`, {
                    method: 'PUT',
                    body: JSON.stringify({ name }),
                });
                const column = this.columns.find((c) => c.id === this.editingColumnId);
                if (column) column.name = data.column.name;
                this.editingColumnId = null;
                this.editingColumnName = '';
            } catch (e) {
                this.showToast(e.message, 'error');
            }
        },

        async deleteColumn(column) {
            if (!confirm(`Hapus kolom "${column.name}"? Kartu di dalamnya akan dipindah ke kolom pertama.`)) return;
            try {
                const data = await this.api(`${this.base}/columns/${column.id}`, { method: 'DELETE' });
                this.columns = this.columns.filter((c) => c.id !== column.id);
                if (data.target_column_id) {
                    const target = this.columns.find((c) => c.id === data.target_column_id);
                    if (target) target.tasks.push(...data.moved_tasks);
                }
                this.showToast('Kolom berhasil dihapus.');
            } catch (e) {
                this.showToast(e.message, 'error');
            }
        },

        moveColumn(index, direction) {
            const target = index + direction;
            if (target < 0 || target >= this.columns.length) return;
            const tmp = this.columns[index];
            this.columns[index] = this.columns[target];
            this.columns[target] = tmp;
            this.columns = [...this.columns];
            this.persistColumnOrder();
        },

        persistColumnOrder() {
            this.api(`${this.base}/columns/reorder`, {
                method: 'PATCH',
                body: JSON.stringify({ columns: this.columns.map((c) => c.id) }),
            }).catch((e) => this.showToast(e.message, 'error'));
        },

        async storeColumn() {
            const name = this.newColumnName.trim();
            if (!name) return;
            try {
                const data = await this.api(`${this.base}/columns`, {
                    method: 'POST',
                    body: JSON.stringify({ name }),
                });
                this.columns.push(data.column);
                this.newColumnName = '';
                this.$nextTick(() => this.initSortables());
            } catch (e) {
                this.showToast(e.message, 'error');
            }
        },

        async saveLabel() {
            const name = this.labelForm.name.trim();
            if (!name) return;
            try {
                const url = this.labelForm.id ? `${this.base}/labels/${this.labelForm.id}` : `${this.base}/labels`;
                const data = await this.api(url, {
                    method: this.labelForm.id ? 'PUT' : 'POST',
                    body: JSON.stringify({ name, color: this.labelForm.color }),
                });
                if (this.labelForm.id) {
                    const index = this.labels.findIndex((l) => l.id === this.labelForm.id);
                    if (index !== -1) this.labels[index] = data.label;
                } else {
                    this.labels.push(data.label);
                }
                this.labelForm = { id: null, name: '', color: '#3b82f6' };
            } catch (e) {
                this.showToast(e.message, 'error');
            }
        },

        editLabel(label) {
            this.labelForm = { id: label.id, name: label.name, color: label.color };
        },

        async deleteLabel(label) {
            if (!confirm(`Hapus label "${label.name}"?`)) return;
            try {
                await this.api(`${this.base}/labels/${label.id}`, { method: 'DELETE' });
                this.labels = this.labels.filter((l) => l.id !== label.id);
                for (const col of this.columns) {
                    for (const task of col.tasks) {
                        task.labels = (task.labels || []).filter((l) => l.id !== label.id);
                    }
                }
            } catch (e) {
                this.showToast(e.message, 'error');
            }
        },

        resetLabelForm() {
            this.labelForm = { id: null, name: '', color: '#3b82f6' };
        },
    }));
});
