import { fetchJsonData, formatStatusDate } from './data.js';
import { renderCellLanguage, renderTooltipLanguage } from './render-language.js';

let gridApi;

// Load data for given view and create grid
export async function loadGrid(view) {
    try {
        // Fetch data
        const jsonData = await fetchJsonData(view.source);

        // Replace date
        if (jsonData.date) {
            const statusDate = formatStatusDate(jsonData.date);
            document.title = document.title + ' ' + statusDate;
            document.body.innerHTML = document.body.innerHTML.replaceAll('<!-- data-status-date -->', statusDate);
        }

        // Define grid
        const columnDefs = [
            view.firstColumn,
            ...buildLanguageColumns(jsonData.languages),
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
            context: { view },
            defaultColDef: defaultColDef,
            headerHeight: 110,
            tooltipShowDelay: 500,
            tooltipHideDelay: 5000,
            ...view.gridOptions,
        };

        const gridDiv = document.querySelector('#ag-grid');

        // Create grid
        gridApi = agGrid.createGrid(gridDiv, gridOptions);
        gridApi.setGridOption('rowData', view.rows(jsonData));

    } catch (error) {
        console.error('Error during grid setup:', error);
    }
}

// Build one column per language
function buildLanguageColumns(languages) {
    const columnDefsLanguages = [];
    if (languages) {
        for (const [key, value] of Object.entries(languages)) {
            columnDefsLanguages.push({
                cellRenderer: renderCellLanguage,
                colId: key,
                filter: false,
                headerClass: 't3-header-cell-language',
                headerName: value,
                headerTooltip: key,
                sortable: true,
                sortingOrder: ['desc', 'asc', null],
                tooltipValueGetter: renderTooltipLanguage,
                unSortIcon: true,
                valueGetter: `(typeof data.translations['${key}'] === "number") ? data.translations['${key}'] : -1`,
                t3LanguageKey: key,
            });
        }
    }
    return columnDefsLanguages;
}
