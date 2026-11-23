/*!
 * Data grid for status page at https://localize.typo3.org/
 */
/*!
 * AG Grid community v36.2.0 by AG Grid Ltd. - https://www.ag-grid.com
 * License - https://www.ag-grid.com/eula/AG-Grid-Community-License.html (MIT License)
 */

import { loadGrid } from './status/grid.js';
import { resolveView } from './status/views/index.js';

// Init grid
loadGrid(resolveView());
