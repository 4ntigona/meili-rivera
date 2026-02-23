/**
 * Meili Rivera Interactivity Store
 */
import { store, getContext } from '@wordpress/interactivity';
import { actions as routerActions } from '@wordpress/interactivity-router';

export const { state, actions, callbacks } = store('meiliRivera/search', {
    state: {
        // Initial state is populated by PHP via wp_interactivity_state
        searchTimeout: null
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

            routerActions.navigate(url.toString());
        },
        updateSearchQuery: (event) => {
            const context = getContext();
            // Normalize search query: lowercase and remove accents
            context.searchQuery = event.target.value
                .toLowerCase()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "");
        },
        submitSearch: (event) => {
            event.preventDefault();
            const form = event.target;
            const input = form.querySelector('input[name="s"]');
            if (!input) return;

            // Use window.location.href to preserve existing query parameters (like filters)
            let url = new URL(window.location.href);
            
            // If we are not on the shop page, we need to navigate there
            if (!url.pathname.includes('/loja')) {
                url = new URL(form.action);
            }
            
            // Strip /page/N/ from path so search always resets to page 1
            url.pathname = url.pathname.replace(/\/page\/\d+\/?$/, '/');

            if (input.value.trim() !== '') {
                url.searchParams.set('s', input.value);
            } else {
                url.searchParams.delete('s');
            }
            
            // Reset pagination
            url.searchParams.delete('query-0-page');
            url.searchParams.delete('product-page');
            url.searchParams.delete('paged');

            routerActions.navigate(url.toString());
        },
        instantSearch: (event) => {
            const context = getContext();
            if (!context.isInstant) return;

            let url = new URL(window.location.href);
            
            // Only perform instant search if we are already on the shop page
            if (!url.pathname.includes('/loja')) {
                return;
            }

            const value = event.target.value;
            
            // Clear previous timeout
            if (state.searchTimeout) {
                clearTimeout(state.searchTimeout);
            }

            // Debounce the search
            state.searchTimeout = setTimeout(() => {
                // Strip /page/N/ from path so search always resets to page 1
                url.pathname = url.pathname.replace(/\/page\/\d+\/?$/, '/');

                if (value.trim() !== '') {
                    url.searchParams.set('s', value);
                } else {
                    url.searchParams.delete('s');
                }

                // Reset pagination
                url.searchParams.delete('query-0-page');
                url.searchParams.delete('product-page');
                url.searchParams.delete('paged');

                routerActions.navigate(url.toString());
            }, 500); // 500ms debounce
        }
    },
    callbacks: {
        init: () => {
            // Store ready
        },
        getDisplay: () => {
            const context = getContext();
            if (!context.searchQuery) {
                return '';
            }
            if (context.itemName && context.itemName.includes(context.searchQuery)) {
                return '';
            }
            return 'none';
        }
    }
});
