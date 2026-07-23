<style>
    .recruitment-ui {
        --rec-ink: #0f172a;
        --rec-muted: #475569;
        --rec-border: #dbe5f0;
        --rec-primary: #0891b2;
        --rec-primary-dark: #0e7490;
        color: var(--rec-ink);
    }

    .recruitment-ui .recruitment-hero,
    .recruitment-ui .recruitment-panel {
        background: rgba(255, 255, 255, 0.96) !important;
        border: 1px solid var(--rec-border) !important;
        box-shadow: 0 18px 45px -30px rgba(15, 23, 42, 0.45) !important;
    }

    .recruitment-ui .recruitment-stats {
        display: grid !important;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)) !important;
        gap: 1rem !important;
    }

    .recruitment-ui .recruitment-stats > *,
    .recruitment-ui .recruitment-card {
        background: rgba(255, 255, 255, 0.98) !important;
        border: 1px solid var(--rec-border) !important;
        border-radius: 1.35rem !important;
        color: var(--rec-ink) !important;
        box-shadow: 0 14px 35px -28px rgba(15, 23, 42, 0.45) !important;
    }

    .recruitment-ui .recruitment-btn-primary,
    .recruitment-ui a.bg-cyan-500,
    .recruitment-ui button.bg-cyan-500,
    .recruitment-ui a.bg-cyan-600,
    .recruitment-ui button.bg-cyan-600,
    .recruitment-ui a.bg-slate-900,
    .recruitment-ui button.bg-slate-900 {
        background: var(--rec-primary) !important;
        border: 1px solid var(--rec-primary-dark) !important;
        color: #fff !important;
        box-shadow: 0 14px 32px -18px rgba(8, 145, 178, 0.75) !important;
    }

    .recruitment-ui .recruitment-btn-primary:hover,
    .recruitment-ui a.bg-cyan-500:hover,
    .recruitment-ui button.bg-cyan-500:hover,
    .recruitment-ui a.bg-cyan-600:hover,
    .recruitment-ui button.bg-cyan-600:hover,
    .recruitment-ui a.bg-slate-900:hover,
    .recruitment-ui button.bg-slate-900:hover {
        background: var(--rec-primary-dark) !important;
        color: #fff !important;
    }

    .recruitment-ui a.bg-indigo-600,
    .recruitment-ui button.bg-indigo-600 {
        background: #4f46e5 !important;
        border: 1px solid #4338ca !important;
        color: #fff !important;
    }

    .recruitment-ui a.bg-indigo-600:hover,
    .recruitment-ui button.bg-indigo-600:hover {
        background: #4338ca !important;
        color: #fff !important;
    }

    .recruitment-ui a.bg-emerald-600,
    .recruitment-ui button.bg-emerald-600 {
        background: #059669 !important;
        border: 1px solid #047857 !important;
        color: #fff !important;
    }

    .recruitment-ui a.bg-emerald-600:hover,
    .recruitment-ui button.bg-emerald-600:hover {
        background: #047857 !important;
        color: #fff !important;
    }

    .recruitment-ui a.bg-white,
    .recruitment-ui button.bg-white,
    .recruitment-ui a.bg-slate-100,
    .recruitment-ui button.bg-slate-100 {
        background: #f8fafc !important;
        border: 1px solid #cbd5e1 !important;
        color: #1e293b !important;
    }

    .recruitment-ui a.bg-cyan-50,
    .recruitment-ui a.bg-cyan-100,
    .recruitment-ui button.bg-cyan-50,
    .recruitment-ui button.bg-cyan-100 {
        background: #cffafe !important;
        border: 1px solid #67e8f9 !important;
        color: #155e75 !important;
    }

    .recruitment-ui a.bg-amber-50,
    .recruitment-ui a.bg-amber-100,
    .recruitment-ui button.bg-amber-50,
    .recruitment-ui button.bg-amber-100 {
        background: #fef3c7 !important;
        border: 1px solid #f59e0b !important;
        color: #92400e !important;
    }

    .recruitment-ui a.bg-emerald-50,
    .recruitment-ui button.bg-emerald-50 {
        background: #d1fae5 !important;
        border: 1px solid #10b981 !important;
        color: #065f46 !important;
    }

    .recruitment-ui a.bg-red-50,
    .recruitment-ui a.bg-rose-50,
    .recruitment-ui button.bg-red-50,
    .recruitment-ui button.bg-rose-50 {
        background: #ffe4e6 !important;
        border: 1px solid #fb7185 !important;
        color: #9f1239 !important;
    }

    .recruitment-ui table {
        color: var(--rec-ink);
    }

    .recruitment-ui th {
        color: #334155 !important;
        background: #f1f5f9 !important;
    }

    .recruitment-ui .text-slate-400,
    .recruitment-ui .text-slate-500 {
        color: #475569 !important;
    }

    .recruitment-ui .text-slate-600,
    .recruitment-ui .text-slate-700 {
        color: #334155 !important;
    }

    .recruitment-ui .text-cyan-700,
    .recruitment-ui .text-sky-700 {
        color: #0e7490 !important;
    }

    .recruitment-ui .text-amber-700,
    .recruitment-ui .text-orange-700 {
        color: #92400e !important;
    }

    .recruitment-ui .text-emerald-700,
    .recruitment-ui .text-emerald-800 {
        color: #065f46 !important;
    }

    .recruitment-ui .text-rose-700,
    .recruitment-ui .text-red-700 {
        color: #9f1239 !important;
    }

    .recruitment-ui .text-indigo-700,
    .recruitment-ui .text-indigo-800 {
        color: #3730a3 !important;
    }

    .recruitment-ui td {
        color: var(--rec-ink);
    }

    .recruitment-ui input,
    .recruitment-ui select,
    .recruitment-ui textarea {
        background: #fff !important;
        border-color: #cbd5e1 !important;
        color: #0f172a !important;
    }

    .recruitment-ui input::placeholder,
    .recruitment-ui textarea::placeholder {
        color: #64748b !important;
    }

    .recruitment-dashboard .recruitment-dashboard-hero {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 45%, #ecfeff 100%) !important;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 20px 50px -35px rgba(15, 23, 42, 0.35) !important;
    }

    .recruitment-dashboard a.bg-slate-900 {
        background: #0f172a !important;
        border: 1px solid #1e293b !important;
        color: #fff !important;
        box-shadow: none !important;
    }

    .recruitment-dashboard a.bg-slate-900:hover {
        background: #1e293b !important;
        color: #fff !important;
    }
</style>
