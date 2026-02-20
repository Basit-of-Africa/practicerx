import React from 'react';

const Select = ({ value, onChange, options = [], className = '', ...props }) => {
    return (
        <select
            value={value}
            onChange={onChange}
            className={`input ${className}`}
            {...props}
        >
            {options.map((o) => (
                <option key={o.value} value={o.value}>{o.label}</option>
            ))}
        </select>
    );
};

export default Select;
