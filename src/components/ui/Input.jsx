import React from 'react';

const Input = ({ value, onChange, placeholder = '', type = 'text', className = '', ...props }) => {
    return (
        <input
            type={type}
            value={value}
            onChange={onChange}
            placeholder={placeholder}
            className={`border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-primary ${className}`}
            {...props}
        />
    );
};

export default Input;
