// Render cell - crowdin language (link)
export function renderCellLanguage(params) {
    const view = params.context.view;
    const usable = view.isUsable(params.data);
    const dataApprovals = params.data.approvals[params.colDef.t3LanguageKey];
    const dataTranslations = params.data.translations[params.colDef.t3LanguageKey];
    let resultStyleAdditionalClasses = 't3-cell-element';
    if (usable && dataTranslations >= 80) {
        resultStyleAdditionalClasses += ' t3-cell-element-success';
    }
    if (usable && dataTranslations >= 50 && dataTranslations < 80) {
        resultStyleAdditionalClasses += ' t3-cell-element-warning';
    }
    if (usable && dataTranslations < 50) {
        resultStyleAdditionalClasses += ' t3-cell-element-danger';
    }
    if (usable && typeof dataTranslations === "number") {
        resultStyleAdditionalClasses += ' t3-cell-element-unit-percent';
    }
    if (usable && (dataApprovals !== dataTranslations)) {
        resultStyleAdditionalClasses += ' t3-cell-element-lang-approval-missing';
    }
    const resultInfo = `${dataApprovals} / ${dataTranslations}`
    if (usable && typeof dataTranslations === "number") {
        return `<a href="${view.crowdinUrl(params.data, params.colDef.t3LanguageKey)}" target="_blank"><span class="${resultStyleAdditionalClasses}">${resultInfo}</span></a>`;
    } else {
        return `<span class="${resultStyleAdditionalClasses}">-</span>`;
    }
}

// Render tooltip - language (state)
export function renderTooltipLanguage(params) {
    const usable = params.context.view.isUsable(params.data);
    const dataApprovals = params.data.approvals[params.colDef.t3LanguageKey];
    const dataTranslations = params.data.translations[params.colDef.t3LanguageKey];
    if (usable && (dataApprovals !== dataTranslations)) {
        return `${dataTranslations - dataApprovals}% Needs approval`;
    } else {
        return '';
    }
}
