import React from 'react';

const Textarea = ({ value, onChange, placeholder = '', className = '', ...props }) => {
    return (
        <textarea
            value={value}
            onChange={onChange}
            placeholder={placeholder}
            className={`border rounded px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-primary ${className}`}
            {...props}
        />
    );
};

export default Textarea;
