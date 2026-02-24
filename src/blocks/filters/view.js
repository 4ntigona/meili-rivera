/**
 * Filters Block View
 */
import { actions } from '../../store';

const navigateWithFilter = (input) => {
	const listName = input.dataset.listName || input.name;
	const value = input.dataset.filterValue || input.value;

	if (!listName || !value) {
		return;
	}

	const url = new URL(window.location.href);
	url.pathname = url.pathname.replace(/\/page\/\d+\/?$/, '/');

	let currentValues = url.searchParams.get(listName)
		? url.searchParams.get(listName).split(',').filter(Boolean)
		: [];

	if (input.checked) {
		if (!currentValues.includes(value)) {
			currentValues.push(value);
		}
	} else {
		currentValues = currentValues.filter((item) => item !== value);
	}

	if (currentValues.length > 0) {
		url.searchParams.set(listName, currentValues.join(','));
	} else {
		url.searchParams.delete(listName);
	}

	url.searchParams.delete('query-0-page');
	url.searchParams.delete('product-page');
	url.searchParams.delete('paged');

	window.location.assign(url.toString());
};

document.addEventListener('change', (event) => {
	const input = event.target;

	if (!(input instanceof HTMLInputElement)) {
		return;
	}

	if (input.type !== 'checkbox' || !input.dataset.listName) {
		return;
	}

	if (window.__meiliFilterNavigating) {
		return;
	}

	window.__meiliFilterNavigating = true;
	navigateWithFilter(input);
});
