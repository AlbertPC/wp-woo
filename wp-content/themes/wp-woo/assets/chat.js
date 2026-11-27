(() => {
	const panel = document.getElementById('bf-chat');
	const voice = document.querySelector('.bf-chat-voice');
	const button = voice.querySelector('button');
	const label = voice.querySelector('.bf-chat-voice__label');
	const bubble = voice.querySelector('.bf-chat-voice__bubble');
	const audio = voice.querySelector('audio');
	const context = voice.querySelector('canvas').getContext('2d');
	const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
	const startLabel = label.textContent.trim();
	const frame = panel.querySelector('iframe');

	let audioContext;
	let analyser;
	let samples;
	let playbackAttempt = 0;
	let greetingTimer;
	let requested = false;
	let speaking = false;
	let animationFrame;
	let lastDraw = -Infinity;
	let level = 0;

	const waves = [
		{ color: '#b7a1dd', amplitude: 13, phase: 1.8, width: 2.5 },
		{ color: '#a290cd', amplitude: 24, phase: 0, width: 2.5 },
		{ color: '#8b79bc', amplitude: 18, phase: 2.7, width: 3 },
		{ color: '#71589f', amplitude: 20, phase: .8, width: 3.5 },
	];

	function draw(now) {
		const still = reduceMotion.matches;
		if (!still) {
			animationFrame = requestAnimationFrame(draw);
			if (now - lastDraw < (speaking ? 15 : 32)) return;
		}
		lastDraw = now;

		let volume = 0;
		if (!still && speaking && analyser) {
			analyser.getFloatTimeDomainData(samples);
			let sum = 0;
			for (const sample of samples) sum += sample * sample;
			volume = Math.min(1, Math.sqrt(sum / samples.length) * 5);
		}
		level = still ? 0 : level + (volume - level) * (volume > level ? .38 : .19);
		const time = still ? 0 : now / 1600;

		context.clearRect(0, 0, 256, 256);
		context.lineCap = 'round';
		for (const wave of waves) {
			context.beginPath();
			const amplitude = wave.amplitude + Math.sin(time + wave.phase) * 1.5 + level * 39;
			for (let x = 32; x <= 224; x += 2) {
				const progress = (x - 32) / 192;
				const envelope = Math.sin(Math.PI * progress) ** 2;
				const phase = progress * Math.PI * 4.5 + wave.phase;
				const y = 128 + Math.sin(phase) * envelope * amplitude;
				if (x === 32) context.moveTo(x, y);
				else context.lineTo(x, y);
			}
			context.lineWidth = wave.width;
			context.strokeStyle = wave.color;
			context.stroke();
		}
	}

	if (context) {
		draw(performance.now());
		reduceMotion.addEventListener('change', () => {
			cancelAnimationFrame(animationFrame);
			lastDraw = -Infinity;
			draw(performance.now());
		});
	}

	function prepareAnalyser() {
		if (reduceMotion.matches || !context || !window.AudioContext) return;
		if (!audioContext) {
			audioContext = new AudioContext();
			analyser = audioContext.createAnalyser();
			analyser.fftSize = 1024;
			samples = new Float32Array(analyser.fftSize);
			audioContext.createMediaElementSource(audio).connect(analyser);
			analyser.connect(audioContext.destination);
		}
		audioContext.resume().catch(() => {});
	}

	function showGreeting() {
		if (bubble.textContent !== audio.dataset.transcript) {
			bubble.textContent = audio.dataset.transcript;
		}
	}

	function stop(linger = 0) {
		playbackAttempt++;
		requested = false;
		speaking = false;
		voice.classList.remove('is-speaking');
		audio.pause();
		if (audio.readyState) audio.currentTime = 0;
		label.textContent = startLabel;
		clearTimeout(greetingTimer);
		if (linger) greetingTimer = setTimeout(() => { bubble.textContent = ''; }, linger);
		else bubble.textContent = '';
	}

	button.addEventListener('click', () => {
		if (requested) {
			stop();
			return;
		}

		clearTimeout(greetingTimer);
		const attempt = ++playbackAttempt;
		requested = true;
		label.textContent = button.dataset.stopLabel;
		prepareAnalyser();
		audio.play().catch(() => {
			if (attempt !== playbackAttempt) return;
			showGreeting();
			stop(6000);
		});
	});

	audio.addEventListener('playing', () => {
		if (!requested) return;
		speaking = true;
		voice.classList.add('is-speaking');
		showGreeting();
	});

	audio.addEventListener('ended', () => stop(2000));
	window.addEventListener('pagehide', () => stop());

	panel.addEventListener('toggle', (event) => {
		if (event.newState === 'open') {
			stop();
			if (!frame.hasAttribute('src')) frame.src = frame.dataset.src;
		}
	});

	voice.hidden = false;
})();
