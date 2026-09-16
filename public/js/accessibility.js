/**
 * VT VALETEC - Accessibility Manager Simplificado para Adultos Mayores
 * Diseñado a medida para la identidad de CENGFARMA
 * 100% Nativo Vanilla ES6+ - Cero dependencias
 */

class AccessibilityManager {
    constructor() {
        this.storageKey = 'cengfarma_a11y_prefs';
        this.speechSynthesis = window.speechSynthesis || null;
        this.speechVoice = null;

        // Estado simplificado (4 herramientas principales)
        this.state = {
            fontScale: 0,            // 0: Normal, 1: Grande (+15%), 2: Muy Grande (+30%)
            contrast: false,         // Alto contraste CENGFARMA (Azul Noche + Verde Esmeralda)
            speechReader: false,     // Lector de voz al tocar textos
            highlightClickable: false// Cursor grande + marco en botones
        };

        this.init();
    }

    init() {
        this.loadPreferences();
        this.applySettings();

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.setupDOM());
        } else {
            this.setupDOM();
        }

        this.initSpeechSynthesis();
    }

    loadPreferences() {
        try {
            const saved = localStorage.getItem(this.storageKey);
            if (saved) {
                const parsed = JSON.parse(saved);
                this.state = { ...this.state, ...parsed };
            }
        } catch (e) {
            console.warn('[A11y] No se pudo leer localStorage:', e);
        }
    }

    savePreferences() {
        try {
            localStorage.setItem(this.storageKey, JSON.stringify(this.state));
        } catch (e) {
            console.warn('[A11y] Error guardando preferencias:', e);
        }
    }

    applySettings() {
        const root = document.documentElement;

        // 1. Escala de fuente
        root.classList.remove('a11y-font-scale-1', 'a11y-font-scale-2');
        if (this.state.fontScale > 0) {
            root.classList.add(`a11y-font-scale-${this.state.fontScale}`);
        }

        // 2. Contraste CENGFARMA
        root.classList.toggle('a11y-contrast-cengfarma', Boolean(this.state.contrast));

        // 3. Resaltar botones y cursor grande
        root.classList.toggle('a11y-highlight-clickable', Boolean(this.state.highlightClickable));

        // 4. Lector de voz activo
        root.classList.toggle('a11y-speech-active', Boolean(this.state.speechReader));

        // Sincronizar botones en la interfaz si ya cargó el DOM
        this.updateUI();
    }

    setupDOM() {
        this.triggerBtn = document.getElementById('a11yTriggerBtn');
        this.panel = document.getElementById('a11yPanel');
        this.backdrop = document.getElementById('a11yBackdrop');
        this.closeBtn = document.getElementById('a11yCloseBtn');
        this.resetBtn = document.getElementById('a11yResetBtn');
        this.announcer = document.getElementById('a11yAnnouncer');

        // Botones de control del stepper de fuente
        this.btnFontDecrease = document.getElementById('a11yFontDecrease');
        this.btnFontIncrease = document.getElementById('a11yFontIncrease');
        this.labelFontScale = document.getElementById('a11yFontLabel');

        // Botones de toggle de las tarjetas
        this.btnContrast = document.getElementById('a11yToggleContrast');
        this.cardContrast = document.getElementById('a11yCardContrast');

        this.btnSpeech = document.getElementById('a11yToggleSpeech');
        this.cardSpeech = document.getElementById('a11yCardSpeech');

        this.btnHighlight = document.getElementById('a11yToggleHighlight');
        this.cardHighlight = document.getElementById('a11yCardHighlight');

        if (!this.panel || !this.triggerBtn) return;

        // Apertura / Cierre
        this.triggerBtn.addEventListener('click', () => this.togglePanel());
        if (this.closeBtn) this.closeBtn.addEventListener('click', () => this.closePanel());
        if (this.backdrop) this.backdrop.addEventListener('click', () => this.closePanel());
        if (this.resetBtn) this.resetBtn.addEventListener('click', () => this.resetAll());

        // Cerrar con Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.panel.classList.contains('a11y-active')) {
                this.closePanel();
                this.triggerBtn.focus();
            }
        });

        // 1. Eventos Tamaño de Fuente
        if (this.btnFontDecrease) {
            this.btnFontDecrease.addEventListener('click', () => {
                if (this.state.fontScale > 0) {
                    this.state.fontScale--;
                    this.onStateChange('Tamaño de letra reducido');
                }
            });
        }
        if (this.btnFontIncrease) {
            this.btnFontIncrease.addEventListener('click', () => {
                if (this.state.fontScale < 2) {
                    this.state.fontScale++;
                    this.onStateChange('Tamaño de letra aumentado');
                }
            });
        }

        // 2. Evento Contraste CENGFARMA
        if (this.btnContrast) {
            this.btnContrast.addEventListener('click', () => {
                this.state.contrast = !this.state.contrast;
                this.onStateChange(this.state.contrast ? 'Contraste nítido CENGFARMA activado' : 'Contraste estándar restaurado');
            });
        }

        // 3. Evento Lector de Voz
        if (this.btnSpeech) {
            this.btnSpeech.addEventListener('click', () => {
                this.state.speechReader = !this.state.speechReader;
                if (!this.state.speechReader && this.speechSynthesis) {
                    this.speechSynthesis.cancel();
                }
                this.onStateChange(this.state.speechReader ? 'Lector de pantalla activado. Toca cualquier texto para escucharlo.' : 'Lector de pantalla desactivado');
                if (this.state.speechReader) {
                    this.speakText('Lector de pantalla activado');
                }
            });
        }

        // 4. Evento Resaltar Botones y Clics
        if (this.btnHighlight) {
            this.btnHighlight.addEventListener('click', () => {
                this.state.highlightClickable = !this.state.highlightClickable;
                this.onStateChange(this.state.highlightClickable ? 'Botones resaltados y cursor grande activado' : 'Resaltado desactivado');
            });
        }

        this.updateUI();
        this.setupSpeechListeners();
    }

    onStateChange(announcementText) {
        this.savePreferences();
        this.applySettings();
        this.announce(announcementText);
    }

    updateUI() {
        if (!this.panel) return;

        // 1. Stepper de fuente
        const fontLabels = ['100%', '115%', '130%'];
        if (this.labelFontScale) {
            this.labelFontScale.textContent = fontLabels[this.state.fontScale];
        }
        if (this.btnFontDecrease) {
            this.btnFontDecrease.disabled = (this.state.fontScale === 0);
            this.btnFontDecrease.style.opacity = (this.state.fontScale === 0) ? '0.4' : '1';
        }
        if (this.btnFontIncrease) {
            this.btnFontIncrease.disabled = (this.state.fontScale === 2);
            this.btnFontIncrease.style.opacity = (this.state.fontScale === 2) ? '0.4' : '1';
        }

        // 2. Contraste CENGFARMA
        if (this.btnContrast) {
            this.btnContrast.classList.toggle('a11y-btn-on', Boolean(this.state.contrast));
            this.btnContrast.textContent = this.state.contrast ? '✓ Activo' : 'Activar';
        }
        if (this.cardContrast) {
            this.cardContrast.classList.toggle('a11y-card-active-cengfarma', Boolean(this.state.contrast));
        }

        // 3. Lector de voz
        if (this.btnSpeech) {
            this.btnSpeech.classList.toggle('a11y-btn-on', Boolean(this.state.speechReader));
            this.btnSpeech.textContent = this.state.speechReader ? '✓ Escuchando' : 'Activar';
        }
        if (this.cardSpeech) {
            this.cardSpeech.classList.toggle('a11y-card-active-cengfarma', Boolean(this.state.speechReader));
        }

        // 4. Resaltar Clics
        if (this.btnHighlight) {
            this.btnHighlight.classList.toggle('a11y-btn-on', Boolean(this.state.highlightClickable));
            this.btnHighlight.textContent = this.state.highlightClickable ? '✓ Activo' : 'Activar';
        }
        if (this.cardHighlight) {
            this.cardHighlight.classList.toggle('a11y-card-active-cengfarma', Boolean(this.state.highlightClickable));
        }
    }

    togglePanel() {
        const isOpen = this.panel.classList.contains('a11y-active');
        if (isOpen) {
            this.closePanel();
        } else {
            this.openPanel();
        }
    }

    openPanel() {
        this.panel.classList.add('a11y-active');
        if (this.backdrop) this.backdrop.classList.add('a11y-active');
        this.triggerBtn.setAttribute('aria-expanded', 'true');
        if (this.closeBtn) this.closeBtn.focus();
        this.announce('Menú de lectura fácil abierto');
    }

    closePanel() {
        this.panel.classList.remove('a11y-active');
        if (this.backdrop) this.backdrop.classList.remove('a11y-active');
        this.triggerBtn.setAttribute('aria-expanded', 'false');
        this.announce('Menú de lectura fácil cerrado');
    }

    resetAll() {
        this.state = {
            fontScale: 0,
            contrast: false,
            speechReader: false,
            highlightClickable: false
        };

        if (this.speechSynthesis) {
            this.speechSynthesis.cancel();
        }

        try {
            localStorage.removeItem(this.storageKey);
        } catch (e) {
            console.warn('[A11y] Error limpiando localStorage:', e);
        }

        this.applySettings();
        this.announce('Todos los ajustes han vuelto a la normalidad.');
    }

    announce(text) {
        if (!this.announcer) return;
        this.announcer.textContent = '';
        setTimeout(() => {
            this.announcer.textContent = text;
        }, 50);
    }

    initSpeechSynthesis() {
        if (!this.speechSynthesis) return;

        const setVoice = () => {
            const voices = this.speechSynthesis.getVoices();
            this.speechVoice = voices.find(v => v.lang.startsWith('es')) || voices[0] || null;
        };

        setVoice();
        if (this.speechSynthesis.onvoiceschanged !== undefined) {
            this.speechSynthesis.onvoiceschanged = setVoice;
        }
    }

    setupSpeechListeners() {
        document.addEventListener('mouseup', () => {
            if (!this.state.speechReader) return;
            const selectedText = window.getSelection().toString().trim();
            if (selectedText && selectedText.length > 1) {
                this.speakText(selectedText);
            }
        });

        document.addEventListener('click', (e) => {
            if (!this.state.speechReader) return;
            if (e.target.closest('#a11yPanel') || e.target.closest('#a11yTriggerBtn')) return;

            const text = e.target.innerText || e.target.getAttribute('aria-label') || e.target.title || e.target.alt;
            if (text && text.trim().length > 0) {
                this.speakText(text.trim());
            }
        });
    }

    speakText(text) {
        if (!this.speechSynthesis) return;
        this.speechSynthesis.cancel();

        const utterance = new SpeechSynthesisUtterance(text);
        utterance.rate = 0.95; // Velocidad ligeramente más pausada para adultos mayores
        utterance.lang = 'es-ES';
        if (this.speechVoice) {
            utterance.voice = this.speechVoice;
        }

        this.speechSynthesis.speak(utterance);
    }
}

// Iniciar automáticamente
window.VT_A11Y = new AccessibilityManager();
