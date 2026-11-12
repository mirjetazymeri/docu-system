<style>
    .testing-ui {
        max-width: 1320px;
        margin: 2rem auto;
        padding: clamp(1.25rem, 3vw, 2.5rem);
        color: #253044;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 22px;
        box-shadow: 0 18px 50px rgba(30, 41, 59, .09);
    }
    .testing-ui__header,
    .testing-ui__toolbar,
    .testing-section__header,
    .testing-detail__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 1rem;
    }
    .testing-ui__header { align-items: flex-start; margin-bottom: 1.75rem; }
    .testing-ui__eyebrow {
        display: block;
        margin-bottom: .35rem;
        color: #4f78c4;
        font-size: .75rem;
        font-weight: 800;
        letter-spacing: .12em;
        text-transform: uppercase;
    }
    .testing-ui__title {
        margin: 0;
        color: #25344d;
        font-size: clamp(1.8rem, 4vw, 2.65rem);
        font-weight: 800;
        line-height: 1.15;
    }
    .testing-ui__subtitle { margin: .55rem 0 0; color: #6b778c; }
    .testing-ui__toolbar { margin-bottom: 1.25rem; }
    .testing-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .45rem;
        min-height: 40px;
        padding: .58rem .95rem;
        color: #fff;
        background: #4f78c4;
        border: 1px solid transparent;
        border-radius: 9px;
        font-size: .87rem;
        font-weight: 700;
        cursor: pointer;
    }
    .testing-btn:hover { color: #fff; background: #4269b0; transform: translateY(-1px); }
    .testing-btn--navy { background: #25344d; }
    .testing-btn--navy:hover { background: #172033; }
    .testing-btn--danger { background: #b83a32; }
    .testing-btn--danger:hover { background: #982c26; }
    .testing-btn--mail { background: #4f78c4; }
    .testing-btn--mail:hover { background: #3f67b0; }
    .testing-btn--disabled,
    .testing-btn--disabled:hover {
        color: #8a96a8;
        background: #e8edf3;
        border-color: #d8e0e9;
        cursor: not-allowed;
        transform: none;
    }
    .testing-btn--outline {
        color: #334155;
        background: #fff;
        border-color: #cbd5e1;
    }
    .testing-btn--outline:hover { color: #315b9f; background: #edf3fc; }
    .testing-btn--small { min-height: 35px; padding: .45rem .7rem; font-size: .8rem; }
    .testing-btn__count { display:inline-flex;min-width:20px;height:20px;padding:0 6px;color:#fff;background:#b83a32;border-radius:999px;font-size:.7rem;align-items:center;justify-content:center; }
    .testing-stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(130px, 1fr));
        gap: .8rem;
        margin-bottom: 1.4rem;
    }
    .testing-stat {
        padding: 1rem;
        background: #f7f9fc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
    }
    .testing-stat span { display: block; color: #718096; font-size: .78rem; }
    .testing-stat strong { color: #25344d; font-size: 1.45rem; }
    .testing-table-wrap { overflow-x: auto; border: 1px solid #e2e8f0; border-radius: 14px; }
    .testing-table { width: 100%; margin: 0; border-collapse: collapse; }
    .testing-table th {
        padding: 13px 15px;
        color: #475569;
        background: #edf2f8;
        border-bottom: 1px solid #d7e0ec;
        font-size: .78rem;
        letter-spacing: .04em;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }
    .testing-table td { padding: 14px 15px; border-bottom: 1px solid #edf1f5; vertical-align: middle; }
    .testing-table tr:last-child td { border-bottom: 0; }
    .testing-table tbody tr:hover { background: #f9fbfd; }
    .testing-actions { display: flex; align-items: center; flex-wrap: wrap; gap: .4rem; }
    .testing-actions form { margin: 0; }
    .testing-badge {
        display: inline-flex;
        padding: .25rem .55rem;
        border-radius: 999px;
        font-size: .72rem;
        font-weight: 700;
    }
    .testing-badge--passed { color: #18715a; background: #e8f7f1; }
    .testing-badge--failed { color: #9a312b; background: #fdeeed; }
    .testing-badge--progress { color: #895f16; background: #fff6dd; }
    .testing-completion-form { margin: 0; }
    .testing-completion {
        display: inline-flex;
        padding: .42rem .65rem;
        color: #6b7789;
        background: #fff;
        border: 1px solid #cfd8e4;
        border-radius: 8px;
        font-size: .72rem;
        font-weight: 750;
        cursor: pointer;
        align-items: center;
        gap: .4rem;
        white-space: nowrap;
    }
    .testing-completion:hover { color: #315b9f; background: #f1f5fb; }
    .testing-completion--checked { color: #17644f; background: #e5f5ef; border-color: #b9dfd1; }
    .testing-empty {
        padding: 3rem 1rem;
        color: #718096;
        text-align: center;
        background: #f8fafc;
        border: 1px dashed #cbd5e1;
        border-radius: 14px;
    }
    .testing-alert {
        margin-bottom: 1.2rem;
        padding: .9rem 1rem;
        border-radius: 10px;
    }
    .testing-alert--success { color: #176b51; background: #edf9f4; border: 1px solid #bce4d5; }
    .testing-alert--danger { color: #8c2e2a; background: #fff1f0; border: 1px solid #efc1bd; }
    .testing-alert--warning { color: #805b16; background: #fff8e7; border: 1px solid #efdca9; }
    .testing-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }
    .testing-field label { display: block; margin-bottom: .4rem; color: #334155; font-weight: 700; }
    .testing-field .form-control {
        min-height: 45px;
        border: 1px solid #d7e0eb;
        border-radius: 9px;
        box-shadow: none;
    }
    .testing-field .form-control:focus { border-color: #7193cf; box-shadow: 0 0 0 3px rgba(79,120,196,.13); }
    .testing-field__error { display: block; margin-top: .35rem; color: #b3312a; font-size: .8rem; }
    .testing-section { margin-top: 1.6rem; padding-top: 1.5rem; border-top: 1px solid #e5eaf1; }
    .testing-section__header { margin-bottom: 1rem; }
    .testing-section__header h2 { margin: 0; color: #25344d; font-size: 1.25rem; font-weight: 800; }
    .testing-detail {
        margin-bottom: 1rem;
        padding: 1.1rem;
        background: #f8fafc;
        border: 1px solid #dfe6ef;
        border-radius: 13px;
    }
    .testing-detail__header { margin-bottom: .9rem; }
    .testing-detail__number { color: #4f78c4; font-weight: 800; }
    .testing-detail__grid {
        display: grid;
        grid-template-columns: 1fr 1.2fr 2fr 1fr;
        gap: .85rem;
    }
    .testing-detail__comment { grid-column: 1 / -1; }
    .testing-add-detail {
        display: flex;
        justify-content: flex-end;
        margin-top: .25rem;
    }
    .testing-form-actions { display: flex; justify-content: space-between; flex-wrap: wrap; gap: .75rem; margin-top: 1.5rem; }
    @media (max-width: 900px) {
        .testing-stats, .testing-detail__grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 600px) {
        .testing-ui { margin: 1rem .5rem; border-radius: 16px; }
        .testing-form-grid, .testing-stats, .testing-detail__grid { grid-template-columns: 1fr; }
        .testing-detail__comment { grid-column: auto; }
        .testing-add-detail .testing-btn { width: 100%; }
        .testing-form-actions .testing-btn { width: 100%; }
    }
</style>
