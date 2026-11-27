(() => {
	const bar = document.querySelector('.bf-product-bar');
	const button = document.querySelector('.bf-product .wp-block-woocommerce-add-to-cart-form form.cart .single_add_to_cart_button');

	if (!bar || !button) {
		return;
	}

	const form = button.form;
	const barButton = bar.querySelector('.bf-product-bar__button');
	const options = bar.querySelector('.bf-product-bar__options');
	const price = bar.querySelector('.bf-product-bar__price');
	const basePrice = price.innerHTML;
	const status = bar.querySelector('.bf-product-bar__status');
	const attributes = Array.from(form.querySelectorAll('.variations select'), (original) => {
		const label = document.createElement('label');
		const caption = document.createElement('span');
		const select = document.createElement('select');
		caption.textContent = original.labels[0]?.textContent.trim() || original.name;
		label.append(caption, select);
		options.append(label);

		select.addEventListener('change', () => {
			original.value = select.value;
			original.dispatchEvent(new Event('change', { bubbles: true }));
		});
		return { original, select };
	});

	function syncOptions() {
		for (const { original, select } of attributes) {
			if (select.innerHTML !== original.innerHTML) {
				select.innerHTML = original.innerHTML;
			}
			select.value = original.value;
			select.disabled = original.disabled;
		}
	}

	function setPrice(html) {
		if (price.innerHTML !== html) {
			price.innerHTML = html;
		}
	}

	function setStatus(message) {
		status.textContent = message;
		status.hidden = !message;
	}

	if (attributes.length) {
		options.hidden = false;
		syncOptions();

		const variationId = form.querySelector('.variation_id');
		const variationForm = jQuery(form);
		const messages = window.wc_add_to_cart_variation_params;

		variationForm.on('woocommerce_update_variation_values wc_variation_form', syncOptions);
		variationForm.on('woocommerce_variation_has_changed hide_variation', () => {
			syncOptions();
			barButton.disabled = true;
			setPrice(basePrice);
			const noMatch = attributes.every(({ original }) => original.value) && form.querySelector('.wc-no-matching-variations');
			setStatus(noMatch ? messages.i18n_no_matching_variations_text : '');
		});
		variationForm.on('show_variation', (event, variation, purchasable) => {
			// WooCommerce updates the variation display asynchronously.
			if (variationId.value !== String(variation.variation_id)) {
				return;
			}
			setPrice(variation.price_html || basePrice);
			barButton.disabled = !purchasable;
			setStatus(purchasable ? '' : messages.i18n_unavailable_text);
		});
	} else {
		barButton.disabled = button.disabled;
	}

	barButton.addEventListener('click', () => button.click());

	function updateVisibility() {
		const hidden = button.getBoundingClientRect().bottom > 0;
		if (bar.hidden !== hidden) {
			bar.hidden = hidden;
		}
	}

	window.addEventListener('scroll', updateVisibility, { passive: true });
	window.addEventListener('resize', updateVisibility);
	window.addEventListener('pageshow', updateVisibility);
	new ResizeObserver(updateVisibility).observe(form.closest('.bf-product'));
	new ResizeObserver(() => {
		document.documentElement.style.setProperty('--bf-product-bar-height', `${bar.offsetHeight}px`);
	}).observe(bar);
	updateVisibility();
})();
