/*!
 * Copyright (c) 2021 Momo Bassit.
 * Licensed under the MIT License (MIT)
 * https://github.com/mdbassit/Coloris
 */

((window, document, Math, undefined) => {
    const ctx = document.createElement('canvas').getContext('2d');
    const currentColor = { r: 0, g: 0, b: 0, h: 0, s: 0, v: 0, a: 1 };
    let container,
        picker,
        colorArea,
        colorMarker,
        colorPreview,
        colorValue,
        clearButton,
        closeButton,
        hueSlider,
        hueMarker,
        alphaSlider,
        alphaMarker,
        currentEl,
        currentFormat,
        oldColor,
        keyboardNav,
        colorAreaDims = {};

    // Настройки по умолчанию
    const settings = {
        el: '[data-coloris]',
        parent: 'body',
        theme: 'default',
        themeMode: 'light',
        rtl: false,
        wrap: true,
        margin: 2,
        format: 'hex',
        formatToggle: false,
        swatches: [],
        swatchesOnly: false,
        alpha: true,
        forceAlpha: false,
        focusInput: true,
        selectInput: false,
        inline: false,
        defaultColor: '#000000',
        clearButton: false,
        clearLabel: 'Clear',
        closeButton: false,
        closeLabel: 'Close',
        onChange: () => undefined,
        a11y: {
            open: 'Open color picker',
            close: 'Close color picker',
            clear: 'Clear the selected color',
            marker: 'Saturation: {s}. Brightness: {v}.',
            hueSlider: 'Hue slider',
            alphaSlider: 'Opacity slider',
            input: 'Color value field',
            format: 'Color format',
            swatch: 'Color swatch',
            instruction:
                'Saturation and brightness selector. Use up, down, left and right arrow keys to select.',
        },
    };

    // Кэш виртуальных экземпляров
    const instances = {};
    let currentInstanceId = '';
    let defaultInstance = {};
    let hasInstance = false;

    /**
     * Настроить пикер цвета.
     * @param {object} options Параметры конфигурации.
     */
    function configure(options) {
        if (typeof options !== 'object') {
            return;
        }

        for (const key in options) {
            switch (key) {
                case 'el':
                    bindFields(options.el);
                    if (options.wrap !== false) {
                        wrapFields(options.el);
                    }
                    break;
                case 'parent':
                    container =
                        options.parent instanceof HTMLElement
                            ? options.parent
                            : document.querySelector(options.parent);
                    if (container) {
                        container.appendChild(picker);
                        settings.parent = options.parent;

                        // document.body особенный
                        if (container === document.body) {
                            container = undefined;
                        }
                    }
                    break;
                case 'themeMode':
                    settings.themeMode = options.themeMode;
                    if (
                        options.themeMode === 'auto' &&
                        window.matchMedia &&
                        window.matchMedia('(prefers-color-scheme: dark)').matches
                    ) {
                        settings.themeMode = 'dark';
                    }
                // Отсутствие оператора break преднамеренно
                case 'theme':
                    if (options.theme) {
                        settings.theme = options.theme;
                    }

                    // Установить тему и цветовую схему
                    picker.className = `clr-picker clr-${settings.theme} clr-${settings.themeMode}`;

                    // Обновить позицию пикера цвета, если используется встроенный режим
                    if (settings.inline) {
                        updatePickerPosition();
                    }
                    break;
                case 'rtl':
                    settings.rtl = !!options.rtl;
                    Array.from(document.getElementsByClassName('clr-field')).forEach((field) =>
                        field.classList.toggle('clr-rtl', settings.rtl),
                    );
                    break;
                case 'margin':
                    options.margin *= 1;
                    settings.margin = !isNaN(options.margin) ? options.margin : settings.margin;
                    break;
                case 'wrap':
                    if (options.el && options.wrap) {
                        wrapFields(options.el);
                    }
                    break;
                case 'formatToggle':
                    settings.formatToggle = !!options.formatToggle;
                    getEl('clr-format').style.display = settings.formatToggle ? 'block' : 'none';
                    if (settings.formatToggle) {
                        settings.format = 'auto';
                    }
                    break;
                case 'swatches':
                    if (Array.isArray(options.swatches)) {
                        const swatchesContainer = getEl('clr-swatches');
                        const swatches = document.createElement('div');

                        // Очистить текущие образцы
                        swatchesContainer.textContent = '';

                        // Создать новые образцы
                        options.swatches.forEach((swatch, i) => {
                            const button = document.createElement('button');

                            button.setAttribute('type', `button`);
                            button.setAttribute('id', `clr-swatch-${i}`);
                            button.setAttribute(
                                'aria-labelledby',
                                `clr-swatch-label clr-swatch-${i}`,
                            );
                            button.style.color = swatch;
                            button.textContent = swatch;

                            swatches.appendChild(button);
                        });

                        // Добавить новые образцы, если они есть
                        if (options.swatches.length) {
                            swatchesContainer.appendChild(swatches);
                        }

                        settings.swatches = options.swatches.slice();
                    }
                    break;
                case 'swatchesOnly':
                    settings.swatchesOnly = !!options.swatchesOnly;
                    picker.setAttribute('data-minimal', settings.swatchesOnly);
                    break;
                case 'alpha':
                    settings.alpha = !!options.alpha;
                    picker.setAttribute('data-alpha', settings.alpha);
                    break;
                case 'inline':
                    settings.inline = !!options.inline;
                    picker.setAttribute('data-inline', settings.inline);

                    if (settings.inline) {
                        const defaultColor = options.defaultColor || settings.defaultColor;

                        currentFormat = getColorFormatFromStr(defaultColor);
                        updatePickerPosition();
                        setColorFromStr(defaultColor);
                    }
                    break;
                case 'clearButton':
                    // Backward compatibility
                    if (typeof options.clearButton === 'object') {
                        if (options.clearButton.label) {
                            settings.clearLabel = options.clearButton.label;
                            clearButton.innerHTML = settings.clearLabel;
                        }

                        options.clearButton = options.clearButton.show;
                    }

                    settings.clearButton = !!options.clearButton;
                    clearButton.style.display = settings.clearButton ? 'block' : 'none';
                    break;
                case 'clearLabel':
                    settings.clearLabel = options.clearLabel;
                    clearButton.innerHTML = settings.clearLabel;
                    break;
                case 'closeButton':
                    settings.closeButton = !!options.closeButton;

                    if (settings.closeButton) {
                        picker.insertBefore(closeButton, colorPreview);
                    } else {
                        colorPreview.appendChild(closeButton);
                    }

                    break;
                case 'closeLabel':
                    settings.closeLabel = options.closeLabel;
                    closeButton.innerHTML = settings.closeLabel;
                    break;
                case 'a11y':
                    const labels = options.a11y;
                    let update = false;

                    if (typeof labels === 'object') {
                        for (const label in labels) {
                            if (labels[label] && settings.a11y[label]) {
                                settings.a11y[label] = labels[label];
                                update = true;
                            }
                        }
                    }

                    if (update) {
                        const openLabel = getEl('clr-open-label');
                        const swatchLabel = getEl('clr-swatch-label');

                        openLabel.innerHTML = settings.a11y.open;
                        swatchLabel.innerHTML = settings.a11y.swatch;
                        closeButton.setAttribute('aria-label', settings.a11y.close);
                        clearButton.setAttribute('aria-label', settings.a11y.clear);
                        hueSlider.setAttribute('aria-label', settings.a11y.hueSlider);
                        alphaSlider.setAttribute('aria-label', settings.a11y.alphaSlider);
                        colorValue.setAttribute('aria-label', settings.a11y.input);
                        colorArea.setAttribute('aria-label', settings.a11y.instruction);
                    }
                    break;
                default:
                    settings[key] = options[key];
            }
        }
    }

    /**
     * Добавить или обновить виртуальный экземпляр.
     * @param {String} selector CSS-селектор элементов, к которым прикрепляется экземпляр.
     * @param {Object} options Параметры для применения к экземпляру.
     */
    function setVirtualInstance(selector, options) {
        if (typeof selector === 'string' && typeof options === 'object') {
            instances[selector] = options;
            hasInstance = true;
        }
    }

    /**
     * Удалить виртуальный экземпляр.
     * @param {String} selector CSS-селектор элементов, к которым прикрепляется экземпляр.
     */
    function removeVirtualInstance(selector) {
        delete instances[selector];

        if (Object.keys(instances).length === 0) {
            hasInstance = false;

            if (selector === currentInstanceId) {
                resetVirtualInstance();
            }
        }
    }

    /**
     * Прикрепить виртуальный экземпляр к элементу, если он соответствует селектору.
     * @param {Object} element Целевой элемент, который получит виртуальный экземпляр, если применимо.
     */
    function attachVirtualInstance(element) {
        if (hasInstance) {
            // Эти параметры можно установить только глобально, а не для каждого экземпляра
            const unsupportedOptions = ['el', 'wrap', 'rtl', 'inline', 'defaultColor', 'a11y'];

            for (let selector in instances) {
                const options = instances[selector];

                // Если элемент соответствует CSS-селектору экземпляра
                if (element.matches(selector)) {
                    currentInstanceId = selector;
                    defaultInstance = {};

                    // Удалить неподдерживаемые параметры
                    unsupportedOptions.forEach((option) => delete options[option]);

                    // Сохранить резервную копию параметров по умолчанию для последующего восстановления
                    for (let option in options) {
                        defaultInstance[option] = Array.isArray(settings[option])
                            ? settings[option].slice()
                            : settings[option];
                    }

                    // Установить параметры экземпляра
                    configure(options);
                    break;
                }
            }
        }
    }

    /**
     * Вернуть любые параметры экземпляра, которые были применены ранее.
     */
    function resetVirtualInstance() {
        if (Object.keys(defaultInstance).length > 0) {
            configure(defaultInstance);
            currentInstanceId = '';
            defaultInstance = {};
        }
    }

    /**
     * Привязать пикер цвета к полям ввода, соответствующим селектору.
     * @param {(string|HTMLElement|HTMLElement[])} selector CSS-селектор, DOM-элемент или список DOM-элементов.
     */
    function bindFields(selector) {
        if (selector instanceof HTMLElement) {
            selector = [selector];
        }

        if (Array.isArray(selector)) {
            selector.forEach((field) => {
                addListener(field, 'click', openPicker);
                addListener(field, 'input', updateColorPreview);
            });
        } else {
            addListener(document, 'click', selector, openPicker);
            addListener(document, 'input', selector, updateColorPreview);
        }
    }

    /**
     * Открыть пикер цвета.
     * @param {object} event Событие, открывающее пикер цвета.
     */
    function openPicker(event) {
        // Пропустить, если используется встроенный режим
        if (settings.inline) {
            return;
        }

        // Сначала применить параметры для каждого экземпляра
        attachVirtualInstance(event.target);

        currentEl = event.target;
        oldColor = currentEl.value;
        currentFormat = getColorFormatFromStr(oldColor);
        picker.classList.add('clr-open');

        updatePickerPosition();
        setColorFromStr(oldColor);

        if (settings.focusInput || settings.selectInput) {
            colorValue.focus({ preventScroll: true });
            colorValue.setSelectionRange(currentEl.selectionStart, currentEl.selectionEnd);
        }

        if (settings.selectInput) {
            colorValue.select();
        }

        // Всегда фокусировать первый элемент при использовании навигации с клавиатуры
        if (keyboardNav || settings.swatchesOnly) {
            getFocusableElements().shift().focus();
        }

        // Вызвать событие "open"
        currentEl.dispatchEvent(new Event('open', { bubbles: false }));
    }

    /**
     * Обновить позицию пикера цвета и смещение цветового градиента
     */
    function updatePickerPosition() {
        const parent = container;
        const scrollY = window.scrollY;
        const pickerWidth = picker.offsetWidth;
        const pickerHeight = picker.offsetHeight;
        const reposition = { left: false, top: false };
        let parentStyle, parentMarginTop, parentBorderTop;
        let offset = { x: 0, y: 0 };

        if (parent) {
            parentStyle = window.getComputedStyle(parent);
            parentMarginTop = parseFloat(parentStyle.marginTop);
            parentBorderTop = parseFloat(parentStyle.borderTopWidth);

            offset = parent.getBoundingClientRect();
            offset.y += parentBorderTop + scrollY;
        }

        if (!settings.inline) {
            const coords = currentEl.getBoundingClientRect();
            let left = coords.x;
            let top = scrollY + coords.y + coords.height + settings.margin;

            // Если пикер цвета находится внутри пользовательского контейнера
            // установить позицию относительно него
            if (parent) {
                left -= offset.x;
                top -= offset.y;

                if (left + pickerWidth > parent.clientWidth) {
                    left += coords.width - pickerWidth;
                    reposition.left = true;
                }

                if (top + pickerHeight > parent.clientHeight - parentMarginTop) {
                    if (pickerHeight + settings.margin <= coords.top - (offset.y - scrollY)) {
                        top -= coords.height + pickerHeight + settings.margin * 2;
                        reposition.top = true;
                    }
                }

                top += parent.scrollTop;

                // В противном случае установить позицию относительно всего документа
            } else {
                if (left + pickerWidth > document.documentElement.clientWidth) {
                    left += coords.width - pickerWidth;
                    reposition.left = true;
                }

                if (top + pickerHeight - scrollY > document.documentElement.clientHeight) {
                    if (pickerHeight + settings.margin <= coords.top) {
                        top = scrollY + coords.y - pickerHeight - settings.margin;
                        reposition.top = true;
                    }
                }
            }

            picker.classList.toggle('clr-left', reposition.left);
            picker.classList.toggle('clr-top', reposition.top);
            picker.style.left = `${left}px`;
            picker.style.top = `${top}px`;
            offset.x += picker.offsetLeft;
            offset.y += picker.offsetTop;
        }

        colorAreaDims = {
            width: colorArea.offsetWidth,
            height: colorArea.offsetHeight,
            x: colorArea.offsetLeft + offset.x,
            y: colorArea.offsetTop + offset.y,
        };
    }

    /**
     * Обернуть связанные поля ввода в div, который добавляет предпросмотр цвета.
     * @param {(string|HTMLElement|HTMLElement[])} selector CSS-селектор, DOM-элемент или список DOM-элементов.
     */
    function wrapFields(selector) {
        if (selector instanceof HTMLElement) {
            wrapColorField(selector);
        } else if (Array.isArray(selector)) {
            selector.forEach(wrapColorField);
        } else {
            document.querySelectorAll(selector).forEach(wrapColorField);
        }
    }

    /**
     * Обернуть поле ввода в div, который добавляет предпросмотр цвета.
     * @param {object} field Поле ввода.
     */
    function wrapColorField(field) {
        const parentNode = field.parentNode;

        if (!parentNode.classList.contains('clr-field')) {
            const wrapper = document.createElement('div');
            let classes = 'clr-field';

            if (settings.rtl || field.classList.contains('clr-rtl')) {
                classes += ' clr-rtl';
            }

            wrapper.innerHTML = '<button type="button" aria-labelledby="clr-open-label"></button>';
            parentNode.insertBefore(wrapper, field);
            wrapper.className = classes;
            wrapper.style.color = field.value;
            wrapper.appendChild(field);
        }
    }

    /**
     * Обновить предпросмотр цвета поля ввода
     * @param {object} event Событие "input", которое запускает изменение цвета.
     */
    function updateColorPreview(event) {
        const parent = event.target.parentNode;

        // Обновлять предпросмотр только если поле было ранее обернуто
        if (parent.classList.contains('clr-field')) {
            parent.style.color = event.target.value;
        }
    }

    /**
     * Закрыть пикер цвета.
     * @param {boolean} [revert] Если true, вернуть цвет к исходному значению.
     */
    function closePicker(revert) {
        if (currentEl && !settings.inline) {
            const prevEl = currentEl;

            // Вернуть цвет к исходному значению, если необходимо
            if (revert) {
                // Это предотвратит выполнение обработчика события "change" на вводе colorValue
                currentEl = undefined;

                if (oldColor !== prevEl.value) {
                    prevEl.value = oldColor;

                    // Вызвать событие "input" для принудительного обновления миниатюры рядом с полем ввода
                    prevEl.dispatchEvent(new Event('input', { bubbles: true }));
                }
            }

            // Вызвать событие "change", если необходимо
            setTimeout(() => {
                // Добавить это в конец цикла событий
                if (oldColor !== prevEl.value) {
                    prevEl.dispatchEvent(new Event('change', { bubbles: true }));
                }
            });

            // Скрыть диалог пикера
            picker.classList.remove('clr-open');

            // Сбросить любые ранее установленные параметры экземпляра
            if (hasInstance) {
                resetVirtualInstance();
            }

            // Вызвать событие "close"
            prevEl.dispatchEvent(new Event('close', { bubbles: false }));

            if (settings.focusInput) {
                prevEl.focus({ preventScroll: true });
            }

            // Это фактически отмечает пикер как закрытый
            currentEl = undefined;
        }
    }

    /**
     * Установить активный цвет из строки.
     * @param {string} str Строка, представляющая цвет.
     */
    function setColorFromStr(str) {
        const rgba = strToRGBA(str);
        const hsva = RGBAtoHSVA(rgba);

        updateMarkerA11yLabel(hsva.s, hsva.v);
        updateColor(rgba, hsva);

        // Обновить пользовательский интерфейс
        hueSlider.value = hsva.h;
        picker.style.color = `hsl(${hsva.h}, 100%, 50%)`;
        hueMarker.style.left = `${(hsva.h / 360) * 100}%`;

        colorMarker.style.left = `${(colorAreaDims.width * hsva.s) / 100}px`;
        colorMarker.style.top = `${colorAreaDims.height - (colorAreaDims.height * hsva.v) / 100}px`;

        alphaSlider.value = hsva.a * 100;
        alphaMarker.style.left = `${hsva.a * 100}%`;
    }

    /**
     * Определить формат цвета из строки.
     * @param {string} str Строка, представляющая цвет.
     * @return {string} Формат цвета.
     */
    function getColorFormatFromStr(str) {
        const format = str.substring(0, 3).toLowerCase();

        if (format === 'rgb' || format === 'hsl') {
            return format;
        }

        return 'hex';
    }

    /**
     * Скопировать активный цвет в связанное поле ввода.
     * @param {number} [color] Значение цвета для перезаписи активного цвета.
     */
    function pickColor(color) {
        color = color !== undefined ? color : colorValue.value;

        if (currentEl) {
            currentEl.value = color;
            currentEl.dispatchEvent(new Event('input', { bubbles: true }));
        }

        if (settings.onChange) {
            settings.onChange.call(window, color, currentEl);
        }

        document.dispatchEvent(new CustomEvent('coloris:pick', { detail: { color, currentEl } }));
    }

    /**
     * Установить активный цвет на основе определенной точки в цветовом градиенте.
     * @param {number} x Позиция слева.
     * @param {number} y Позиция сверху.
     */
    function setColorAtPosition(x, y) {
        const hsva = {
            h: hueSlider.value * 1,
            s: (x / colorAreaDims.width) * 100,
            v: 100 - (y / colorAreaDims.height) * 100,
            a: alphaSlider.value / 100,
        };
        const rgba = HSVAtoRGBA(hsva);

        updateMarkerA11yLabel(hsva.s, hsva.v);
        updateColor(rgba, hsva);
        pickColor();
    }

    /**
     * Обновить метку доступности цветового маркера.
     * @param {number} saturation Насыщенность
     * @param {number} value Значение
     */
    function updateMarkerA11yLabel(saturation, value) {
        let label = settings.a11y.marker;

        saturation = saturation.toFixed(1) * 1;
        value = value.toFixed(1) * 1;
        label = label.replace('{s}', saturation);
        label = label.replace('{v}', value);
        colorMarker.setAttribute('aria-label', label);
    }

    /**
     * Получить позиции pageX и pageY указателя.
     * @param {object} event Объект MouseEvent или TouchEvent.
     * @return {object} Позиции pageX и pageY.
     */
    function getPointerPosition(event) {
        return {
            pageX: event.changedTouches ? event.changedTouches[0].pageX : event.pageX,
            pageY: event.changedTouches ? event.changedTouches[0].pageY : event.pageY,
        };
    }

    /**
     * Переместить цветовой маркер при перетаскивании.
     * @param {object} event Объект MouseEvent.
     */
    function moveMarker(event) {
        const pointer = getPointerPosition(event);
        let x = pointer.pageX - colorAreaDims.x;
        let y = pointer.pageY - colorAreaDims.y;

        if (container) {
            y += container.scrollTop;
        }

        setMarkerPosition(x, y);

        // Предотвратить прокрутку во время перетаскивания маркера
        event.preventDefault();
        event.stopPropagation();
    }

    /**
     * Переместить цветовой маркер при нажатии клавиш со стрелками.
     * @param {number} offsetX Горизонтальное смещение для перемещения.
     * @param {number} offsetY Вертикальное смещение для перемещения.
     */
    function moveMarkerOnKeydown(offsetX, offsetY) {
        let x = colorMarker.style.left.replace('px', '') * 1 + offsetX;
        let y = colorMarker.style.top.replace('px', '') * 1 + offsetY;

        setMarkerPosition(x, y);
    }

    /**
     * Установить позицию цветового маркера.
     * @param {number} x Позиция слева.
     * @param {number} y Позиция сверху.
     */
    function setMarkerPosition(x, y) {
        // Убедиться, что маркер не выходит за границы
        x = x < 0 ? 0 : x > colorAreaDims.width ? colorAreaDims.width : x;
        y = y < 0 ? 0 : y > colorAreaDims.height ? colorAreaDims.height : y;

        // Установить позицию
        colorMarker.style.left = `${x}px`;
        colorMarker.style.top = `${y}px`;

        // Обновить цвет
        setColorAtPosition(x, y);

        // Убедиться, что маркер сфокусирован
        colorMarker.focus();
    }

    /**
     * Обновить поле ввода пикера цвета и миниатюру предпросмотра.
     * @param {Object} rgba Значения красного, зеленого, синего и альфа.
     * @param {Object} [hsva] Значения оттенка, насыщенности, значения и альфа.
     */
    function updateColor(rgba = {}, hsva = {}) {
        let format = settings.format;

        for (const key in rgba) {
            currentColor[key] = rgba[key];
        }

        for (const key in hsva) {
            currentColor[key] = hsva[key];
        }

        const hex = RGBAToHex(currentColor);
        const opaqueHex = hex.substring(0, 7);

        colorMarker.style.color = opaqueHex;
        alphaMarker.parentNode.style.color = opaqueHex;
        alphaMarker.style.color = hex;
        colorPreview.style.color = hex;

        // Принудительно перерисовать цветовые и альфа-градиенты как обходной путь для ошибки Google Chrome
        colorArea.style.display = 'none';
        colorArea.offsetHeight;
        colorArea.style.display = '';
        alphaMarker.nextElementSibling.style.display = 'none';
        alphaMarker.nextElementSibling.offsetHeight;
        alphaMarker.nextElementSibling.style.display = '';

        if (format === 'mixed') {
            format = currentColor.a === 1 ? 'hex' : 'rgb';
        } else if (format === 'auto') {
            format = currentFormat;
        }

        switch (format) {
            case 'hex':
                colorValue.value = hex;
                break;
            case 'rgb':
                colorValue.value = RGBAToStr(currentColor);
                break;
            case 'hsl':
                colorValue.value = HSLAToStr(HSVAtoHSLA(currentColor));
                break;
        }

        // Выбрать текущий формат в переключателе формата
        document.querySelector(`.clr-format [value="${format}"]`).checked = true;
    }

    /**
     * Установить оттенок при перемещении его ползунка.
     */
    function setHue() {
        const hue = hueSlider.value * 1;
        const x = colorMarker.style.left.replace('px', '') * 1;
        const y = colorMarker.style.top.replace('px', '') * 1;

        picker.style.color = `hsl(${hue}, 100%, 50%)`;
        hueMarker.style.left = `${(hue / 360) * 100}%`;

        setColorAtPosition(x, y);
    }

    /**
     * Установить альфа-канал при перемещении его ползунка.
     */
    function setAlpha() {
        const alpha = alphaSlider.value / 100;

        alphaMarker.style.left = `${alpha * 100}%`;
        updateColor({ a: alpha });
        pickColor();
    }

    /**
     * Преобразовать HSVA в RGBA.
     * @param {object} hsva Значения оттенка, насыщенности, значения и альфа.
     * @return {object} Значения красного, зеленого, синего и альфа.
     */
    function HSVAtoRGBA(hsva) {
        const saturation = hsva.s / 100;
        const value = hsva.v / 100;
        let chroma = saturation * value;
        let hueBy60 = hsva.h / 60;
        let x = chroma * (1 - Math.abs((hueBy60 % 2) - 1));
        let m = value - chroma;

        chroma = chroma + m;
        x = x + m;

        const index = Math.floor(hueBy60) % 6;
        const red = [chroma, x, m, m, x, chroma][index];
        const green = [x, chroma, chroma, x, m, m][index];
        const blue = [m, m, x, chroma, chroma, x][index];

        return {
            r: Math.round(red * 255),
            g: Math.round(green * 255),
            b: Math.round(blue * 255),
            a: hsva.a,
        };
    }

    /**
     * Преобразовать HSVA в HSLA.
     * @param {object} hsva Значения оттенка, насыщенности, значения и альфа.
     * @return {object} Значения оттенка, насыщенности, светлоты и альфа.
     */
    function HSVAtoHSLA(hsva) {
        const value = hsva.v / 100;
        const lightness = value * (1 - hsva.s / 100 / 2);
        let saturation;

        if (lightness > 0 && lightness < 1) {
            saturation = Math.round(
                ((value - lightness) / Math.min(lightness, 1 - lightness)) * 100,
            );
        }

        return {
            h: hsva.h,
            s: saturation || 0,
            l: Math.round(lightness * 100),
            a: hsva.a,
        };
    }

    /**
     * Преобразовать RGBA в HSVA.
     * @param {object} rgba Значения красного, зеленого, синего и альфа.
     * @return {object} Значения оттенка, насыщенности, значения и альфа.
     */
    function RGBAtoHSVA(rgba) {
        const red = rgba.r / 255;
        const green = rgba.g / 255;
        const blue = rgba.b / 255;
        const xmax = Math.max(red, green, blue);
        const xmin = Math.min(red, green, blue);
        const chroma = xmax - xmin;
        const value = xmax;
        let hue = 0;
        let saturation = 0;

        if (chroma) {
            if (xmax === red) {
                hue = (green - blue) / chroma;
            }
            if (xmax === green) {
                hue = 2 + (blue - red) / chroma;
            }
            if (xmax === blue) {
                hue = 4 + (red - green) / chroma;
            }
            if (xmax) {
                saturation = chroma / xmax;
            }
        }

        hue = Math.floor(hue * 60);

        return {
            h: hue < 0 ? hue + 360 : hue,
            s: Math.round(saturation * 100),
            v: Math.round(value * 100),
            a: rgba.a,
        };
    }

    /**
     * Разобрать строку в RGBA.
     * @param {string} str Строка, представляющая цвет.
     * @return {object} Значения красного, зеленого, синего и альфа.
     */
    function strToRGBA(str) {
        const regex = /^((rgba)|rgb)[\D]+([\d.]+)[\D]+([\d.]+)[\D]+([\d.]+)[\D]*?([\d.]+|$)/i;
        let match, rgba;

        // По умолчанию черный для недействительных цветовых строк
        ctx.fillStyle = '#000';

        // Использовать canvas для преобразования строки в допустимую цветовую строку
        ctx.fillStyle = str;
        match = regex.exec(ctx.fillStyle);

        if (match) {
            rgba = {
                r: match[3] * 1,
                g: match[4] * 1,
                b: match[5] * 1,
                a: match[6] * 1,
            };
        } else {
            match = ctx.fillStyle
                .replace('#', '')
                .match(/.{2}/g)
                .map((h) => parseInt(h, 16));
            rgba = {
                r: match[0],
                g: match[1],
                b: match[2],
                a: 1,
            };
        }

        return rgba;
    }

    /**
     * Преобразовать RGBA в Hex.
     * @param {object} rgba Значения красного, зеленого, синего и альфа.
     * @return {string} Шестнадцатеричная цветовая строка.
     */
    function RGBAToHex(rgba) {
        let R = rgba.r.toString(16);
        let G = rgba.g.toString(16);
        let B = rgba.b.toString(16);
        let A = '';

        if (rgba.r < 16) {
            R = '0' + R;
        }

        if (rgba.g < 16) {
            G = '0' + G;
        }

        if (rgba.b < 16) {
            B = '0' + B;
        }

        if (settings.alpha && (rgba.a < 1 || settings.forceAlpha)) {
            const alpha = (rgba.a * 255) | 0;
            A = alpha.toString(16);

            if (alpha < 16) {
                A = '0' + A;
            }
        }

        return '#' + R + G + B + A;
    }

    /**
     * Преобразовать значения RGBA в CSS-строку rgb/rgba.
     * @param {object} rgba Значения красного, зеленого, синего и альфа.
     * @return {string} CSS-цветовая строка.
     */
    function RGBAToStr(rgba) {
        if (!settings.alpha || (rgba.a === 1 && !settings.forceAlpha)) {
            return `rgb(${rgba.r}, ${rgba.g}, ${rgba.b})`;
        } else {
            return `rgba(${rgba.r}, ${rgba.g}, ${rgba.b}, ${rgba.a})`;
        }
    }

    /**
     * Преобразовать значения HSLA в CSS-строку hsl/hsla.
     * @param {object} hsla Значения оттенка, насыщенности, светлоты и альфа.
     * @return {string} CSS-цветовая строка.
     */
    function HSLAToStr(hsla) {
        if (!settings.alpha || (hsla.a === 1 && !settings.forceAlpha)) {
            return `hsl(${hsla.h}, ${hsla.s}%, ${hsla.l}%)`;
        } else {
            return `hsla(${hsla.h}, ${hsla.s}%, ${hsla.l}%, ${hsla.a})`;
        }
    }

    /**
     * Инициализировать пикер цвета.
     */
    function init() {
        // Отрисовать пользовательский интерфейс
        container = undefined;
        picker = document.createElement('div');
        picker.setAttribute('id', 'clr-picker');
        picker.className = 'clr-picker';
        picker.innerHTML =
            `<input id="clr-color-value" name="clr-color-value" class="clr-color" type="text" value="" spellcheck="false" aria-label="${settings.a11y.input}">` +
            `<div id="clr-color-area" class="clr-gradient" role="application" aria-label="${settings.a11y.instruction}">` +
            '<div id="clr-color-marker" class="clr-marker" tabindex="0"></div>' +
            '</div>' +
            '<div class="clr-hue">' +
            `<input id="clr-hue-slider" name="clr-hue-slider" type="range" min="0" max="360" step="1" aria-label="${settings.a11y.hueSlider}">` +
            '<div id="clr-hue-marker"></div>' +
            '</div>' +
            '<div class="clr-alpha">' +
            `<input id="clr-alpha-slider" name="clr-alpha-slider" type="range" min="0" max="100" step="1" aria-label="${settings.a11y.alphaSlider}">` +
            '<div id="clr-alpha-marker"></div>' +
            '<span></span>' +
            '</div>' +
            '<div id="clr-format" class="clr-format">' +
            '<fieldset class="clr-segmented">' +
            `<legend>${settings.a11y.format}</legend>` +
            '<input id="clr-f1" type="radio" name="clr-format" value="hex">' +
            '<label for="clr-f1">Hex</label>' +
            '<input id="clr-f2" type="radio" name="clr-format" value="rgb">' +
            '<label for="clr-f2">RGB</label>' +
            '<input id="clr-f3" type="radio" name="clr-format" value="hsl">' +
            '<label for="clr-f3">HSL</label>' +
            '<span></span>' +
            '</fieldset>' +
            '</div>' +
            '<div id="clr-swatches" class="clr-swatches"></div>' +
            `<button type="button" id="clr-clear" class="clr-clear" aria-label="${settings.a11y.clear}">${settings.clearLabel}</button>` +
            '<div id="clr-color-preview" class="clr-preview">' +
            `<button type="button" id="clr-close" class="clr-close" aria-label="${settings.a11y.close}">${settings.closeLabel}</button>` +
            '</div>' +
            `<span id="clr-open-label" hidden>${settings.a11y.open}</span>` +
            `<span id="clr-swatch-label" hidden>${settings.a11y.swatch}</span>`;

        // Добавить пикер цвета в DOM
        document.body.appendChild(picker);

        // Ссылки на элементы пользовательского интерфейса
        colorArea = getEl('clr-color-area');
        colorMarker = getEl('clr-color-marker');
        clearButton = getEl('clr-clear');
        closeButton = getEl('clr-close');
        colorPreview = getEl('clr-color-preview');
        colorValue = getEl('clr-color-value');
        hueSlider = getEl('clr-hue-slider');
        hueMarker = getEl('clr-hue-marker');
        alphaSlider = getEl('clr-alpha-slider');
        alphaMarker = getEl('clr-alpha-marker');

        // Привязать пикер к селектору по умолчанию
        bindFields(settings.el);
        wrapFields(settings.el);

        addListener(picker, 'mousedown', (event) => {
            picker.classList.remove('clr-keyboard-nav');
            event.stopPropagation();
        });

        addListener(colorArea, 'mousedown', (event) => {
            addListener(document, 'mousemove', moveMarker);
        });

        addListener(colorArea, 'contextmenu', (event) => {
            event.preventDefault();
        });

        addListener(colorArea, 'touchstart', (event) => {
            document.addEventListener('touchmove', moveMarker, { passive: false });
        });

        addListener(colorMarker, 'mousedown', (event) => {
            addListener(document, 'mousemove', moveMarker);
        });

        addListener(colorMarker, 'touchstart', (event) => {
            document.addEventListener('touchmove', moveMarker, { passive: false });
        });

        addListener(colorValue, 'change', (event) => {
            const value = colorValue.value;

            if (currentEl || settings.inline) {
                const color = value === '' ? value : setColorFromStr(value);
                pickColor(color);
            }
        });

        addListener(clearButton, 'click', (event) => {
            pickColor('');
            closePicker();
        });

        addListener(closeButton, 'click', (event) => {
            pickColor();
            closePicker();
        });

        addListener(getEl('clr-format'), 'click', '.clr-format input', (event) => {
            currentFormat = event.target.value;
            updateColor();
            pickColor();
        });

        addListener(picker, 'click', '.clr-swatches button', (event) => {
            setColorFromStr(event.target.textContent);
            pickColor();

            if (settings.swatchesOnly) {
                closePicker();
            }
        });

        addListener(document, 'mouseup', (event) => {
            document.removeEventListener('mousemove', moveMarker);
        });

        addListener(document, 'touchend', (event) => {
            document.removeEventListener('touchmove', moveMarker);
        });

        addListener(document, 'mousedown', (event) => {
            keyboardNav = false;
            picker.classList.remove('clr-keyboard-nav');
            closePicker();
        });

        addListener(document, 'keydown', (event) => {
            const key = event.key;
            const target = event.target;
            const shiftKey = event.shiftKey;
            const navKeys = ['Tab', 'ArrowUp', 'ArrowDown', 'ArrowLeft', 'ArrowRight'];

            if (key === 'Escape') {
                closePicker(true);
                return;

                // Закрыть пикер цвета и сохранить выбранный цвет при нажатии Enter
            } else if (key === 'Enter' && target.tagName !== 'BUTTON') {
                closePicker();
                return;

                // Отображать кольца фокуса при использовании клавиатуры
            } else if (navKeys.includes(key)) {
                keyboardNav = true;
                picker.classList.add('clr-keyboard-nav');
            }

            // Задержать фокус внутри пикера цвета, пока он открыт
            if (key === 'Tab' && target.matches('.clr-picker *')) {
                const focusables = getFocusableElements();
                const firstFocusable = focusables.shift();
                const lastFocusable = focusables.pop();

                if (shiftKey && target === firstFocusable) {
                    lastFocusable.focus();
                    event.preventDefault();
                } else if (!shiftKey && target === lastFocusable) {
                    firstFocusable.focus();
                    event.preventDefault();
                }
            }
        });

        addListener(document, 'click', '.clr-field button', (event) => {
            // Сбросить любые ранее установленные параметры экземпляра
            if (hasInstance) {
                resetVirtualInstance();
            }

            // Открыть пикер цвета
            event.target.nextElementSibling.dispatchEvent(new Event('click', { bubbles: true }));
        });

        addListener(colorMarker, 'keydown', (event) => {
            const movements = {
                ArrowUp: [0, -1],
                ArrowDown: [0, 1],
                ArrowLeft: [-1, 0],
                ArrowRight: [1, 0],
            };

            if (Object.keys(movements).includes(event.key)) {
                moveMarkerOnKeydown(...movements[event.key]);
                event.preventDefault();
            }
        });

        addListener(colorArea, 'click', moveMarker);
        addListener(hueSlider, 'input', setHue);
        addListener(alphaSlider, 'input', setAlpha);
    }

    /**
     * Вернуть список фокусируемых элементов внутри пикера цвета.
     * @return {array} Список фокусируемых DOM-элементов.
     */
    function getFocusableElements() {
        const controls = Array.from(picker.querySelectorAll('input, button'));
        const focusables = controls.filter((node) => !!node.offsetWidth);

        return focusables;
    }

    /**
     * Ярлык для getElementById для оптимизации минифицированного JS.
     * @param {string} id Идентификатор элемента.
     * @return {object} DOM-элемент с предоставленным идентификатором.
     */
    function getEl(id) {
        return document.getElementById(id);
    }

    /**
     * Ярлык для addEventListener для оптимизации минифицированного JS.
     * @param {object} context Контекст, к которому прикрепляется слушатель.
     * @param {string} type Тип события.
     * @param {(string|function)} selector Цель события, если используется делегирование, обработчик события, если нет.
     * @param {function} [fn] Обработчик события, если используется делегирование.
     */
    function addListener(context, type, selector, fn) {
        const matches = Element.prototype.matches || Element.prototype.msMatchesSelector;

        // Делегировать событие цели селектора
        if (typeof selector === 'string') {
            context.addEventListener(type, (event) => {
                if (matches.call(event.target, selector)) {
                    fn.call(event.target, event);
                }
            });

            // Если селектор не является строкой, то это функция
            // в этом случае нам нужен обычный слушатель событий
        } else {
            fn = selector;
            context.addEventListener(type, fn);
        }
    }

    /**
     * Вызвать функцию только когда DOM готов.
     * @param {function} fn Функция для вызова.
     * @param {array} [args] Аргументы для передачи в функцию.
     */
    function DOMReady(fn, args) {
        args = args !== undefined ? args : [];

        if (document.readyState !== 'loading') {
            fn(...args);
        } else {
            document.addEventListener('DOMContentLoaded', () => {
                fn(...args);
            });
        }
    }

    // Полифил для Nodelist.forEach
    if (NodeList !== undefined && NodeList.prototype && !NodeList.prototype.forEach) {
        NodeList.prototype.forEach = Array.prototype.forEach;
    }

    // Предоставить пикер цвета глобальной области видимости
    window.Coloris = (() => {
        const methods = {
            set: configure,
            wrap: wrapFields,
            close: closePicker,
            setInstance: setVirtualInstance,
            removeInstance: removeVirtualInstance,
            updatePosition: updatePickerPosition,
            ready: DOMReady,
        };

        function Coloris(options) {
            DOMReady(() => {
                if (options) {
                    if (typeof options === 'string') {
                        bindFields(options);
                    } else {
                        configure(options);
                    }
                }
            });
        }

        for (const key in methods) {
            Coloris[key] = (...args) => {
                DOMReady(methods[key], args);
            };
        }

        return Coloris;
    })();

    // Инициализировать пикер цвета, когда DOM готов
    DOMReady(init);
})(window, document, Math);

