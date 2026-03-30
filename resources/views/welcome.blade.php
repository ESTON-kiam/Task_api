<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Task API — Dashboard</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg: #0f1117;
            --surface: #1a1d27;
            --surface2: #232639;
            --border: #2e3150;
            --accent: #6c63ff;
            --accent2: #5ad8a6;
            --red: #ff6b6b;
            --yellow: #ffd166;
            --text: #e2e8f0;
            --muted: #8892a4;
            --radius: 10px;
        }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
        }

        header {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        header h1 { font-size: 1.3rem; font-weight: 700; letter-spacing: -0.3px; }
        header span { color: var(--accent); }
        .version-badge {
            background: var(--accent);
            color: #fff;
            font-size: 0.7rem;
            padding: 2px 8px;
            border-radius: 20px;
            font-weight: 600;
        }

        .layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            min-height: calc(100vh - 60px);
        }

        aside {
            background: var(--surface);
            border-right: 1px solid var(--border);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        aside h2 { font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; color: var(--muted); margin-bottom: 0.25rem; }

        .form-group { display: flex; flex-direction: column; gap: 0.35rem; }
        .form-group label { font-size: 0.8rem; color: var(--muted); }

        input, select {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: 6px;
            color: var(--text);
            padding: 0.5rem 0.75rem;
            font-size: 0.875rem;
            width: 100%;
            transition: border 0.2s;
        }
        input:focus, select:focus { outline: none; border-color: var(--accent); }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.4rem;
            padding: 0.55rem 1.2rem;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: opacity 0.15s, transform 0.1s;
        }
        .btn:hover { opacity: 0.88; transform: translateY(-1px); }
        .btn:active { transform: translateY(0); }
        .btn-primary { background: var(--accent); color: #fff; width: 100%; margin-top: 0.25rem; }
        .btn-sm { padding: 0.3rem 0.75rem; font-size: 0.78rem; }
        .btn-advance { background: var(--accent2); color: #0f1117; }
        .btn-delete  { background: var(--red); color: #fff; }
        .btn-report  { background: var(--yellow); color: #0f1117; width: 100%; }

        .divider { border: none; border-top: 1px solid var(--border); }

        main { padding: 2rem; overflow-y: auto; }

        .toolbar {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .toolbar h2 { font-size: 1.1rem; flex: 1; }

        .filter-bar { display: flex; gap: 0.5rem; align-items: center; }
        .filter-bar select { width: auto; }

        .task-grid {
            display: grid;
            gap: 0.875rem;
        }

        .task-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 1rem 1.25rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            transition: border-color 0.2s;
        }
        .task-card:hover { border-color: var(--accent); }

        .priority-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-top: 5px;
            flex-shrink: 0;
        }
        .priority-dot.high   { background: var(--red); }
        .priority-dot.medium { background: var(--yellow); }
        .priority-dot.low    { background: var(--accent2); }

        .task-info { flex: 1; min-width: 0; }
        .task-title { font-weight: 600; font-size: 0.95rem; margin-bottom: 0.3rem; }
        .task-meta  { display: flex; gap: 0.75rem; flex-wrap: wrap; font-size: 0.78rem; color: var(--muted); }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .badge.pending    { background: #2e3150; color: #9ba8d0; }
        .badge.in_progress { background: #1e3a5f; color: #6db8ff; }
        .badge.done       { background: #1a3a2a; color: var(--accent2); }

        .badge.high   { background: #3a1a1a; color: var(--red); }
        .badge.medium { background: #3a2e1a; color: var(--yellow); }
        .badge.low    { background: #1a3a30; color: var(--accent2); }

        .task-actions { display: flex; gap: 0.4rem; flex-shrink: 0; align-items: center; }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: var(--muted);
        }
        .empty-state svg { opacity: 0.3; margin-bottom: 1rem; }

        .toast-container {
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            z-index: 1000;
        }
        .toast {
            background: var(--surface2);
            border: 1px solid var(--border);
            border-left: 4px solid var(--accent);
            border-radius: 8px;
            padding: 0.75rem 1.25rem;
            font-size: 0.85rem;
            max-width: 320px;
            animation: slideIn 0.25s ease;
        }
        .toast.error { border-left-color: var(--red); }
        .toast.success { border-left-color: var(--accent2); }

        @keyframes slideIn {
            from { transform: translateX(40px); opacity: 0; }
            to   { transform: translateX(0);    opacity: 1; }
        }

        /* Report modal */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            z-index: 200;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.active { display: flex; }
        .modal {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 2rem;
            width: 480px;
            max-width: 95vw;
        }
        .modal h3 { font-size: 1rem; margin-bottom: 1.25rem; }
        .modal-close { float: right; cursor: pointer; color: var(--muted); background: none; border: none; font-size: 1.2rem; }

        .report-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; margin-top: 1rem; }
        .report-table th, .report-table td { border: 1px solid var(--border); padding: 0.5rem 0.75rem; text-align: center; }
        .report-table th { background: var(--bg); color: var(--muted); font-weight: 600; text-transform: uppercase; font-size: 0.75rem; }
        .report-table td:first-child { text-align: left; font-weight: 600; }

        .loader { text-align: center; padding: 3rem; color: var(--muted); }

        @media (max-width: 768px) {
            .layout { grid-template-columns: 1fr; }
            aside { border-right: none; border-bottom: 1px solid var(--border); }
        }
    </style>
</head>
<body>

<header>
    <h1>Task<span>API</span></h1>
    <!-- <span class="version-badge">v1.0</span> -->
</header>

<div class="layout">
    <!-- SIDEBAR: Create task form -->
    <aside>
        <div>
            <h2>New Task</h2>
        </div>

        <div class="form-group">
            <label for="task-title">Title *</label>
            <input id="task-title" type="text" placeholder="e.g. Fix login bug" maxlength="255" />
        </div>

        <div class="form-group">
            <label for="task-due">Due Date *</label>
            <input id="task-due" type="date" />
        </div>

        <div class="form-group">
            <label for="task-priority">Priority *</label>
            <select id="task-priority">
                <option value="">Select priority…</option>
                <option value="high">🔴 High</option>
                <option value="medium">🟡 Medium</option>
                <option value="low">🟢 Low</option>
            </select>
        </div>

        <button class="btn btn-primary" onclick="createTask()">＋ Create Task</button>

        <hr class="divider" />

        <div>
            <h2>Daily Report</h2>
        </div>

        <div class="form-group">
            <label for="report-date">Report Date</label>
            <input id="report-date" type="date" />
        </div>

        <button class="btn btn-report" onclick="fetchReport()">📊 View Report</button>
    </aside>

    <!-- MAIN: Task list -->
    <main>
        <div class="toolbar">
            <h2>Tasks</h2>
            <div class="filter-bar">
                <label style="font-size:0.8rem;color:var(--muted)">Filter:</label>
                <select id="filter-status" onchange="loadTasks()">
                    <option value="">All statuses</option>
                    <option value="pending">Pending</option>
                    <option value="in_progress">In Progress</option>
                    <option value="done">Done</option>
                </select>
                <button class="btn btn-sm" style="background:var(--surface2);color:var(--text)" onclick="loadTasks()">↻ Refresh</button>
            </div>
        </div>

        <div id="task-list" class="task-grid">
            <div class="loader">Loading tasks…</div>
        </div>
    </main>
</div>

<!-- Report Modal -->
<div class="modal-overlay" id="report-modal">
    <div class="modal">
        <button class="modal-close" onclick="closeReport()">✕</button>
        <h3>📊 Daily Report — <span id="report-date-label"></span></h3>
        <div id="report-content"></div>
    </div>
</div>

<!-- Toast container -->
<div class="toast-container" id="toasts"></div>

<script>
    const API = '/api';

    // ── Init ──────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        // Default due date = today
        const today = new Date().toISOString().slice(0, 10);
        document.getElementById('task-due').value = today;
        document.getElementById('task-due').min   = today;
        document.getElementById('report-date').value = today;
        loadTasks();
    });

    // ── Create Task ───────────────────────────────
    async function createTask() {
        const title    = document.getElementById('task-title').value.trim();
        const due_date = document.getElementById('task-due').value;
        const priority = document.getElementById('task-priority').value;

        if (!title || !due_date || !priority) {
            return toast('Please fill in all fields.', 'error');
        }

        try {
            const res  = await fetch(`${API}/tasks`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ title, due_date, priority }),
            });
            const data = await res.json();

            if (!res.ok) {
                const msg = extractError(data);
                return toast(msg, 'error');
            }

            toast('Task created!', 'success');
            document.getElementById('task-title').value = '';
            loadTasks();
        } catch (e) {
            toast('Network error. Is the server running?', 'error');
        }
    }

    // ── Load Tasks ────────────────────────────────
    async function loadTasks() {
        const status = document.getElementById('filter-status').value;
        const url    = status ? `${API}/tasks?status=${status}` : `${API}/tasks`;
        const list   = document.getElementById('task-list');
        list.innerHTML = '<div class="loader">Loading…</div>';

        try {
            const res  = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();

            if (!res.ok) return (list.innerHTML = `<div class="empty-state">${data.message || 'Error loading tasks.'}</div>`);

            const tasks = data.data || [];
            if (!tasks.length) {
                list.innerHTML = `
                    <div class="empty-state">
                        <svg width="48" height="48" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        <p>${data.message || 'No tasks yet. Create one!'}</p>
                    </div>`;
                return;
            }

            list.innerHTML = tasks.map(renderTask).join('');
        } catch (e) {
            list.innerHTML = '<div class="empty-state">Could not reach the API. Is Laravel running?</div>';
        }
    }

    function renderTask(t) {
        const nextLabel = { pending: 'Start', in_progress: 'Complete', done: null }[t.status];
        const canDelete = t.status === 'done';

        return `
        <div class="task-card" id="task-${t.id}">
            <div class="priority-dot ${t.priority}"></div>
            <div class="task-info">
                <div class="task-title">${escHtml(t.title)}</div>
                <div class="task-meta">
                    <span>📅 ${t.due_date}</span>
                    <span class="badge ${t.priority}">${t.priority}</span>
                    <span class="badge ${t.status}">${t.status.replace('_', ' ')}</span>
                </div>
            </div>
            <div class="task-actions">
                ${nextLabel ? `<button class="btn btn-sm btn-advance" onclick="advanceStatus(${t.id}, '${nextStatus(t.status)}')">${nextLabel}</button>` : ''}
                ${canDelete ? `<button class="btn btn-sm btn-delete" onclick="deleteTask(${t.id})">Delete</button>` : ''}
            </div>
        </div>`;
    }

    function nextStatus(s) {
        return { pending: 'in_progress', in_progress: 'done' }[s] || '';
    }

    // ── Advance Status ────────────────────────────
    async function advanceStatus(id, status) {
        try {
            const res  = await fetch(`${API}/tasks/${id}/status`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ status }),
            });
            const data = await res.json();
            if (!res.ok) return toast(data.message || 'Error updating status.', 'error');
            toast(data.message, 'success');
            loadTasks();
        } catch (e) {
            toast('Network error.', 'error');
        }
    }

    // ── Delete Task ───────────────────────────────
    async function deleteTask(id) {
        if (!confirm('Delete this task? This cannot be undone.')) return;
        try {
            const res  = await fetch(`${API}/tasks/${id}`, {
                method: 'DELETE',
                headers: { 'Accept': 'application/json' },
            });
            const data = await res.json();
            if (!res.ok) return toast(data.message || 'Could not delete task.', 'error');
            toast('Task deleted.', 'success');
            loadTasks();
        } catch (e) {
            toast('Network error.', 'error');
        }
    }

    // ── Report ────────────────────────────────────
    async function fetchReport() {
        const date = document.getElementById('report-date').value;
        if (!date) return toast('Please select a date.', 'error');

        try {
            const res  = await fetch(`${API}/tasks/report?date=${date}`, { headers: { 'Accept': 'application/json' } });
            const data = await res.json();
            if (!res.ok) return toast(data.message || 'Report error.', 'error');

            document.getElementById('report-date-label').textContent = date;
            document.getElementById('report-content').innerHTML = renderReport(data.summary);
            document.getElementById('report-modal').classList.add('active');
        } catch (e) {
            toast('Network error.', 'error');
        }
    }

    function renderReport(summary) {
        const rows = ['high', 'medium', 'low'].map(p => {
            const s = summary[p] || {};
            return `<tr>
                <td><span class="badge ${p}">${p}</span></td>
                <td>${s.pending || 0}</td>
                <td>${s.in_progress || 0}</td>
                <td>${s.done || 0}</td>
                <td><strong>${(s.pending||0)+(s.in_progress||0)+(s.done||0)}</strong></td>
            </tr>`;
        }).join('');

        return `
        <table class="report-table">
            <thead>
                <tr><th>Priority</th><th>Pending</th><th>In Progress</th><th>Done</th><th>Total</th></tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>`;
    }

    function closeReport() {
        document.getElementById('report-modal').classList.remove('active');
    }

    // ── Helpers ───────────────────────────────────
    function toast(msg, type = 'info') {
        const el = document.createElement('div');
        el.className = `toast ${type}`;
        el.textContent = msg;
        document.getElementById('toasts').appendChild(el);
        setTimeout(() => el.remove(), 4000);
    }

    function escHtml(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function extractError(data) {
        if (data.errors) {
            return Object.values(data.errors).flat().join(' ');
        }
        return data.message || 'Something went wrong.';
    }
</script>

</body>
</html>
