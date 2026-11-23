import { escapeHtml } from '../render-language.js';

const crowdinKey = 'typo3-cms';
const expandedBranches = new Set();

export default {
    source: 'status_core.json',
    rows: (jsonData) => buildRows(jsonData.branches),
    firstColumn: {
        headerName: 'TYPO3 CMS',
        cellRenderer: renderCellBranch,
        colId: 'branch',
        lockPinned: true,
        pinned: 'left',
        width: 260,
    },
    gridOptions: {
        onCellClicked: toggleBranch,
        onCellKeyDown: toggleBranchByKey,
        postSortRows: keepExtensionsBelowBranch,
        isExternalFilterPresent: () => true,
        doesExternalFilterPass: isRowExpanded,
    },
    isUsable: () => true,
    crowdinUrlPath: (row, languageKey) => `${crowdinKey}/${languageKey}`,
};

// Flatten branches into branch rows, each followed by its extension rows
function buildRows(branches) {
    const rows = [];
    for (const branch of branches ?? []) {
        rows.push({ branch: branch.branch, translations: branch.translations, approvals: branch.approvals });
        for (const [extension, status] of Object.entries(branch.extensions ?? {})) {
            rows.push({ branch: branch.branch, extension, translations: status.translations, approvals: status.approvals });
        }
    }
    return rows;
}

// Render branch with toggle, or indented extension key
function renderCellBranch(params) {
    if (params.data.extension) {
        return `<span class="t3-cell-core-extension">${escapeHtml(params.data.extension)}</span>`;
    }
    const expanded = expandedBranches.has(params.data.branch);
    return `<span class="t3-cell-core-branch" role="button" aria-expanded="${expanded}">`
        + `<span aria-hidden="true">${expanded ? '▾' : '▸'}</span> ${escapeHtml(params.data.branch)}</span>`;
}

// Expand/collapse extensions of clicked branch
function toggleBranch(params) {
    if (params.column.getColId() !== 'branch' || params.data.extension) {
        return false;
    }
    const branch = params.data.branch;
    if (expandedBranches.has(branch)) {
        expandedBranches.delete(branch);
    } else {
        expandedBranches.add(branch);
    }
    params.api.onFilterChanged();
    params.api.refreshCells({ rowNodes: [params.node], force: true });
    return true;
}

// Expand/collapse extensions of focused branch cell by Enter or Space
function toggleBranchByKey(params) {
    if (['Enter', ' '].includes(params.event.key) && toggleBranch(params)) {
        params.event.preventDefault();
    }
}

// Show branch rows always, extension rows only below an expanded branch
function isRowExpanded(node) {
    return !node.data.extension || expandedBranches.has(node.data.branch);
}

// Keep branches in data order, sort extensions within their branch
function keepExtensionsBelowBranch(params) {
    const branchNodes = [];
    const extensionNodes = new Map();
    for (const node of params.nodes) {
        if (node.data.extension) {
            if (!extensionNodes.has(node.data.branch)) {
                extensionNodes.set(node.data.branch, []);
            }
            extensionNodes.get(node.data.branch).push(node);
        } else {
            branchNodes.push(node);
        }
    }
    params.nodes.length = 0;
    branchNodes.sort((a, b) => a.sourceRowIndex - b.sourceRowIndex);
    for (const node of branchNodes) {
        params.nodes.push(node, ...(extensionNodes.get(node.data.branch) ?? []));
    }
}
