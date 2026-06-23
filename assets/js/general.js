// region cellRender
/**
 * Generates an HTML element dynamically based on the provided parameters.
 *
 * @param {Object} params - Configuration object for the element.
 * @param {string} params.type - The type of element to create (e.g., 'button', 'checkbox', 'dropdown').
 * @param {string} [params.id] - The ID attribute for the element.
 * @param {string} [params.class] - The class attribute for styling.
 * @param {string} [params.title] - The title attribute (tooltip).
 * @param {string} [params.text] - The text content for the element.
 * @param {string} [params.icon] - The FontAwesome or Bootstrap icon class (if applicable).
 * @param {Object} [params.dataTags] - Additional `data-*` attributes to apply.
 * @param {Object} [params.events] - Event listeners to attach (e.g., `{ click: () => alert('Clicked!') }`).
 * @param {Object[]} [params.listItems] - Items for dropdown/select components.
 * @param {Object[]} [params.options] - Options for select elements.
 * @param {string} [params.lblYes] - Custom label for `yn` type when `data` is true.
 * @param {string} [params.lblNo] - Custom label for `yn` type when `data` is false.
 * @param {*} [params.data] - The data content related to the element.
 * @returns {HTMLElement} - The generated HTML element.
 */
function cellRender({ type, id, class: className, title, text, icon, dataTags, events, listItems, options, lblYes, lblNo, data, disabled, name }) {
    if (!type) return null;

    function createElement(tag, attributes = {}, eventListeners = {}) {
        const element = document.createElement(tag);
        Object.entries(attributes).forEach(([key, value]) => {
            if (value !== undefined) element.setAttribute(key, value);
        });
        Object.entries(eventListeners).forEach(([event, handler]) => {
            element.addEventListener(event, handler);
        });
        return element;
    }

    function applyDataTags(element, dataAttrs) {
        if (dataAttrs) {
            Object.entries(dataAttrs).forEach(([key, value]) => {
                element.setAttribute(`data-${key}`, value);
            });
        }
    }

    const renderers = {
        button: () => {
            const button = createElement(
                'button',
                {
                    id,
                    class: className,
                    title,
                    name: name,
                    'data-toggle': 'tooltip',
                    'data-placement': 'top',
                    disabled: disabled ? 'disabled' : undefined,
                },
                events,
            );

            applyDataTags(button, dataTags);

            if (icon) button.appendChild(createElement('i', { class: icon }));
            if (text) button.appendChild(document.createTextNode(text));

            return button.outerHTML;
        },

        check: () => createElement('i', { class: data > 0 ? 'fa fa-check' : 'fa fa-times' }).outerHTML,

        checkbox: () =>
            createElement(
                'input',
                {
                    type: 'checkbox',
                    id,
                    class: className,
                    disabled: disabled ? 'disabled' : undefined,
                    title,
                    checked: data ? 'checked' : undefined,
                    ...(dataTags && Object.fromEntries(Object.entries(dataTags).map(([key, value]) => [`data-${key}`, value]))),
                },
                events,
            ).outerHTML,

        color: () => {
            const colorBox = createElement('i', { class: 'fas fa-square' });
            colorBox.style.color = data.trim();
            return colorBox.outerHTML;
        },

        dropdown: () => {
            const wrapper = createElement('div', { class: 'dropdown' });

            const btnTag = createElement(
                'button',
                {
                    id,
                    class: `btn dropdown-toggle ${className || 'btn-sm btn-primary'}`,
                    title,
                    'data-bs-toggle': 'dropdown',
                    'aria-expanded': 'false',
                    type: 'button',
                },
                events,
            );

            applyDataTags(btnTag, dataTags);
            if (icon) btnTag.appendChild(createElement('i', { class: icon }));
            if (text) btnTag.appendChild(document.createTextNode(text));

            const dropdownMenu = createElement('ul', { class: 'dropdown-menu' });

            if (listItems) {
                listItems.forEach(({ id, class: itemClass, title, href, disabled, icon, text, dataTags }) => {
                    const li = createElement('li');
                    const a = createElement('a', {
                        id,
                        class: `dropdown-item ${itemClass || ''}`,
                        title,
                        href,
                        disabled: disabled ? 'disabled' : undefined,
                    });

                    applyDataTags(a, dataTags);
                    if (icon) a.appendChild(createElement('i', { class: `${icon} fa-fw me-2` }));
                    a.appendChild(document.createTextNode(text || ''));
                    li.appendChild(a);
                    dropdownMenu.appendChild(li);
                });
            }

            wrapper.appendChild(btnTag);
            wrapper.appendChild(dropdownMenu);
            return wrapper.outerHTML;
        },

        icon: () =>
            createElement('i', {
                class: icon,
                class: className,
                title: title,
            }).outerHTML,

        select: () => {
            const select = createElement('select', { id, class: className }, events);
            applyDataTags(select, dataTags);

            if (options) {
                options.forEach(({ id: optionId, name }) => {
                    const opt = createElement('option', {
                        value: optionId,
                        selected: optionId == data ? 'selected' : undefined,
                    });
                    opt.textContent = name;
                    select.appendChild(opt);
                });
            }

            return select.outerHTML;
        },

        span: () => {
            const span = createElement('span', { class: className || '' }, events);
            if (text) span.appendChild(document.createTextNode(text));

            return span.outerHTML;
        },

        switch: () => {
            const input = createElement(
                'input',
                {
                    type: 'checkbox',
                    id,
                    class: `form-check-input ${className || ''}`.trim(),
                    disabled: disabled ? 'disabled' : undefined,
                    title,
                    checked: data ? 'checked' : undefined,
                    role: 'switch',
                    ...(dataTags && Object.fromEntries(Object.entries(dataTags).map(([key, value]) => [`data-${key}`, value]))),
                },
                events,
            );

            const wrapper = document.createElement('div');
            wrapper.className = 'form-check form-switch';
            wrapper.appendChild(input);

            return wrapper.outerHTML;
        },

        yn: () => (data > 0 ? lblYes || 'Yes' : lblNo || 'No'),

        buttonsGroup: () => {
            const buttonsGroup = createElement('div', { id, class: className || 'btn-group' });

            listItems?.forEach((item) => {
                if (item.type === 'buttonsGroup') return;

                if (item.type) {
                    const renderedHTML = cellRender(item);
                    if (!renderedHTML) return;

                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = renderedHTML;
                    const firstChild = tempDiv.firstChild;

                    if (item.type === 'dropdown' && firstChild.classList.contains('dropdown')) {
                        firstChild.classList.add('btn-group');
                    }

                    buttonsGroup.appendChild(firstChild);
                } else {
                    const { id, class: itemClass, title, disabled, icon, text, dataTags } = item;
                    const button = createElement('button', {
                        id,
                        class: `btn ${itemClass || ''}`,
                        title,
                        disabled: disabled ? 'disabled' : undefined,
                    });

                    applyDataTags(button, dataTags);
                    if (icon) button.appendChild(createElement('i', { class: `${icon} fa-fw me-2` }));
                    if (text) button.appendChild(document.createTextNode(text));
                    buttonsGroup.appendChild(button);
                }
            });

            return buttonsGroup.outerHTML;
        },
    };

    return renderers[type] ? renderers[type]() : document.createTextNode(data).outerHTML;
}
//endregion
// region alertNotify
/**
 * Returns a Bootstrap 5 alert element and appends it to the 'alertcontainer'.
 *
 * @param {string} type - Required. Bootstrap alert type: primary, secondary, success, danger, warning, info, light, or dark.
 * @param {string} text - Required. Text to display (use 'alert-link' class for links).
 * @param {string} [icon] - Optional. Icon class.
 * @param {string} [id] - Optional. Unique identifier (appends a unique number if omitted).
 * @param {number} [timeout] - Optional. Auto-dismiss timeout in milliseconds (0 = no auto-dismiss).
 * @param {Array} [buttons] - Optional. Array of button objects.
 * @param {string} buttons[].type - Button type: primary, secondary, success, etc.
 * @param {string} buttons[].text - Button text.
 * @param {string} [buttons[].icon] - Optional. Button icon class.
 * @param {Function | string} [buttons[].callback] - Optional. Callback function when button is clicked.
 * @returns {HTMLElement} - The created alert element.
 */