// Помечаем инициализированные coloris поля, чтобы избежать повторной инициализации
let colorisConfigured = false;

function configureColoris() {
    if (colorisConfigured) return;
    if (typeof window.Coloris === 'undefined') return;

    colorisConfigured = true;

    window.Coloris({
        el: '[data-coloris]',
        format: 'hex',
        formatToggle: false,
        theme: 'default',
        themeMode: 'light',
        margin: 10,
        alpha: false,
        swatches: [
            '#000000',
            '#FFFFFF',
            '#C6FF1A',
            '#00F6FF',
            '#A600FF',
            '#FF7A00',
            '#6D00FF',
            '#7AD3FF',
            '#FF00A8',
            '#B45CFF',
            '#FF4F70',
            '#4CFFE3',
            '#FF4FE3',
            '#00FF7B',
            '#2A4CFF',
            '#C8FF4F',
            '#FF6B2F',
            '#00D4FF',
            '#FFF500',
            '#0066FF',
            '#B900FF',
            '#FF8A00',
            '#00FFE1',
        ],
        clearButton: false,
        clearLabel: 'Очистить',
        closeLabel: 'Закрыть',
    });
}

/**
 * Найти ближайший Livewire компонент для элемента
 * @param {HTMLElement} element
 * @returns {Object|null}
 */
