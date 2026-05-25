import './bootstrap';
import 'sweetalert2/dist/sweetalert2.min.css';

import $ from 'jquery';

window.$ = $;
window.jQuery = $;
let itcityThreeLoader = null;

window.loadITCityThree = async () => {
	if (window.THREE && window.ITCityOrbitControls) {
		return {
			THREE: window.THREE,
			OrbitControls: window.ITCityOrbitControls,
		};
	}

	if (!itcityThreeLoader) {
		itcityThreeLoader = Promise.all([
			import('three'),
			import('three/examples/jsm/controls/OrbitControls.js'),
		]).then(([threeModule, controlsModule]) => {
			window.THREE = threeModule;
			window.ITCityOrbitControls = controlsModule.OrbitControls;
			return {
				THREE: window.THREE,
				OrbitControls: window.ITCityOrbitControls,
			};
		});
	}

	return itcityThreeLoader;
};

const resolveSwal = () => window.Swal ?? null;

const itcitySwalClass = {
	popup: 'itcity-swal-popup',
	title: 'itcity-swal-title',
	htmlContainer: 'itcity-swal-html',
	actions: 'itcity-swal-actions',
	timerProgressBar: 'itcity-swal-progress',
	confirmButton: 'btn btn-primary',
	cancelButton: 'btn btn-outline-secondary',
};

const mergeSwalClass = (extra = {}) => ({
	...itcitySwalClass,
	...extra,
});

window.itcityAlert = (options = {}) => {
	const swal = resolveSwal();
	const title = options.title ?? '';
	const text = options.text ?? title;

	if (!swal) {
		window.alert(text);
		return Promise.resolve();
	}

	const isToast = !!options.toast;

	return swal.fire({
		title,
		text,
		html: options.html,
		icon: options.icon ?? 'info',
		toast: isToast,
		position: isToast ? (options.position ?? 'top-end') : 'center',
		timer: options.timer ?? (isToast ? 2600 : undefined),
		timerProgressBar: isToast,
		showConfirmButton: options.showConfirmButton ?? !isToast,
		confirmButtonText: options.confirmButtonText ?? 'Aceptar',
		buttonsStyling: false,
		customClass: mergeSwalClass({
			popup: isToast ? `${itcitySwalClass.popup} itcity-swal-toast` : itcitySwalClass.popup,
			confirmButton: options.confirmButtonClass ?? 'btn btn-primary',
			...options.customClass,
		}),
	});
};

window.itcityConfirm = async (options = {}) => {
	const swal = resolveSwal();
	const text = options.text ?? options.title ?? '¿Deseas continuar?';

	if (!swal) {
		return window.confirm(text);
	}

	const result = await swal.fire({
		title: options.title ?? 'Confirmar acción',
		text,
		icon: options.icon ?? 'warning',
		showCancelButton: true,
		reverseButtons: true,
		focusCancel: true,
		confirmButtonText: options.confirmButtonText ?? 'Sí, continuar',
		cancelButtonText: options.cancelButtonText ?? 'Cancelar',
		buttonsStyling: false,
		customClass: mergeSwalClass({
			confirmButton: options.confirmButtonClass ?? 'btn btn-danger ms-2',
			cancelButton: options.cancelButtonClass ?? 'btn btn-outline-secondary me-2',
			...options.customClass,
		}),
	});

	return result.isConfirmed;
};

document.addEventListener('submit', async (event) => {
	const form = event.target;
	if (!(form instanceof HTMLFormElement)) return;

	if (form.dataset.swalConfirmed === 'true') {
		delete form.dataset.swalConfirmed;
		return;
	}

	const submitter = event.submitter instanceof HTMLElement ? event.submitter : null;
	const source = submitter?.dataset.confirm ? submitter : (form.dataset.confirm ? form : null);
	if (!source) return;

	event.preventDefault();

	const confirmed = await window.itcityConfirm({
		title: source.dataset.confirmTitle ?? 'Confirmar acción',
		text: source.dataset.confirm,
		icon: source.dataset.confirmIcon ?? 'warning',
		confirmButtonText: source.dataset.confirmButtonText ?? 'Sí, continuar',
		cancelButtonText: source.dataset.cancelButtonText ?? 'Cancelar',
		confirmButtonClass: source.dataset.confirmButtonClass,
		cancelButtonClass: source.dataset.cancelButtonClass,
	});

	if (!confirmed) return;

	form.dataset.swalConfirmed = 'true';
	HTMLFormElement.prototype.submit.call(form);
}, true);

const parseBooleanFlag = (value, fallback = false) => {
	if (value === undefined) return fallback;
	return value === '1' || value === 'true' || value === 'yes';
};

document.addEventListener('DOMContentLoaded', async () => {
	const flashItems = Array.from(document.querySelectorAll('[data-swal-flash]'));
	if (!flashItems.length) return;

	for (const item of flashItems) {
		const title = item.dataset.swalTitle ?? '';
		const text = item.dataset.swalText ?? item.textContent?.trim() ?? '';
		const html = item.dataset.swalHtml ?? null;
		const icon = item.dataset.swalIcon ?? 'info';
		const toast = parseBooleanFlag(item.dataset.swalToast, true);
		const showConfirmButton = parseBooleanFlag(item.dataset.swalShowConfirmButton, !toast);
		const timer = item.dataset.swalTimer ? Number(item.dataset.swalTimer) : (toast ? 2600 : undefined);

		item.style.display = 'none';

		await window.itcityAlert({
			title,
			text,
			html,
			icon,
			toast,
			timer,
			showConfirmButton,
			confirmButtonText: item.dataset.swalConfirmButtonText ?? 'Aceptar',
			position: item.dataset.swalPosition ?? 'top-end',
		});
	}
});