function alertNotify({ type, text, icon = '', id = '', timeout = 3000, buttons = [] }) {
    let container = document.querySelector('main > .toast-container');

    // Create container if it doesn't exist
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        const mainElement = document.querySelector('main');
        if (mainElement) {
            mainElement.appendChild(container);
        } else {
            document.body.appendChild(container);
        }
    }

    const isLight = ['warning', 'light'].includes(type);
    const toast = document.createElement('div');
    toast.className = 'toast shadow-lg border-0 rounded-3 mb-0';
    Object.assign(toast, { role: 'alert', 'aria-live': 'assertive', 'aria-atomic': 'true' });

    toast.innerHTML = `
        <div class="toast-header bg-${type} text-${isLight ? 'dark' : 'white'} rounded-top border-bottom-0">
            ${icon ? `<i class="${icon} me-2 ${isLight ? '' : 'text-white'}"></i>` : ''}
            <strong class="me-auto">${text}</strong>
            <button type="button" class="btn-close ${isLight ? '' : 'btn-close-white'}" data-bs-dismiss="toast"></button>
        </div>`;

    if (buttons.length) {
        const body = document.createElement('div');
        body.className = `toast-body d-flex gap-2 justify-content-end bg-${type}-subtle text-dark ${timeout <= 0 ? 'rounded-bottom' : ''}`;

        buttons.forEach(({ text, icon, type: btnType = 'primary', callback }) => {
            const btn = document.createElement('button');
            btn.className = `btn btn-sm btn-${btnType} shadow-sm`;
            btn.innerHTML = icon ? `<i class="${icon}"></i> ${text}` : text;
            btn.onclick = () => {
                // Only execute if callback is a function - never eval user input
                if (typeof callback === 'function') {
                    callback();
                } else if (typeof callback === 'string') {
                    // Log warning - string callbacks are deprecated for security
                    console.warn('String callbacks are deprecated. Use function references instead.');
                }
                bootstrap.Toast.getInstance(toast)?.hide();
            };
            body.appendChild(btn);
        });
        toast.appendChild(body);
    }

    if (timeout > 0) {
        const bar = document.createElement('div');
        bar.className = `bg-${type}`;
        Object.assign(bar.style, { height: '100%', width: '100%', transition: `width ${timeout}ms linear` });

        const wrapper = document.createElement('div');
        wrapper.className = 'rounded-bottom overflow-hidden';
        Object.assign(wrapper.style, { height: '4px', backgroundColor: 'rgba(0,0,0,0.1)', marginTop: '-1px' });

        wrapper.appendChild(bar);
        toast.appendChild(wrapper);
        setTimeout(() => (bar.style.width = '0%'), 50);
    }

    container.appendChild(toast);
    const bsToast = new bootstrap.Toast(toast, { autohide: timeout > 0 && !buttons.length, delay: timeout });
    bsToast.show();
    toast.addEventListener('hidden.bs.toast', () => toast.remove());

    return toast;
}
//endregion
// region serializeObject
$.fn.serializeObject = function () {
    var o = {};
    var a = this;
    $.each(a, function () {
        if (o[this.name] !== undefined) {
            if (this.type != 'radio' && !o[this.name].push) {
                o[this.name] = [o[this.name]];
            }
            if (this.type == 'select-multiple') {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    o[this.name].push(
                        $(this)
                            .select2('data')
                            .map(({ id }) => id),
                    );
                } else {
                    //TODO select without select2
                }
            } else if (this.type == 'select-one') {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    o[this.name].push(
                        $(this)
                            .select2('data')
                            .map(({ id }) => id)[0] || '',
                    );
                } else {
                    //TODO select without select2
                }
            } else if (this.type == 'checkbox') {
                if ($(this).attr('value') === undefined) {
                    o[this.name].push(+this.checked);
                } else if (this.checked) {
                    o[this.name].push(+this.value.trim());
                }
            } else if (this.type == 'radio') {
                if (this.checked) {
                    if (!o[this.name].push) {
                        o[this.name] = [o[this.name]];
                    }
                    o[this.name].push(this.value.trim());
                }
            } else {
                o[this.name].push(this.value.trim() || '');
            }
        } else {
            if (this.type == 'select-multiple') {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    o[this.name] = $(this)
                        .select2('data')
                        .map(({ id }) => id);
                } else {
                    //TODO select without select2
                }
            } else if (this.type == 'select-one') {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    o[this.name] =
                        $(this)
                            .select2('data')
                            .map(({ id }) => id)[0] || '';
                } else {
                    //TODO select without select2
                }
            } else if (this.type == 'checkbox') {
                if ($(this).attr('value') === undefined) {
                    o[this.name] = +this.checked;
                } else if (this.checked) {
                    o[this.name] = +this.value.trim();
                }
            } else if (this.type == 'radio') {
                if (this.checked) {
                    o[this.name] = this.value.trim();
                }
            } else {
                o[this.name] = this.value.trim() || '';
            }
        }
    });
    return o;
};
//endregion
// region functions
// Cleans XML styles from pasted Microsoft Word
function CleanPastedHTML(input) {
    // 1. remove line breaks / Mso classes
    var stringStripper = /(\n|\r| class=(")?Mso[a-zA-Z]+(")?)/g;
    var output = input.replace(stringStripper, ' ');
    // 2. strip Word generated HTML comments
    var commentSripper = new RegExp('<!--(.*?)-->', 'g');
    var output = output.replace(commentSripper, '');
    var tagStripper = new RegExp('<(/)*(meta|link|span|\\?xml:|st1:|o:|font)(.*?)>', 'gi');
    // 3. remove tags leave content if any
    output = output.replace(tagStripper, '');
    // 4. Remove everything in between and including tags '<style(.)style(.)>'
    var badTags = ['style', 'script', 'applet', 'embed', 'noframes', 'noscript'];
    for (var i = 0; i < badTags.length; i++) {
        tagStripper = new RegExp('<' + badTags[i] + '.*?' + badTags[i] + '(.*?)>', 'gi');
        output = output.replace(tagStripper, '');
    }
    // 5. remove attributes ' style="..."'
    var badAttributes = ['style', 'start', 'lang'];
    for (var i = 0; i < badAttributes.length; i++) {
        var attributeStripper = new RegExp(' ' + badAttributes[i] + '="(.*?)"', 'gi');
        output = output.replace(attributeStripper, '');
    }

    return output;
}

