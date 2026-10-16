(function () {
    'use strict';

    const STATE = { IDLE: 'IDLE', SCANNING: 'SCANNING', PROCESSING: 'PROCESSING', SUCCESS: 'SUCCESS', ERROR: 'ERROR', RESETTING: 'RESETTING' };
    let currentState = STATE.IDLE;
    let html5QrCode = null;
    let cameraId = null;
    let soundOn = true;
    let logoClickCount = 0;
    let logoClickTimer = null;
    const lastScans = [];
    const MAX_LAST_SCANS = 10;
    const RESULT_DISPLAY_MS = 2000;
    const COOLDOWN_MS = 700;
    let lastScanFinishedAt = 0;

    const el = (id) => document.getElementById(id);

    function setState(next) {
        currentState = next;
    }

    // ---------------------------------------------------------------
    // Audio (Web Audio API synthesized beeps - no external mp3 needed)
    // ---------------------------------------------------------------
    let audioCtx = null;
    function unlockAudio() {
        try {
            audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            const buffer = audioCtx.createBuffer(1, 1, 22050);
            const src = audioCtx.createBufferSource();
            src.buffer = buffer;
            src.connect(audioCtx.destination);
            src.start(0);
        } catch (e) { /* ignore */ }
    }

    function playSound(type) {
        if (!soundOn || !audioCtx) return;
        const patterns = {
            success: [[880, 0.12]],
            late: [[660, 0.1], [440, 0.15]],
            error: [[220, 0.25]],
        };
        const seq = patterns[type] || patterns.success;
        let t = audioCtx.currentTime;
        seq.forEach(([freq, dur]) => {
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            osc.type = 'sine';
            osc.frequency.value = freq;
            gain.gain.setValueAtTime(0.25, t);
            gain.gain.exponentialRampToValueAtTime(0.001, t + dur);
            osc.connect(gain).connect(audioCtx.destination);
            osc.start(t);
            osc.stop(t + dur);
            t += dur;
        });
    }

    // ---------------------------------------------------------------
    // Scanner lifecycle
    // ---------------------------------------------------------------
    async function initScanner() {
        try {
            const cameras = await Html5Qrcode.getCameras();
            const select = el('camSelect');
            select.innerHTML = '';
            cameras.forEach((cam) => {
                const opt = document.createElement('option');
                opt.value = cam.id;
                opt.textContent = cam.label || 'Kamera';
                select.appendChild(opt);
            });

            const backCam = cameras.find((c) => /back|belakang|rear/i.test(c.label));
            cameraId = backCam ? backCam.id : (cameras[0] ? cameras[0].id : null);
            if (cameraId) select.value = cameraId;

            select.addEventListener('change', () => {
                cameraId = select.value;
                stopScanner().then(startScanner);
            });

            if (!cameraId) {
                showCameraError();
                return;
            }

            await startScanner();
        } catch (e) {
            showCameraError();
        }
    }

    function showCameraError() {
        el('scanHint').textContent = 'Kamera tidak tersedia.';
        const hint = el('scanHint');
        const btn = document.createElement('button');
        btn.className = 'btn btn-sm btn-outline';
        btn.style.marginTop = '10px';
        btn.textContent = 'COBA LAGI';
        btn.onclick = () => location.reload();
        hint.after(btn);
    }

    async function startScanner() {
        if (!cameraId) return;
        html5QrCode = new Html5Qrcode('reader');
        try {
            await html5QrCode.start(
                cameraId,
                { fps: 10, qrbox: { width: 260, height: 260 } },
                handleScan,
                () => {}
            );
            setState(STATE.SCANNING);
        } catch (e) {
            showCameraError();
        }
    }

    async function stopScanner() {
        if (html5QrCode) {
            try { await html5QrCode.stop(); } catch (e) { /* already stopped */ }
            try { html5QrCode.clear(); } catch (e) { /* ignore */ }
        }
    }

    function handleScan(decodedText) {
        const now = Date.now();
        if (currentState !== STATE.SCANNING) return;
        if (now - lastScanFinishedAt < COOLDOWN_MS) return;

        setState(STATE.PROCESSING);
        sendAttendance(decodedText);
    }

    async function sendAttendance(token) {
        try {
            const res = await fetch('/api/attendance/scan', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': window.CSRF_TOKEN || '' },
                body: JSON.stringify({ token: token }),
            });
            const data = await res.json();

            if (data.success) {
                if (data.status === 'late') {
                    showLate(data);
                } else {
                    showSuccess(data);
                }
                updateLastScans(data);
                updateStatistics();
            } else {
                showError(data);
            }
        } catch (e) {
            showError({ message: 'Tidak dapat terhubung ke server.' });
        }
    }

    function showSuccess(data) {
        setState(STATE.SUCCESS);
        playSound('success');
        renderResult('success', '✓ PRESENSI BERHASIL', data);
        scheduleReset();
    }

    function showLate(data) {
        setState(STATE.SUCCESS);
        playSound('late');
        renderResult('late', '⚠ TERLAMBAT', data);
        scheduleReset();
    }

    function showError(data) {
        setState(STATE.ERROR);
        playSound('error');
        const overlay = el('resultOverlay');
        const card = el('resultCard');
        card.innerHTML = `
            <div class="title error">✕ ${escapeHtml(errorTitle(data.status))}</div>
            <div class="meta">${escapeHtml(data.message || 'Terjadi kesalahan')}</div>
        `;
        overlay.style.display = 'flex';
        scheduleReset();
    }

    function errorTitle(status) {
        const map = {
            invalid_qr: 'QR TIDAK VALID',
            already_attended: 'SUDAH PRESENSI',
            already_completed: 'PRESENSI SELESAI',
            window_closed: 'WAKTU PRESENSI BERAKHIR',
            not_school_day: 'BUKAN HARI SEKOLAH',
            inactive: 'STATUS TIDAK AKTIF',
            rate_limited: 'TERLALU CEPAT',
        };
        return map[status] || 'GAGAL';
    }

    function renderResult(type, title, data) {
        const overlay = el('resultOverlay');
        const card = el('resultCard');
        const person = data.person || {};
        const attendance = data.attendance || {};
        const photo = person.photo || `https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(person.name || '?')}`;
        card.innerHTML = `
            <img class="photo" src="${escapeHtml(photo)}" onerror="this.src='https://api.dicebear.com/7.x/initials/svg?seed=${encodeURIComponent(person.name || '?')}'">
            <div class="title ${type}">${title}</div>
            <div class="name">${escapeHtml(person.name || '-')}</div>
            <div class="meta">${escapeHtml(person.nis ? 'NIS ' + person.nis : (person.nip ? 'NIP ' + person.nip : ''))} ${escapeHtml(person.class || person.position || '')}</div>
            <div class="meta">${escapeHtml(attendance.time || '')} &middot; ${escapeHtml((attendance.type === 'out' ? 'PULANG' : 'MASUK'))}</div>
        `;
        overlay.style.display = 'flex';
    }

    function scheduleReset() {
        setTimeout(resetScanner, RESULT_DISPLAY_MS);
    }

    function resetScanner() {
        setState(STATE.RESETTING);
        el('resultOverlay').style.display = 'none';
        lastScanFinishedAt = Date.now();
        setState(STATE.SCANNING);
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = String(str == null ? '' : str);
        return div.innerHTML;
    }

    // ---------------------------------------------------------------
    // Stats + last scans
    // ---------------------------------------------------------------
    async function updateStatistics() {
        try {
            const res = await fetch('/api/kiosk/stats');
            const json = await res.json();
            if (json.success) {
                el('statHadir').textContent = json.data.hadir;
                el('statTerlambat').textContent = json.data.terlambat;
                el('statPulang').textContent = json.data.sudah_pulang;
                el('statBelum').textContent = json.data.belum_hadir;
            }
        } catch (e) { /* silent */ }
    }

    function updateLastScans(data) {
        const person = data.person || {};
        const attendance = data.attendance || {};
        lastScans.unshift({
            name: person.name,
            sub: person.class || person.position || '',
            time: attendance.time,
            status: data.status,
        });
        if (lastScans.length > MAX_LAST_SCANS) lastScans.pop();
        renderLastScans();
    }

    function renderLastScans() {
        const container = el('lastScans');
        container.innerHTML = lastScans.map((s) => `
            <div class="last-scan-item ${s.status === 'late' ? 'late' : 'present'}">
                <div>
                    <div style="font-weight:700;">${escapeHtml(s.name)}</div>
                    <div class="text-muted" style="font-size:11px;">${escapeHtml(s.sub)}</div>
                </div>
                <div style="text-align:right;">
                    <div>${escapeHtml(s.time)}</div>
                    <div style="font-size:11px; font-weight:700;">${s.status === 'late' ? 'TERLAMBAT' : 'HADIR'}</div>
                </div>
            </div>
        `).join('');
    }

    // ---------------------------------------------------------------
    // Health check (low frequency polling)
    // ---------------------------------------------------------------
    async function checkServerHealth() {
        const statusEl = el('serverStatus');
        const textEl = el('serverStatusText');
        try {
            const res = await fetch('/api/health', { cache: 'no-store' });
            const json = await res.json();
            if (json.success) {
                statusEl.classList.remove('offline');
                textEl.textContent = 'SERVER ONLINE';
                return;
            }
            throw new Error('offline');
        } catch (e) {
            statusEl.classList.add('offline');
            textEl.textContent = 'SERVER OFFLINE';
        }
    }

    // ---------------------------------------------------------------
    // Clock
    // ---------------------------------------------------------------
    function tickClock() {
        const now = new Date();
        el('clockTime').textContent = now.toLocaleTimeString('id-ID', { hour12: false });
        const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', "Jum'at", 'Sabtu'];
        const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        el('clockDate').textContent = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
    }

    // ---------------------------------------------------------------
    // Exit flow: click logo 5x then PIN
    // ---------------------------------------------------------------
    function setupExitFlow() {
        el('logoClick').addEventListener('click', () => {
            logoClickCount++;
            clearTimeout(logoClickTimer);
            logoClickTimer = setTimeout(() => { logoClickCount = 0; }, 2000);
            if (logoClickCount >= 5) {
                logoClickCount = 0;
                el('pinModal').style.display = 'flex';
                el('exitPin').value = '';
                el('pinError').style.display = 'none';
                el('exitPin').focus();
            }
        });

        el('pinCancel').addEventListener('click', () => {
            el('pinModal').style.display = 'none';
        });

        el('pinSubmit').addEventListener('click', async () => {
            const pin = el('exitPin').value;
            try {
                const res = await fetch('/kiosk/verify-exit-pin', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ pin: pin, _csrf_token: window.CSRF_TOKEN }),
                });
                const json = await res.json();
                if (json.success) {
                    window.location.href = '/kiosk/logout';
                } else {
                    el('pinError').style.display = 'block';
                }
            } catch (e) {
                el('pinError').style.display = 'block';
            }
        });
    }

    // ---------------------------------------------------------------
    // Boot
    // ---------------------------------------------------------------
    function startKiosk() {
        el('startScreen').style.display = 'none';
        el('kioskScreen').style.display = 'flex';

        unlockAudio();
        setupExitFlow();

        if (document.documentElement.requestFullscreen) {
            document.documentElement.requestFullscreen().catch(() => {});
        }

        tickClock();
        setInterval(tickClock, 1000);

        checkServerHealth();
        setInterval(checkServerHealth, 30000);

        updateStatistics();
        setInterval(updateStatistics, 20000);

        initScanner();
    }

    document.addEventListener('DOMContentLoaded', () => {
        el('btnStartKiosk').addEventListener('click', startKiosk, { once: true });
    });
})();
