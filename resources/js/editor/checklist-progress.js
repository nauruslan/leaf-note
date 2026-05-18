// Глобальные объявления для ESLint
/* global console */

/**
 * Класс для управления прогресс-баром чек-листа
 */
export class ChecklistProgressBar {
    constructor(editorInstance, progressElementId = 'checklist-progress-bar') {
        this.editorInstance = editorInstance;
        this.progressElementId = progressElementId;
        this.progressElement = null;
        this.initialized = false;

        this.init();
    }

    /**
     * Инициализация прогресс-бара
     */
    init() {
        this.progressElement = document.getElementById(this.progressElementId);

        if (!this.progressElement) {
            console.warn('[ChecklistProgressBar] Element not found:', this.progressElementId);
            return;
        }

        if (!this.progressElement.querySelector('.progress-circle')) {
            this.progressElement.innerHTML = `
                <div class="flex flex-col items-center justify-center gap-3">
                    <div class="text-center">
                        <h4 class="text-sm font-medium text-gray-700 mb-1">Прогресс выполнения</h4>
                        <p class="progress-text text-xs text-gray-500">0 из 0 задач выполнено</p>
                    </div>
                    <div class="relative w-[220px] h-[220px]">
                        <svg viewBox="0 0 100 100" class="w-full h-full" style="transform: rotate(-90deg); display: block;">
                            <circle cx="50" cy="50" r="45" fill="none" stroke="#e5e7eb" stroke-width="8" stroke-linecap="round" />
                            <circle class="progress-circle" cx="50" cy="50" r="45" fill="none" stroke-width="8" stroke-linecap="round" stroke-dasharray="283" stroke-dashoffset="283" style="transition: stroke-dashoffset 0.5s ease, stroke 1.5s ease;" />
                        </svg>
                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 text-center">
                            <span class="progress-percentage font-bold text-lg text-gray-900">0%</span>
                        </div>
                    </div>
                </div>
            `;
        }

        this.initialized = true;

        // Первоначальное обновление
        setTimeout(() => {
            this.update();
        }, 100);
    }

    /**
     * Подсчёт выполненных и общих задач
     */
    countChecklistItems(data) {
        let completed = 0;
        let total = 0;

        if (!data || !Array.isArray(data)) {
            return { completed: 0, total: 0 };
        }

        for (const item of data) {
            total++;
            if (item.checked === true) {
                completed++;
            }
        }

        return { completed, total };
    }

    /**
     * Получение цвета прогресса в зависимости от процента
     */
    getProgressColor(percentage) {
        if (percentage <= 10) return '#FF4C4C';
        if (percentage <= 30) return '#FF8A4C';
        if (percentage <= 50) return '#FFC04C';
        if (percentage <= 70) return '#B4D84C';
        if (percentage <= 90) return '#6ED84C';
        return '#2ABF2A';
    }

    /**
     * Обновление прогресс-бара
     */
    update() {
        if (!this.initialized || !this.progressElement) {
            return;
        }

        if (!this.editorInstance) {
            console.warn('[ChecklistProgressBar] Editor instance not available');
            return;
        }

        // ChecklistEditor возвращает массив задач через getData()
        const data = this.editorInstance.getData() || [];
        const stats = this.countChecklistItems(data);

        const percentage = stats.total > 0 ? Math.round((stats.completed / stats.total) * 100) : 0;

        const circumference = 283; // 2 * π * 45
        const offset = circumference - (percentage / 100) * circumference;
        const color = this.getProgressColor(percentage);

        const progressCircle = this.progressElement.querySelector('.progress-circle');
        const percentageText = this.progressElement.querySelector('.progress-percentage');
        const progressText = this.progressElement.querySelector('.progress-text');

        if (progressCircle) {
            progressCircle.style.strokeDashoffset = offset.toFixed(1);
            progressCircle.setAttribute('stroke', color);
        }
        if (percentageText) {
            percentageText.textContent = `${percentage}%`;
        }
        if (progressText) {
            progressText.textContent = `${stats.completed} из ${stats.total} задач выполнено`;
        }
    }

    /**
     * Уничтожение прогресс-бара
     */
    destroy() {
        this.initialized = false;
        this.progressElement = null;
        this.editorInstance = null;
    }
}
