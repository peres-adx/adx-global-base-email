/**
 * Architect and developed by Rafael Peres
 * Core Infrastructure Services - ESM
 * Architecture: Clean Code, Reactive UI & High Performance
 * Standards: SOLID, DRY, Flat/Linear Code, Guard Clauses, No-Elses, Single Responsibility, Separation of Concerns, ESM and Custom Events
 */

import { DatabaseUiController }		from './modules/DatabaseUiController.js';
import { AutoAuthService }				from './modules/AutoAuthService.js';
import { DevOpsService }					from './modules/DevOpsService.js';
import { HexMapper }							from './modules/HexMapper.js';
import { SetupPasswordEngine }		from './modules/SetupPasswordEngine.js';

document.addEventListener('DOMContentLoaded', () => {

	SetupPasswordEngine.checkRouteAndIntercept();

	const urlParams = new URLSearchParams(window.location.search);
	if (urlParams.get('token')) return; 

	if (window.location.hash) history.replaceState("", document.title, window.location.pathname + window.location.search);

	const devOps					= new DevOpsService();
	const headerObserver	= new MutationObserver((_, obs) => {

		const dbSelect = document.querySelector('.swagger-ui select[data-variable="database"]');

		if (!dbSelect) return;

		dbSelect.addEventListener('change', (e) => DatabaseUiController.toggleWorkspace(e.target.value));
		obs.disconnect();

	});

	headerObserver.observe(document.body, { childList: true, subtree: true });

	document.getElementById('binInput')?.addEventListener('input', (e) => {

		const output = document.getElementById('hexOutput');
		if (!output) return;
  
		const inputVal		= e.target.value.trim();
		output.innerText	= inputVal ? HexMapper.transform(inputVal) : "INFORME O CÓDIGO BIN NO CAMPO AO LADO";

	});

	document.getElementById('btnRunAudit')?.addEventListener('click', () => devOps.runAudit());

	document.addEventListener('click', (e) => {

		const { target }	= e;
		const targetText	= target.innerText?.toUpperCase() || '';

		const routes = [
		{
			match:		() => target.closest('.execute') && target.closest('#operations-Auth-post_login'),
			action:		() => AutoAuthService.captureTokenAndLogin()
		},
		{
			match:		() => {
				const isCustomLogout			= target.id === 'core-btn-logout';
				const isNativeModalLogout	= target.classList.contains('auth') && target.classList.contains('modal-btn') && targetText === 'LOGOUT';
				return isCustomLogout || isNativeModalLogout;
			},
			action:		() => {
				AutoAuthService.purgeAuthorization(); 
				DatabaseUiController.terminateSession();
			}
		},
		{
			match:		() => target.classList.contains('btn-done') || targetText === 'CLOSE' || target.closest('.close-modal'),
			action: 	() => {
				const isAuthorized = document.querySelector('.auth-wrapper .authorize.authorized');
				if (!isAuthorized) return;

				DatabaseUiController.lock();
				AutoAuthService.revealAuthorizeButton();
			}
		}];

		const activeRoute = routes.find(route => route.match());
		activeRoute?.action();

	});

});