//Get the button
let bttButton = document.getElementById('btn-back-to-top');

// When the user scrolls down 20px from the top of the document, show the button
window.onscroll = function () {
    scrollFunction();
};

function scrollFunction() {
    if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        bttButton.style.display = 'block';
    } else {
        bttButton.style.display = 'none';
    }
}

// When the user clicks on the button, scroll to the top of the document
bttButton.addEventListener('click', backToTop);

function backToTop() {
    document.body.scrollTop = 0;
    document.documentElement.scrollTop = 0;
}

// XHR spinner
var $loading = $('#spinner-div').hide();
$(document)
    .ajaxStart(function () {
        $loading.show();
    })
    .ajaxStop(function () {
        $loading.hide();
    });

// Global CSRF token interceptor for AJAX requests
$(document).ready(function() {
    $.ajaxSetup({
        beforeSend: function(xhr, settings) {
            // Only add CSRF token to state-changing requests
            if (['POST', 'PUT', 'DELETE', 'PATCH'].includes(settings.type.toUpperCase())) {
                var csrfToken = document.querySelector('meta[name="csrf-token"]');
                if (csrfToken) {
                    xhr.setRequestHeader('X-CSRF-Token', csrfToken.content);
                }
            }
        },
        complete: function(xhr, status) {
            // Check for session expiration in response
            try {
                // Try to parse as JSON
                if (xhr.responseText) {
                    var response = JSON.parse(xhr.responseText);
                    
                    // Check if session expired
                    if (response.session_expired === true || 
                        (response.success === false && response.redirect === '/signout')) {
                            
                        // Show notification with message from server
                        alertNotify({
                            type: 'warning',
                            text: headLabels.nteSessionExpired,
                            icon: 'fas fa-exclamation-triangle',
                            timeout: 3000,
                        });
                        
                        // Redirect to signin
                        setTimeout(function() {
                            window.location.href = '/signout';
                        }, 2000);
                    }
                }
            } catch(e) {
                // Not JSON, ignore
                // Check HTTP status 401 (Unauthorized)
                if (xhr.status === 401) {
                    console.warn('Received 401 Unauthorized, checking for session expiration...');
                    window.location.href = '/signout';
                }
            }
        }
    });
});

