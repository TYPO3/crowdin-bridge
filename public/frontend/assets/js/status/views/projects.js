// View - projects (extensions) from status.json
const sourceCrowdin = 'https://crowdin.com/project/';
const sourceTYPO3ExtensionRepository = 'https://extensions.typo3.org/extension/';

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
    crowdinUrl: (row, languageKey) => `${sourceCrowdin}${row.crowdinKey}/${languageKey}`,
};

// Render extension with link to crowdin project & source (currently TER only)
function renderCellExtension(params) {
    const link_crowdin = `<a href="${sourceCrowdin}${params.data.crowdinKey}" target="_blank" title="Crowdin">${params.data.extensionKey}</a>`;
    // Render admonition for extension state (translationsAvailable)
    let availability = '';
    if (params.data.translationsAvailable === false) {
        availability += ' <span class="t3-admonition t3-admonition-warning" role="alert" title="No translations available"></span>';
    }
    return ` ${(link_crowdin) + availability}`;
}
