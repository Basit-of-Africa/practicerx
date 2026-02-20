import React, { createContext, useContext, useState, useCallback, useEffect } from 'react';

const ToastContext = createContext(null);

let idCounter = 1;

export const useToast = () => {
    return useContext(ToastContext);
};

const ToastProvider = ({ children }) => {
    const [toasts, setToasts] = useState([]);

    const addToast = useCallback(({ title = '', description = '', variant = 'default', duration = 5000 }) => {
        const id = idCounter++;
        setToasts((t) => [...t, { id, title, description, variant }]);
        return id;
    }, []);

    const removeToast = useCallback((id) => {
        setToasts((t) => t.filter((x) => x.id !== id));
    }, []);

    useEffect(() => {
        const timers = toasts.map((toast) => {
            const timer = setTimeout(() => removeToast(toast.id), 6000);
            return () => clearTimeout(timer);
        });
        return () => timers.forEach((t) => t && t());
    }, [toasts, removeToast]);

    return (
        <ToastContext.Provider value={{ addToast, removeToast }}>
            {children}
            <div className="toast-container" aria-live="polite">
                {toasts.map((toast) => (
                    <div key={toast.id} className={`toast toast-${toast.variant} toast-enter`} role="status" onAnimationEnd={() => {}}>
                        {toast.title && <div className="toast-title">{toast.title}</div>}
                        {toast.description && <div className="toast-desc">{toast.description}</div>}
                        <button className="toast-close" onClick={() => removeToast(toast.id)} aria-label="Dismiss">✕</button>
                    </div>
                ))}
            </div>
        </ToastContext.Provider>
    );
};

export default ToastProvider;