// Initials for user icons
try {
    $('.profileContact').initial();
} catch (err) {
    console.log('Initial unavailable');
}

// Date pasre functions for easy date input
function localeDateObject(locale = chrLocale) {
    var positions = {};
    var strDate = new Date(2023, 10, 12).toLocaleString(locale);
    positions.month = strDate.indexOf('11');
    positions.date = strDate.indexOf('12');
    if (strDate.indexOf('2023') > -1) {
        positions.year = strDate.indexOf('2023');
    } else {
        positions.year = strDate.indexOf('23');
    }

    var result = Object.entries(positions)
        .sort(([, a], [, b]) => a - b)
        .reduce((r, [k, v]) => ({ ...r, [k]: v }), {});

    return result;
}

function parseTinyDate(dateString, locale = chrLocale) {
    var result = localeDateObject(locale);
    result[Object.keys(result)[0]] = parseInt(dateString.substring(0, 2));
    result[Object.keys(result)[1]] = parseInt(dateString.substring(2, 4));
    result[Object.keys(result)[2]] = parseInt(dateString.substring(4, 6));
    if (result.year > 20) {
        result.month--;
        result.year += 2000;
        result = new Date(result.year, result.month, result.date).toLocaleDateString(locale);
    } else {
        result = false;
    }

    return result;
}

function parseShortDate(dateString, locale = chrLocale) {
    var result = localeDateObject(locale);
    var i = 0;
    var o = 0;
    while (i < 8) {
        if (Object.keys(result)[o] == 'year') {
            result[Object.keys(result)[o]] = parseInt(dateString.substring(i, i + 4));
            i += 4;
        } else {
            result[Object.keys(result)[o]] = parseInt(dateString.substring(i, i + 2));
            i += 2;
        }
        o++;
    }
    result.month--;

    result = new Date(result.year, result.month, result.date).toLocaleDateString(locale);

    return result;
}

function formatWithLeadingZeros(value) {
    return value.toString().padStart(2, '0');
}

function formatDateWithLeadingZeros(date) {
    var dateParts = date.match(/(\d{1,2})\/(\d{1,2})\/(\d{4})/);
    if (dateParts) {
        var month = formatWithLeadingZeros(dateParts[1]);
        var day = formatWithLeadingZeros(dateParts[2]);
        var year = dateParts[3];
        return `${month}/${day}/${year}`;
    }
    return date;
}

const fixMonthAbbreviation = (input, lang) => {
    const replacements = {
        es: { sep: 'sept' },
        de: { sep: 'sept' },
    };

    if (replacements[lang]) {
        for (const [wrong, correct] of Object.entries(replacements[lang])) {
            const regex = new RegExp(`\\b${wrong}\\b`, 'i');
            input = input.replace(regex, correct);
        }
    }
    return input;
};

