<?php
// render.php
?>
<div data-wp-interactive="meiliRivera/search" class="meili-pagination" data-wp-bind--hidden="!state.hasResults"
    style="margin-top:20px; display:flex; gap:10px; justify-content:center;">
    <button data-wp-on--click="actions.navigate" data-wp-context='{ "page": 1 }'
        data-wp-bind--disabled="state.page === 1">
        &laquo; Primeira
    </button>

    <!-- Simple Previous/Next for now. Full numbering requires more complex loop logic in Interactivity API without internal PHP loops -->
    <button data-wp-on--click="actions.navigate" data-wp-context='{ "page": state.page - 1 }'
        data-wp-bind--disabled="state.page === 1">
        &lsaquo; Anterior
    </button>

    <span>
        Página <span data-wp-text="state.page"></span> de <span data-wp-text="state.totalPages"></span>
    </span>

    <button data-wp-on--click="actions.navigate" data-wp-context='{ "page": state.page + 1 }'
        data-wp-bind--disabled="state.page === state.totalPages">
        Próxima &rsaquo;
    </button>

    <button data-wp-on--click="actions.navigate" data-wp-context='{ "page": state.totalPages }'
        data-wp-bind--disabled="state.page === state.totalPages">
        Última &raquo;
    </button>
</div>