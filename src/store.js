/**
 * Meili Rivera Interactivity Store
 */
import { store, getContext } from '@wordpress/interactivity';

console.log("[Meili Rivera] Store Module Loaded");

export const { state, actions } = store('meiliRivera/search', {
    state: {
        // Initial state is populated by PHP via wp_interactivity_state
    },
    actions: {
        setFilter: (args) => {
            // Robust event detection: in some versions/directives, args IS the event object.
            // In others, it's an object containing { event, state, context }.
            const event = args.event || (args.target ? args : null);
            const context = getContext();

            if (!event || !event.target) {
                console.error("[Meili Rivera] Could not resolve event or target", { args, context });
                return;
            }

            const listName = context.listName;
            const value = context.value;
            const isChecked = event.target.checked;

            console.log(`[Meili Rivera] Filter Change: ${listName}=${value} (Checked: ${isChecked})`);

            const url = new URL(window.location.href);

            // Strip /page/N/ from the path to reset pagination when a filter changes.
            // e.g. /loja/page/3/ becomes /loja/
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

            // Reset all known pagination params
            url.searchParams.delete('query-0-page');
            url.searchParams.delete('product-page');
            url.searchParams.delete('paged');

            const nextUrl = url.toString();
            console.log("[Meili Rivera] Navigating to:", nextUrl);

            window.location.assign(nextUrl);
        }
    },
    callbacks: {
        init: () => {
            console.log("[Meili Rivera] Store Initialized", state.config);
        }
    }
});