function findLivewireComponent(element) {
    // Livewire 4: ищем ближайший элемент с wire:id
    let current = element;
    while (current && current !== document.body) {
        const wireId = current.getAttribute('wire:id');
        if (wireId && typeof Livewire !== 'undefined' && Livewire.find) {
            return Livewire.find(wireId);
        }
        current = current.parentElement;
    }
    return null;
}

/**
 * Синхронизировать значение цвета с Livewire
 * @param {HTMLElement} input
 * @param {string} colorValue
 */
function syncColorWithLivewire(input, colorValue) {
    const component = findLivewireComponent(input);
    if (component) {
        component.set('color', colorValue);
    }
}

function initColorisFields() {
    const colorisInputs = document.querySelectorAll(
        '[data-coloris]:not([data-coloris-initialized])',
    );

    if (colorisInputs.length === 0) return;

    // Сначала настроить Coloris (если ещё не настроен)
    configureColoris();

    // Затем обернуть поля (нужно делать каждый раз для новых элементов)
    if (typeof window.Coloris !== 'undefined') {
        window.Coloris.wrap('[data-coloris]:not([data-coloris-initialized])');
    }

    // Отметить поля как инициализированные для предотвращения дублирования обертки
    colorisInputs.forEach((input) => {
        input.setAttribute('data-coloris-initialized', 'true');

        // Добавить обработчик input для синхронизации с Livewire
        input.addEventListener('input', (e) => {
            syncColorWithLivewire(input, e.target.value);
        });

        // Добавить обработчик change для синхронизации с Livewire
        input.addEventListener('change', (e) => {
            syncColorWithLivewire(input, e.target.value);
        });

        // Синхронизировать начальное значение из Livewire
        // Небольшая задержка для того, чтобы Livewire успел инициализироваться
        setTimeout(() => {
            const component = findLivewireComponent(input);
            if (component) {
                const colorValue = component.get('color');
                if (colorValue && !input.value) {
                    input.value = colorValue;
                    // Обновить превью цвета
                    const parent = input.parentNode;
                    if (parent && parent.classList.contains('clr-field')) {
                        parent.style.color = colorValue;
                    }
                }
            }
        }, 50);
    });
}

