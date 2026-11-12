<style>
    .doc-ui {
        --doc-primary: #25344d;
        --doc-primary-hover: #172033;
        --doc-accent: #4f78c4;
        --doc-blue: #667fac;
        --doc-danger: #a22b20;
        --doc-text: #253044;
        --doc-muted: #6b778c;
        --doc-border: #e2e8f0;
        --doc-surface: #ffffff;
        max-width: 1280px;
        margin: 2rem auto;
        padding: clamp(1.25rem, 3vw, 2.5rem);
        color: var(--doc-text);
        background: linear-gradient(145deg, #fff 0%, #fbfdfc 100%);
        border: 1px solid rgba(18, 60, 53, .07);
        border-radius: 24px;
        box-shadow: 0 18px 55px rgba(20, 45, 40, .09);
    }

    .doc-ui__header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .doc-ui__eyebrow {
        display: block;
        margin-bottom: .4rem;
        color: var(--doc-accent);
        font-size: .75rem;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .doc-ui__title {
        margin: 0;
        color: var(--doc-primary);
        font-size: clamp(1.8rem, 4vw, 2.7rem);
        font-weight: 800;
        line-height: 1.15;
    }

    .doc-ui__subtitle {
        margin: .55rem 0 0;
        color: var(--doc-muted);
        font-size: 1rem;
    }

    .doc-ui__toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: .8rem;
        margin-bottom: 1.5rem;
    }

    .doc-ui__filters {
        display: grid;
        grid-template-columns: minmax(240px, 2fr) repeat(2, minmax(170px, 1fr));
        gap: .8rem;
        width: 100%;
        margin-bottom: 1.25rem;
        padding: 1rem;
        background: #f7f9fc;
        border: 1px solid var(--doc-border);
        border-radius: 16px;
    }

    .doc-ui .form-control {
        min-height: 46px;
        border: 1px solid #d7e2de;
        border-radius: 10px;
        box-shadow: none;
    }

    .doc-ui .form-control:focus {
        border-color: var(--doc-accent);
        box-shadow: 0 0 0 3px rgba(79, 120, 196, .14);
    }

    .doc-ui__grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(285px, 1fr));
        gap: 1.25rem;
    }

    .doc-card {
        display: flex;
        flex-direction: column;
        min-height: 230px;
        padding: 1.4rem;
        background: var(--doc-surface);
        border: 1px solid var(--doc-border);
        border-radius: 18px;
        box-shadow: 0 8px 24px rgba(26, 58, 51, .07);
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    }

    .doc-card:hover {
        transform: translateY(-4px);
        border-color: rgba(37, 169, 154, .35);
        box-shadow: 0 16px 34px rgba(26, 58, 51, .12);
    }

    .doc-card__top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-bottom: 1rem;
    }

    .doc-card__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        color: var(--doc-primary);
        background: #edf3fc;
        border-radius: 12px;
        font-size: 1.1rem;
    }

    .doc-card__id {
        padding: .3rem .65rem;
        color: var(--doc-muted);
        background: #f3f6f5;
        border-radius: 999px;
        font-size: .76rem;
        font-weight: 700;
    }

    .doc-card__title {
        margin: 0 0 .5rem;
        color: var(--doc-text);
        font-size: 1.15rem;
        font-weight: 800;
        overflow-wrap: anywhere;
    }

    .doc-card__meta {
        margin: 0 0 .4rem;
        color: var(--doc-muted);
        font-size: .9rem;
    }

    .doc-card__actions {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: .5rem;
        margin-top: auto;
        padding-top: 1.25rem;
        border-top: 1px solid #edf2f0;
    }

    .doc-card__actions form { margin: 0; }

    .app-card__pdf {
        display: flex;
        margin-top: 1rem;
    }

    .app-card__pdf .doc-btn {
        width: 100%;
        color: var(--doc-primary);
        background: #f3f6fb;
        border-color: #ccd7e8;
    }

    .app-card__pdf .doc-btn:hover {
        color: var(--doc-primary);
        background: #e8eef8;
        border-color: #aebfda;
    }

    .app-card__footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: .75rem;
        margin-top: auto;
        padding-top: 1rem;
        border-top: 1px solid #edf2f0;
    }

    .app-card__navigation,
    .app-card__management {
        display: flex;
        align-items: center;
        gap: .5rem;
    }

    .app-card__management {
        flex-shrink: 0;
        padding-left: .75rem;
        border-left: 1px solid var(--doc-border);
    }

    .app-card__management form { margin: 0; }
    .app-card__management .doc-btn {
        width: 40px;
        padding-right: 0;
        padding-left: 0;
    }

    .doc-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        min-height: 40px;
        padding: .55rem .9rem;
        border: 1px solid transparent;
        border-radius: 10px;
        font-size: .86rem;
        font-weight: 700;
        line-height: 1;
        cursor: pointer;
        transition: transform .15s ease, background-color .15s ease, box-shadow .15s ease;
    }

    .doc-btn:hover {
        color: #fff;
        text-decoration: none;
        transform: translateY(-1px);
    }

    .doc-btn--primary { color: #fff; background: var(--doc-primary); }
    .doc-btn--primary:hover { background: var(--doc-primary-hover); }
    .doc-btn--accent { color: #fff; background: var(--doc-accent); }
    .doc-btn--accent:hover { background: #4269b0; }
    .doc-btn--blue { color: #fff; background: var(--doc-blue); }
    .doc-btn--blue:hover { background: #426794; }
    .doc-btn--danger { color: #fff; background: var(--doc-danger); }
    .doc-btn--danger:hover { background: #842117; }
    .doc-btn--outline {
        color: var(--doc-primary);
        background: #fff;
        border-color: #cbd9d5;
    }
    .doc-btn--outline:hover { color: var(--doc-primary); background: #edf3fc; }

    .doc-ui__empty {
        padding: 3rem 1rem;
        color: var(--doc-muted);
        text-align: center;
        background: #f8fbfa;
        border: 1px dashed #cad9d4;
        border-radius: 16px;
    }

    .doc-alert {
        display: flex;
        align-items: flex-start;
        gap: .85rem;
        margin-bottom: 1.25rem;
        padding: 1rem 1.1rem;
        border: 1px solid;
        border-radius: 13px;
    }

    .doc-alert > i {
        margin-top: .15rem;
        font-size: 1.2rem;
    }

    .doc-alert strong { display: block; margin-bottom: .2rem; }
    .doc-alert p { margin: 0; }
    .doc-alert ul { margin: .4rem 0 .3rem 1.2rem; padding: 0; }
    .doc-alert small { color: inherit; opacity: .75; }
    .doc-alert__label { display: block; margin-top: .65rem; font-weight: 700; }

    .doc-alert--success {
        color: #176b51;
        background: #edf9f4;
        border-color: #bce4d5;
    }

    .doc-alert--warning {
        color: #895b12;
        background: #fff8e7;
        border-color: #efd79e;
    }

    .doc-alert--danger {
        color: #8c2e2a;
        background: #fff1f0;
        border-color: #efc1bd;
    }

    .doc-alert--detailed {
        box-shadow: 0 8px 20px rgba(140, 46, 42, .07);
    }

    @media (max-width: 767px) {
        .doc-ui { margin: 1rem .5rem; border-radius: 18px; }
        .doc-ui__header { flex-direction: column-reverse; }
        .doc-ui__filters { grid-template-columns: 1fr; }
        .doc-ui__toolbar { align-items: stretch; flex-direction: column; }
        .doc-ui__toolbar .doc-btn { width: 100%; }
        .doc-card { min-height: 0; }
        .doc-card__actions .doc-btn,
        .doc-card__actions form { flex: 1 1 auto; }
        .doc-card__actions form .doc-btn { width: 100%; }
        .app-card__footer {
            align-items: stretch;
            flex-direction: column;
        }
        .app-card__navigation .doc-btn { flex: 1; }
        .app-card__management {
            justify-content: flex-end;
            padding-top: .75rem;
            padding-left: 0;
            border-top: 1px solid var(--doc-border);
            border-left: 0;
        }
    }
</style>
