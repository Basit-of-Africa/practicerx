import React from 'react';

const Button = ({ children, variant = 'primary', className = '', type = 'button', ...props }) => {
    const variants = {
        primary: 'btn btn-primary',
        ghost: 'btn btn-ghost',
    };

    return (
        <button type={type} className={`${variants[variant] || variants.primary} ${className}`} {...props}>
            {children}
        </button>
    );
};

export default Button;
