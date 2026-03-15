import { initSoundControls } from "./sound.js";

const LEVEL_LOW = 15;
const LEVEL_HIGH = 85;
const SESSION_DURATION = 180;

let activeTimer = null;
let sessionTimer = null;

document.addEventListener("turbo:before-render", () => {
    if (activeTimer) {
        clearInterval(activeTimer);
        activeTimer = null;
    }
    if (sessionTimer) {
        clearInterval(sessionTimer);
        sessionTimer = null;
    }
});

document.addEventListener("turbo:load", () => {
    const container = document.getElementById("breathing-container");
    const water = document.getElementById("water");
    const phaseLabel = document.getElementById("phase-label");
    const phaseCountdown = document.getElementById("phase-countdown");
    const btnStart = document.getElementById("btn-start");
    const btnStop = document.getElementById("btn-stop");

    const sessionTimerEl = document.getElementById("session-timer");
    const sessionCountdown = document.getElementById("session-countdown");

    if (container && water && phaseLabel && phaseCountdown && btnStart && btnStop) {
        init(container, water, phaseLabel, phaseCountdown, btnStart, btnStop, sessionTimerEl, sessionCountdown);
    }
});

function formatTime(seconds) {
    const m = Math.floor(seconds / 60);
    const s = seconds % 60;
    return `${m}:${s.toString().padStart(2, "0")}`;
}

function init(container, water, phaseLabel, phaseCountdown, btnStart, btnStop, sessionTimerEl, sessionCountdown) {
    const INSPIRATION = parseInt(container.dataset.inspiration, 10);
    const APNEA = parseInt(container.dataset.apnea, 10);
    const EXPIRATION = parseInt(container.dataset.expiration, 10);

    const phases = [];
    phases.push({
        label: "Inspirez",
        duration: INSPIRATION,
        waterTo: LEVEL_HIGH,
    });
    if (APNEA > 0)
        phases.push({ label: "Retenez", duration: APNEA, waterTo: LEVEL_HIGH });
    phases.push({ label: "Expirez", duration: EXPIRATION, waterTo: LEVEL_LOW });

    let running = false;

    function stopSession() {
        running = false;
        clearInterval(activeTimer);
        activeTimer = null;
        clearInterval(sessionTimer);
        sessionTimer = null;
        btnStop.classList.add("hidden");
        btnStart.classList.remove("hidden");
        sessionTimerEl.style.display = "none";
        phaseLabel.textContent = "—";
        phaseCountdown.textContent = "—";
        water.style.transition = "height 0.6s ease";
        water.style.height = LEVEL_LOW + "%";
        sound.reset();
    }

    function startSessionTimer() {
        let remaining = SESSION_DURATION;
        sessionCountdown.textContent = formatTime(remaining);
        sessionTimerEl.style.display = "flex";

        sessionTimer = setInterval(() => {
            remaining--;
            sessionCountdown.textContent = formatTime(remaining);
            if (remaining <= 0) {
                stopSession();
            }
        }, 1000);
    }

    function setWater(percent, durationSec) {
        water.style.transition = `height ${durationSec}s ease-in-out`;
        water.style.height = percent + "%";
    }

    function runPhase(index, onDone) {
        const phase = phases[index];
        phaseLabel.textContent = phase.label;
        setWater(phase.waterTo, phase.duration);
        let remaining = phase.duration;
        phaseCountdown.textContent = remaining;
        activeTimer = setInterval(() => {
            remaining--;
            phaseCountdown.textContent = remaining;
            if (remaining <= 0) {
                clearInterval(activeTimer);
                activeTimer = null;
                onDone();
            }
        }, 1000);
    }

    function runCycle() {
        let i = 0;
        function next() {
            if (!running) return;
            if (i >= phases.length) i = 0;
            runPhase(i, () => {
                i++;
                next();
            });
        }
        next();
    }

    const sound = initSoundControls();

    btnStart.addEventListener("click", () => {
        running = true;
        btnStart.classList.add("hidden");
        btnStop.classList.remove("hidden");
        startSessionTimer();
        runCycle();
    });

    btnStop.addEventListener("click", () => {
        stopSession();
    });
}
