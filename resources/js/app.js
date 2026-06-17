
import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const pageLoaderState = {
    bodyClass: 'page-loader-active',
    visibleClass: 'is-visible',
    element: null,
};

const defer = window.queueMicrotask
    ? window.queueMicrotask.bind(window)
    : (callback) => Promise.resolve().then(callback);

function getPageLoader() {
    if (pageLoaderState.element) {
        return pageLoaderState.element;
    }

    pageLoaderState.element = document.getElementById('page-loader');

    return pageLoaderState.element;
}

function showPageLoader() {
    const loader = getPageLoader();

    if (!loader) {
        return;
    }

    loader.hidden = false;
    loader.setAttribute('aria-hidden', 'false');
    loader.classList.add(pageLoaderState.visibleClass);
    document.body.classList.add(pageLoaderState.bodyClass);
}

function hidePageLoader() {
    const loader = getPageLoader();

    if (!loader) {
        return;
    }

    loader.classList.remove(pageLoaderState.visibleClass);
    loader.setAttribute('aria-hidden', 'true');
    loader.hidden = true;
    document.body.classList.remove(pageLoaderState.bodyClass);
}

function isModifiedNavigation(event) {
    return event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey;
}

function shouldIgnoreLink(link) {
    if (!link) {
        return true;
    }

    if (link.hasAttribute('download') || link.dataset.noLoader !== undefined) {
        return true;
    }

    const target = link.getAttribute('target');

    if (target && target.toLowerCase() !== '_self') {
        return true;
    }

    const href = (link.getAttribute('href') || '').trim();

    if (!href || href === '#' || href.startsWith('#')) {
        return true;
    }

    if (href.toLowerCase().startsWith('javascript:')) {
        return true;
    }

    let destination;

    try {
        destination = new URL(link.href, window.location.href);
    } catch {
        return true;
    }

    const current = new URL(window.location.href);

    if (destination.origin !== current.origin) {
        return true;
    }

    const isAnchorNavigation =
        destination.pathname === current.pathname &&
        destination.search === current.search &&
        destination.hash !== '';

    if (isAnchorNavigation || destination.href === current.href) {
        return true;
    }

    return false;
}

document.addEventListener('click', (event) => {
    if (event.defaultPrevented || isModifiedNavigation(event)) {
        return;
    }

    const link = event.target.closest('a[href]');

    if (shouldIgnoreLink(link)) {
        return;
    }

    defer(() => {
        if (!event.defaultPrevented) {
            showPageLoader();
        }
    });
});

window.addEventListener('load', hidePageLoader);
window.addEventListener('pageshow', hidePageLoader);
