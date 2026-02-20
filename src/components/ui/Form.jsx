import React from 'react';

const Form = ({ children, onSubmit, className = '' }) => {
    const handle = (e) => {
        e.preventDefault();
        if (!onSubmit) return;

        const fd = new FormData(e.target);
        const obj = {};
        for (const [k, v] of fd.entries()) {
            // handle multiple values
            if (Object.prototype.hasOwnProperty.call(obj, k)) {
                if (Array.isArray(obj[k])) obj[k].push(v); else obj[k] = [obj[k], v];
            } else {
                obj[k] = v;
            }
        }

        onSubmit(obj);
    };

    return (
        <form onSubmit={handle} className={className}>
            {children}
        </form>
    );
};

export default Form;
