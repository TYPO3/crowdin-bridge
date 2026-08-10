/*!
 * Data grid for status page at https://localize.typo3.org/
 */
/*!
 * AG Grid community v34.3.1 by AG Grid Ltd. - https://www.ag-grid.com
 * License - https://www.ag-grid.com/eula/AG-Grid-Community-License.html (MIT License)
 */

// Config - sources
const sourceCrowdin = 'https://crowdin.com/project/';
const sourceTYPO3ExtensionRepository = 'https://extensions.typo3.org/extension/';
const sourceJsonData = 'status.json';

let gridApi;

// Init grid
document.addEventListener('DOMContentLoaded', async () => {
    try {
        // Fetch data
        const jsonData = await fetchJsonData(sourceJsonData);

        // Define grid
        const columnDefsLanguages = [];
        if (jsonData.languages) {
            for (const [key, value] of Object.entries(jsonData.languages)) {
                columnDefsLanguages.push({
                    cellRenderer: renderCellProjectLanguage,
                    colId: key,
                    filter: false,
                    headerClass: 't3-header-cell-language',
                    headerName: value,
                    headerTooltip: key,
                    sortable: true,
                    sortingOrder: ['desc', 'asc', null],
                    tooltipValueGetter: renderTooltipProjectLanguage,
                    unSortIcon: true,
                    valueGetter: `(typeof data.translations['${key}'] === "number") ? data.translations['${key}'] : -1`,
                    t3LanguageKey: key,
                });
            }
        }

        const columnDefs = [
            {
                headerName: 'Extension',
                cellRenderer: renderCellExtension,
                field: 'extensionKey',
                filter: true,
                lockPinned: true,
                pinned: 'left',
                sortable: true,
                width: 260,
            },
            ...columnDefsLanguages
        ];

        const defaultColDef = {
            filter: false,
            flex: 1,
            initialWidth: 100,
            minWidth: 100,
            sortable: false,
        };

        const gridOptions = {
            alwaysShowHorizontalScroll: true,
            columnDefs: columnDefs,
            defaultColDef: defaultColDef,
            headerHeight: 110,
            tooltipShowDelay: 500,
            tooltipHideDelay: 5000,
        };

        const gridDiv = document.querySelector('#ag-grid');

        // Create grid
        gridApi = agGrid.createGrid(gridDiv, gridOptions);
        gridApi.setGridOption('rowData', jsonData.projects);

    } catch (error) {
        console.error('Error during grid setup:', error);
    }
});

// Fetch json from given source
async function fetchJsonData(sourceJson) {
    try {
        const response = await fetch(sourceJson);
        return await response.json();
    } catch (error) {
        console.error('Error fetching JSON:', error);
        throw error; // Re-throw error to be handled in DOMContentLoaded
    }
}

// Render extension with link to crowdin project & source (currently TER only)
function renderCellExtension(params) {
    const link_crowdin = `<a href="${sourceCrowdin}${params.data.crowdinKey}" target="_blank" title="Crowdin">${params.data.extensionKey}</a>`;
    // Source link only for TER extensions
    let link_src;
    if ( params.data.extensionKey != 'typo3-cms') {
        link_src = `<a href="${sourceTYPO3ExtensionRepository}${params.data.extensionKey}" target="_blank" title="TYPO3 extension repository">ter</a>`;
    }
    // Render admonition for extension state (translationsAvailable)
    let availability = '';
    if (params.data.translationsAvailable === false) {
        availability += ' <span class="t3-admonition t3-admonition-warning" role="alert" title="No translations available"></span>';
    }
    return ` ${(link_crowdin) + (link_src ? ' | ' + link_src : '') + availability}`;
}

// Render cell - crowdin project language (link)
function renderCellProjectLanguage(params) {
    const dataApprovals = params.data.approvals[params.colDef.t3LanguageKey];
    const dataTranslations = params.data.translations[params.colDef.t3LanguageKey];
    let resultStyleAdditionalClasses = 't3-cell-element';
    if (params.data.usable && dataTranslations >= 80) {
        resultStyleAdditionalClasses += ' t3-cell-element-success';
    }
    if (params.data.usable && dataTranslations >= 50 && dataTranslations < 80) {
        resultStyleAdditionalClasses += ' t3-cell-element-warning';
    }
    if (params.data.usable && dataTranslations < 50) {
        resultStyleAdditionalClasses += ' t3-cell-element-danger';
    }
    if (params.data.usable && typeof dataTranslations === "number") {
        resultStyleAdditionalClasses += ' t3-cell-element-unit-percent';
    }
    if (params.data.usable && (dataApprovals !== dataTranslations)) {
        resultStyleAdditionalClasses += ' t3-cell-element-lang-approval-missing';
    }
    const resultInfo = `${dataApprovals} / ${dataTranslations}`
    if (params.data.usable && typeof dataTranslations === "number") {
        return `<a href="${sourceCrowdin}${params.data.crowdinKey}/${params.colDef.t3LanguageKey}" target="_blank"><span class="${resultStyleAdditionalClasses}">${resultInfo}</span></a>`;
    } else {
        return `<span class="${resultStyleAdditionalClasses}">-</span>`;
    }
}

// Render tooltip - project language (state)
function renderTooltipProjectLanguage(params) {
    const dataApprovals = params.data.approvals[params.colDef.t3LanguageKey];
    const dataTranslations = params.data.translations[params.colDef.t3LanguageKey];
    if (params.data.usable && (dataApprovals !== dataTranslations)) {
        return `${dataTranslations - dataApprovals}% Needs approval`;
    } else {
        return '';
    }
}
