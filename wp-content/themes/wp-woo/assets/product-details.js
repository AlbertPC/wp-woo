(() => {
	const details = document.querySelector('.bf-product .wp-block-woocommerce-product-details');
	const tabs = details?.querySelector('.wc-tabs');
	const description = details?.querySelector('#tab-description');
	const descriptionTab = tabs?.querySelector('.description_tab');
	if (!descriptionTab || !description) {
		return;
	}

	const { readMore, readLess } = bitformaProductDetails;
	const previewObserver = new ResizeObserver((entries) => {
		for (const { target } of entries) {
			const viewport = target.parentElement;
			if (viewport.classList.contains('is-collapsed') && target.offsetWidth) {
				viewport.nextElementSibling.hidden = target.scrollHeight <= viewport.clientHeight;
			}
		}
	});

	function addPreview(panel, heading) {
		const viewport = document.createElement('div');
		viewport.id = `${panel.id}-preview`;
		viewport.className = 'bf-product-preview is-collapsed';
		const content = document.createElement('div');
		content.className = 'bf-product-preview__content';
		const button = document.createElement('button');
		button.type = 'button';
		button.className = 'bf-product-preview-toggle';
		button.setAttribute('aria-controls', viewport.id);
		button.hidden = true;

		function setExpanded(expanded) {
			viewport.classList.toggle('is-collapsed', !expanded);
			button.setAttribute('aria-expanded', String(expanded));
			button.textContent = expanded ? readLess : readMore;
		}

		for (const node of [...panel.childNodes]) {
			if (node !== heading) {
				content.append(node);
			}
		}
		viewport.append(content);
		panel.append(viewport, button);
		setExpanded(false);
		button.addEventListener('click', () => setExpanded(viewport.classList.contains('is-collapsed')));
		viewport.addEventListener('focusin', ({ target }) => {
			if (viewport.classList.contains('is-collapsed') && target.getBoundingClientRect().bottom > viewport.getBoundingClientRect().bottom) {
				setExpanded(true);
			}
		});
		previewObserver.observe(content);
	}

	const headings = [...description.querySelectorAll('h3')].filter((heading) => heading.textContent.trim());
	let descriptionHeading = description.querySelector(':scope > h2:first-child');
	const descriptionTitle = descriptionTab.textContent.trim();
	const sections = [];
	const range = document.createRange();

	// Work from the end so extraction leaves the next section boundary in the description.
	for (let index = headings.length - 1; index >= 0; index--) {
		range.selectNodeContents(description);
		range.setStartAfter(headings[index]);
		if (headings[index + 1]) {
			range.setEndBefore(headings[index + 1]);
		}
		sections.unshift(range.extractContents());
	}

	const nextTab = descriptionTab.nextElementSibling;
	const nextPanel = description.nextElementSibling;
	headings.forEach((heading, index) => {
		const title = heading.textContent.trim();
		if (index === 0 && title.toLocaleLowerCase() === descriptionTitle.toLocaleLowerCase()) {
			// Keep one Descripción tab when the product starts with an identically named section.
			if (!descriptionHeading) {
				description.prepend(heading);
				descriptionHeading = heading;
			} else {
				if (heading.id && !descriptionHeading.id) {
					descriptionHeading.id = heading.id;
				}
				heading.remove();
			}
			description.append(sections[index]);
			return;
		}

		const key = `bf-section-${index + 1}`;
		const tab = document.createElement('li');
		tab.id = `tab-title-${key}`;
		tab.className = `${key}_tab`;
		tab.setAttribute('role', 'presentation');
		const link = document.createElement('a');
		link.href = `#tab-${key}`;
		link.textContent = title;
		link.setAttribute('role', 'tab');
		link.setAttribute('aria-controls', `tab-${key}`);
		tab.append(link);
		tabs.insertBefore(tab, nextTab);

		const panel = document.createElement('div');
		panel.id = `tab-${key}`;
		panel.className = `woocommerce-Tabs-panel woocommerce-Tabs-panel--${key} panel entry-content wc-tab`;
		panel.setAttribute('role', 'tabpanel');
		panel.setAttribute('aria-labelledby', tab.id);
		panel.append(heading, sections[index]);
		description.parentElement.insertBefore(panel, nextPanel);
		addPreview(panel, heading);
	});

	addPreview(description, descriptionHeading);

	const additionalInformation = details.querySelector('#tab-additional_information');
	if (additionalInformation) {
		addPreview(additionalInformation, additionalInformation.querySelector(':scope > h2:first-child'));
	}
})();
