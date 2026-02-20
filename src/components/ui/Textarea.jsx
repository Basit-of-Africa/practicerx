import React from 'react';

const Textarea = ({ value, onChange, placeholder = '', className = '', ...props }) => {
    return (
        <textarea
            value={value}
            onChange={onChange}
            placeholder={placeholder}
            className={`textarea ${className}`}
            {...props}
        />
    );
};

export default Textarea;