function dateSqlToShort(sqlDate, locale = 'en') {
    return DateTime.fromSQL(sqlDate).setLocale(locale).toFormat('dd-MMM-yyyy').replace(/\./g, ''); // Removes any periods in the string
}

function dateSqlToShortTime(sqlDate, locale = 'en') {
    return DateTime.fromSQL(sqlDate).setLocale(locale).toFormat('dd-MMM-yyyy HH:mm').replace(/\./g, ''); // Removes any periods in the string
}

function dateShortToSql(shortDate, lang = 'en') {
    const parsedDate = DateTime.fromFormat(fixMonthAbbreviation(shortDate, lang), 'dd-MMM-yyyy', { locale: lang });

    return parsedDate.isValid ? parsedDate.toSQLDate() : null;
}

function monthsToShortNames(monthsRange, locale = 'en') {
    const monthsStr = typeof monthsRange === 'string' ? monthsRange : String(monthsRange || '');
    const months = monthsStr
        .split(',')
        .map((m) => Number(m.trim()))
        .filter((m) => !isNaN(m));

    if (!months.every((month) => month >= 1 && month <= 12)) {
        throw new Error('Invalid month value. Months must be between 1 and 12.');
    }

    const monthNames = months.map((month) => DateTime.fromObject({ month }, { locale }).toFormat('MMM'));
    return monthNames.join(', ');
}

function arrayColumn(input, columnKey, indexKey = null) {
    return input.map(function (value, index) {
        if (indexKey && value[indexKey]) {
            if (columnKey) {
                return { [value[indexKey]]: value[columnKey] };
            } else {
                return { [value[indexKey]]: value };
            }
        } else {
            if (columnKey) {
                return value[columnKey];
            } else {
                return value;
            }
        }
    });
}

// Specific for exporting Financials tables// Specific for exporting Financials tables
function extractTableData(tableId) {

    var table = document.getElementById(tableId);
    if (!table) return [];

    var headers = table.querySelectorAll('thead th');

    var headerData = Array.from(headers).map((header) => {

        // Buscar si el th contiene un span con title
        var spanWithTitle = header.querySelector('span[title]');

        if (spanWithTitle) {
            return spanWithTitle.getAttribute('title').trim();
        }

        // fallback normal
        return header.innerText.trim();
    });

    var rows = table.querySelectorAll('tbody tr');
    var data = [];

    data.push(headerData);

    rows.forEach((row) => {
        var cols = row.querySelectorAll('td');
        var rowData = formatValuesExport(cols);
        data.push(rowData);
    });


    return data;
}


function formatValuesExport(cols) {
    return Array.from(cols)
        .map((col) => {
            // Extrae los titulos reales del tooltip en lugar del texto truncado.
            var elementWithTitle = col.querySelector('[title]');
            if (elementWithTitle) {
                var fullValue = elementWithTitle.getAttribute('title');
                if (fullValue && fullValue.trim() !== '') {
                    return fullValue.trim();
                }
            }
            // Verificar si hay íconos de check o times
            var icon = col.querySelector('i');
            if (icon) {
                if (icon.classList.contains('fa-check')) {
                    return '✔';
                } else if (icon.classList.contains('fa-times')) {
                    return '✘';
                }
            }

            var value = col.textContent.trim();
            if (value === '') {
                return ' ';
            }

            var select = col.querySelector('select.select2-hidden-accessible');
            if (select) {
                var selectedOption = select.options[select.selectedIndex];
                return selectedOption ? selectedOption.text.trim() : '';
            }

            // Verificar si es una fecha válida usando dateShortToSql
            var sqlDate = dateShortToSql(value, chrLocale);
            if (sqlDate) {
                return { __excelDate: sqlDate };
            }

            var currencyMatch = value.match(/^([A-Z]{3})\s*/)
            if (currencyMatch) {
                var amountPart = value.replace(currencyMatch[0], '').trim()
                if (amountPart && !isNaN(amountPart.replace(',', '.'))) {
                    return parseFloat(amountPart.replace(',', '.'))
                } else {
                    return value
                }
            }

            if (!isNaN(value.replace(',', '.')) && value !== '') {
                return parseFloat(value.replace(',', '.'))
            }

            if (/[A-Za-z]/.test(value)) {
                return value
            }

            // Manejar valores numéricos con espacios o comas
            if (value.includes(' ') || value.includes(',')) {
                value = value.replace(/\s+/g, '').replace(',', '.');
                if (!isNaN(value)) {
                    value = parseFloat(value).toFixed(2);
                }
            }

            return isNaN(value) ? value : parseFloat(value);
        })
        .filter((value) => value !== null); // Filtra valores nulos
}
//End Specific for exporting Financials tables

const formatNumber = (number, locale, digits = 2) => {
    return new Intl.NumberFormat(locale, {
        minimumFractionDigits: digits,
        maximumFractionDigits: digits,
    }).format(number);
};

const formatCurrency = (currency, number, locale) => {
    return new Intl.NumberFormat(locale, {
        style: 'currency',
        currency,
    }).format(number);
};

