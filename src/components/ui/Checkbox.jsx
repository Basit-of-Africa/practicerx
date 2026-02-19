import React from 'react';

const Checkbox = ({ checked, onChange, label = '', className = '', ...props }) => {
    return (
        <label className={`inline-flex items-center space-x-2 ${className}`}>
            <input type="checkbox" checked={checked} onChange={onChange} {...props} />
            {label && <span>{label}</span>}
        </label>
    );
};

export default Checkbox;
