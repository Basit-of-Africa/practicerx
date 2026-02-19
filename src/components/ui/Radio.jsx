import React from 'react';

const Radio = ({ name, value, checked, onChange, label = '', className = '', ...props }) => {
    return (
        <label className={`inline-flex items-center space-x-2 ${className}`}>
            <input type="radio" name={name} value={value} checked={checked} onChange={onChange} {...props} />
            {label && <span>{label}</span>}
        </label>
    );
};

export default Radio;