const parseNumber = (formattedNumber, locale) => {
    // Get the decimal and thousand separators for the locale
    const parts = new Intl.NumberFormat(locale).formatToParts(1234.5);
    const decimalSeparator = parts.find((part) => part.type === 'decimal')?.value || '.';
    const groupSeparator = parts.find((part) => part.type === 'group')?.value || ',';

    // Simple approach: split and remove all non-numeric characters except the decimal separator
    let normalized = formattedNumber;
    normalized = normalized.replace(decimalSeparator, '|||DECIMAL|||'); // First, replace the decimal separator with a placeholder
    normalized = normalized.replace(/[^0-9|||DECIMAL|||]/g, ''); // Remove all non-digit characters
    normalized = normalized.replace('|||DECIMAL|||', '.'); // Replace placeholder with dot

    return parseFloat(normalized);
};

const parseCurrency = (formattedCurrency, locale, returnCurrency = false) => {
    // Get the decimal and thousand separators for the locale
    const parts = new Intl.NumberFormat(locale).formatToParts(1234.5);
    const decimalSeparator = parts.find((part) => part.type === 'decimal')?.value || '.';

    let currency = null;

    // First, try to find currency symbols (including prefixed ones like US$, CA$, A$, etc.)
    const currencySymbolMatch = formattedCurrency.match(/[A-Z]{0,2}[$€£¥₹₽¢₡₩₪₦]/);

    if (currencySymbolMatch) {
        // Convert symbol (with potential prefix) to currency code
        const symbol = currencySymbolMatch[0];
        currency =
            Intl.supportedValuesOf('currency').find((code) => {
                const parts = new Intl.NumberFormat(locale, {
                    style: 'currency',
                    currency: code,
                }).formatToParts(1);
                return parts.find((p) => p.type === 'currency')?.value === symbol;
            }) || null;
    }

    // If no symbol match, try to find a standalone 3-letter currency code
    if (!currency) {
        const currencyCodeMatch = formattedCurrency.match(/\b[A-Z]{3}\b/);
        if (currencyCodeMatch) {
            const code = currencyCodeMatch[0];
            try {
                new Intl.NumberFormat(locale, { style: 'currency', currency: code });
                currency = code;
            } catch (e) {
                currency = null;
            }
        }
    }

    // Remove currency codes, symbols, and prefixes
    let cleanedNumber = formattedCurrency
        .replace(/\b[A-Z]{3}\b/, '') // Remove standalone 3-letter codes
        .replace(/[A-Z]{0,2}[$€£¥₹₽¢₡₩₪₦]/g, '') // Remove symbols with prefixes
        .trim();

    cleanedNumber = cleanedNumber.replace(decimalSeparator, '|||DECIMAL|||'); // First, replace the decimal separator with a placeholder
    cleanedNumber = cleanedNumber.replace(/[^0-9|||DECIMAL|||]/g, ''); // Remove all non-digit characters
    cleanedNumber = cleanedNumber.replace('|||DECIMAL|||', '.'); // Replace placeholder with dot

    const number = parseFloat(cleanedNumber);

    return returnCurrency ? { number, currency } : number;
};

const hasSqBrackets = (string) => {
    const stripped = string.replace(/\[|\]/g, '');
    return stripped !== string;
};

const removeSqBrackets = (string) => {
    return string.replace(/\[|\]/g, '');
};

