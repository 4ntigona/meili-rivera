/**
 * Meili Rivera Interactivity Store
 */
import { store, getContext } from '@wordpress/interactivity';

export const { state, actions } = store('meiliRivera/search', {
    state: {
        // Initial state is populated by PHP via wp_interactivity_state
    },
    actions: {
        setFilter: (args) => {
            const event = args.event || (args.target ? args : null);
            const context = getContext();

            if (!event || !event.target) {
                return;
            }

            const listName = context.listName;
            const value = context.value;
            const isChecked = event.target.checked;

            const url = new URL(window.location.href);

            // Strip /page/N/ from path so filters always reset to page 1
            url.pathname = url.pathname.replace(/\/page\/\d+\/?$/, '/');

            let currentValues = url.searchParams.get(listName) ? url.searchParams.get(listName).split(',') : [];

            if (isChecked) {
                if (!currentValues.includes(value)) {
                    currentValues.push(value);
                }
            } else {
                currentValues = currentValues.filter(v => v !== value);
            }

            if (currentValues.length > 0) {
                url.searchParams.set(listName, currentValues.join(','));
            } else {
                url.searchParams.delete(listName);
            }

            // Reset all pagination parameters
            url.searchParams.delete('query-0-page');
            url.searchParams.delete('product-page');
            url.searchParams.delete('paged');

            window.location.assign(url.toString());
        }
    },
    callbacks: {
        init: () => {
            // Store ready
        }
    }
});
