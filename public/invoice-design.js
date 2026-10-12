import { initializeItems } from './invoice/scripts/items.js';
import { initializeCustomFields, initializeCustomerControls } from './invoice/scripts/fields.js';
import { initializeTabsAndEditor } from './invoice/scripts/tabs.js';
import { initializePreview } from './invoice/scripts/preview.js';
import { initializeDraft } from './invoice/scripts/draft.js';
import { populateSalesPeople } from './staff-data.js';

const items = initializeItems();
const customFields = initializeCustomFields();

initializeCustomerControls();
populateSalesPeople(document.getElementById('sales-person'));
initializeTabsAndEditor();
initializePreview(items);
initializeDraft(items, customFields);