// MutationObserver для динамически добавленных полей coloris
let colorisObserver = null;

function setupColorisObserver() {
    if (colorisObserver) return;

    colorisObserver = new MutationObserver((mutationsList) => {
        let hasNewColorisInputs = false;

        for (const mutation of mutationsList) {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1) {
                    if (node.matches('[data-coloris]') || node.querySelector('[data-coloris]')) {
                        hasNewColorisInputs = true;
                    }
                }
            });
        }

        if (hasNewColorisInputs) {
            // Повторно инициализировать coloris
            setTimeout(initColorisFields, 10);
        }
    });

    colorisObserver.observe(document.body, {
        childList: true,
        subtree: true,
    });
}

// Основная инициализация
function initialize() {
    initColorisFields();
    setupColorisObserver();
}

// Инициализация при готовности DOM
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialize);
} else {
    initialize();
}

// Повторная инициализация после обновлений Livewire
if (typeof Livewire !== 'undefined') {
    Livewire.hook('element.updated', (el) => {
        // Проверить, что el - это DOM-элемент
        if (!el || typeof el.querySelectorAll !== 'function') return;

        // Проверить, содержит ли обновлённый элемент поля coloris
        const colorisInputsInside = el.querySelectorAll(
            '[data-coloris]:not([data-coloris-initialized])',
        );
        if (colorisInputsInside.length) {
            setTimeout(initColorisFields, 10);
        }
    });

    Livewire.hook('message.processed', () => {
        // Небольшая задержка для гарантии полного обновления DOM
        setTimeout(initColorisFields, 10);
    });
}
