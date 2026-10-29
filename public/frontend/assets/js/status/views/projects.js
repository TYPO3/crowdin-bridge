import { crowdinUrl, escapeHtml } from '../render-language.js';

export default {
    source: 'status.json',
    rows: (jsonData) => jsonData.projects,
    firstColumn: {
        headerName: 'Extension',
        cellRenderer: renderCellExtension,
        field: 'extensionKey',
        filter: true,
        lockPinned: true,
        pinned: 'left',
        sortable: true,
        width: 260,
    },
    isUsable: (row) => row.usable,
    crowdinUrlPath: (row, languageKey) => `${row.crowdinKey}/${languageKey}`,
};

// Render extension with link to crowdin project
function renderCellExtension(params) {
    const link_crowdin = `<a href="${crowdinUrl(params.data.crowdinKey)}" target="_blank" title="Crowdin">${escapeHtml(params.data.extensionKey)}</a>`;
    // Render admonition for extension state (translationsAvailable)
    let availability = '';
    if (params.data.translationsAvailable === false) {
        availability += ' <span class="t3-admonition t3-admonition-warning" role="alert" title="No translations available"></span>';
    }
    return ` ${(link_crowdin) + availability}`;
}
