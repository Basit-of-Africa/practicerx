import React, { useEffect, useRef } from 'react';
import Icon from './Icon';

const Modal = ({ isOpen, onClose, children, title = '' }) => {
    const containerRef = useRef(null);
    const dialogRef = useRef(null);
    const previouslyFocused = useRef(null);

    useEffect(() => {
        if ( ! isOpen ) return;

        previouslyFocused.current = document.activeElement;

        // Move focus into the dialog
        const dialog = dialogRef.current;
        if ( dialog ) {
            // find first focusable element
            const focusable = dialog.querySelectorAll('a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), [tabindex]:not([tabindex="-1"])');
            if ( focusable.length ) {
                focusable[0].focus();
            } else {
                dialog.setAttribute('tabindex', '-1');
                dialog.focus();
            }
        }

        // Make background inert / aria-hidden for screen readers
        const root = document.getElementById('practicerx-root');
        const siblings = [];
        if ( root ) {
            for (const child of Array.from(root.children)) {
                if ( child !== containerRef.current ) {
                    siblings.push(child);
                    try {
                        // apply inert if supported
                        if ('inert' in HTMLElement.prototype) {
                            child.inert = true;
                        } else {
                            child.setAttribute('aria-hidden', 'true');
                        }
                    } catch (err) {
                        try { child.setAttribute('aria-hidden', 'true'); } catch (e) {}
                    }
                }
            }
        }

        const focusSentinelBefore = document.createElement('div');
        const focusSentinelAfter = document.createElement('div');
        focusSentinelBefore.tabIndex = 0;
        focusSentinelAfter.tabIndex = 0;

        const onFocusBefore = () => {
            // focus last focusable in dialog
            const focusable = dialog.querySelectorAll('a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), [tabindex]:not([tabindex="-1"])');
            if ( focusable.length ) focusable[focusable.length - 1].focus();
        };
        const onFocusAfter = () => {
            const focusable = dialog.querySelectorAll('a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), [tabindex]:not([tabindex="-1"])');
            if ( focusable.length ) focusable[0].focus();
        };
        focusSentinelBefore.addEventListener('focus', onFocusBefore);
        focusSentinelAfter.addEventListener('focus', onFocusAfter);

        // insert sentinels around dialog
        if ( dialog && dialog.parentNode ) {
            dialog.parentNode.insertBefore(focusSentinelBefore, dialog);
            dialog.parentNode.insertBefore(focusSentinelAfter, dialog.nextSibling);
        }

        const onKey = (e) => {
            if ( e.key === 'Escape' ) {
                e.stopPropagation();
                onClose();
                return;
            }

            if ( e.key === 'Tab' ) {
                // trap focus inside dialog
                const focusable = dialog.querySelectorAll('a[href], area[href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), button:not([disabled]), [tabindex]:not([tabindex="-1"])');
                if ( focusable.length === 0 ) {
                    e.preventDefault();
                    return;
                }
                const first = focusable[0];
                const last = focusable[focusable.length - 1];

                if ( e.shiftKey && document.activeElement === first ) {
                    e.preventDefault();
                    last.focus();
                } else if ( ! e.shiftKey && document.activeElement === last ) {
                    e.preventDefault();
                    first.focus();
                }
            }
        };

        document.addEventListener('keydown', onKey, true);
        return () => {
            document.removeEventListener('keydown', onKey, true);
            // remove sentinels
            try {
                focusSentinelBefore.removeEventListener('focus', onFocusBefore);
                focusSentinelAfter.removeEventListener('focus', onFocusAfter);
                if ( focusSentinelBefore.parentNode ) focusSentinelBefore.parentNode.removeChild(focusSentinelBefore);
                if ( focusSentinelAfter.parentNode ) focusSentinelAfter.parentNode.removeChild(focusSentinelAfter);
            } catch (err) {}

            // restore background inert/aria-hidden
            for (const el of siblings) {
                try {
                    if ('inert' in HTMLElement.prototype) {
                        el.inert = false;
                    } else {
                        el.removeAttribute('aria-hidden');
                    }
                } catch (e) {
                    try { el.removeAttribute('aria-hidden'); } catch (ee) {}
                }
            }

            // restore focus
            try {
                if ( previouslyFocused.current && previouslyFocused.current.focus ) {
                    previouslyFocused.current.focus();
                }
            } catch (err) {
                // ignore
            }
        };
    }, [ isOpen, onClose ] );

    if ( ! isOpen ) return null;

    const titleId = title ? 'prx-modal-title' : undefined;
    const descId = title ? 'prx-modal-desc' : undefined;

    return (
        <div ref={containerRef} className="fixed inset-0 z-50 flex items-center justify-center" aria-hidden={!isOpen}>
            <div className="absolute inset-0 bg-black opacity-40 anim-fade-in" onClick={onClose} />
            <div
                ref={dialogRef}
                role="dialog"
                aria-modal="true"
                aria-labelledby={titleId}
                aria-describedby={descId}
                className="modal-content relative bg-white rounded-md shadow-lg max-w-xl w-full p-6 anim-scale-in focus-ring"
            >
                <button aria-label="Close dialog" className="modal-close" onClick={onClose}><Icon name="x" /></button>
                {title && <h3 id={titleId} className="text-lg font-semibold mb-3">{title}</h3>}
                <div id={descId} className="modal-body">{children}</div>
                <div className="mt-4 modal-actions">
                    <button aria-label="Close dialog" className="btn-block px-4 py-2 rounded bg-gray-100 touch-target" onClick={onClose}>Cancel</button>
                </div>
            </div>
        </div>
    );
};

export default Modal;