function toMixedCase(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function createStrongLabel(labelText) {
    var strong = document.createElement('strong');
    strong.textContent = labelText;
    return strong;
}
//endregion
// region datatables
var dt_scrollPos = 0;

/**
 * Stores the relational scroll position for data tables.
 *
 * @param {object} table - Required. Datatable object(variable).
 * @param {boolean} reload - Optional. Ajax reload after storage.
 */
function saveDTScrollPos(table, reload = false) {
    if ($.fn.dataTable.isDataTable(table)) {
        dt_scrollPos = $(table.table().node()).parent().scrollTop() / $(table.table().node()).parent().get(0).scrollHeight;
    } else {
        dt_scrollPos = 0;
    }
    if (reload) {
        table.ajax.reload();
    }
}

/**
 * Scrolls the data table to the relational scroll position.
 *
 * @param {object} table - Required. Datatable object(variable).
 */
function setDTScrollPos(table) {
    $(table.table().node())
        .parent()
        .scrollTop($(table.table().node()).parent().get(0).scrollHeight * dt_scrollPos);
}
//endregion

// Function to escape HTML characters in datatables
function escapeHtml(unsafe) {
    return unsafe.replace(/&/g, '&amp;').replace(/</g, '<').replace(/>/g, '>').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}

//Function to convert special dates to Excel format and record date cells
function formatExcelDates(data) {
    var dateCells = [];
    var formattedData = data.map((row) => (row ? [...row] : row));

    for (var i = 0; i < formattedData.length; i++) {
        var row = formattedData[i];
        if (!row) continue;

        for (var j = 0; j < row.length; j++) {
            var cell = row[j];
            if (cell && typeof cell === 'object' && cell.__excelDate) {
                var sqlDate = cell.__excelDate;
                var dateObj = new Date(sqlDate + 'T00:00:00Z');
                if (!isNaN(dateObj.getTime())) {
                    var excelEpoch = Date.UTC(1899, 11, 30);
                    var excelDate = (dateObj.getTime() - excelEpoch) / (1000 * 60 * 60 * 24);
                    row[j] = excelDate;
                    dateCells.push({ r: i, c: j });
                } else {
                    row[j] = sqlDate;
                }
            }
        }
    }

    var worksheet = XLSX.utils.aoa_to_sheet(formattedData);

    dateCells.forEach(function (cell) {
        var cellRef = XLSX.utils.encode_cell({ r: cell.r, c: cell.c });
        if (worksheet[cellRef]) {
            worksheet[cellRef].t = 'n'; // tipo número
            worksheet[cellRef].z = 'yyyy-mm-dd'; // formato fecha
        }
    });

    return worksheet;
}

// Function to format values for divs to excel
function formatTable(table) {
    table.find('td').each(function () {
        var value = $(this).text().trim();

        if (!value) return;

        var sqlDate = dateShortToSql(value, chrLocale);
        if (sqlDate) {
            $(this).text(sqlDate);
            return;
        }

        var numValue = value.replace(/\s+/g, '').replace(/,(\d{2})$/, '.$1');

        if (!isNaN(numValue) && numValue !== '') {
            $(this).text(parseFloat(numValue).toFixed(2));
        }
    });
}

// Functions to create dynamic SearchPanes and show or hide them
// Function to create button
function createFilterMenu($container, table, activeIndices = [], toggleId = 'toggleFilterMenu', labels = {}, extraPanes = []) {
    if ($(`#${toggleId}`).length > 0) return;

    let $colFilters = $('<div class="col d-flex align-items-center justify-content-start"></div>');
    let $dropdownWrapper = $('<div class="dropdown"></div>');

    let $filterBtn = $(` 
        <button type="button" id="${toggleId}"
                class="btn btn-sm btn-outline-secondary dropdown-toggle ms-2"
                data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-filter me-1"></i>${labels.lblFilter || 'Filters'}
        </button>
    `);

    let $dropdownMenu = $('<div class="dropdown-menu p-2 shadow" id="filterMenu"></div>');

    // Existing columns in the table
    table.settings()[0].aoColumns.forEach((col, index) => {
        // Excluir columnas con la clase "no-filter"
        if (col.className && col.className.includes('no-filter')) return;

        let isChecked = activeIndices.includes(index);
        let $item = $(`
            <div class="form-check form-switch">
                <input class="form-check-input filter-pane-toggle" type="checkbox"
                    id="filter_${index}" data-col="${index}" ${isChecked ? 'checked' : ''}>
                <label class="form-check-label small" for="filter_${index}">
                    ${col.title || col.data}
                </label>
            </div>
        `);
        $dropdownMenu.append($item);

        // Ocultar los panes de búsqueda de las columnas con "no-filter"
        if (col.className && col.className.includes('no-filter')) {
            $('.dtsp-panesContainer .dtsp-searchPane').each(function () {
                if ($(this).data('column-index') === index) {
                    $(this).hide();
                }
            });
        }
    });

    // Add extra panes if provided
    extraPanes.forEach((pane) => {
        let $item = $(`
            <div class="form-check form-switch">
                <input class="form-check-input filter-pane-toggle" type="checkbox"
                    id="filter_${pane.id}" data-col="${pane.id}" checked>
                <label class="form-check-label small" for="filter_${pane.id}">
                    ${pane.header}
                </label>
            </div>
        `);
        $dropdownMenu.append($item);
    });

    $dropdownWrapper.append($filterBtn).append($dropdownMenu);
    $colFilters.append($dropdownWrapper);
    $container.append($colFilters);

    // Mantener consistencia con SearchPanes
    $('.dtsp-nameButton, .dtsp-countButton').remove();
    $('.dtsp-panesContainer .dtsp-searchPane').each(function (index) {
        $(this).attr('data-column-index', index);
    });
}

//Functions to restore Visibility after ajax reload in table
function restorePaneVisibility(table, paneVisibility) {
    if (!table.searchPanes || !table.searchPanes.container()) return;

    setTimeout(() => {
        table.searchPanes.rebuildPane();
        table.searchPanes
            .container()
            .find('.dtsp-searchPane')
            .each(function () {
                const colIndex = $(this).attr('data-column-index');
                if (paneVisibility[colIndex] === false) {
                    $(this).addClass('dtsp-hidden').css('display', 'none');
                    $(`#filter_${colIndex}`).prop('checked', false);
                } else {
                    $(this).removeClass('dtsp-hidden').css('display', 'block');
                    $(`#filter_${colIndex}`).prop('checked', true);
                }
            });
    }, 50);
}

function getPaneVisibility(table) {
    let paneVisibility = {};
    table.searchPanes
        .container()
        .find('.dtsp-searchPane')
        .each(function () {
            const colIndex = $(this).attr('data-column-index');
            paneVisibility[colIndex] = $(this).css('display') !== 'none';
        });
    return paneVisibility;
}
// Code for close searchpaned when button .close is clicked
$(document).on('click', '.dtsp-searchPane .clearButton', function () {
    const $pane = $(this).closest('.dtsp-searchPane');
    setTimeout(() => {
        const $collapseButton = $pane.find('.dtsp-collapseButton');
        if ($collapseButton.length) {
            if ($collapseButton.attr('aria-expanded') !== 'false') {
                $collapseButton.click();
            }
        }
    }, 200);
});

// Functions to create total
function createFooterOnce(tableId, columnsCount) {
    const $table = $(`#${tableId}`);
    if ($table.find('tfoot').length === 0) {
        let footerRow = '<tr>';
        for (let i = 0; i < columnsCount; i++) {
            footerRow += i === 0 ? '<th>Total</th>' : '<th></th>';
        }
        footerRow += '</tr>';
        $table.append('<tfoot>' + footerRow + '</tfoot>');
    }
}

function applyFooterTotals(tableId, sumColumns) {
    const api = $(`#${tableId}`).DataTable();
    const rowsData = api.rows({ search: 'applied' }).data();
    const columns = api.settings()[0].aoColumns;

    sumColumns.forEach((colIndex) => {
        let total = 0;

        rowsData.each((row) => {
            let value = parseFloat(row[columns[colIndex].data]) || 0;
            total += value;
        });

        const formatted = objIntl.format(total).replace('USD', '').trim();
        const col = api.column(colIndex);

        $(col.footer()).html(formatted).data('storedTotal', formatted).css({ 'text-align': 'right', 'font-weight': 'bold' });

        if (!col.visible()) {
            $(col.footer()).hide();
        } else {
            $(col.footer()).show();
        }
    });
}
//endregion
//region Clean SearchPanes
function cleanSearchPanes() {
    const containers = document.querySelectorAll('.dtsp-panes.dtsp-panesContainer');
    containers.forEach((container) => {
        Array.from(container.childNodes)
            .filter((node) => node.nodeType === Node.TEXT_NODE && node.textContent.trim() !== '')
            .forEach((node) => node.remove());
    });
}
//endregion
//region select2 autofocus
$(document).on('select2:open', function (e) {
    const select2 = $(e.target).data('select2');
    if (!select2.options.get('multiple')) {
        select2.dropdown.$search.get(0).focus();
    }
});
//endregion
//#region truncateTitle
function truncateTitle(text, maxLength) {
    if (text.length <= maxLength) {
        return text;
    }

    const short = text.substring(0, maxLength) + '…';
    return `<span title="${text}">${short}</span>`;
}
//#endregion

//#region Validators
$('.onlyNumeric')
    .keypress(function (e) {
        if (isNaN(this.value + String.fromCharCode(e.charCode))) return false
    })
    .on('cut copy paste', function (e) {
        e.preventDefault()
    });
//#endregion

/**
 * Redirects to a URL using the specified method and target.
 * @param {string} url - The destination URL
 * @param {string} [method='GET'] - HTTP method: 'GET' or 'POST'
 * @param {string} [target='_self'] - Where to open: '_self', '_blank', '_parent', '_top'
 * @param {Object} [data={}] - Key/value pairs to send as POST data
 */
function redirect(url, method = 'GET', target = '_self', data = {}) {
  if (!url) throw new Error('URL is required');

  const upperMethod = method.toUpperCase();

  if (upperMethod === 'GET') {
    // Append data as query params if provided
    if (Object.keys(data).length > 0) {
      const params = new URLSearchParams(data).toString();
      url = `${url}${url.includes('?') ? '&' : '?'}${params}`;
    }

    if (target === '_self') {
      window.location.href = url;
    } else {
      window.open(url, target);
    }

  } else if (upperMethod === 'POST') {
    // Build a temporary form and submit it
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    form.target = target;

    Object.entries(data).forEach(([key, value]) => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = key;
      input.value = value;
      form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);

  } else {
    throw new Error(`Unsupported method: "${method}". Use 'GET' or 'POST'.`);
  }
}

// Event Switcher Handler
$(document).on('click', '.event-item', function (e) {
    e.preventDefault();
    var id = $(this).data('id');
    $.ajax({
        type: "POST",
        url: "/controller/SwitchEvent",
        data: { id: id },
        dataType: "json",
        success: function (response) {
            if (response.data && response.data.result) {
                window.location.reload(true); // recarga completa
            } else {
                alertNotify({
                    type: 'danger',
                    text: response.data?.error || 'Error switching event',
                    icon: 'fa fa-exclamation-triangle'
                });
            }
        },
        error: function () {
            alertNotify({
                type: 'danger',
                text: 'Communication error',
                icon: 'fa fa-exclamation-triangle'
            });
        }
    });
});
