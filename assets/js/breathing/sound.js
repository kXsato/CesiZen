let audioCtx = null;
let gainNode = null;
let noiseSource = null;
let soundActive = false;

function buildPinkNoiseBuffer(ctx) {
    const sampleRate = ctx.sampleRate;
    const frameCount = sampleRate * 10;
    const buffer = ctx.createBuffer(1, frameCount, sampleRate);
    const data   = buffer.getChannelData(0);
    let b0 = 0, b1 = 0, b2 = 0, b3 = 0, b4 = 0, b5 = 0, b6 = 0;
    for (let i = 0; i < frameCount; i++) {
        const w = Math.random() * 2 - 1;
        b0 = 0.99886 * b0 + w * 0.0555179;
        b1 = 0.99332 * b1 + w * 0.0750759;
        b2 = 0.96900 * b2 + w * 0.1538520;
        b3 = 0.86650 * b3 + w * 0.3104856;
        b4 = 0.55000 * b4 + w * 0.5329522;
        b5 = -0.7616 * b5 - w * 0.0168980;
        data[i] = (b0 + b1 + b2 + b3 + b4 + b5 + b6 + w * 0.5362) * 0.11;
        b6 = w * 0.115926;
    }
    return buffer;
}

export function startSound(volume) {
    if (!audioCtx) {
        audioCtx = new AudioContext();

        const filter = audioCtx.createBiquadFilter();
        filter.type = 'lowpass';
        filter.frequency.value = 400;
        filter.Q.value = 0.5;

        gainNode = audioCtx.createGain();
        filter.connect(gainNode);
        gainNode.connect(audioCtx.destination);
        audioCtx._filter = filter;
    }

    if (audioCtx.state === 'suspended') audioCtx.resume();
    gainNode.gain.value = volume;

    noiseSource = audioCtx.createBufferSource();
    noiseSource.buffer = buildPinkNoiseBuffer(audioCtx);
    noiseSource.loop = true;
    noiseSource.connect(audioCtx._filter);
    noiseSource.start();
}

export function stopSound() {
    if (noiseSource) {
        noiseSource.stop();
        noiseSource = null;
    }
}

export function setVolume(value) {
    if (gainNode) gainNode.gain.value = value;
}

export function initSoundControls() {
    const btnSound     = document.getElementById('btn-sound');
    const iconOn       = document.getElementById('icon-sound-on');
    const iconOff      = document.getElementById('icon-sound-off');
    const soundLabel   = document.getElementById('sound-label');
    const volumeSlider = document.getElementById('volume-slider');

    if (!btnSound || !iconOn || !iconOff || !soundLabel || !volumeSlider) {
        return { reset() {} };
    }

    btnSound.addEventListener('click', () => {
        soundActive = !soundActive;
        if (soundActive) {
            startSound(volumeSlider.value / 100);
            iconOn.classList.remove('hidden');
            iconOff.classList.add('hidden');
            soundLabel.textContent = 'Son activé';
        } else {
            stopSound();
            iconOn.classList.add('hidden');
            iconOff.classList.remove('hidden');
            soundLabel.textContent = 'Son';
        }
    });

    volumeSlider.addEventListener('input', () => setVolume(volumeSlider.value / 100));

    return {
        reset() {
            stopSound();
            soundActive = false;
            iconOn.classList.add('hidden');
            iconOff.classList.remove('hidden');
            soundLabel.textContent = 'Son';
        }
    };
}
