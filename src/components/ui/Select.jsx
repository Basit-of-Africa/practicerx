import React from 'react';

const Select = ({ value, onChange, options = [], className = '', ...props }) => {
    return (
        <select
            value={value}
            onChange={onChange}
            className={`border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-primary ${className}`}
            {...props}
        >
            {options.map((o) => (
                <option key={o.value} value={o.value}>{o.label}</option>
            ))}
        </select>
    );
};

export default Select;
