/**
 * Meili Rivera Store
 * 
 * Uses WordPress Interactivity API to manage search state.
 * Performs client-side Meilisearch queries and updates the view.
 */
import { store, getContext } from '@wordpress/interactivity';
import { MeiliSearch } from 'meilisearch';

const { host, publicKey, indexName } = window.MeiliBlockData || {
    host: 'http://127.0.0.1:7700',
    publicKey: '',
    indexName: 'wordpress_content'
};

const client = new MeiliSearch({
    host: host,
    apiKey: publicKey,
});

const index = client.index(indexName);

store('meiliRivera/search', {
    state: {
        query: '',
        facets: {}, // { 'product_cat': ['Books'], 'pa_author': ['John Doe'] }
        results: [],
        totalHits: 0,
        totalPages: 0,
        page: 1,
        limit: 12,
        processingTimeMs: 0,
        isLoading: false,
        error: null,
        facetDistribution: {},

        get hasResults() {
            const state = store('meiliRivera/search').state;
            return state.results.length > 0;
        },

        get paginationPages() {
            const state = store('meiliRivera/search').state;
            const pages = [];
            for (let i = 1; i <= state.totalPages; i++) {
                pages.push(i);
            }
            return pages;
        }
    },
    actions: {
        setQuery: ({ ref }) => {
            const state = store('meiliRivera/search').state;
            // Debounce could be handled here or in the event listener, 
            // for simplicity in this example we assume onInput triggers this.
            state.query = ref.value;
            state.page = 1; // Reset page on new search
            const { actions } = store('meiliRivera/search');
            actions.search();
        },

        setFilter: ({ context, event }) => {
            // Context passed from the checkbox/element
            const list = context.listName; // e.g., 'product_cat'
            const value = context.value;   // e.g., 'Books'
            const checked = event.target.checked;

            const state = store('meiliRivera/search').state;

            if (!state.facets[list]) {
                state.facets[list] = [];
            }

            if (checked) {
                if (!state.facets[list].includes(value)) {
                    state.facets[list].push(value);
                }
            } else {
                state.facets[list] = state.facets[list].filter(item => item !== value);
                if (state.facets[list].length === 0) {
                    delete state.facets[list];
                }
            }

            state.page = 1;
            const { actions } = store('meiliRivera/search');
            actions.search();
        },

        navigate: ({ context }) => {
            const page = context.page;
            const state = store('meiliRivera/search').state;
            if (page === state.page) return;

            state.page = page;
            const { actions } = store('meiliRivera/search');
            actions.search();
        },

        search: async () => {
            const state = store('meiliRivera/search').state;
            state.isLoading = true;
            state.error = null;

            try {
                // Build Filter String
                // Facets are ANDed between lists, ORed within lists
                // (cat=A OR cat=B) AND (auth=X OR auth=Y)
                const filterArray = [];
                Object.keys(state.facets).forEach(key => {
                    const values = state.facets[key];
                    if (values.length > 0) {
                        const OR_group = values.map(v => `${key} = "${v}"`).join(' OR ');
                        filterArray.push(`(${OR_group})`);
                    }
                });
                const filterString = filterArray.join(' AND ');

                const searchParams = {
                    q: state.query,
                    limit: state.limit,
                    offset: (state.page - 1) * state.limit,
                    filter: filterString,
                    facets: ['*'] // Request all facets or specific indexed ones
                };

                const search = await index.search(state.query, searchParams);

                state.results = search.hits;
                state.totalHits = search.estimatedTotalHits;
                state.totalPages = Math.ceil(search.estimatedTotalHits / state.limit);
                state.processingTimeMs = search.processingTimeMs;
                state.facetDistribution = search.facetDistribution || {};

            } catch (err) {
                console.error(err);
                state.error = 'Ocorreu um erro na busca.';
            } finally {
                state.isLoading = false;
            }
        },

        init: () => {
            // Initial search on load
            store('meiliRivera/search').actions.search();
        }
    },
    callbacks: {
        logResults: () => {
            const state = store('meiliRivera/search').state;
            console.log('Results Updated:', state.results);
        }
    }
});
