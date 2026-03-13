export function initChecklistProgressBar(editor, progressElementId = 'checklist-progress-bar') {
    const progressElement = document.getElementById(progressElementId);

    if (!progressElement) {
        console.warn('[ChecklistProgressBar] Element not found:', progressElementId);
        return null;
    }

    if (!progressElement.querySelector('.progress-circle')) {
        progressElement.innerHTML = `
            <div class="flex flex-col items-center justify-center gap-3">
                <div class="text-center">
                    <h4 class="text-sm font-medium text-gray-700 mb-1">Прогресс выполнения</h4>
                    <p class="progress-text text-xs text-gray-500">0 из 0 задач выполнено</p>
                </div>
                <div class="relative w-[80px] h-[80px]">
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

    function countChecklistItems(content) {
        let completed = 0;
        let total = 0;

        if (!content || !Array.isArray(content)) {
            return { completed: 0, total: 0 };
        }

        function traverse(nodes) {
            for (const node of nodes) {
                if (!node || typeof node !== 'object') continue;

                if (node.type === 'checklistItem') {
                    // Проверяем, не пустая ли задача
                    const isEmpty = isChecklistItemEmpty(node);
                    
                    // Считаем только задачи с контентом
                    if (!isEmpty) {
                        total++;
                        if (node.attrs?.checked === true) {
                            completed++;
                        }
                    }
                }

                if (node.content && Array.isArray(node.content)) {
                    traverse(node.content);
                }
            }
        }

        traverse(content);
        return { completed, total };
    }

    function isChecklistItemEmpty(node) {
        if (!node.content || !Array.isArray(node.content)) {
            return true;
        }

        for (const child of node.content) {
            if (child.type === 'paragraph') {
                if (!child.content || child.content.length === 0) {
                    return true;
                }

                // Проверяем, содержит ли параграф текст
                for (const paragraphChild of child.content) {
                    if (paragraphChild.type === 'text' && paragraphChild.text && paragraphChild.text.trim() !== '') {
                        return false;
                    }
                    if (paragraphChild.type === 'image' || paragraphChild.type === 'link') {
                        return false;
                    }
                }

                // Если дошли сюда, значит параграф пустой (только br или пустой текст)
                return true;
            }
        }

        return true;
    }

    function getProgressColor(percentage) {
        if (percentage <= 10) return '#FF4C4C';
        if (percentage <= 30) return '#FF8A4C';
        if (percentage <= 50) return '#FFC04C';
        if (percentage <= 70) return '#B4D84C';
        if (percentage <= 90) return '#6ED84C';
        return '#2ABF2A';
    }

    function updateProgress() {
        if (!editor) {
            console.warn('[ChecklistProgressBar] Editor not available');
            return;
        }

        const content = editor.getJSON()?.content || [];
        const stats = countChecklistItems(content);

        const percentage = stats.total > 0 ? Math.round((stats.completed / stats.total) * 100) : 0;

        const circumference = 283; // 2 * π * 45
        const offset = circumference - (percentage / 100) * circumference;
        const color = getProgressColor(percentage);

        const progressCircle = progressElement.querySelector('.progress-circle');
        const percentageText = progressElement.querySelector('.progress-percentage');
        const progressText = progressElement.querySelector('.progress-text');

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

    // Подписываемся на изменения редактора
    if (editor) {
        editor.on('transaction', ({ editor: ed }) => {
            setTimeout(() => {
                updateProgress();
            }, 10);
        });
    }

    // Первоначальное обновление
    setTimeout(() => {
        updateProgress();
    }, 100);

    return {
        update: updateProgress,
        destroy: () => {
            // Очистка
        },
    };
}
